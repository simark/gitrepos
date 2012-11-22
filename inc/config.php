<?php

/*
 * Infos de la base de donn�es.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'gitrepos');
define('DB_USER', 'gitrepos');
define('DB_PASS', 'CZMvcVTrNwRPGnPH');

/*
 * Restructions pour les noms et mots de passe.
 */
define('REPONAME_MIN_LEN', 3);
define('REPONAME_MAX_LEN', 25);
define('USERNAME_MIN_LEN', 3);
define('USERNAME_MAX_LEN', 25);
define('PASSWORD_MIN_LEN', 6);

class Pattern {
  private static $repo = null;
  private static $user = null;

  public static function MatchesRepo($name) {
    if (!self::$repo)
      self::$repo = sprintf("/^[a-zA-Z0-9_]{%d,%d}$/", REPONAME_MIN_LEN, REPONAME_MAX_LEN);
    return preg_match(self::$repo, $name);
  }
  public static function MatchesUser($name) {
    if (!self::$user)
      self::$user = sprintf("/^[a-zA-Z0-9_]{%d,%d}$/", USERNAME_MIN_LEN, USERNAME_MAX_LEN);
    return preg_match(self::$user, $name);
  }
  public static function MatchesPoly($email) {
    // Je n'ai pas permis les commentaires, ni les espaces, ni les caractère entre guillement dans le domain.
    // Screw them s'ils les utilisent.
    return preg_match("/@(|.*\.)polymtl\.ca$/", $email);
  }
}

/*
 * Noms d'utilisateurs ayant de droit de faire un commit sur l'entrep�t
 * d'administration.
 */
$admins = array('gitolite-admin', 'simark');

/*
 * Le dossier contenant le fichier .gitconfig de l'utilisateur qui ex�cute le
 * script.
 */
define('HOME_DIR', '/home/system/git/');

/*
 * Chemin (absolu ou relatif) du clone de l'entrep�t administrateur.
 */
define('ADMIN_REPO_PATH', '/home/system/git/gitolite-admin');

/*
 * Chemins (relatifs) des composantes dans l'entrep�t administrateur.
 */
define('CONFIG_PATH', 'conf/gitolite.conf');
define('KEYDIR_PATH', 'keydir/');

/*
 * Chemin relatif ou absolu du journal.
 */
define('LOG_PATH', 'log');

/*
 * Chemin absolu du fichier d'exclusion mutuelle.
 */
define('MUTEX_PATH', '/tmp/gitrepos_mutex');

/*
 * Chemin absolu du fichier de date de g�n�ration des permissions.
 */
define('PERM_TIMESTAMP_PATH', '/tmp/gitrepos_lastgen');

/*
 * Chemin absolu du fichier qui indique une demande de reg�n�ration des
 * permissions.
 */
define('PERM_GEN_PATH', '/tmp/gitrepos_permgen');


/*
 * Niveaux de prolixit� de la journalisation.
 *
 * Error, Warning et Debug.
 */
define('LOG_E', 0);
define('LOG_W', 1);
define('LOG_D', 2);

define('LOG_LEVEL', LOG_D);

define('E_SUCCESS', -1);
define('E_REPO_EXISTS', -2);
define('E_REPO_NO_EXISTS', -3);
define('E_USER_EXISTS', -4);
define('E_USER_NO_EXISTS', -5);
define('E_INVALID_LEVEL', -6);
define('E_INVALID_USER', -7);
define('E_EMAIL_EXISTS', -8);
define('E_INVALID_REPO_NAME', -9);
define('E_INVALID_PERMISSION', -10);
define('E_WEIRD_SHIT', -11);

?>
