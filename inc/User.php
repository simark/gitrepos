<?php

require_once('inc/db.php');

class User
{
  public $ID;
  public $OpenID;
  public $Name;
  public $Email;
  public $IsStudent;
  public $PubKey;
  public $Username;
  public $NotStudent;

  public function __construct(array $data) {
    $this->ID = $data['id'];
    $this->OpenID = $data['openid'];
    $this->Name = $data['name'];
    $this->Email = $data['email'];
    $this->IsStudent = $data['isstudent'];
    $this->PubKey = $data['pubkey'];
    $this->Username = $data['username'];
    $this->NotStudent = $data['notStudent'];
  }

  public static function ByID($db, $unsafe_id) {
    $id = $db->escape($unsafe_id);
    $db->query("SELECT * FROM Users as u WHERE u.id = '$id'");
    $data = $db->fetch();
    if (!$data)  return null;
    return new User($data);
  }

  public static function ByOpenID($db, $unsafe_openid) {
    $openid = $db->escape($unsafe_openid);
    $db->query("SELECT * FROM Users as u WHERE u.openid = '$openid'");
    $data = $db->fetch();
    if (!$data)  return null;
    return new User($data);
  }

  public static function ByName($db, $unsafe_name) {
    $name = $db->escape($unsafe_name);

    $db->query("SELECT * FROM Users as u WHERE u.username = '$name'");
    $data = $db->fetch();
    if (!$data)  return null;
    return new User($data);
  }

  public function SetStopAsking($db) {
    $uid = $this->ID;
    $db->query("UPDATE Users SET notStudent = '1' WHERE Users.id='$uid';");
  }

  public function SetIsStudent($db) {
    $uid = $this->ID;
    $db->query("UPDATE Users SET isstudent = '1' WHERE Users.id='$uid';");
  }

    public function Save($db, $unsafe_username, $unsafe_name, $unsafe_email, $unsafe_pubKey) {
      $username = $db->escape($unsafe_username);
      $name = $db->escape($unsafe_name);
      $email = $db->escape($unsafe_email);
      $pubKey = $db->escape($unsafe_pubKey);
      $uid = $this->ID;

      $db->query("UPDATE Users
                    SET name = '$name', email = '$email', pubkey = '$pubKey', username = '$username'
                    WHERE Users.id = $uid;");
    }
}
