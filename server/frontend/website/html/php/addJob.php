<?php
include "log.php";
$userID = $_POST['userID'];
$jobName = $_POST['jobName'];
$numberOfMoteTypes = $_POST['numberOfMoteTypes'];
$dcube_included = $_POST['dcube_included'];

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobName"], $_POST["numberOfMoteTypes"], $_POST["dcube_included"]);
//echo("test");
if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $jobName) || !preg_match("/[A-Za-z0-9.\-@]+/", $numberOfMoteTypes) || sizeof($userID) == 0 || sizeof($jobName) == 0 || sizeof($numberOfMoteTypes) == 0){
    #header("HTTP/1.0 404 Not Found");
    #include "404missing.php";
    #missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);

    $arr = array(
        "status" => "ERROR",
        "message" => "bad request",
        "operation" => "add job",
        "user" => $userID,
        "time" => date('Y-m-d H:i:s')
    );
    # log_php($arr);

    exit();
} else {
    #get db pass
    include "databaseHandler.php";
    $indriya_db_pass = getpsw();

    $uploadPath = "/var/www/files";

    if (is_dir($uploadPath) && is_writable($uploadPath)) {
        $con = mysqli_connect('localhost','root',$indriya_db_pass);
        if (!$con) {
            $arr = array(
                "status" => "ERROR",
                "message" => "could not connect to databse",
            );
            echo json_encode($arr);

            $arr["operation"] = "add job";
            $arr["user"] = $userID;
            $arr["time"] = date('Y-m-d H:i:s');
            # log_php($arr);

            exit();
        }

        mysqli_select_db($con,"indriyaDB");

        if($dcube_included == "dcube_no"){
            $sql="INSERT INTO jobs (jobName, users_userID) VALUES('".$jobName."', '".$userID."')";
        }
        else{
            if($dcube_included == "dcube_only")
                $sql="INSERT INTO jobs (jobName, users_userID, dcube) VALUES('".$jobName."', '".$userID."', 1)";
            else if($dcube_included == "dcube_yes")
                $sql="INSERT INTO jobs (jobName, users_userID, dcube) VALUES('".$jobName."', '".$userID."', 2)";
        }
        $result = mysqli_query($con,$sql);
        $jobID = $con->insert_id;

        // insert dcube file if any
        if($dcube_included != "dcube_no"){
            $MoteTypeID = $_POST['dcube_moteTypeID'];
            $sql="INSERT INTO files (fileName, jobs_jobID, moteTypes_moteTypeID, dcube) VALUES('".$_POST['dcube_fileName']."', '".$jobID."', '".$MoteTypeID."', 1)";
            $result = mysqli_query($con,$sql);
            $fileID = $con->insert_id;
            
            //insert file-motes relation for the last inserted file
            $fileName = $_POST['dcube_fileName'];
            $info = new SplFileInfo($fileName);
            
            $tempFilename = $_FILES['dcube_file']['tmp_name'];
            $t = move_uploaded_file($tempFilename, $uploadPath.'/'.$fileID. '.'.$info->getExtension());
        }
        
        //insert files using jobID
        $unsupportedFiles = array();
        for($i = 0; $i < $numberOfMoteTypes; $i++) {
            $numberOfFiles = $_POST['numberOfFiles'.$i];
            $MoteTypeID = $_POST['moteTypeID'.$i];
            
            for($j = 0; $j < $numberOfFiles; $j++) {
                $sql="INSERT INTO files (fileName, jobs_jobID, moteTypes_moteTypeID) VALUES('".$_POST['fileName'.$i.$j]."', '".$jobID."', '".$MoteTypeID."')";
                $result = mysqli_query($con,$sql);
                $fileID = $con->insert_id;
                
                //insert file-motes relation for the last inserted file
                $moteArr = $_POST['motes'.$i.$j];
                foreach($moteArr as $key => $moteID) {
                    $sql="INSERT INTO file_mote (files_fileID, motes_moteID) VALUES('".$fileID."', '".$moteID."')";
                    $result = mysqli_query($con,$sql);
                }

                $fileName = $_POST['fileName'.$i.$j];
                $info = new SplFileInfo($fileName);
                
                $tempFilename = $_FILES['file'.$i.$j]['tmp_name'];
                $t = move_uploaded_file($tempFilename, $uploadPath.'/'.$fileID. '.'.$info->getExtension());

                //check on file type from API, add unsupported format to json list
                $url = 'http://localhost:5000/check_binary';

                //mote type
                $sql="select * from moteTypes where moteTypeID = '".$MoteTypeID."'";
                $resultMoteType = mysqli_query($con,$sql);
                $moteTypeRow = mysqli_fetch_array($resultMoteType);

                $obj = array(
                    "binary_file" => $fileID. '.'.$info->getExtension(),
                    "type" => $moteTypeRow['moteTypeName']
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

                if($response['result'] == 0) {
                    if($unsupportedFiles[$moteTypeRow['moteTypeName']] == NULL)
                        $unsupportedFiles[$moteTypeRow['moteTypeName']] = $fileName;
                    else
                        $unsupportedFiles[$moteTypeRow['moteTypeName']] = $unsupportedFiles[$moteTypeRow['moteTypeName']] . ', ' . $fileName;
                }
            }
        }
        if(sizeof($unsupportedFiles) != 0){
            //delete job and files
            $sql="SELECT * FROM files WHERE jobs_jobID = '".$jobID."'";
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
            $sql="DELETE FROM jobs WHERE jobID = '".$jobID."'";
            $result = mysqli_query($con,$sql);

            $arr = array(
                "status" => "ERROR",
                "message" => "unsuported file format",
                "files" => $unsupportedFiles,
            );
            echo json_encode($arr);

            $arr["operation"] = "add job";
            $arr["user"] = $userID;
            $arr["time"] = date('Y-m-d H:i:s');
            # log_php($arr);
        } else {
            $arr = array(
                "status" => "SUCCESS",
                "jobID" => $jobID
            );
            echo json_encode($arr);

            $arr["operation"] = "add job";
            $arr["user"] = $userID;
            $arr["time"] = date('Y-m-d H:i:s');
            # log_php($arr);
        }
        mysqli_close($con);
    } else {
        $arr = array(
            "status" => "ERROR",
            "message" => "files directory is not writable"
        );
        echo json_encode($arr);

        $arr["operation"] = "add job";
        $arr["user"] = $userID;
        $arr["time"] = date('Y-m-d H:i:s');
        # log_php($arr);
    }
}
?>