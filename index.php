<?php

require_once('config.php');
require_once('include/func.php');

$mkfile = false;

if (!isset($_COOKIE['hash'])) {
	$hash = md5(openssl_random_pseudo_bytes(16));
	$mkfile = true;
} else {
	$hash = $_COOKIE['hash'];
}

setcookie('hash', $hash, time() + 365 * 86400);

$filename = get_file_from_hash($hash);
$mkfile = !file_exists($filename);

if ($mkfile) {
	mkdir(dirname($filename), 0777, true);
	touch($filename);
}

$text = file_get_contents($filename);

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
		<?php js('js/jquery-1.11.3.min.js'); ?>
		<?php js('js/bootstrap.min.js'); ?>
		<?php js('js/codemirror.js'); ?>
		<?php js('js/codemirror-markdown.js'); ?>
		<?php js('js/js-markdown-extra.js'); ?>
		<script>
		$(function() {
			var input = $('#text-input');
			var preview = $('#preview');
			var cm = CodeMirror.fromTextArea(input.get(0), {
				lineNumbers: true,
				mode: "markdown",
				lineWrapping: true
			});
			
			var scrollsync = $('#scroll-sync');
			var enablepreview = $('#enable-preview');
			
			cm.on('change', function() {
				if (enablepreview.prop('checked')) {
					cm.save();
					preview.html(Markdown(input.val()).replace(/<table>/g, '<table class="table">'));
				}
			});
			cm.on('scroll', function() {
				if (scrollsync.prop('checked')) {
					preview.scrollTop(cm.getScrollInfo().top);
				}
			});
			
			$('#line-wrapping').click(function() {
				cm.setOption('lineWrapping', $('#line-wrapping').prop('checked'));
			});
			
			$(window).bind('beforeunload', function(e) {
				if (!updateToServer(false)) {
					e.preventDefault();
				}
			});
			
			setInterval(60000, updateToServer);
			
			function updateToServer(async) {
				if ('undefined' === typeof async) { async = true; }
				var success = false;
				cm.save();
				$.ajax({
					type: 'post',
					url: 'update.php',
					data: {content: input.val()},
					async: async,
					timeout: 3000,
					success: function() { success = true; }
				});
				return success;
			}
		});		
		</script>
    </head>
	<body>
		<input type="hidden" name="origin" value="<?php echo $hash; ?>"/>
		<nav class="navbar navbar-default navbar-fixed-bottom">
			<div class="container-fluid">
				<label class="checkbox-inline btn" for="enable-preview">
					<input id="enable-preview" type="checkbox" checked />Enable Preview
				</label>
				<label class="checkbox-inline btn" for="scroll-sync">
					<input id="scroll-sync" type="checkbox" checked />Scroll Sync
				</label>
				<label class="checkbox-inline btn" for="line-wrapping">
					<input id="line-wrapping" type="checkbox" checked />Line Wrapping
				</label>
				<a class="btn btn-primary navbar-btn" style="margin-right:10px;" href="export.php">
					<i class="glyphicon glyphicon-export"></i> Export
				</a>
				<a class="btn btn-danger navbar-btn" style="margin-right:10px;" href="discard.php">
					<i class="glyphicon glyphicon-trash"></i> Discard
				</a>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="glyphicon glyphicon-info-sign"></i></a>
						<ul class="dropdown-menu">
							<li><a href="#">MDEdit.in</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="#" style="color:#aaa">Thanks To:</a></li>
							<li><a href="http://getbootstrap.com">Bootstrap</a></li>
							<li><a href="http://codemirror.net/">CodeMirror</a></li>
							<li><a href="https://github.com/tanakahisateru/js-markdown-extra">Js-Markdown-Extra</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</nav>
		<div class="content" style="padding-right:4px;">
			<textarea name="content" id="text-input" class="form-control" style="height:100%;width:100%"><?php echo $text; ?></textarea>
		</div>
		<div class="content" style="padding-left:4px;">
			<div id="preview">
				<?php
					echo $html;
				?>
			</div>
		</div>
	</body>
</html>