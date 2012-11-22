<?php

require_once('inc/db.php');
require_once('inc/gitolite-conf.php');

class User
{
  public $ID;
  public $OpenID;
  public $Name;
  public $Email;
  public $IsStudent;
  public $PubKey;
  public $Username;

  private function __construct(array $data) {
    $this->ID = $data['id'];
    $this->OpenID = $data['openid'];
    $this->Name = $data['name'];
    $this->Email = $data['email'];
    $this->IsStudent = $data['isstudent'];
    $this->PubKey = $data['pubkey'];
    $this->Username = $data['username'];
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

  public function SetIsStudent($db) {
    $uid = $this->ID;
    $db->query("UPDATE Users SET isstudent = '1' WHERE Users.id='$uid';");
  }

  public static function MakeEmpty() {
    return new User(array('id'=>'','openid'=>'','name'=>'','email'=>'','isstudent'=>false,'pubkey'=>'','username'=>''));
  }

  private function Update() {
    gitolite_set_key($this->Username, $this->PubKey);
    Session::Reset($this);
  }

  /**
   * This will only save email, name, username and pubkey
   */
  public function Save($db) {
    $name = $db->escape($this->Name);
    $email = $db->escape($this->Email);
    $username = $db->escape($this->Username);
    $pub_key = $db->escape($this->PubKey);
    $uid = $this->ID;

    $db->query("UPDATE Users
                  SET name = '$name', email = '$email', pubkey = '$pub_key', username = '$username'
                  WHERE Users.id = $uid;");


    $this->Name = $name;
    $this->Email = $email;
    $this->Username = $username;
    $this->PubKey = $pub_key;
    $this->Update();
    return null;
  }

  public function Insert($db, $unsafe_openid, $unsafe_name, $unsafe_email, $unsafe_pubKey, $unsafe_username) {
    $openid = $db->escape($unsafe_openid);
    $name = $db->escape($unsafe_name);
    $email = $db->escape($unsafe_email);
    $pub_key = $db->escape($unsafe_pubKey);
    $username = $db->escape($unsafe_username);

    $db->query("INSERT INTO Users (id, openid, name, email, isstudent, pubkey, username)
                  VALUES (NULL, '$openid', '$name', '$email', '0', '$pub_key', '$username');");

    $this->ID = $db->dernierID();
    $this->OpenID = $openid;
    $this->Name = $name;
    $this->Email = $email;
    $this->PubKey = $pub_key;
    $this->Username = $username;
    $this->Update();
  }
}
