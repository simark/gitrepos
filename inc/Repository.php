<?php

require_once('inc/config.php');
require_once('inc/db.php');

class Repository
{
  public $ID;
  public $Name;
  public $Description;
  public $Options;

  /**
   * Creates a new repo in mysql.
   * @param $unsafe_name Name of the repo.
   * @param $unsafe_desc Desc of the repo.
   */
  public static function Create($db, $unsafe_name, $unsafe_desc) {
    $name = $db->escape($unsafe_name);
    if (!Pattern::MatchesRepo($name))  return E_INVALID_REPO_NAME;

    $repo = self::ByName($db, $name);
    if ($repo != null)  return E_REPO_EXISTS;

    $desc = $db->escape($unsafe_desc);
    $db->query("INSERT INTO Repos VALUES (NULL, '$name',	'$desc');");

    return E_SUCCESS;
  }

  public function __construct(array $data) {
    $this->ID = $data['id'];
    $this->Name = $data['name'];
    $this->Description = $data['description'];
    $this->Options = array();
  }

  public static function ByID($db, $unsafe_id) {
    $id = $db->escape($unsafe_id);
    $db->query("SELECT * FROM Repos as r WHERE r.id = '$id'");
    $data = $db->fetch();
    if (!$data)  return null;
    return new Repository($data);
  }

  public static function ByUserID($db, $unsafe_id) {
    $id = $db->escape($unsafe_id);
    $db->query("SELECT r.*, urp.* FROM Repos as r, UserRepoPerms as urp WHERE r.id = urp.repo AND urp.user = '$id'");
    $data = $db->fetchAll();
    if (!$data)  return array();

    $return = array();
    foreach ($data as $value) {
      $obj = new Repository($value);
      $obj->Options['perm'] = Permission::CreateFromID($db, $value['Perm']);
      $obj->Options['is_admin'] = $value['is_admin'];
      $obj->Options['is_owner'] = $value['is_owner'];
      $return[] = $obj;
    }
    return $return;
  }

  public static function ByName($db, $unsafe_name) {
    $name = $db->escape($unsafe_name);
    $db->query("SELECT * FROM Repos as r WHERE r.name = '$name'");
    $data = $db->fetch();
    if (!$data)  return null;
    return new Repository($data);
  }

  public static function SetAdmin($db, User $user, Repository $repo, $on = 1) {
    $uid = $user->ID;
    $rid = $repo->ID;
    $db->query("UPDATE UserRepoPerms as urp SET is_admin = '$on' WHERE urp.user = $uid AND urp.Repo = $rid;");
  }

  public static function SetOwner($db, User $user, Repository $repo) {
    $uid = $user->ID;
    $rid = $repo->ID;
    $db->query("UPDATE UserRepoPerms as urp SET is_owner = '1' WHERE urp.user = $uid AND urp.Repo = $rid;");
  }
}
