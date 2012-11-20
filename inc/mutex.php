<?php
/*
 * Ces fonctions permettent de faire de l'exclusion mutuelle de deux scripts
 * roulant en parallèle.
 */

require_once('inc/config.php');
require_once('log.php');

$mutex_depth = 0;
$mutex_file = null;

/**
 * Prend le mutex. Si le mutex est déjà pris par le processu
 *
 * @return Un référence vers le mutex à passer à mutex_release.
 */
function mutex_lock() {
	global $mutex_depth, $mutex_file;

	log_d('mutex_lock start');
	$ret = true;

	if ($mutex_depth == 0) {
		log_d('mutex_lock trying to acquire lock');
		$mutex_file = fopen(MUTEX_PATH, "w");
		$ret = flock($mutex_file, LOCK_EX);
		log_d('mutex_lock lock acquired');
	}

	$mutex_depth++;
	log_d("mutex_lock mutex_depth now at $mutex_depth");

	return $ret;
}

/**
 * Relâche le mutex.
 *
 * @return True si l'opération a réussi.
 */
function mutex_release() {
	global $mutex_depth, $mutex_file;

	log_d('mutex_release start');
	$ret = true;

	if ($mutex_depth < 1) {
		log_d('mutex_release mutex not owned');
		return false;
	}

	$mutex_depth--;
	log_d("mutex_release mutex_depth now at $mutex_depth");
	
	if ($mutex_depth == 0) {
		log_d('mutex_release trying to release lock');
		$ret = flock($mutex_file, LOCK_UN);
		fclose($mutex_file);
		$mutex_file = null;
		log_d('mutex_release lock released');
	}

	return $ret;
}

?>
