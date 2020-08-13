<?php
$q = $_GET['r'];

if(sizeof($q) == 0){
	#header("HTTP/1.0 404 Not Found");
	#include "404missing.php";
    #missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
	exit();
} else {
    $uploadPath = "/var/www/results";
    
    list($fileName, $jobName, $resultNumber) = explode("@", $q);

    $filePath = $uploadPath.'/'.$fileName.'.zip';

    if (is_file($filePath)){
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=".$jobName.'_result_'.$resultNumber.'.zip');
        readfile($filePath);
    }
}
?>