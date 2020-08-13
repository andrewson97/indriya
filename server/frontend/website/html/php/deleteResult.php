<?php
$q = $_POST["resultID"];

$userID = $_POST["userID"];
$isMyForm = isset($_POST, $_POST["userID"], $_POST["resultID"]);

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $q) || sizeof($q) == 0){
    #header("HTTP/1.0 404 Not Found");
    #include "404missing.php";
    #missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
    exit();
} else {
#get db pass
include "databaseHandler.php";
    $indriya_db_pass = getpsw();

$uploadPath = "/var/www/results";

$con = mysqli_connect('localhost','root',$indriya_db_pass);
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,"indriyaDB");

$sql="SELECT * FROM results WHERE resultID = '".$q."'";
$result = mysqli_query($con,$sql);
if (!mysqli_num_rows($result)){
    echo 0;
} else{
    if (is_file($uploadPath.'/'.$q.'.zip')){
        if(is_writable($uploadPath.'/'.$q.'.zip'))
        {
            $ok = unlink($uploadPath.'/'.$q.'.zip');
        }
    }
    
    $sql="DELETE FROM results WHERE resultID = '".$q."'";
    $result = mysqli_query($con,$sql);
    echo 1;
}

mysqli_close($con);
}
?>