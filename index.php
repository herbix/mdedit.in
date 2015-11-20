<?php

require_once('config.php');
require_once('include/func.php');
require_once('include/template.php');

define('FLAG_DEFAULT', 7);

if (!isset($_COOKIE['hash'])) {
	$hash = gen_hash();
} else {
	$hash = $_COOKIE['hash'];
	if (!preg_match('/^[a-z0-9]{32}$/', $hash)) {
		$hash = gen_hash();
	}
}

$flag = isset($_COOKIE['flag']) ? $_COOKIE['flag'] : FLAG_DEFAULT;

setcookie('hash', $hash, time() + 365 * 86400);

$filename = get_file_from_hash($hash);
$mkfile = !file_exists($filename);

if ($mkfile) {
	$text = $template;
} else {
	$text = file_get_contents($filename);
}

spl_autoload_register(function($class){
	require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim('include\\'.$class, '\\')).'.php';
});

use \Michelf\MarkdownExtra;

$parser = new MarkdownExtra;
$html = $parser->transform($text);
$html = str_replace('<table>', '<table class="table">', $html);

?>
<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>mdedit.in - Just Another Online Markdown Editor</title>
		<?php css('css/bootstrap.min.css') ?>
		<?php css('css/bootstrap-theme.min.css') ?>
		<?php css('css/codemirror.css') ?>
		<?php css('css/style.css') ?>
		<?php css('css/font-awesome.min.css') ?>
		<?php js('js/jquery-1.11.3.min.js'); ?>
		<?php js('js/bootstrap.min.js'); ?>
		<?php js('js/codemirror.js'); ?>
		<?php js('js/codemirror-markdown.js'); ?>
		<?php js('js/js-markdown-extra.js'); ?>
		<?php js('js/jquery.cookie.js'); ?>
		<?php js('js/script.js'); ?>
		<script type="text/javascript">
			var FLAG_DEFAULT = <?php echo FLAG_DEFAULT; ?>
		</script>
    </head>
	<body>
		<div class="content-outer">
			<div class="content" style="padding-right:4px">
				<textarea name="content" id="text-input" class="form-control" style="height:100%;width:100%"><?php echo $text; ?></textarea>
			</div>
			<div class="content" style="padding-left:4px">
				<div id="preview">
					<?php
						echo $html;
					?>
				</div>
			</div>
		</div>
		<nav class="navbar navbar-default navbar-fixed-bottom">
			<div class="container-fluid">
				<div id="config" class="btn-group" data-toggle="buttons">
					<label class="btn btn-default navbar-btn<?php if ($flag & 1) echo ' active'; ?>" for="enable-preview" title="Enable Preview">
						<input id="enable-preview" type="checkbox" <?php if ($flag & 1) echo 'checked'; ?> />
						<i class="fa fa-eye"></i>
					</label>
					<label class="btn btn-default navbar-btn<?php if ($flag & 2) echo ' active'; ?>" for="scroll-sync" title="Scroll Sync">
						<input id="scroll-sync" type="checkbox" <?php if ($flag & 2) echo 'checked'; ?> />
						<i class="fa fa-columns"></i>
					</label>
					<label class="btn btn-default navbar-btn<?php if ($flag & 4) echo ' active'; ?>" for="line-wrapping" title="Line Wrapping">
						<input id="line-wrapping" type="checkbox" <?php if ($flag & 4) echo 'checked'; ?> />
						<i class="fa fa-paragraph"></i>
					</label>
				</div>
				<a class="btn btn-primary navbar-btn" onclick="updateToServer(false)" href="export.php" title="Download">
					<i class="fa fa-cloud-download"></i>
				</a>
				<a class="btn btn-danger navbar-btn" href="discard.php" title="Discard">
					<i class="fa fa-trash"></i>
				</a>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-info-sign"></i></a>
						<ul class="dropdown-menu">
							<li><a href="#">MDEdit.in</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="#" style="color:#aaa">Thanks:</a></li>
							<li><a href="http://getbootstrap.com">Bootstrap</a></li>
							<li><a href="http://codemirror.net/">CodeMirror</a></li>
							<li><a href="http://fortawesome.github.io/Font-Awesome/">Font Awesome</a></li>
							<li><a href="https://github.com/tanakahisateru/js-markdown-extra">Js-Markdown-Extra</a></li>
							<li><a href="https://michelf.ca/projects/php-markdown/">PHP Markdown</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</nav>
	</body>
</html>