<?php

require_once('inc/Router.php');
require_once('inc/db.php');
require_once('inc/User.php');

class Session {
  private static $openidkey = 'openid';
  private static $userkey = 'user';

  private static function isLogged() {
    return isset($_SESSION[self::$openidkey]);
  }

  public static function LogIn($openid) {
    $_SESSION[self::$openidkey] = $openid;
  }

  public static function LogOut() {
    unset($_SESSION[self::$openidkey]);
    if (isset($_SESSION[self::$userkey]))
      unset($_SESSION[self::$userkey]);
  }

  public static function Id() {
    return isset($_SESSION[self::$openidkey])
      ? $_SESSION[self::$openidkey] : null;
  }

  public static function Reset(User $user) {
    $_SESSION[self::$userkey] = $user;
  }

  public static function User() {
    return isset($_SESSION[self::$userkey]) ? $_SESSION[self::$userkey] : null;
  }

  public static function PublicZone() {
    if (session_id() == '')  session_start();
    if (self::isLogged()) {
      if (self::User() == null)  Router::To('account.php');
      else Router::To('myrepos.php');
    }
  }

  public static function PrivateZone() {
    if (session_id() == '')  session_start();
    if (self::isLogged())  return;
    Router::To('login.php');
    exit(0);
  }
}
/*
function session_start_public() {
	session_start();
	
	if (session_logged_in()) {
		header('Location: myrepos.php');
		exit(0);
	}
}

function session_start_private() {
	session_start();
	
	if (!session_logged_in()) {
		header('Location: login.php');
		exit(0);
	}
}

function session_start_both() {
	session_start();
}

function session_logged_in() {
	return isset($_SESSION['user']);
}

function session_username() {
	return isset($_SESSION['user']) ? $_SESSION['user'] : false;
}

function session_login($username) {
	$_SESSION['user'] = $username;
	$_SESSION['last_user'] = $username;
}

function session_logout() {
	unset($_SESSION['user']);
}

?>
*/
