<?php

require_once('inc/db.php');
require_once('inc/session.php');
require_once('inc/form.php');
require_once('inc/template.php');
require_once('inc/gitolite-conf.php');

Session::PrivateZone();

$data = array('user' => Session::User(), 'errors' => array(), 'reponame' => get_get('r'));

/* Form submit */
if ($_SERVER['REQUEST_METHOD'] == 'POST')  ModifyPermissions($data);

// Normal page
try {
	$db = db_connect();
	$repo = db_get_repo_user($db, $data['reponame'], Session::Id());
	db_close($db);
} catch (MySQLException $ex) {
  $data['errors'][] = $ex;
}

$data['repo'] = $repo;
$data['server_time'] = time();
$data['perm_gen_time'] = filemtime(PERM_TIMESTAMP_PATH);

echo render_template('repodetail', $data);

// -------------- Helpers ---------------------------

function AddIfNecessary($db, &$data, Repository $repo) {
  $username = get_post('usernameToAdd');
  if ($username === '')  return;

  $newUser = User::ByName($db, $username);
  if (!$newUser) {
    $data['errors'][] = "Le username à ajouter n'existe pas (".htmlspecialchars($username).").";
    return;
  }

  $perm = Permission::CreateFromID($db, get_post('permLevel'));
  if ($perm == null) {
    $data['errors'][] = "Le niveau de permission n'est pas valide.";
    return;
  }

  $oldPerm = Permission::ByUserRepo($db, $newUser, $repo);
  if ($oldPerm != null) {
    $data['errors'][] = 'Vous avez essayé d\'ajouter une permission pour <b>' .
      htmlspecialchars($username) . '</b> alors qu\'il a déjà des permissions pour ce référentiel.';
    return;
  }

  try {
    Permission::SetPerm($db, $perm, $newUser, $repo);

    echo '-'. get_post('is_admin') . '-';
    if (get_post('is_admin') == 'on')  Repository::SetAdmin($db, $newUser, $repo);
  } catch (MySQLException $ex) {
    $data['errors'][] = $ex;
  }
}

function ModifIfNecessary($db, Repository $repo) {
  foreach ($_POST as $key => $value) {
    if (!is_numeric($key))  continue;
    $user = User::ByID($db, $key);
    if ($user == null)  continue;
    $perm = Permission::CreateFromID($db, $value);
    if ($perm == null) {
      if ($value == -1)  Permission::RemPerm($db, $user, $repo);
      continue;
    }
    Permission::SetPerm($db, $perm, $user, $repo);
    Repository::SetAdmin($db, $user, $repo, get_post("_$key") == 'on' ? 1 : 0);
  }
}

function ModifyPermissions(&$data) {
  try {
    $db = db_connect();

    $repo = Repository::ByID($db, get_post('repoId'));
    if ($repo == null) {
      $data['errors'][] = 'Le référentiel demandé n\'existe pas.';
      return;
    }


    $perm = Permission::ByUserRepo($db, $data['user'], $repo);
    if ($perm == null || $perm->ID != Permission::Admin($db)->ID) {
      $data['errors'][] = 'Vous n\'avez pas les droits sur ce référentiel.';
      return;
    }

    AddIfNecessary($db, $data, $repo);

    ModifIfNecessary($db, $repo);

    db_close($db);
  } catch (MySQLException $ex) {
    $data['errors'][] = $ex;
  }
}
