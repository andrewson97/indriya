<?php
$userID = $_POST['userID'];

$userID = filter_var($userID, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"]);

if(!$isMyForm || !filter_var($userID, FILTER_VALIDATE_EMAIL) || sizeof($userID) == 0){
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

  $url = 'http://localhost:5000/cancel_job';

  # get all jobs with upcoming schedule time
  $sql="SELECT * FROM runtimes";
  $resultRuntimes = mysqli_query($con,$sql);
  while($runtimeRow = mysqli_fetch_array($resultRuntimes)){
    if($runtimeRow['start'] > date('Y-m-d H:i:s')) {
      // get resultID for this runtime
      $sql="select * from results where results.jobs_jobID = '".$runtimeRow['jobs_jobID']."' and results.runtimes_runtimeID = '".$runtimeRow['runtimeID']."'";
      $result_result_table = mysqli_query($con,$sql);
      $result_result_row = mysqli_fetch_array($result_result_table);

      $resultID = $result_result_row['resultID'];

      $obj = array(
          "result_id" => $resultID.'',
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

      //ok, cancel schedule
      if($response['result'] == 1) {
        $sql="DELETE FROM runtimes WHERE runtimeID = '".$runtimeRow['runtimeID']."'";
          $result = mysqli_query($con,$sql);
      }
    }
  }

  echo '1';

  mysqli_close($con);
}
?>