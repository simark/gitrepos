<?php
/*
	Ce script est utile pour générer des hash de mots de passe dans le but de
	les remplacer à la main dans la BD.
*/
require_once('inc/password.php');

if ($argc < 2) {
	echo "Specify a password." . PHP_EOL;
	return 1;
}

for ($i = 1; $i < $argc; $i++) {
	$pw = $argv[$i];
	echo "$pw : " . crypt_password($pw) . PHP_EOL;
}


return 0;

?>
