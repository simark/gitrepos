<?php

require_once('inc/session.php');
require_once('inc/db.php');
require_once('inc/template.php');
require_once('inc/password.php');
require_once('inc/session.php');
require_once('inc/form.php');
require_once('inc/Validate.php');
require_once('inc/gitolite-conf.php');

Session::PrivateZone();

$data = array('user' => Session::User(), 'errors' => array(), 'msg' => '', 'email' => false);

if ($_SERVER['REQUEST_METHOD'] == 'POST')  Modify($data);

if (!$data['user']->NotStudent && !$data['user']->IsStudent) {
  $data['msg'] =$data['email']
    ? "Un courriel de vérification a été envoyé à l'addresse que vous avez fournit."
    : "Il semble que votre compte n'aie pas été vérifié comme étant celui d'un étudiant de Polytechnique.
    Le service de référentiel Git offert par le Step est principalement orienté pour les étudiants.
    Vous pouvez tout de même y accéder mais vous ne pourrez pas créé de nouveau référentiel.
    Pour valider votre compte, assurez vous que vous avez utilisé votre addresse courriel de Poly et
    appuyer sur <b>valider</b>.";
}

echo render_template('account', $data);

//----------- Helpers ----------------------

function Save(&$data) {
  $db = db_connect();
  $data['user']->Save($db, get_post('username'), get_post('name'), get_post('email'), get_post('pubkey'));
  db_close($db);
}

function Validate(&$data) {
  $ok = SendValidationMail($data['user']->Name, $data['user']->Email, "lolsaure");
  if (!$ok) {
    $data['errors'][] = "Send Mail Failed.";
  }
  return;
  try {
    $db = db_connect();
    $validate = Validate::ByUser($db, $data['user']);
    if ($validate != null) {
      $data['errors'][] = "Un code de validation a déjà été envoyé concernant cet utilisateur.";
      db_close($db);
      return;
    }
    Validate::Create($db, $data['user']);
    $validate = Validate::ByUser($db, $data['user']);
    if ($validate == null) {
      $data['errors'][] = "Something weird just happened. Please contact God, ask him why he dit that.";
      db_close($db);
      return;
    }
    db_close($db);

    SendValidationMail($data['user']->Name, $data['user']->Email, $validate->Code);
    $data['email'] = true;
  } catch (MySQLException $ex) {
    $data['errors'][] = $ex;
  }
}

function StopAsking(&$data) {
  $db = db_connect();
  $data['user']->SetStopAsking($db);
  db_close($db);
}

function Modify(&$data) {
  if (get_post('save'))  Save($data);
  else if (get_post('validate'))  Validate($data);
  else if (get_post('stop'))  StopAsking($data);

  Session::LogOut();
  Session::LogIn($data['user']->OpenID);
  $data['user'] = Session::User();
  gitolite_set_key($data['user']->Username, $data['user']->PubKey);
}

function SendValidationMail($name, $email, $code) {
// subject
  $subject = 'Courriel de vérification pour votre compte Git au step.';

// message
  $message = '
Bonjour '.$name.',

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
  $headers .= 'To: '.$name.' <'.$email.'>' . "\r\n";
  $headers .= 'From: Service Git du Step <gitadmin@step.com>' . "\r\n";

// Mail it
  mail($email, $subject, $message, $headers);
}