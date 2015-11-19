<?php

require_once('config.php');
require_once('include/func.php');
require_once('include/template.php');

if (!isset($_COOKIE['hash']) || !isset($_POST['content'])) {
	exit;
}

$filename = get_file_from_hash($_COOKIE['hash']);

file_put_contents('test.md', $template);

if ($template != $_POST['content']) {
	mkdir(dirname($filename), 0777, true);
	file_put_contents($filename, $_POST['content']);
}

?>