<?php

require_once('config.php');
require_once('include/func.php');

if (!isset($_COOKIE['hash'])) {
	exit;
}

$filename = get_file_from_hash($_COOKIE['hash']);
unlink($filename);
rmdir(dirname($filename));

setcookie('hash', null, -1);

header('Location: .');

?>