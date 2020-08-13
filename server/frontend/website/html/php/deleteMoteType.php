<?php
$q = $_POST["moteTypeID"];

$userID = $_POST["userID"];
$isMyForm = isset($_POST, $_POST["userID"], $_POST["moteTypeID"]);

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

$uploadPath = "/var/www/files";
if (!is_dir($uploadPath)) {
        if(!is_writable($uploadPath)){
            echo 0;
            return;
        }
}

$con = mysqli_connect('localhost','root',$indriya_db_pass);
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,"indriyaDB");

//delete files using mote type from file system
$jobIds = [];
$sql="SELECT * FROM files WHERE moteTypes_moteTypeID = '".$q."'";
$moteFiles = mysqli_query($con,$sql);
if (mysqli_num_rows($moteFiles)){
    while($moteFilesRow = mysqli_fetch_array($moteFiles)){
        $add = 1;
        for($i = 0; $i < sizeof($jobIds); $i++) {
            if($jobIds[$i] == $moteFilesRow['jobs_jobID'])
                $add = 0;
        }
        if($add == 1)
            $jobIds[] = $moteFilesRow['jobs_jobID'];
    }
}

for($i = 0; $i < sizeof($jobIds); $i++) {
    $sql="SELECT * FROM jobs WHERE jobID = '".$jobIds[$i]."'";
    $result = mysqli_query($con,$sql);
    if (mysqli_num_rows($result)){
        $ok = 0;
        $row = mysqli_fetch_array($result);
        $sql="SELECT * FROM files WHERE jobs_jobID = '".$row['jobID']."'";
        $jobFiles = mysqli_query($con,$sql);
        if (mysqli_num_rows($jobFiles)){
            while($filesRow = mysqli_fetch_array($jobFiles)){
                $files = glob($uploadPath.'/'.$filesRow['fileID'].'.*');
                $file = $files[0];

                if (is_file($file)){
                    if(is_writable($file))
                    {
                        $ok = unlink($file);
                    }
                }
            }
        }
        
        $sql="SELECT * FROM results WHERE jobs_jobID = '".$jobIds[$i]."'";
        $resultFiles = mysqli_query($con,$sql);
        if (mysqli_num_rows($resultFiles)){
            while($filesRow = mysqli_fetch_array($resultFiles)){
                if($filesRow['status'] == 2){
                    if (is_file($uploadPathResults.'/'.$filesRow['resultID'].'.zip')){
                        if(is_writable($uploadPathResults.'/'.$filesRow['resultID'].'.zip'))
                        {
                            unlink($uploadPathResults.'/'.$filesRow['resultID'].'.zip');
                        }
                    }
                }
            }
        }
        
        if($ok) {
            $sql="DELETE FROM jobs WHERE jobID = '".$jobIds[$i]."'";
            $result = mysqli_query($con,$sql);
        }
    }
}


//delete mote type from DB
$sql="DELETE FROM moteTypes WHERE moteTypeID = '".$q."'";
$result = mysqli_query($con,$sql);

echo $result;

mysqli_close($con);
}
?>