<?php
/**
 * Ce fichier contient les fonctions pour générer le fichier de configuration
 * de gitolite contenant les entrepôts et les permissions.
 *
 * @author Simon Marchi <simon.marchi@polymtl.ca>
 */

require_once('config.php');
require_once('log.php');

/**
 * Imprime l'entr�e d'un entrep�t dans un flux de sortie (g�n�ralement le
 * fichier de configuration).
 *
 * @param out Flux de sortie
 * @param repo Entrep�t � imprimer
 */
function _conf_print_repo($out, $repo) {
	fprintf($out, "repo    %s\n", $repo['name']);
	fprintf($out, "        RW+ = %s\n", $repo['owner']);
	
	foreach ($repo['perms'] as $perm) {
		fprintf($out, "        %s = %s\n", $perm['level'], $perm['user']);
	}
	
	fprintf($out, "\n");
}

/**
 * Imprime l'entr�e de l'entrep�t administrateur.
 *
 * @param out Flux de sortie
 * @param admins Liste des administrateurs de l'entrep�t gitolite-admin
 */
function _conf_print_admin_repo($out, $admins) {
	fprintf($out, "@admins = %s\n", implode(' ', $admins));
	fprintf($out, "repo    gitolite-admin\n");
	fprintf($out, "        RW+ = @admins\n");
	fprintf($out, "\n");
}

/**
 * Change le r�pertoire de travail.
 *
 * @param dir Le nouveau r�pertoire de travail.
 * @return L'ancien r�pertoire de travail, faux en cas d'�chec.
 */
function _gitolite_chdir($dir) {
	$oldcwd = getcwd();
	if (!$oldcwd) {
		log_e("getcwd error");
		
		return false;
	}
	
	$ret = chdir($dir);
	if (!$ret) {
		log_e("chdir error");
		
		return false;
	}
	
	return $oldcwd;
}

/**
 * G�n�re le fichier de configuration des entrep�ts de gitolite.
 *
 * @param repos Liste des entrep�ts.
 * @param admins Liste des administrateurs de l'entrep�t gitolite-admin
 *
 * @return Vrai si le fichier de configuration a �t� �crit avec succ�s, faux
 *     autrement.
 */
function gitolite_generate_config($repos, $admins) {
	log_d("gitolite_generate_config: start");

	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}
	
	$out = fopen(CONFIG_PATH, "w");
	if (!$out) {
		log_e("Can't open gitolite config (" . CONFIG_PATH . ")");
		goto err;
	}

	/* Imprimer l'entrep�t admin */
	_conf_print_admin_repo($out, $admins);
	
	/* Imprimer les entrep�ts */
	foreach ($repos as $repo) {
		_conf_print_repo($out, $repo);
	}

	$ret = fclose($out);
	if (!$ret) {
		log_e("fclose error");
		goto err;
	}
	
	if (!_gitolite_chdir($oldcwd)) {
		log_e("chdir error");
		goto err;
	}
	
	log_d("gitolite_generate_config completed successfully");
	
	return true;

err:
	if (!_gitolite_chdir($oldcwd)) {
		log_e("chdir error");
	}

	return false;
}

/**
 * Envoie la configuration, si n�cessaire.
 * 
 * @return Vrai si la configuration a bien �t� envoy�e.
 */
function gitolite_commit_config() {
	log_d("gitolite_commit_config: start");

	/* N�cessaire pour que git trouve le fichier .gitconfig */
	putenv("HOME=".HOME_DIR);
	
	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}

	/* Check if we need to commit the config */
	log_d("gitolite_commit_config: git status");
	exec("git status --porcelain ".CONFIG_PATH." 2>&1", $status_output, $ret);
	if ($ret != 0) {
		log_e("git status error");
		log_e($status_output);
		
		goto err;
	}
	
	if (count($status_output) < 1) {
		log_d("No changes detected, no need to commit config");
		
		goto done;
	}
	
	log_d($status_output);
	
	/* Commit */
	log_d("gitolite_commit_config: git commit");
	exec("git commit -m 'Config changed' ".CONFIG_PATH." 2>&1", $commit_output,
		$ret);
	
	if ($ret != 0) {
		log_e("git commit error");
		log_e($commit_output);
		
		goto err;
	}
	
	log_d($commit_output);
	
	/* Push */
	log_d("gitolite_commit_config: git push");
	exec("git push --porcelain 2>&1", $push_output, $ret);
	
	if ($ret != 0) {
		log_e("git push error");
		log_e($push_output);
		
		goto err;
	}
	
	log_d($push_output);
	
