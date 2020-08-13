<?php
$userID = $_POST["userID"];
$q = $_POST["jobID"];

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobID"]);

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
$uploadPathResults = "/var/www/results";
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

$sql="SELECT * FROM jobs WHERE jobID = '".$q."'";
$result = mysqli_query($con,$sql);
if (!mysqli_num_rows($result)){
    echo 0;
} else{
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
    
    $sql="SELECT * FROM results WHERE jobs_jobID = '".$row['jobID']."'";
    $resultFiles = mysqli_query($con,$sql);
    if (mysqli_num_rows($resultFiles)){
        $url = 'http://localhost:5000/cancel_job';
        while($filesRow = mysqli_fetch_array($resultFiles)){
            //report cancel job if waiting or running
            if($filesRow['status'] < 1){
                $obj = array(
                    "result_id" => $filesRow['resultID'].'',
                );
            
                $opts = array('http' =>
                  array(
                      'method'  => 'POST',
                      'header'  => 'Content-type: application/json',
                      'content' => json_encode($obj)
                  )
                 );
                
                $context  = stream_context_create($opts);
                $result = file_get_contents($url, false, $context);

            }

            //delete zip file
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
        $sql="DELETE FROM jobs WHERE jobID = '".$q."'";
        $result = mysqli_query($con,$sql);
    }
    echo $ok;
}

mysqli_close($con);
}
?>