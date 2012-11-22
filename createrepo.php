<?php

require_once('inc/session.php');
require_once('inc/db.php');
require_once('inc/form.php');
require_once('inc/config.php');
require_once('inc/template.php');
require_once('inc/gitolite-conf.php');
require_once('inc/Router.php');

Session::PrivateZone();
$data = array('user' => Session::User(), 'errors' => array());

if ($_SERVER['REQUEST_METHOD'] == 'POST')
  createRepo(get_post('name'), get_post('description'), $data);

echo render_template('createrepo', $data);

// --------------- Create Repo -------------------------
function createRepo($name, $description, &$data) {

  if (!Pattern::MatchesRepo($name)) {
    $data['errors'][] = "Nom d'entrepôt invalide";
    return;
  }

  if (!$data['user']->IsStudent) {
    $data['errors'][] = "Vous devez être un étudiant pour pouvoir créer un entrepôt.";
    return;
  }

  $ret = null;
  try {

    $db = db_connect();
    $ret = db_add_repo($db, $name, $description, $data['user']);
    if ($ret != E_SUCCESS) {

    }
    db_close($db);
  } catch (MySQLException $ex) {
    $data['errors'][] = $ex;
  }

  if ($ret == E_SUCCESS) {
    set_config_changed();
    Router::To("repodetail.php?r=$name");
  }

  switch ($ret) {
    case E_REPO_EXISTS:
      $data['errors'][] = 'Ce nom d\'entrepôt est déjà utilisé.';
      break;
    case E_INVALID_REPO_NAME:
      $data['errors'][] = 'Ce nom d\'entrepôt est invalid'. $name;
      break;
    case E_WEIRD_SHIT:
      $data['errors'][] = 'N\'est pas supposé arrivé.';
      break;
  }
}

