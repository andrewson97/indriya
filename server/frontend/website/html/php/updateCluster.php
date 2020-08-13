<?php
$q = $_POST["userID"];
$clusterID = $_POST["clusterID"];
$clusterName = $_POST["clusterName"];
$floorLevel = $_POST["floorLevel"];

$isMyForm = isset($_POST, $_POST["userID"], $_POST["clusterID"], $_POST["clusterName"], $_POST["floorLevel"]);

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $clusterID) || !preg_match("/[A-Za-z0-9.\-@]+/", $clusterName) || !preg_match("/[A-Za-z0-9.\-@#]+/", $floorLevel) || sizeof($clusterID) == 0 || sizeof($clusterName) == 0 || sizeof($floorLevel) == 0){
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

$sql="UPDATE clusters SET clusterName = '".$clusterName."', floorLevel = '".$floorLevel."' WHERE clusterID = '".$clusterID."'";
$result = mysqli_query($con,$sql);

echo $result;

mysqli_close($con);
}
?>