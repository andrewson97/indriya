<?php
$q = $_POST["userID"];
$moteTypeID = $_POST["moteTypeID"];
$moteTypeName = $_POST["moteTypeName"];

$isMyForm = isset($_POST, $_POST["userID"], $_POST["moteTypeID"], $_POST["moteTypeName"]);

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $moteTypeID) || !preg_match("/[A-Za-z0-9.\-@]+/", $moteTypeName) || sizeof($q) == 0 || sizeof($moteTypeID) == 0 || sizeof($moteTypeName) == 0){
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

	$sql="SELECT * FROM users WHERE userID = '".$q."'";
    $userResult = mysqli_query($con,$sql);
    if (mysqli_num_rows($userResult)) {

		$sql="UPDATE moteTypes SET moteTypeName = '".$moteTypeName."' WHERE moteTypeID = '".$moteTypeID."'";
		$result = mysqli_query($con,$sql);

		echo $result;

		mysqli_close($con);
	}
	else{
		mysqli_close($con);
		
		die('Could not connect: ' . mysqli_error($con));
	}
}
?>