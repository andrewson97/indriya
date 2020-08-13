<?php
$userID = $_POST['userID'];
$quota = $_POST['quota'];
$admin = $_POST['admin'];
$allMotes = $_POST['allMotes'];
$details = $_POST['details'];

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["quota"], $_POST["admin"], $_POST["allMotes"], $_POST["details"]);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $quota) || !preg_match("/[A-Za-z0-9.\-@]+/", $admin) || !preg_match("/[A-Za-z0-9.\-@]+/", $allMotes) || !preg_match("/[A-Za-z0-9.\-@]+/", $details) || sizeof($userID) == 0 || sizeof($quota) == 0 || sizeof($admin) == 0 || sizeof($allMotes) == 0 || sizeof($details) == 0){
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

$sql="UPDATE users SET quota = '".$quota."', admin = '".$admin."', allMotes = '".$allMotes."', details = '".$details."' WHERE userID = '".$userID."'";
$result = mysqli_query($con,$sql);

echo $result;

mysqli_close($con);
}
?>