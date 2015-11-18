<?php

function css($file) {
	?><link href="<?php echo LINK_BASE, $file ?>" rel="stylesheet" media="screen" />
<?php
}

function js($file) {
	?><script type="text/javascript" src="<?php echo LINK_BASE, $file ?>"></script>
<?php
}

function get_file_from_hash($hash) {
	return dirname(dirname(__FILE__)).'/data/'.substr($hash, 0, 2).'/'.$hash;
}

?>