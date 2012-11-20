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
    $db = db_connect();
    $_SESSION[self::$userkey] = User::ByOpenID($db, self::Id());
    db_close($db);
  }

  public static function LogOut() {
    unset($_SESSION[self::$openidkey]);
    unset($_SESSION[self::$userkey]);
  }

  public static function Id() {
    return isset($_SESSION[self::$openidkey])
      ? $_SESSION[self::$openidkey] : null;
  }

  public static function User() {
    return isset($_SESSION[self::$userkey]) ? $_SESSION[self::$userkey] : null;
  }

  public static function PublicZone() {
    if (session_id() == '')  session_start();
    if (!self::isLogged()) return;
    Router::To('myrepos.php');
    exit(0);
  }

  public static function PrivateZone() {
    if (session_id() == '')  session_start();
    if (self::isLogged())  return;
    Router::To('login.php');
    exit(0);
  }
}

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
