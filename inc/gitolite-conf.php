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
 * Imprime l'entrée d'un entrepôt dans un flux de sortie (généralement le
 * fichier de configuration).
 *
 * @param out Flux de sortie
 * @param repo Entrepôt à imprimer
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
 * Imprime l'entrée de l'entrepôt administrateur.
 *
 * @param out Flux de sortie
 * @param admins Liste des administrateurs de l'entrepôt gitolite-admin
 */
function _conf_print_admin_repo($out, $admins) {
	fprintf($out, "@admins = %s\n", implode(' ', $admins));
	fprintf($out, "repo    gitolite-admin\n");
	fprintf($out, "        RW+ = @admins\n");
	fprintf($out, "\n");
}

/**
 * Change le répertoire de travail.
 *
 * @param dir Le nouveau répertoire de travail.
 * @return L'ancien répertoire de travail, faux en cas d'échec.
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
 * Génère le fichier de configuration des entrepôts de gitolite.
 *
 * @param repos Liste des entrepôts.
 * @param admins Liste des administrateurs de l'entrepôt gitolite-admin
 *
 * @return Vrai si le fichier de configuration a été écrit avec succès, faux
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

	/* Imprimer l'entrepôt admin */
	_conf_print_admin_repo($out, $admins);
	
	/* Imprimer les entrepôts */
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
 * Envoie la configuration, si nécessaire.
 * 
 * @return Vrai si la configuration a bien été envoyée.
 */
function gitolite_commit_config() {
	log_d("gitolite_commit_config: start");

	/* Nécessaire pour que git trouve le fichier .gitconfig */
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
 * Écrit la clé d'un utilisateur dans le dossier keys de gitolite.
 *
 * @param username Le nom de l'utilisateur.
 * @param key La clé.
 * @return True si la clé a bien été écrite.
 */
function gitolite_set_key($username, $key) {
	log_d("gitolite_set_key: start ($username)");
	
	/* chdir */
	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}

	/* Écrire la clé */
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
 * Lit la clé d'un utilisateur du dossier keys de gitolite.
 *
 * @param username Le nom de l'utilisateur.
 * @return Chaine contenant la clé,
 *         false en cas d'erreur, incluant si la clé n'est pas trouvée.
 */
function gitolite_get_key($username) {
	log_d("gitolite_get_key: start ($username)");
	
	/* chdir */
	$oldcwd = _gitolite_chdir(ADMIN_REPO_PATH);
	if (!$oldcwd) {
		log_e("chdir error");
		return false;
	}

	/* Lire la clé */
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
 * Envoie les clés modifiées ou ajoutées.
 */
function gitolite_commit_keys() {
	log_d("gitolite_commit_keys: start");

	/* Nécessaire pour que git trouve le fichier .gitconfig */
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
 * Appeler cette fonction indique que la configuration a changée, et qu'une
 * regénération des permissions est nécessaire.
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
 * Indique que la configuration a été sauvegardée.
 */
function done_config_changed() {
	unlink(PERM_GEN_PATH);
	touch(PERM_TIMESTAMP_PATH);
}

?>
