<?php

require_once('inc/session.php');
require_once('inc/db.php');
require_once('inc/rfc822.php');
require_once('inc/template.php');
require_once('inc/password.php');
require_once('inc/session.php');
require_once('inc/form.php');
require_once('inc/Validate.php');
require_once('inc/gitolite-conf.php');
require_once('inc/Router.php');

Session::PrivateZone();

$data = array('errors' => array(), 'msg' => '');

$user = Session::User();
if ($user == null) {
  $user = User::MakeEmpty();
  $user->OpenID = Session::Id();
}

$data['user'] = $user;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if ($user->ID)  mod_user($data);
  else  {
    new_user($data);
    Router::To('myrepos.php');
  }
}

if ($user->Username == '') {
  $data['msg'] =
"Veuillez s'il vous plaît entrez les informations nécessaires de manière à ce que nous puissions
 créer votre compte et vous pourrez ensuite poursuivre avec son utilidation.
 <br /><br />
 Entrez une addresse @[...]polymtl.ca pour jouïr de certaines fonctionnalitée avancées.";
}

echo render_template('account', $data);

//----------- Helpers ----------------------

function mod_user(&$data) {
  $username = get_post('username');
  $email = get_post('email');

  $err = is_data_valid($username, $email);
  if ($err != null) {
    $data['errors'][] = $err;
    return;
  }

  // Si nous avons confirmer l'utilisateur,
  // il ne peut plus changer son courriel.
  if ($data['user']->IsStudent && $data['user']->Email != $email) {
    $data['errors'][] = "Vous ne pouvez plus changer votre addresse courriel une fois confirmée.";
    return;
  }

  $data['user']->Name = get_post('name');
  $data['user']->Email = $email;
  $data['user']->Username = $username;
  $data['user']->PubKey = get_post('pubkey');

  try {
    $db = db_connect();
    $data['user']->Save($db);

    // Send verification
    if (Pattern::MatchesPoly($email) && !$data['user']->IsStudent) {
      $validation = Validate::MakeWithNoUser();
      $err = SendValidationMail($data['user'], $validation->Code);
      if ($err != null) {
        $data['errors'][] = $err;
        db_close($db);
        return;
      }
      $validation->Save($db, $data['user']);
    }

    db_close($db);
  } catch (MySQLException $ex) {
    $data['errors'][] = $err;
  }
}

function new_user(&$data) {
  $username = get_post('username');
  $email = get_post('email');

  $err = is_data_valid($username, $email);
  if ($err != null) {
    $data['errors'][] = $err;
    return;
  }

  try {
    $db = db_connect();

    $err = is_username_used($db, $username);
    if ($err != null) {
      $data['errors'][] = $err;
      db_close($db);
      return;
    }

    // Save User
    $user = User::MakeEmpty();
    $err = $user->Insert($db, Session::Id(), get_post('name'), $email, get_post('pubkey'), $username);
    $data['user'] = $user;

    // Send verification
    if (Pattern::MatchesPoly($email)) {
      $validation = Validate::MakeWithNoUser();
      $err = SendValidationMail($user, $validation->Code);
      if ($err != null) {
        $data['errors'][] = $err;
        db_close($db);
        return;
      }
      $validation->Save($db, $user);
    }

    db_close($db);
  } catch (MySQLException $ex) {
    $data['errors'][] = $err;
  }
}

function is_data_valid($username, $email) {
  if (!Pattern::MatchesUser($username))
    return "Votre nom d'utilisateur ne respecte pas le bon format.";
  if (!is_valid_email_address($email))
    return "Votre addresse courriel n'est pas valide selon RFC5322.";
  return null;
}

function is_username_used($db, $username) {
  $user = null;
  $err = null;
  try {
    $user = User::ByName($db, $username);
  } catch (MySQLException $ex) {
    $err = $ex;
  }
  if ($err)  return $err;
  if ($user != null)  return "Le nom d'utilisateur que vous avez choisit existe déjà.";
  return null;
}

function SendValidationMail(User $user, $code) {
// subject
  $subject = 'Courriel de vérification pour votre compte Git au step.';

// message
  $message = '
Bonjour '.$user->Name.',

Ce courriel vous est envoyé puisque vous avez demandé une confirmation
d\'addresse courriel pour la création d\'un référentiel Git sur les
serveurs du Step.

Veuillez naviguer vers le lien suivant pour finalisez la validation :
              http://localhost/validator.php?code='.$code.'
';

// To send HTML mail, the Content-type header must be set
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

// Additional headers
  $headers .= 'To: '.$user->Name.' <'.$user->Email.'>' . "\r\n";
  $headers .= 'From: Service Git du Step <gitadmin@step.com>' . "\r\n";

// Mail it
  $ok = mail($user->Email, $subject, $message, $headers);
  if (!$ok)  return "Problème dans l'envoie du email. Veuillez communiquer avec le STEP.";
  return null;
}