done:
	if (!_gitolite_chdir($oldcwd)) {
		log_e("chdir error");
		return false;
	}	
	
	log_d("gitolite_commit_config completed successfully");
	
	return true;

err:
	_gitolite_chdir($oldcwd);

	return false;
}

/**
 * �crit la cl� d'un utilisateur dans le dossier keys de gitolite.
 *
 * @param username Le nom de l'utilisateur.
 * @param key La cl�.
 * @return True si la cl� a bien �t� �crite.
 */
function gitolite_set_key($username, $key) {
	log_d("gitolite_set_key: start ($username)");
	
	/* chdir */
	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}

	/* �crire la cl� */
	$filename = KEYDIR_PATH."$username.pub";
	$ret = file_put_contents($filename, $key . "\n");
	if (!$ret) {
		log_e("file_put_contents error");
		goto err;
	}

	set_config_changed();
	
	if (!_gitolite_chdir($oldcwd)) {
		log_e("chdir error");
		return false;
	}
	
	log_d("gitolite_set_key completed successfully");

	return true;

err:
	_gitolite_chdir($oldcwd);
	return false;
}

/**
 * Lit la cl� d'un utilisateur du dossier keys de gitolite.
 *
 * @param username Le nom de l'utilisateur.
 * @return Chaine contenant la cl�,
 *         false en cas d'erreur, incluant si la cl� n'est pas trouv�e.
 */
function gitolite_get_key($username) {
	log_d("gitolite_get_key: start ($username)");
	
	/* chdir */
	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}

	/* Lire la cl� */
	$filename = KEYDIR_PATH."$username.pub";
	$ret = file_get_contents($filename);
	if (!$ret) {
		log_e("file_get_contents error");
		goto err;
	}
	
	if (!_gitolite_chdir($oldcwd)) {
		log_e("chdir error");
		return false;
	}
	
	log_d("gitolite_get_key completed successfully");

	return $ret;

err:
	_gitolite_chdir($oldcwd);
	return false;
}

/**
 * Envoie les cl�s modifi�es ou ajout�es.
 */
function gitolite_commit_keys() {
	log_d("gitolite_commit_keys: start");

	/* N�cessaire pour que git trouve le fichier .gitconfig */
	putenv("HOME=".HOME_DIR);
	
	/* chdir */
	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}

	/* Status */
	log_d("gitolite_commit_keys: git status");
	exec("git status ".KEYDIR_PATH." --porcelain 2>&1", $status_output, $ret);	
	if ($ret != 0) {
		log_e("git status error");
		log_e($status_output);
		
		goto err;
	}
	
	if (count($status_output) < 1) {
		log_d("No keys to commit");
		goto done;
	}
	
	log_d($status_output);
	
	/* Add */
	log_d("gitolite_commit_keys: git add");
	exec("git add ".KEYDIR_PATH." 2>&1", $add_output, $ret);
	
	if ($ret != 0) {
		log_e("git add error");
		log_e($add_output);
		
		goto err;
	}
	
	log_d($add_output);
	
	/* Commit */
	log_d("gitolite_commit_keys: git commit");
	exec("git commit -m 'Keys changed' ".KEYDIR_PATH." 2>&1", $commit_output,
		$ret);
	
	if ($ret != 0) {
		log_e("git commit error");
		log_e($commit_output);
		
		goto err;
	}
	
	log_d($commit_output);
	
	/* Push */
	log_d("gitolite_commit_keys: git push");
	exec("git push 2>&1", $push_output, $ret);
	
	if ($ret != 0) {
		log_e("git push error");
		log_e($push_output);
		
		goto err;
	}
	
	log_d($push_output);

done:
	if (!_gitolite_chdir($oldcwd)) {
		log_e("chdir error");
		return false;
	}
	
	log_d("gitolite_commit_keys completed successfully");
	
	return true;

err:
	_gitolite_chdir($oldcwd);
	return false;
}

/**
 * Appeler cette fonction indique que la configuration a chang�e, et qu'une
 * reg�n�ration des permissions est n�cessaire.
 */
 function set_config_changed() {
	// Pour pallier au fait que le serveur roule en www-https et que le cron job
	// roule en git... suphp ?
	system('sudo -u git /usr/bin/touch ' . escapeshellarg(PERM_GEN_PATH));
}

function has_config_changed() {
	return is_file(PERM_GEN_PATH);
}

/**
 * Indique que la configuration a �t� sauvegard�e.
 */
function done_config_changed() {
	unlink(PERM_GEN_PATH);
	touch(PERM_TIMESTAMP_PATH);
}

?>
