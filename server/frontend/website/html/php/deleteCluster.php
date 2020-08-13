<?php
$userID = $_POST["userID"];
$q = $_POST["clusterID"];

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["clusterID"]);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $q) || sizeof($q) == 0){
	#header("HTTP/1.0 404 Not Found");
	#include "404missing.php";
	#missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
	exit();
} else {
	#get db pass
	include "databaseHandler.php";
	$indriya_db_pass = getpsw();

	$con = mysqli_connect('localhost','root',$indriya_db_pass);
	if (!$con) {
	    die('Could not connect: ' . mysqli_error($con));
	}

	mysqli_select_db($con,"indriyaDB");
	$sql="DELETE FROM clusters WHERE clusterID = '".$q."'";
	$result = mysqli_query($con,$sql);

	echo $result;

	mysqli_close($con);
}
?>