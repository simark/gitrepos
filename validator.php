<?php
require_once('inc/db.php');
require_once('inc/form.php');
require_once('inc/template.php');
require_once('inc/Validate.php');

$data = array('errors' => array(), 'user' => null, 'msg' => 'Votre addresse est vérifiée.');

$code = get_get('code');
if (!$code) {
  $data['errors'][] = 'Aucun code de vérification n\'a été fournit.';
  $data['msg'] = 'Une erreure c\'est produite.';
  echo render_template('validator', $data);
  return;
}

try {
  $db = db_connect();
  $validation = Validate::ByCode($db, $code);
  if (!$validation) {
    $data['errors'][] = 'Le code de vérification fournit ne ressemble à aucun que nous avons émis.';
    $data['msg'] = 'Une erreure c\'est produite.';
    echo render_template('validator', $data);
    return;
  }

  $validation->Delete($db);

  $user = User::ByID($db, $validation->User);
  $user->SetIsStudent($db);

  db_close($db);

} catch (MySQLException $ex) {
  $data['errors'][] = $ex;
}



echo render_template('validator', $data);
