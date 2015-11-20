<?php

require_once('config.php');
require_once('include/func.php');

if (!isset($_COOKIE['hash'])) {
	exit;
}

$hash = $_COOKIE['hash'];

if (!preg_match('/^[a-z0-9]{32}$/', $hash)) {
	setcookie('hash', gen_hash(), time() + 365 * 86400);
	exit;
}

$filename = get_file_from_hash($hash);
unlink($filename);
rmdir(dirname($filename));

setcookie('hash', null, -1);

header('Location: .');

?>