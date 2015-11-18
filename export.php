<?php

require_once('config.php');
require_once('include/func.php');

if (!isset($_COOKIE['hash'])) {
	exit;
}

$filename = get_file_from_hash($_COOKIE['hash']);

header('Content-Disposition: attachment;filename="export.md"');

echo file_get_contents($filename);

?>