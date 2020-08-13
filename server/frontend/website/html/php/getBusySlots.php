<?php
$q = $_POST["userID"];
$jobID = $_POST["jobID"];

$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobID"]);

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $jobID) || sizeof($q) == 0 || sizeof($jobID) == 0){
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

$sql="SELECT * FROM users WHERE userID = '".$q."'";
$userResult = mysqli_query($con,$sql);
if (mysqli_num_rows($userResult)) {
    $sql="SELECT * FROM jobs WHERE jobID = '".$jobID."'";
    $result = mysqli_query($con,$sql);
    if (mysqli_num_rows($result)) {
        echo '<?xml version="1.0" encoding="ISO-8859-1"?>
        <job>';

        // date("Y/m/d H:i:s")
        echo "<timeNowAtServer>" . gmdate("Y.m.d.H.i.s") . "</timeNowAtServer>";

        $row = mysqli_fetch_array($result);
        echo "<jobName>" . $row['jobName'] . "</jobName>";
        $dcube = $row['dcube'];

        //get from sum of running jobs ==> from results table.
        $sql="SELECT * FROM runtimes inner join results on results.runtimes_runtimeID = runtimes.runtimeID inner join jobs on jobs.jobID = results.jobs_jobID and jobs.users_userID = '".$row['users_userID']."'";
        $usedQuota = 0;
        $resultR = mysqli_query($con,$sql);
        if (mysqli_num_rows($resultR)) {
            while($rowR = mysqli_fetch_array($resultR)) {
                if($rowR['status'] < 1){
                    $s = strtotime($rowR['start']);
                    $e = strtotime($rowR['end']);
                    $usedQuota +=  round(abs($e - $s) / 60,2);
                }
            }
        }
        echo "<usedQuota>" . $usedQuota . "</usedQuota>";

        //get all motes for the selected job
        $sql="select file_mote.motes_moteID from file_mote inner join files on files.fileID = file_mote.files_fileID inner join jobs on jobs.jobID = files.jobs_jobID and jobs.jobID = '".$jobID."'";
        $result = mysqli_query($con,$sql);
        $jobMotes = array();
        $i = 0;
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
                        while($timeRow = mysqli_fetch_array($resultTime)) {
                            if($timeRow['end'] > date('Y-m-d H:i:s')) {
                                echo "<start>" . $timeRow['start'] . "</start>";
                                echo "<end>" . $timeRow['end'] . "</end>";
                            }
                        }
                    }
                    break;
                }
            }    
        }

        if($dcube != 0){
            // get runtimes for all jobs with dcube
            $sql="select * from runtimes inner join jobs on runtimes.jobs_jobID = jobs.jobID and jobs.dcube != 0";
            $resultTime = mysqli_query($con,$sql);
            if (mysqli_num_rows($resultTime)) {
                while($timeRow = mysqli_fetch_array($resultTime)) {
                    if($timeRow['end'] > date('Y-m-d H:i:s')) {
                        echo "<start>" . $timeRow['start'] . "</start>";
                        echo "<end>" . $timeRow['end'] . "</end>";
                    }
                }
            }
        }

        echo "</job>";
    }

    mysqli_close($con);
}
else
{
    mysqli_close($con);
    die('Could not connect: ' . mysqli_error($con));
}
}
?>
