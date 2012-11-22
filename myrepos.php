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

try {
  $db = db_connect();
  $repoList = Repository::ByUserID($db, $user->ID);
  $data['adminRepoList'] = filterPerm($repoList);
  $data['repoList'] = $repoList;
  db_close($db);

} catch (MySQLException $ex) {
  $data['errors'][] = $ex;
}

echo render_template('myrepos', $data);

// ------------------- Helper --------------------
function filterPerm(array $repoList) {
  $newArray = array();
  foreach ($repoList as $repo) {
    if (!$repo->Options['is_admin'])  continue;
    $newArray[] = $repo;
  }
  return $newArray;
}