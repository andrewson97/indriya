<?php
$q = $_POST['userID'];
$jobID = $_POST['jobID'];
$resultID = $_POST['resultID'];
$runtimeID = $_POST['runtimeID'];

$q = filter_var($q, FILTER_SANITIZE_EMAIL);

$isMyForm = isset($_POST, $_POST["userID"], $_POST["jobID"], $_POST["resultID"], $_POST["runtimeID"]);

if(!$isMyForm || !filter_var($q, FILTER_VALIDATE_EMAIL) || !preg_match("/[A-Za-z0-9.\-@]+/", $jobID) || !preg_match("/[A-Za-z0-9.\-@]+/", $resultID) || !preg_match("/[A-Za-z0-9.\-@]+/", $runtimeID) || sizeof($jobID) == 0 || sizeof($resultID) == 0 || sizeof($runtimeID) == 0){
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

  //$url = 'http://localhost:8080/php/test.php';
  $url = 'http://localhost:5000/cancel_job';
      
      
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
          mysqli_select_db($con,"indriyaDB");
          $sql="DELETE FROM runtimes WHERE runtimeID = '".$runtimeID."'";
          $result = mysqli_query($con,$sql);

          // call cancel job api on dcube if dcube job
      }

      echo $response['result'];

  mysqli_close($con);
}
?>