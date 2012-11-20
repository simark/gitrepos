<?php

require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/SReg.php";
require_once "Auth/OpenID/PAPE.php";

class OpenIDError
{
  public function __construct($msg)
  {
    $this->Msg = $msg;
  }

  public $Msg;
}

class OpenIDHelper
{
  private static $kSessionKey = 'openid_consumer_session';
  private static $kError = 0;
  private static $kFirstStep = 1;

  public $Store;
  public $Error;

  public function __construct()
  {
    $this->Store = sys_get_temp_dir();
    $this->Store .= DIRECTORY_SEPARATOR . '_php_consumer';

    if (!file_exists($this->Store) && !mkdir($this->Store)) {
      $this->curError = new OpenIDError("Could not create the FileStore directory '$this->Store'. " .
        " Please check the effective permissions.");
    }
  }

  public static function Scheme()
  {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
      return 'https';
    }
    return 'http';
  }

  public static function TrustRoot()
  {
    return sprintf("%s://%s:%s%s/", self::Scheme(), $_SERVER['SERVER_NAME'],
      $_SERVER['SERVER_PORT'], dirname($_SERVER['PHP_SELF']));
  }

  public static function ReturnTo()
  {
    return self::TrustRoot() . 'login.php';
  }

  public static function AuthHasBegun()
  {
    return isset($_SESSION[self::$kSessionKey]) && $_SESSION[self::$kSessionKey] == self::$kFirstStep;
  }

  public static function BeginAuth()
  {
    $_SESSION[self::$kSessionKey] = self::$kFirstStep;
  }

  public static function EndAuth()
  {
    $_SESSION[self::$kSessionKey] = self::$kError;
  }
}

class OpenID
{
  public static function Auth()
  {
    if (OpenIDHelper::AuthHasBegun())
      return new OpenIDError('Unknown state in openID auth.');

    $helper = new OpenIDHelper();
    if ($helper->Error != null)
      return $helper->Error;

    $openIDUrl = "";
    $err = self::getOpenIDURL($openIDUrl);
    if ($err) return $err;

    $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_FileStore($helper->Store));

    // Begin the OpenID authentication process.
    $auth_request = $consumer->begin($openIDUrl);

    // No auth request means we can't begin OpenID.
    if (!$auth_request) return new OpenIDError("Authentication error; not a valid OpenID Uri.");

    $sreg_request = Auth_OpenID_SRegRequest::build(array('nickname'), array('fullname', 'email'));

    if ($sreg_request) {
      $auth_request->addExtension($sreg_request);
    }

    $policy_uris = null;
    if (isset($_GET['policies'])) {
      $policy_uris = $_GET['policies'];
    }

    $pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
    if ($pape_request) {
      $auth_request->addExtension($pape_request);
    }

    OpenIDHelper::BeginAuth();
    // Generate form markup and render it.
    $form_id = 'openid_message';
    $form_html = $auth_request->htmlMarkup(
      OpenIDHelper::TrustRoot(),
      OpenIDHelper::ReturnTo(),
      false, array('id' => $form_id));

    // Display an error if the form markup couldn't be generated;
    // otherwise, render the HTML.
    if (Auth_OpenID::isFailure($form_html)) {
      return new OpenIDError("Could not redirect to server: " . $form_html->message);
    } else {
      print $form_html;
    }
    return null;
  }

  public static function AuthCallback(&$id)
  {
    if (!OpenIDHelper::AuthHasBegun()) return null;

    $helper = new OpenIDHelper();
    if ($helper->Error) return $helper->Error;

    $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_FileStore($helper->Store));
    $response = $consumer->complete(OpenIDHelper::ReturnTo());

    // Check the response status.
    if ($response->status == Auth_OpenID_CANCEL) {
      // This means the authentication was cancelled.
      return new OpenIDError('Verification cancelled.');
    }
    if ($response->status == Auth_OpenID_FAILURE) {
      // Authentication failed; display the error message.
      return new OpenIDError("OpenID authentication failed: " . $response->message);
    }
    if ($response->status != Auth_OpenID_SUCCESS) {
      // Authentication failed; display the error message.
      return new OpenIDError('Unknown auth status');
    }

    OpenIDHelper::EndAuth();
    $id = htmlentities($response->getDisplayIdentifier());
    return null;
  }

  /**
   * @param $oidUrl out_OpenIDUrl
   * @return null|OpenIDError Error or null if good.
   */
  private static function getOpenIDURL(&$oidUrl)
  {
    if (isset($_GET['openid_identifier']) && !empty($_GET['openid_identifier'])) {
      $oidUrl = $_GET['openid_identifier'];
      return null;
    }

    return new OpenIDError("Expected an OpenID URL.");
  }
}
