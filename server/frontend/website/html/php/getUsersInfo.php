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

$sql="SELECT * FROM users";
$result = mysqli_query($con,$sql);

if (!mysqli_num_rows($result)) {
    //no jobs for this user
    echo null;
} else{
    //found jobs in the database    
    echo '<?xml version="1.0" encoding="ISO-8859-1"?>
    <users>';
    while($row = mysqli_fetch_array($result)) {
        echo "<user>";
            echo "<userID>" . $row['userID'] . "</userID>";
            echo "<runningTime>" . $row['runningTime'] . "</runningTime>";
            echo "<totalSubmissions>" . $row['totalSubmissions'] . "</totalSubmissions>";
            echo "<allMotes>" . $row['allMotes'] . "</allMotes>";
            echo "<quota>" . $row['quota'] . "</quota>";
            echo "<admin>" . $row['admin'] . "</admin>";
            echo "<create_date>" . $row['create_date'] . "</create_date>";
            echo "<details>" . $row['details'] . "</details>";

            /*
            $sql="SELECT * FROM jobs WHERE users_userID = '".$row['userID']."'";
            $jobs = mysqli_query($con,$sql);
            if (mysqli_num_rows($jobs)){
                echo "<jobs>";
                while($jobsRow = mysqli_fetch_array($jobs)){
                    echo "<job>";
                    echo "<MoteTypes>";
                    $sql="SELECT * FROM files WHERE jobs_jobID = '".$jobsRow['jobID']."'";
                    $jobFiles = mysqli_query($con,$sql);
                    if (mysqli_num_rows($jobFiles)){
                            while($filesRow = mysqli_fetch_array($jobFiles)){
                                $sql="SELECT * FROM moteTypes WHERE moteTypeID = '".$filesRow['moteTypes_moteTypeID']."'";
                                $jobMoteTypes = mysqli_query($con,$sql);
                                if (mysqli_num_rows($jobMoteTypes)){
                                    $moteTypesRow = mysqli_fetch_array($jobMoteTypes);
                                    echo "<moteTypeID>" .$moteTypesRow['moteTypeID']. "</moteTypeID>";
                                    echo "<moteTypeName>" .$moteTypesRow['moteTypeName']. "</moteTypeName>";
                                }
                            }
                    }
                    echo "</MoteTypes>";
                    echo "</job>";
                }
                echo "</jobs>";                
            }
            */
        echo "</user>";        
    }
    
    echo "<moteTypesTotal>";
    
        $sql="SELECT * FROM moteTypes";
        $allMoteTypes = mysqli_query($con,$sql);
        if (mysqli_num_rows($allMoteTypes)){
            while($moteTypeRow = mysqli_fetch_array($allMoteTypes)){
                echo "<moteTypeTotal>";
                    echo "<moteTypeName>" . $moteTypeRow['moteTypeName'] . "</moteTypeName>";
                    echo "<moteTypeTime>" . $moteTypeRow['runningTime'] . "</moteTypeTime>";
                echo "</moteTypeTotal>";
            }
        }

        // get runtimes for all jobs with dcube
        $sql="select * from runtimes inner join jobs on runtimes.jobs_jobID = jobs.jobID and jobs.dcube != 0";
        $resultTime = mysqli_query($con,$sql);
        $dcube_total = 0;
        if (mysqli_num_rows($resultTime)) {
            while($timeRow = mysqli_fetch_array($resultTime)) {
                $s = strtotime($timeRow['start']);
                $e = strtotime($timeRow['end']);
                $dcube_total +=  round(abs($e - $s) / 60,2);
            }
        }
        echo "<moteTypeTotal>";
            echo "<moteTypeName>" . "DCube" . "</moteTypeName>";
            echo "<moteTypeTime>" . $dcube_total . "</moteTypeTime>";
        echo "</moteTypeTotal>";
    echo "</moteTypesTotal>";
    
    echo "</users>";
}

mysqli_close($con);
}
?>
