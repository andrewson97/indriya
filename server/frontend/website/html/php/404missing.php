<?php
	function missing404($domain, $url) {
		echo "<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1>";
		echo "<p>The requested URL ". $url ." was not found on this server.</p>";
		#echo "<hr><address>Apache/2.4.18 (Ubuntu) Server at ". $domain ." Port 80</address></body></html>";
	}
?>
