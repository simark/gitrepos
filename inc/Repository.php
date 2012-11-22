<?php

require_once('inc/config.php');
require_once('inc/db.php');

class Repository
{
  public $ID;
  public $Name;
  public $Description;
  public $OPermission;

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
    $this->OPermission = null;
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
    $db->query("SELECT r.*, urp.perm FROM Repos as r, UserRepoPerms as urp WHERE r.id = urp.repo AND urp.user = '$id'");
    $data = $db->fetchAll();
    if (!$data)  return array();

    $return = array();
    foreach ($data as $value) {
      $obj = new Repository($value);
      $obj->OPermission = Permission::CreateFromID($db, $value['perm']);
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
}
