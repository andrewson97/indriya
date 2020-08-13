<?php
	#include all php files
	$php_path = '/var/www/php/';
	set_include_path($php_path);

	#get the called file
	$func = $_POST['func'];
	echo 
?>