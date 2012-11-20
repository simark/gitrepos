<?php

require_once('inc/session.php');
require_once('inc/form.php');
require_once('inc/template.php');
require_once('openid.php');

Session::PublicZone();
$errors = array();

// OpenID auth was requested.
if (get_get('action') == 'verify') {
  $err = OpenID::Auth();
  if ($err)  $errors[] = $err->Msg;
  exit(0);
}

// OpenID auth begun and now we have an answer.
if (OpenIDHelper::AuthHasBegun()) {
  $openid = '';
  $err = OpenID::AuthCallback($openid);
  if ($err != null) $errors[] = $err->Msg;
  if ($openid != '') {
    Session::LogIn($openid);
    Session::PublicZone();
  }
}

echo render_template('login', array('errors' => $errors));
