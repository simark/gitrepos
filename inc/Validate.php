<?php

require_once('inc/db.php');
require_once('inc/User.php');

class Validate
{
  public $User;
  public $Code;

  private function __construct(array $data) {
    $this->User = $data['user'];
    $this->Code = $data['code'];
  }

  public static function ByUser($db, User $user) {
    $uid = $user->ID;
    $db->query("SELECT * FROM Validation as v WHERE v.user = '$uid';");
    $data = $db->fetch();
    if (!$data)  return null;
    return new Validate($data);
  }

  public static function ByCode($db, $unsafe_code) {
    $code = $db->escape($unsafe_code);
    $db->query("SELECT * FROM Validation as v WHERE v.code = '$code';");
    $data = $db->fetch();
    if (!$data)  return null;
    return new Validate($data);
  }

  public function Delete($db) {
    $uid = $this->User;
    $db->query("DELETE FROM Validation WHERE Validation.user = $uid;");
  }

  private static function MakeRandomCode() {
    $str = '';
    for ($i = 0; $i < 10; $i++) {
      $str .= mt_rand(1000, 9999);
    }
    return base64_encode($str);
  }

  public static function Create($db, User $user) {
    $uid = $user->ID;
    $code = self::MakeRandomCode();
    $db->query("INSERT INTO Validation (user, code) VALUES ('$uid', '$code');");
  }
}
