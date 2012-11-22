<?php
/*
 * cron.php
 *
 * Met à jour les permissions qui sont en attente dans l'entrepôt
 * administrateur. Il est possible de l'appeler à la main (php cron.php) afin
 * de forcer la mise à jour
 *
 */


require_once('inc/gitolite-conf.php');
require_once('inc/db.php');
require_once('inc/mutex.php');
require_once('inc/Repository.php');


function main() {
	global $admins;

	log_d('cron.php starting');
	register_shutdown_function(function () {
			mutex_release();
			log_d('cron.php exiting');
	});

	if (!isset($admins) || count($admins) == 0) {
		echo "La liste d'administrateurs est vide ou n'existe pas";
		return 1;
	}

	mutex_lock();

	if (!has_config_changed()) {
		log_d("Flag that indicates that config changed is not present - quitting.");
		return;
	}

	log_d("Flag that indicates that config changed is present.");
	done_config_changed();

	try {
		$db = db_connect();

		//$repos = db_get_all_repos($db);
    $repos = Repository::GitoliteConfig($db);

		db_close($db);
	} catch (MySQLException $ex) {
		echo $ex;
		return;
	}

	if ($repos === false) {
		echo "Could not get repository list";
		return;
	}

	if (!gitolite_generate_config($repos, $admins)) {
		echo "Error generating config\n";
		return;
	}

	if (!gitolite_commit_config()) {
		echo "Commit config error\n";
		return;
	}

	if (!gitolite_commit_keys()) {
		echo "Commit keys error\n";
		return;
	}
}

main();


?>
