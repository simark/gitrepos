<?php

function get_post($key) {
	return isset($_POST[$key]) ? $_POST[$key] : false;
}

function get_get($key) {
	return isset($_GET[$key]) ? $_GET[$key] : false;
}

?>