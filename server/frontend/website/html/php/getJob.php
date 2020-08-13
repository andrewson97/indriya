<?php
$q = $_POST["jobID"];

$userID = $_POST["userID"];
$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobID"]);

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

//get all details about selected job

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
<job>';

$sql="SELECT * FROM jobs WHERE jobID = '".$q."'";
$result = mysqli_query($con,$sql);

if (mysqli_num_rows($result)) {
    //found job in the database
    $row = mysqli_fetch_array($result);
    
    echo "<jobID>" . $row['jobID'] . "</jobID>";
    echo "<jobName>" . $row['jobName'] . "</jobName>";
    echo "<dcube>" . $row['dcube'] . "</dcube>";

    //add files of each job
    $sql="SELECT * FROM files WHERE jobs_jobID = '".$row['jobID']."'";
    $jobFiles = mysqli_query($con,$sql);
    if (mysqli_num_rows($jobFiles)){
        echo "<files>";
        while($filesRow = mysqli_fetch_array($jobFiles)){
            if($filesRow['dcube'] == 0){
                echo "<file>";
                    echo "<fileID>" . $filesRow['fileID'] . "</fileID>";
                    echo "<fileName>" . $filesRow['fileName'] . "</fileName>";
                    echo "<file_dcube>" . $filesRow['dcube'] . "</file_dcube>";

                    $sql="SELECT * FROM moteTypes WHERE moteTypeID = '".$filesRow['moteTypes_moteTypeID']."'";
                    $jobMoteTypes = mysqli_query($con,$sql);
                    if (mysqli_num_rows($jobMoteTypes)){
                        $moteTypesRow = mysqli_fetch_array($jobMoteTypes);
                        echo "<moteTypeID>" . $moteTypesRow['moteTypeID'] . "</moteTypeID>";
                        echo "<moteTypeName>" . $moteTypesRow['moteTypeName'] . "</moteTypeName>";
                    }
                
                    //add motes for each file
                    echo "<motes>";
                    $sql="SELECT * FROM file_mote WHERE files_fileID = '".$filesRow['fileID']."'";
                    $fileMotes = mysqli_query($con,$sql);
                    while($fileMotesRow = mysqli_fetch_array($fileMotes)){
                        echo "<moteID>" . $fileMotesRow['motes_moteID'] . "</moteID>";
                    }
                    echo "</motes>";

                echo "</file>";
            }
            if($row['dcube'] != 0 && $filesRow['dcube'] == 1){
                echo "<dcube_file>";
                    echo "<dcube_fileID>" . $filesRow['fileID'] . "</dcube_fileID>";
                    echo "<dcube_fileName>" . $filesRow['fileName'] . "</dcube_fileName>";
                echo "</dcube_file>";
            }
        }
        echo "</files>";
    }
}
echo "</job>";

mysqli_close($con);
}
?>