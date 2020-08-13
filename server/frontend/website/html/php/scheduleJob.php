<?php
$userID = $_POST['userID'];
$jobID = $_POST['jobID'];
$s = $_POST['ts'];
$e = $_POST['te'];

$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobID"], $_POST["ts"], $_POST["te"]);

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $jobID) || sizeof($userID) == 0 || sizeof($jobID) == 0 || sizeof($s) == 0 || sizeof($e) == 0){
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

//check if selected time is free
//get all motes for the selected job
$sql="select file_mote.motes_moteID from file_mote inner join files on files.fileID = file_mote.files_fileID inner join jobs on jobs.jobID = files.jobs_jobID and jobs.jobID = '".$jobID."'";
$result = mysqli_query($con,$sql);
$jobMotes = array();
while($motesRow = mysqli_fetch_array($result)){
    $jobMotes[] = $motesRow['motes_moteID'];
}

$sql="SELECT * FROM jobs";
$result = mysqli_query($con,$sql);
while($row = mysqli_fetch_array($result)) {
    //get all motes for each job
    $sql="select file_mote.motes_moteID from file_mote inner join files on files.fileID = file_mote.files_fileID inner join jobs on jobs.jobID = files.jobs_jobID and jobs.jobID = '".$row['jobID']."'";
    $resultMotes = mysqli_query($con,$sql);
    while($motesRow = mysqli_fetch_array($resultMotes)){
        if(array_search($motesRow['motes_moteID'], $jobMotes) !== false) {
            //get all run time for this job and go to next job
            $sql="select * from runtimes where jobs_jobID = '".$row['jobID']."'";
            $resultTime = mysqli_query($con,$sql);
            if (mysqli_num_rows($resultTime)) {
                $overlapping = 0;
                while($timeRow = mysqli_fetch_array($resultTime)) {
                    if((date('Y-m-d H:i:s', $s) < $timeRow['end']) && (date('Y-m-d H:i:s', $e) > $timeRow['start']))
                        $overlapping++;
                }
            }
            break;
        }
    }

    // check overlapping for dcube jobs
    if($row['dcube'] != 0){
        $sql="select * from runtimes inner join jobs on runtimes.jobs_jobID = jobs.jobID and jobs.dcube != 0";
        $resultTime = mysqli_query($con,$sql);
        if (mysqli_num_rows($resultTime)) {
            while($timeRow = mysqli_fetch_array($resultTime)) {
                if((date('Y-m-d H:i:s', $s) < $timeRow['end']) && (date('Y-m-d H:i:s', $e) > $timeRow['start']))
                    $overlapping++;
            }
        }
    }

    if($overlapping != 0)
        break;
}

if($overlapping == 0) {
    //add time to DB
    $sql="INSERT INTO runtimes (start, end, jobs_jobID) VALUES('".date('Y-m-d H:i:s', $s)."', '".date('Y-m-d H:i:s', $e)."', '".$jobID."')";
    $result = mysqli_query($con,$sql);
    $runtimeID = $con->insert_id;

    //add result to DB
    $sql="INSERT INTO results (status, jobs_jobID, runtimes_runtimeID) VALUES('-1', '".$jobID."', '".$runtimeID."')";
    $result = mysqli_query($con,$sql);
    $resultID = $con->insert_id;

    // get dcube about job
    $sql="SELECT * FROM jobs WHERE jobID = '".$jobID."'";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_array($result);
    $dcube = $row['dcube'];
    if($dcube != 0){
        $sql="select * from files where jobs_jobID = '".$jobID."' and files.dcube = 1";
        $result = mysqli_query($con,$sql);
        $row = mysqli_fetch_array($result);
        $fileName = $row['fileName'];
        $info = new SplFileInfo($fileName);
        $dcube_file = $row['fileID'].'.'.$info->getExtension();
    }
    
    if($dcube == 2 || $dcube == 0){
        //send to gatway
        //$url = 'http://localhost:8080/php/test.php';
        $url = 'http://localhost:5000/new_job';
        
        //build up job_config array
        $job_config_arr = array();
        //fileID, motetype, motes
        $sql="select * from files where jobs_jobID = '".$jobID."' and files.dcube = 0";
        $result = mysqli_query($con,$sql);
        while($fileRow = mysqli_fetch_array($result)){
            //file array
            $file_arr = array();
            
            $fileName = $fileRow['fileName'];
            $info = new SplFileInfo($fileName);
            
            //file
            $file_arr["binary_file"] = $fileRow['fileID'].'.'.$info->getExtension();

            //mote type
            $sql="select * from moteTypes where moteTypeID = '".$fileRow['moteTypes_moteTypeID']."'";
            $resultMoteType = mysqli_query($con,$sql);
            $moteTypeRow = mysqli_fetch_array($resultMoteType);

            $file_arr["type"] = $moteTypeRow['moteTypeName'];
            
            $mote_arr = array();
            $sql="select * from motes inner join file_mote on motes.moteID = file_mote.motes_moteID and file_mote.files_fileID = '".$fileRow['fileID']."'";
            $resultMotes = mysqli_query($con,$sql);
            while($moteRow = mysqli_fetch_array($resultMotes)) {
                $mote_arr[] = $moteRow['virtual_id'];
            }
            
            $file_arr['mote_list'] = $mote_arr;
            
            $job_config_arr[] = $file_arr;
        }
        
        
        $obj = array(
            "user" => $userID,
            "result_id" => $resultID.'',
            "job_config" => $job_config_arr,
            "time" => array(
                "from" => $s,
                "to" => $e
            ),
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
        
        $result = str_replace("'", '"', $result);
        
        $response = json_decode($result, true);

        //error, delete schedule
        if($response['result'] == 0) {
            mysqli_select_db($con,"indriyaDB");
            $sql="DELETE FROM runtimes WHERE runtimeID = '".$runtimeID."'";
            $result = mysqli_query($con,$sql);
        } else{
            // schedule dcube
            if($dcube == 2){
                $url = 'http://localhost:5000/new_dcube_job';

                $obj = array(
                    "result_id" => $resultID.'',
                    "time_to" => $e,
                    "binary_file" => $dcube_file,
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
        }
        /* else {
            //update total submissions for user
            $sql="SELECT * FROM users WHERE userID = '".$userID."'";
            $result = mysqli_query($con,$sql);
            $row = mysqli_fetch_array($result);
            //get old user number of submissions
            $oldTotalSubmissions = $row['totalSubmissions'];
            //increase by one
            $oldTotalSubmissions += 1;
            //update total submissions for user
            $sql="UPDATE users SET totalSubmissions = '".$oldTotalSubmissions."' WHERE userID = '".$userID."'";
            $resultU = mysqli_query($con,$sql);
        }*/

        echo $response['result'];
    } else if ($dcube == 1){
        $url = 'http://localhost:5000/new_dcube_job';

        $obj = array(
            "result_id" => $resultID.'',
            "time_to" => $e,
            "binary_file" => $dcube_file,
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

	echo 1;
    }
} else {
    echo -1;
}

mysqli_close($con);
}
?>
