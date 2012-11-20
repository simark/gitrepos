<?php

/* Fonction pour générer un salt blowfish */
function gen_blowfish_salt() {
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./";
	$len = strlen($chars);
	
	$salt = "$2a$04$";
	
	for ($i = 0; $i < 22; $i++) {
		$salt .= $chars[rand() % $len];
	}

	$salt .= "$";

	return $salt;
}

function crypt_password($password) {
	$salt = gen_blowfish_salt();
	
	$hash = crypt($password, $salt);
	
	return $hash;
}


function check_password($password, $hash) {
	$hash2 = crypt($password, $hash);
	
	return $hash2 === $hash;
}

?>
