<?php

require_once('config.php');
require_once('include/func.php');
require_once('include/template.php');

if (!isset($_COOKIE['hash']) || !isset($_POST['content'])) {
	exit;
}

$hash = $_COOKIE['hash'];

if (!preg_match('/^[a-z0-9]{32}$/', $hash)) {
	setcookie('hash', gen_hash(), time() + 365 * 86400);
	exit;
}

$filename = get_file_from_hash($hash);

if ($template != $_POST['content']) {
	mkdir(dirname($filename), 0777, true);
	file_put_contents($filename, $_POST['content']);
}

?>