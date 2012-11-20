<?php

require_once('inc/session.php');
require_once('openid.php');

Session::PrivateZone();
Session::LogOut();
Session::PrivateZone();