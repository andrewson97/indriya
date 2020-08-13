<?php
$q = $_POST["userID"];

$isMyForm = isset($_POST, $_POST["userID"]);

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || sizeof($q) == 0){
    #header("HTTP/1.0 404 Not Found");
    #include "404missing.php";
    #missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
    exit();
} else {
#get db pass
include "databaseHandler.php";
    $indriya_db_pass = getpsw();

header('Content-Type: text/xml');
header("Cache-Control: no-cache, must-revalidate");
//A date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$con = mysqli_connect('localhost','root',$indriya_db_pass);
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,"indriyaDB");

$sql="SELECT * FROM clusters";
$result = mysqli_query($con,$sql);

if (!mysqli_num_rows($result)) {
    echo null;
} else {
    echo '<?xml version="1.0" encoding="ISO-8859-1"?>
    <clusters>';
    while($row = mysqli_fetch_array($result)) {
        echo "<cluster>";
        echo "<clusterName>" . $row['clusterName'] . "</clusterName>";
        echo "<clusterID>" . $row['clusterID'] . "</clusterID>";
        echo "</cluster>";
    }
    echo "</clusters>";
}

mysqli_close($con);
}
?>