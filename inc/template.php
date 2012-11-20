<?php

require_once('smarty3/Smarty.class.php');
require_once('inc/session.php');

function render_template($template_name, $vars = array()) {
	$smarty = new Smarty;
	$smarty->setTemplateDir('templates');

	$smarty->assign('username', session_username());
	
	foreach ($vars as $key => $val) {
		$smarty->assign($key, $val);
	}
	
	return $smarty->fetch("$template_name.tpl");
}

?>
