<?php

require_once('inc/form.php');
require_once('inc/Repository.php');
require_once('inc/Permission.php');
require_once('inc/db.php');
require_once('inc/template.php');

//session_start();
//session_destroy();
Session::PrivateZone();

$user = null;
try {
  $user = Session::User();
} catch (MySQLException $ex) {
  $data['errors'][] = $ex;
}

$data = array('user' => $user, 'errors' => array(),
  'adminRepoList' => array(), 'repoList' => array());

echo '<pre>';
var_dump($user);
echo '</pre>';

try {
  $db = db_connect();
  $repoList = Repository::ByUserID($db, $user->ID);
  if ($repoList == null) {
    $data['errors'][] = "Couldn't fetch Repositories by user ID.";
    db_close($db);
    echo render_template('myrepos', $data);
    return;
  }
  $data['adminRepoList'] = filterPerm($repoList, Permission::Admin($db));
  $data['repoList'] = $repoList;
  db_close($db);

} catch (MySQLException $ex) {
  $data['errors'][] = $ex;
}

echo render_template('myrepos', $data);

// ------------------- Helper --------------------
function filterPerm(array $repoList, Permission $perm) {
  $newArray = array();
  foreach ($repoList as $repo) {
    if ($repo->OPermission->ID != $perm->ID)  continue;
    $newArray[] = $repo;
  }
  return $newArray;
}