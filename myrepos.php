<?php

require_once('inc/form.php');
require_once('inc/Repository.php');
require_once('inc/Permission.php');
require_once('inc/db.php');
require_once('inc/template.php');

Session::PrivateZone();

$user = Session::User();
$data = array('user' => $user, 'errors' => array(),
  'adminRepoList' => array(), 'repoList' => array());

try {
  $db = db_connect();
  $repoList = Repository::ByUserID($db, $user->ID);

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