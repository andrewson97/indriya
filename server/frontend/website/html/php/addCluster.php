<?php
$q = $_POST["userID"];
$clusterName = $_POST['clusterName'];
$floorLevel = $_POST['floorLevel'];

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["clusterName"], $_POST["floorLevel"]);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $clusterName) || !preg_match("/[A-Za-z0-9.\-@#]+/", $floorLevel) || sizeof($q) == 0 || sizeof($clusterName) == 0 || sizeof($floorLevel) == 0){
	#header('HTTP/1.0 404 Not Found');
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

		$sql="INSERT INTO clusters (clusterName, floorLevel) VALUES('".$clusterName."', '".$floorLevel."')";
		$result = mysqli_query($con,$sql);
		if($result)
		    echo $con->insert_id;
		else
		    echo -1;

		mysqli_close($con);
	} else{
		mysqli_close($con);

		die('Could not connect: ' . mysqli_error($con));
	}
}
?>