<?php
	function log_php($obj) {
		$log_file = "/var/log/indriya/php.log";
		$handle = fopen($log_file, 'a');
		fwrite($handle, json_encode($obj));
		fwrite($handle, "\n");
		fclose($handle);
	}
?>