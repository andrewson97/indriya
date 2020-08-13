<?php
$q = $_POST["userID"];
$physical_id = $_POST['physical_id'];
$virtual_id = $_POST['virtual_id'];
$gateway_ip = $_POST['gateway_ip'];
$gateway_ttyid = $_POST['gateway_ttyid'];
$gateway_port = $_POST['gateway_port'];
$coordinates = $_POST['coordinates'];
$moteTypes_moteTypeID = $_POST['moteTypeID'];
$clusters_clusterID = $_POST['clusterID'];

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["physical_id"], $_POST["virtual_id"], $_POST["gateway_ip"], $_POST["gateway_ttyid"], $_POST["gateway_port"], $_POST["coordinates"], $_POST["moteTypeID"], $_POST["clusterID"]);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $physical_id) || !preg_match("/[A-Za-z0-9.\-@]+/", $virtual_id) || !preg_match("/[A-Za-z0-9.\-@]+/", $gateway_ip) || !preg_match("/[A-Za-z0-9.\-@]+/", $gateway_ttyid) || !preg_match("/[A-Za-z0-9.\-@]+/", $gateway_port) || !preg_match("/[A-Za-z0-9.\-@]+/", $coordinates) || !preg_match("/[A-Za-z0-9.\-@]+/", $moteTypeID) || !preg_match("/[A-Za-z0-9.\-@]+/", $clusterID) || sizeof($q) || sizeof($physical_id) == 0 || sizeof($virtual_id) == 0 || sizeof($gateway_ip) == 0 || sizeof($gateway_ttyid) == 0 || sizeof($gateway_port) == 0 || sizeof($coordinates) == 0 || sizeof($moteTypes_moteTypeID) == 0 || sizeof($clusters_clusterID) == 0){
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

	$sql="INSERT INTO motes (physical_id, virtual_id, gateway_ip, gateway_ttyid, gateway_port, coordinates, moteTypes_moteTypeID, clusters_clusterID, status) VALUES('".$physical_id."', '".$virtual_id."', '".$gateway_ip."', '".$gateway_ttyid."', '".$gateway_port."', '".$coordinates."', '".$moteTypes_moteTypeID."', '".$clusters_clusterID."', '-1')";
	$result = mysqli_query($con,$sql);
	if($result)
	    echo $con->insert_id;
	else
	    echo -1;

	mysqli_close($con);
}
?>