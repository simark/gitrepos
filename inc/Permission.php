<?php

require_once('inc/config.php');
require_once('inc/db.php');

class Permission
{
  public $ID;
  public $Perm;

  private static $init;
  private static $r;
  private static $rw;
  private static $rwp;

  private function __construct(array $data) {
    $this->ID = $data['id'];
    $this->Perm = $data['name'];
  }

  public static function init($db) {
    $db->query("SELECT * FROM Perms;");
    $perms = $db->fetchAll();
    foreach($perms as $perm) {
      switch ($perm['name']) {
        case "R":
          self::$r = $perm['id'];
          break;
        case "RW":
          self::$rw = $perm['id'];
          break;
        case "RW+":
          self::$rwp = $perm['id'];
          break;
      }
    }
    self::$init = true;
  }

  public static function ByUserRepo($db, User $user, Repository $repo) {
    $uid = $user->ID;
    $rid = $repo->ID;

    $db->query("SELECT p.* FROM UserRepoPerms as urp, Perms as p
        WHERE '$uid' = urp.user AND urp.perm = p.id AND urp.repo = '$rid'");

    $result = $db->fetch();
    if (!$result)  return null;
    return new Permission($result);
  }

  private static function NewPerm($db, Permission $perm, User $user, Repository $repo) {
    $repoID = $repo->ID;
    $userID = $user->ID;
    $permID = $perm->ID;
    $db->query("INSERT INTO UserRepoPerms (user, Repo, Perm, is_admin, is_owner) VALUES ('$userID', '$repoID', '$permID', '0', '0')");
  }

  private static function ModPerm($db, Permission $perm, User $user, Repository $repo) {
    $repoID = $repo->ID;
    $userID = $user->ID;
    $permID = $perm->ID;
    $db->query("UPDATE UserRepoPerms SET Perm = '$permID' WHERE user = '$userID' AND Repo = '$repoID';");
  }

  public static function SetPerm($db, Permission $perm, User $user, Repository $repo) {
    if (!self::$init)  self::init($db);
    if ($perm != self::Read($db) && $perm != self::ReadWrite($db) && $perm != self::Admin($db))
      return E_INVALID_PERMISSION;

    $curPerm = Permission::ByUserRepo($db, $user, $repo);
    if ($curPerm == null)
      self::NewPerm($db, $perm, $user, $repo);
    else
      self::ModPerm($db, $perm, $user, $repo);
    return E_SUCCESS;
  }

  public static function RemPerm($db, User $user, Repository $repo) {
    if (!self::$init)  self::init($db);

    $curPerm = Permission::ByUserRepo($db, $user, $repo);
    if ($curPerm == null)  return;

    $repoID = $repo->ID;
    $userID = $user->ID;

    $db->query("DELETE FROM UserRepoPerms WHERE user = '$userID' AND Repo = '$repoID';");
  }

  public static function Read($db) {
    if (!self::$init)  self::init($db);
    return new Permission(array('id' => self::$r, 'name' => 'R'));
  }

  public static function ReadWrite($db) {
    if (!self::$init)  self::init($db);
    return new Permission(array('id' => self::$rw, 'name' => 'RW'));
  }

  public static function Admin($db) {
    if (!self::$init)  self::init($db);
    return new Permission(array('id' => self::$rwp, 'name' => 'RW+'));
  }

  public static function CreateFromID($db, $id) {
    if (!self::$init)  self::init($db);
    $array = array('id' => $id);
    switch ($id) {
      case self::$r:
        $array['name'] = 'R'; break;
      case self::$rw:
        $array['name'] = 'RW'; break;
      case self::$rwp:
        $array['name'] = 'RW+'; break;
      default:
        return null;
    }
    return new Permission($array);
  }
}
