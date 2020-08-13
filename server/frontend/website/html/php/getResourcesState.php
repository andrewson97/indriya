<?php
$q = $_POST["moteTypeID"];
$allTime = $_POST["allTime"];

$userID = $_POST["userID"];
$isMyForm = isset($_POST, $_POST["userID"], $_POST["moteTypeID"], $_POST["allTime"]);

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $q) || !preg_match("/[A-Za-z0-9.\-@]+/", $allTime) || sizeof($q) == 0){
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

echo '<?xml version="1.0" encoding="ISO-8859-1"?>
    <data>';

    echo "<timeNowAtServer>" . gmdate("Y.m.d.H.i.s") . "</timeNowAtServer>";

    
    echo '<moteTypes>';
        if($q == "all")
            $sql="SELECT * FROM moteTypes";
        else
            $sql="SELECT * FROM moteTypes WHERE moteTypeID = '".$q."'";

        $result = mysqli_query($con,$sql);
        if (mysqli_num_rows($result)) {
            //mote types
            while($row = mysqli_fetch_array($result)) {
                echo "<moteType>";
                    echo "<moteTypeID>" . $row['moteTypeID'] . "</moteTypeID>";
                    echo "<moteTypeName>" . $row['moteTypeName'] . "</moteTypeName>";
                echo "</moteType>";
            }
        }

    echo "</moteTypes>";
    
    
    //clusters
    echo "<clusters>";

        $sql="SELECT * FROM clusters";
        $result = mysqli_query($con,$sql);
        if (mysqli_num_rows($result)) {
            //mote types
            while($row = mysqli_fetch_array($result)) {
                echo "<cluster>";
                    echo "<clusterID>" . $row['clusterID'] . "</clusterID>";
                    echo "<clusterName>" . $row['clusterName'] . "</clusterName>";
                    //echo "<floorLevel>" . $row['floorLevel'] . "</floorLevel>";
                echo "</cluster>";
            }
        }

    echo "</clusters>";

    //motes
    echo "<motes>";

        $sql="SELECT * FROM motes";
        $result = mysqli_query($con,$sql);
        if (mysqli_num_rows($result)) {
            //mote types
            while($row = mysqli_fetch_array($result)) {
                echo "<mote>";
                    echo "<moteID>" . $row['moteID'] . "</moteID>";
                    //echo "<physical_id>" . $row['physical_id'] . "</physical_id>";
                    echo "<virtual_id>" . $row['virtual_id'] . "</virtual_id>";
                    //echo "<gateway_ip>" . $row['gateway_ip'] . "</gateway_ip>";
                    //echo "<gateway_ttyid>" . $row['gateway_ttyid'] . "</gateway_ttyid>";
                    //echo "<gateway_port>" . $row['gateway_port'] . "</gateway_port>";
                    //echo "<coordinates>" . $row['coordinates'] . "</coordinates>";
                    echo "<moteTypes_moteTypeID>" . $row['moteTypes_moteTypeID'] . "</moteTypes_moteTypeID>";
                    echo "<clusters_clusterID>" . $row['clusters_clusterID'] . "</clusters_clusterID>";
                    //echo "<status>" . $row['status'] . "</status>";
                    echo "<status>" . 1 . "</status>";
                echo "</mote>";
            }
        }

    echo "</motes>";

    //motes busy slots
    echo "<busy>";
    if($q == "all")
        $sql="SELECT * FROM files";
    else
        $sql="SELECT * FROM files where moteTypes_moteTypeID = '".$q."'";

    $result = mysqli_query($con,$sql);
    while($row = mysqli_fetch_array($result)) {
        echo "<file>";
        
        //get all motes for each file
        $sql="select * from file_mote inner join motes where file_mote.files_fileID = '".$row['fileID']."' and file_mote.motes_moteID=motes.moteID";
        $resultMotes = mysqli_query($con,$sql);
        while($motesRow = mysqli_fetch_array($resultMotes)){
            echo "<moteID>" . $motesRow['motes_moteID'] . "</moteID>";
            echo "<moteTypeID>" . $row['moteTypes_moteTypeID'] . "</moteTypeID>";
            echo "<clusters_clusterID>" . $motesRow['clusters_clusterID'] . "</clusters_clusterID>";
        }
        
        //get all run times for the job owns this file
        $sql="select * from runtimes where jobs_jobID = '".$row['jobs_jobID']."'";
        $resultTime = mysqli_query($con,$sql);
        if (mysqli_num_rows($resultTime)) {
            while($timeRow = mysqli_fetch_array($resultTime)) {
                if($timeRow['end'] > date('Y-m-d H:i:s') || ($allTime && $timeRow['end'] > date('Y-m-1 0:0:0'))) {
                    $sql="select * from results where results.jobs_jobID = '".$row['jobs_jobID']."' and results.runtimes_runtimeID = '".$timeRow['runtimeID']."'";
                    $result_result_table = mysqli_query($con,$sql);
                    $result_result_row = mysqli_fetch_array($result_result_table);
                    
                    echo "<jobs_jobID>" . $row['jobs_jobID'] . "</jobs_jobID>";
                    echo "<start>" . $timeRow['start'] . "</start>";
                    echo "<end>" . $timeRow['end'] . "</end>";
                    echo "<runtimeID>" . $timeRow['runtimeID'] . "</runtimeID>";
                    echo "<result_resultID>" . $result_result_row['resultID'] . "</result_resultID>";
                }
            }
        }
        
        echo "</file>";
    }

    echo "</busy>";

echo "</data>";

mysqli_close($con);
}
?>