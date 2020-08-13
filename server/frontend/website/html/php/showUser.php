<?php
$q = $_POST["searchUserID"];

$isMyForm = isset($_POST, $_POST["searchUserID"]);

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

// lookup all hints from array if $q is different from "" 
if ($q !== "") {
    $q = strtolower($q);
    
    $con = mysqli_connect('localhost','root',$indriya_db_pass);
    if (!$con) {
        die('Could not connect: ' . mysqli_error($con));
    }

    mysqli_select_db($con,"indriyaDB");
    $sql="SELECT * FROM users WHERE INSTR(userID, '".$q."') > 0";
    $result = mysqli_query($con,$sql);
    if (!mysqli_num_rows($result)) {
        echo "not found";        
    }
    else{
        $row = mysqli_fetch_array($result);        
        //total jobs
        //$sqlJobs="SELECT * FROM jobs WHERE users_userID = '".$row['userID']."'";
        //$userJobs = mysqli_query($con,$sqlJobs);

        $arr = array(
            "userID"=>$row['userID'].'',
            "quota"=>$row['quota'].'',
            "admin"=>$row['admin'].'',
            "allMotes"=>$row['allMotes'].'',
            "totalSubmissions"=>$row['totalSubmissions'].'',
            //mysqli_num_rows($userJobs).'',
            "runningTime"=>$row['runningTime'].'',
            "create_date"=>$row['create_date'].'',
            "details"=>$row['details'].''
        );
        echo json_encode($arr);
    }
    
    mysqli_close($con);
}
}
?>