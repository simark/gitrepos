<?php

require_once('inc/db.php');
require_once('inc/session.php');

class Auth
{
  private static function gen_blowfish_salt()
  {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./";
    $len = strlen($chars);

    $salt = "$2a$04$";

    for ($i = 0; $i < 22; $i++) {
      $salt .= $chars[rand() % $len];
    }

    $salt .= "$";

    return $salt;
  }

  private static function check_password($password, $hash)
  {
    $hash2 = crypt($password, $hash);

    return $hash2 === $hash;
  }

  /**
   * Authenticate a user.
   * @param $username Username
   * @param $password Password
   * @return bool True if the user is valid.
   */
  public static function Check($username, $password)
  {
    $db = db_connect();
    $userData = db_get_user($db, $username);
    db_close($db);

    $_SESSION['last_user'] = $username;

    if ($userData == null) return false;
    if (!self::check_password($password, $userData['password']))  return false;

    session_login($username);
    return true;
  }
}
