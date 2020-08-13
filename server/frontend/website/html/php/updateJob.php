<?php
include "log.php";
$userID = $_POST['userID'];
$jobID = $_POST['jobID'];
$jobName = $_POST['jobName'];
$numberOfMoteTypes = $_POST['numberOfMoteTypes'];
$dcube = $_POST['dcube'];

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobID"], $_POST["jobName"], $_POST["numberOfMoteTypes"], $_POST["dcube"]);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $jobID) || !preg_match("/[A-Za-z0-9.\-@]+/", $jobName) || !preg_match("/[A-Za-z0-9.\-@]+/", $numberOfMoteTypes) || !preg_match("/[A-Za-z0-9.\-@]+/", $dcube) || sizeof($userID) == 0 || sizeof($jobID) == 0 || sizeof($jobName) == 0 || sizeof($numberOfMoteTypes) == 0){
    #header("HTTP/1.0 404 Not Found");
    #include "404missing.php";
    #missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
    $arr = array(
        "status" => "ERROR",
        "message" => "bad request",
        "operation" => "edit job",
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

    $con = mysqli_connect('localhost','root',$indriya_db_pass);
    if (!$con) {
        $arr = array(
            "status" => "ERROR",
            "message" => "could not connect to databse",
        );
        echo json_encode($arr);

        $arr["operation"] = "edit job";
        $arr["user"] = $userID;
        $arr["time"] = date('Y-m-d H:i:s');
        # log_php($arr);

        exit();
    }

    if (is_dir($uploadPath) && is_writable($uploadPath)) {
        mysqli_select_db($con,"indriyaDB");

        // if dcube = 0, delete dcube files for this job
        if($dcube == 0){
            $sql="SELECT * FROM files WHERE jobs_jobID = '".$jobID."' and files.dcube = 1";
            $dcube_jobFiles = mysqli_query($con,$sql);
            while($dcubeFile_row = mysqli_fetch_array($dcube_jobFiles)){
                // delete from file system
                $files = glob($uploadPath.'/'.$dcubeFile_row['fileID'].'.*');
                $file = $files[0];

                if (is_file($file)){
                    if(is_writable($file))
                    {
                        $ok = unlink($file);
                        
                        // delete from db
                        $sql="DELETE FROM files WHERE fileID = '".$dcubeFile_row['fileID']."'";
                        $ok = mysqli_query($con,$sql);
                    }
                }
            }
        }
        // id dcube = 2, check on old file and new file
        if($dcube == 2){
            if(sizeof($_POST['dcube_oldFileID']) == 0){
                // delete old file, if any!
                $sql="SELECT * FROM files WHERE jobs_jobID = '".$jobID."' and files.dcube = 1";
                $dcube_jobFiles = mysqli_query($con,$sql);
                while($dcubeFile_row = mysqli_fetch_array($dcube_jobFiles)){
                    // delete from file system
                    $files = glob($uploadPath.'/'.$dcubeFile_row['fileID'].'.*');
                    $file = $files[0];

                    if (is_file($file)){
                        if(is_writable($file))
                        {
                            $ok = unlink($file);
                            
                            // delete from db
                            $sql="DELETE FROM files WHERE fileID = '".$dcubeFile_row['fileID']."'";
                            $ok = mysqli_query($con,$sql);
                        }
                    }
                }
                
                // upload new file

                $sql="INSERT INTO files (fileName, jobs_jobID, moteTypes_moteTypeID, dcube) VALUES ('".$_POST['dcube_fileName']."', '".$jobID."', '".$_POST['dcube_moteTypeID']."', 1)";
                $result = mysqli_query($con,$sql);
                $fileID = $con->insert_id;

                $fileName = $_POST['dcube_fileName'];
                $info = new SplFileInfo($fileName);
                
                $tempFilename = $_FILES['dcube_file']['tmp_name'];
                $ok = move_uploaded_file($tempFilename, $uploadPath.'/'.$fileID.'.'.$info->getExtension());
            }
            // else, if old file ==> do nothing!
        }
        
        //delete all job's old files if not exist in data
        $sql="SELECT * FROM files WHERE jobs_jobID = '".$jobID."' and files.dcube = 0";
        $jobFiles = mysqli_query($con,$sql);

        //insert files using jobID
        $unsupportedFiles = array();
        $allNewFile = array();
        for($i = 0; $i < $numberOfMoteTypes; $i++) {
            $moteTypeID = $_POST['moteTypeID'.$i];
            //new files
            $numberOfFiles = $_POST['numberOfFiles'.$i];
            for($j = 0; $j < $numberOfFiles; $j++) {
                $sql="INSERT INTO files (fileName, jobs_jobID, moteTypes_moteTypeID) VALUES('".$_POST['fileName'.$i.$j]."', '".$jobID."', '".$moteTypeID."')";
                $result = mysqli_query($con,$sql);
                $fileID = $con->insert_id;

                $allNewFile[] = $fileID;

                //insert file-motes relation for the last inserted file
                $moteArr = $_POST['motes'.$i.$j];
                foreach($moteArr as $key => $moteID) {
                    $sql="INSERT INTO file_mote (files_fileID, motes_moteID) VALUES('".$fileID."', '".$moteID."')";
                    $ok = mysqli_query($con,$sql);
                }

                $fileName = $_POST['fileName'.$i.$j];
                $info = new SplFileInfo($fileName);
                
                $tempFilename = $_FILES['file'.$i.$j]['tmp_name'];
                $ok = move_uploaded_file($tempFilename, $uploadPath.'/'.$fileID.'.'.$info->getExtension());

                //check on file type from API, add unsupported format to json list
                $url = 'http://localhost:5000/check_binary';

                //mote type
                $sql="select * from moteTypes where moteTypeID = '".$moteTypeID."'";
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

		/*$log_file = "/var/log/indriya/php_debug.log";
		$handle = fopen($log_file, 'a');
		fwrite($handle, json_encode($obj));
		fwrite($handle, "\n");
		fwrite($handle, $result);
		fwrite($handle, "\n");
		fclose($handle);*/

                if($response['result'] == 0) {
                    $unsupportedFiles[] = $fileName;
                }
            }
        }

        if(sizeof($unsupportedFiles) != 0) {
            for($i = 0; $i < sizeof($allNewFile); $i++) {
                $files = glob($uploadPath.'/'.$allNewFile[$i].'.*');
                $file = $files[0];
                if (is_file($file)){
                    if(is_writable($file))
                    {
                        $ok = unlink($file);
                    }
                }
                $sql="DELETE FROM files WHERE fileID = '".$allNewFile[$i]."'";
                $result = mysqli_query($con,$sql);
            }
            
            $arr = array(
                "status" => "ERROR",
                "message" => "unsuported file format",
                "files" => $unsupportedFiles,
            );
            echo json_encode($arr);

            $arr["operation"] = "edit job";
            $arr["user"] = $userID;
            $arr["time"] = date('Y-m-d H:i:s');
            #log_php($arr);
        } else {
            if (mysqli_num_rows($jobFiles)){
                while($filesRow = mysqli_fetch_array($jobFiles)){                    
                    $moteTypeID = $filesRow['moteTypes_moteTypeID'];

                    //delete file only if it does not exist in data form old files
                    $oldFileArr = $_POST['oldFileID'.$moteTypeID];
                    if(fileExist($filesRow['fileID'], $oldFileArr) == 0) {
                        
                        $files = glob($uploadPath.'/'.$filesRow['fileID'].'.*');
                        $file = $files[0];

                        if (is_file($file)){
                            if(is_writable($file))
                            {
                                $ok = unlink($file);
                                
                                $sql="DELETE FROM files WHERE fileID = '".$filesRow['fileID']."'";
                                $ok = mysqli_query($con,$sql);
                            }
                        }
                    } else {
                        //delete file-mote relation for existing old files
                        $sql="DELETE FROM file_mote WHERE files_fileID = '".$filesRow['fileID']."'";
                        $ok = mysqli_query($con,$sql);
                    }
                }
            }
            
            //update db info
            $sql="UPDATE jobs SET jobName = '".$jobName."', dcube = ".$dcube." WHERE jobID = '".$jobID."'";
            $ok = mysqli_query($con,$sql);
            
            //insert files using jobID
            for($i = 0; $i < $numberOfMoteTypes; $i++) {
                $moteTypeID = $_POST['moteTypeID'.$i];
                
                //old files, just insert association
                $oldFileArr = $_POST['oldFileID'.$moteTypeID];
                foreach($oldFileArr as $key1 => $oldFileID) {
                    $oldMotesArr = $_POST['oldMotes'.$oldFileID];
                    foreach($oldMotesArr as $key2 => $moteID) {
                        $sql="INSERT INTO file_mote (files_fileID, motes_moteID) VALUES('".$oldFileID."', '".$moteID."')";
                        $ok = mysqli_query($con,$sql);
                    }
                }
            }

            $arr = array(
                "status" => "SUCCESS",
                "jobID" => $jobID
            );
            echo json_encode($arr);

            $arr["operation"] = "edit job";
            $arr["user"] = $userID;
            $arr["time"] = date('Y-m-d H:i:s');
            #log_php($arr);
        }
    } else {
        $arr = array(
            "status" => "ERROR",
            "message" => "files directory is not writable"
        );
        echo json_encode($arr);

        $arr["operation"] = "edit job";
        $arr["user"] = $userID;
        $arr["time"] = date('Y-m-d H:i:s');
        # log_php($arr);
    }

    mysqli_close($con);
}
?>

<?php
function fileExist($fileID, $oldFileArr) {
    foreach($oldFileArr as $key => $oldFileID) {
        if($fileID == $oldFileID)
            return 1;
    }
    return 0;
}
?>
