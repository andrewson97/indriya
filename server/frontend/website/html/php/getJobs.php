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

$uploadPath = "/var/www/results";

//url to check on job status
$url = 'http://localhost:5000/get_burn_results';

$con = mysqli_connect('localhost','root',$indriya_db_pass);
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,"indriyaDB");

$sql="SELECT * FROM users WHERE userID = '".$q."'";
$userResult = mysqli_query($con,$sql);
if (mysqli_num_rows($userResult)) {

    echo '<?xml version="1.0" encoding="ISO-8859-1"?>
    <data>';


    echo "<timeNowAtServer>" . gmdate("Y.m.d.H.i.s") . "</timeNowAtServer>";

    $sql="SELECT * FROM jobs WHERE users_userID = '".$q."'";
    $result = mysqli_query($con,$sql);

    $totalRunTime = 0;
    if (mysqli_num_rows($result)) {
        //found jobs in the database
        echo '<jobs>';
        while($row = mysqli_fetch_array($result)) {
            echo "<job>";
                echo "<jobID>" . $row['jobID'] . "</jobID>";
                echo "<jobName>" . $row['jobName'] . "</jobName>";
                echo "<dcube>" . $row['dcube'] . "</dcube>";

                if($row['dcube'] != 0){
                    $sql="SELECT * FROM files WHERE jobs_jobID = '".$row['jobID']."' and dcube = 1";
                    $dcubeFile = mysqli_query($con,$sql);
                    $dcubeFile_row = mysqli_fetch_array($dcubeFile);
                    echo "<dcube_filename>" . $dcubeFile_row['fileName'] . "</dcube_filename>";
                }
                
                //add files of each job
                $sql="SELECT * FROM files WHERE jobs_jobID = '".$row['jobID']."' and files.dcube = 0";
                $jobFiles = mysqli_query($con,$sql);
                if (mysqli_num_rows($jobFiles)){
                    echo "<files>";
                    while($filesRow = mysqli_fetch_array($jobFiles)){
                        echo "<file>";
                            echo "<fileID>" . $filesRow['fileID'] . "</fileID>";
                            echo "<fileName>" . $filesRow['fileName'] . "</fileName>";
                            
                            $sql="SELECT * FROM moteTypes WHERE moteTypeID = '".$filesRow['moteTypes_moteTypeID']."'";
                            $jobMoteTypes = mysqli_query($con,$sql);
                            if (mysqli_num_rows($jobMoteTypes)){
                                $moteTypesRow = mysqli_fetch_array($jobMoteTypes);
                                echo "<moteTypeID>" . $moteTypesRow['moteTypeID'] . "</moteTypeID>";
                                echo "<moteTypeName>" . $moteTypesRow['moteTypeName'] . "</moteTypeName>";
                            }
                        
                        echo "</file>";
                    }
                    echo "</files>";
                }
            
                //add results of each job
                $sql = "SELECT * FROM results inner join runtimes WHERE results.jobs_jobID = '".$row['jobID']."' and results.runtimes_runtimeID = runtimes.runtimeID order by runtimes.start desc";
                $jobResults = mysqli_query($con,$sql);
                if (mysqli_num_rows($jobResults)){
                    echo "<results>";
                    while($resultRow = mysqli_fetch_array($jobResults)){
                        $status = $resultRow['status'];
                        $endTime = $resultRow['end'];
                        
                        //if status = -1, check on start time ==>Waiting
                        if($status == -1) {
                            //check if the job is running
                            $obj = array(
                                "result_id" => $resultRow['resultID'].'',
                            );                        
                            $opts = array('http' =>
                              array(
                                  'method'  => 'POST',
                                  'header'  => 'Content-type: application/json',
                                  'content' => json_encode($obj)
                              )
                             );                        
                            $context  = stream_context_create($opts);
                            $resultU = file_get_contents($url, false, $context);
                            $resultU = str_replace("'", '"', $resultU);
                            $response = json_decode($resultU, true);
                            if(!empty($response)) {
                                //job started, check on status
                                $num_burned_motes = 0;
                                foreach ($response['job_config'] as &$mote_type_arr) {
                                    foreach($mote_type_arr as &$mote_arr){
                                        $num_burned_motes += $mote_arr['burn'];
                                    }
                                }

                                //if num_burned_motes = 0, failed => clear => set status to 2 and clear time
                                if($num_burned_motes == 0) {
                                    $sql="UPDATE runtimes set end = '".$resultRow['start']."' where runtimeID = '".$resultRow['runtimes_runtimeID']."'";
                                    $resultU = mysqli_query($con,$sql);

                                    $endTime = $resultRow['start'];

                                    $sql="UPDATE results SET status = '3' WHERE resultID = '".$resultRow['resultID']."'";
                                    $resultU = mysqli_query($con,$sql);
                                    
                                    $status = 3;
                                } else {
                                    //else, move to next step => set status to 0
                                    $sql="UPDATE results SET status = '0' WHERE resultID = '".$resultRow['resultID']."'";
                                    $resultU = mysqli_query($con,$sql);
                                    
                                    $status = 0;
                                }
                            }
                        }
                        
                        //if status = 0, check on end time ==>Running
                        if($status == 0 || $status == -1) {
                            if($resultRow['end'] <= date('Y-m-d H:i:s')) {
                                //job is ended, status = 1
                                $sql="UPDATE results SET status = '1' WHERE resultID = '".$resultRow['resultID']."'";
                                $resultU = mysqli_query($con,$sql);
                                
                                $status = 1;
                                
                                //increament run time for this user
                                $s = strtotime($resultRow['start']);
                                $e = strtotime($resultRow['end']);
                                $totalRunTime +=  round(abs($e - $s) / 60,2);
                                
                                //increament run time for mote type
                                //get mote types for the job running this result
                                $sql="select * from moteTypes inner join files on files.moteTypes_moteTypeID = moteTypes.moteTypeID inner join jobs on jobs.jobID = files.jobs_jobID and jobs.jobID = '".$row['jobID']."'";
                                $resultMoteType = mysqli_query($con,$sql);
                                while($moteTypeRow = mysqli_fetch_array($resultMoteType)){
                                    //get mote type usage
                                    $oldRunTime = $moteTypeRow['runningTime'];
                                    
                                    $total = $oldRunTime + round(abs($e - $s) / 60,2);
                                    
                                    $sql="UPDATE moteTypes SET runningTime = '".$total."' WHERE moteTypeID = '".$moteTypeRow['moteTypeID']."'";
                                    $resultU = mysqli_query($con,$sql);
                                }
                            }
                        }
                        
                        //if ststus = 1 (done, no file), check on file
                        if($status == 1) {
                            if (is_file($uploadPath.'/'.$resultRow['resultID'].'.zip')){
                                $sql="UPDATE results SET status = '2' WHERE resultID = '".$resultRow['resultID']."'";
                                $resultU = mysqli_query($con,$sql);
                                
                                $status = 2;
                            }
                        }
                        
                        //send it to client after update
                        echo "<result>";
                            echo "<resultID>" . $resultRow['resultID'] . "</resultID>";
                            echo "<start>" . $resultRow['start'] . "</start>";
                            echo "<end>" . $endTime . "</end>";
                            echo "<status>" . $status . "</status>";
                            echo "<runtimeID>" . $resultRow['runtimes_runtimeID'] . "</runtimeID>";
                        echo "</result>";
                    }
                    echo "</results>";
                }
            echo "</job>";
        }
        echo "</jobs>";
    }

    $sql="SELECT * FROM users WHERE userID = '".$q."'";
    $result = mysqli_query($con,$sql);
    $row = mysqli_fetch_array($result);

    if($totalRunTime > 0) {
        //get old user number of submissions
        $oldTotalSubmissions = $row['totalSubmissions'];
        //increase by one
        $oldTotalSubmissions += 1;

        //get old user run time
        $oldRunTime = $row['runningTime'];    
        $totalRunTime += $oldRunTime;
        
        //update running time for user
        $sql="UPDATE users SET runningTime = '".$totalRunTime."', totalSubmissions = '".$oldTotalSubmissions."' WHERE userID = '".$q."'";
        $resultU = mysqli_query($con,$sql);
    }

    echo '<user>';
    echo "<quota>" . $row['quota'] . "</quota>";
    //get from sum of running jobs ==> from results table.
    $sql="SELECT * FROM runtimes inner join results on results.runtimes_runtimeID = runtimes.runtimeID inner join jobs on jobs.jobID = results.jobs_jobID and jobs.users_userID = '".$q."'";
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
    echo '</user>';

    echo "</data>";

    mysqli_close($con);
    }
    else{
        mysqli_close($con);

        die('Could not connect: ' . mysqli_error($con));
    }
}
?>
