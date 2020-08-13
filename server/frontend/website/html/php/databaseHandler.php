<?php
	function getpsw() {
		$file_handle = fopen("/var/www/conf.d/indriya_db_pass", "r");
		$indriya_db_pass = fgets($file_handle, 28);
		fclose($file_handle);

		return $indriya_db_pass;
	}
?>