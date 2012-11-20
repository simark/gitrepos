<?php

require_once('config.php');

/**
 * Retourne le chemin absolu du journal.
 *
 * @return Le chemin absolu du journal
 */
function _get_real_log_path() {
	$path = LOG_PATH;
	
	if (strlen($path) == 0) {
		return $path;
	}
	
	if ($path[0] == '/') {
		return $path;
	}
	
	if (isset($_SERVER['PWD'])) {
		return $_SERVER['PWD']."/".$path;
	} else {
		return dirname($_SERVER['SCRIPT_FILENAME'])."/".$path;
	}
	
}

/**
 * Vide le fichier de journal lorsqu'appelée la première fois.
 */
function _init_log() {
	static $done = false;

	if (!$done) {
		$path = _get_real_log_path();
		file_put_contents($path, "\n", FILE_APPEND);
		$done = true;
	}
} 

function _get_uniqid() {
	static $id;
	
	if (!isset($id)) {
		$id = uniqid();
	}
	
	return $id;
}

/**
 * Écrit une entrée dans le journal.
 *
 * @param prefix Préfixe de l'entrée
 * @param msg Texte de l'entrée
 */
function _log($prefix, $msg) {
	_init_log();
	
	$path = _get_real_log_path();

	if (is_array($msg)) {
		foreach ($msg as $line) {
			_log($prefix, $line);
		}
	} else {
		$bt = debug_backtrace();
		$fn_name = "";

		file_put_contents($path, _get_uniqid() . " " . date('r') . " " . $prefix . " " . $fn_name . " " . $msg . "\n", FILE_APPEND);
	}
}

function _get_caller() {
	$bt = debug_backtrace();

	$idx = 1;

	$file = basename($bt[$idx]['file']);
	$line = $bt[$idx]['line'];
	$fn = $bt[$idx+1]['function'];
	return "$file:$line:$fn";
}

function log_d($msg) {
	if (LOG_LEVEL >= LOG_D) {
		_log("D " . _get_caller() . ":", $msg);
	}
}

function log_w($msg) {
	if (LOG_LEVEL >= LOG_W) {
		_log("W" . _get_caller() . ":", $msg);
	}
}

function log_e($msg) {
	if (LOG_LEVEL >= LOG_E) {
		_log("E" . _get_caller() . ":", $msg);
	}
}

?>
