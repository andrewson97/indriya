<?php
#include "404missing.php";

$email = $_POST["email"];
$user_id = $_POST["userID"];
$id_token = $_POST["id_token"];
$CLIENT_ID = $_POST["client_id"];

$isMyForm = isset($_POST, $_POST["userID"], $_POST["email"], $_POST["id_token"]);

$email = filter_var($email, FILTER_SANITIZE_EMAIL);

if(!$isMyForm || !filter_var($email, FILTER_VALIDATE_EMAIL) || sizeof($email) == 0 || sizeof($user_id) == 0 || sizeof($id_token) == 0){
  #header("HTTP/1.0 404 Not Found");
  #missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
  exit();
} else {
  require_once 'vendor/autoload.php';
  // Get $id_token via HTTPS POST.
  $client = new Google_Client(['client_id' => $CLIENT_ID]);
  $payload = $client->verifyIdToken($id_token);

  if ($payload) {
    $email_ver = $payload['email'];
    $id_ver = $payload['sub'];
    $email_verified_ver = $payload['email_verified'];
    // If request specified a G Suite domain:
    //$domain = $payload['hd'];

    #get db pass
    if($email_verified_ver == true && $email_ver == $email && $id_ver == $user_id) {
      include "databaseHandler.php";
      $indriya_db_pass = getpsw();

      $con = mysqli_connect('localhost','root',$indriya_db_pass);
      if (!$con) {
          echo 'Could not connect: ' . mysqli_connect_error();
      }
      echo mysqli_get_host_info($con);

      mysqli_select_db($con,"indriyaDB");

      $sql="SELECT * FROM users WHERE userID = '".$email."'";
      $result = mysqli_query($con,$sql);

      if (!mysqli_num_rows($result)) {
        //new user, first login
        //get mqtt_passw for this user
        $url = 'http://localhost:5000/new_mqtt_user';
        $obj = array(
                "user" => $email.''
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
        if(empty($response))
          $mqtt_passw = "NULL";
        else
          $mqtt_passw = $response['password'];

        $sql="INSERT INTO users (userID, quota, admin, allMotes, runningTime, totalSubmissions, mqtt_passw, details) VALUES('".$email."', 0, 0, 0, 0, 0, '".$mqtt_passw."', '-')";
        $result = mysqli_query($con,$sql);
        $arr = array(
          "admin"=>"0",
          "allMotes"=>"0",
          "mqtt_passw"=>$mqtt_passw.''
        );
        echo json_encode($arr);
      } else{
          //found user in the database
          $row = mysqli_fetch_array($result);

          //try to get password again
          $mqtt_passw = $row['mqtt_passw'];
          if($mqtt_passw == "NULL") {
            $url = 'http://localhost:5000/new_mqtt_user';
            $obj = array(
                    "user" => $email.''
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
            if(empty($response))
              $mqtt_passw = "NULL";
            else
              $mqtt_passw = $response['password'];
          }
          //update if get it
          if($mqtt_passw != "NULL") {
            $sql="UPDATE users SET mqtt_passw = '".$mqtt_passw."' WHERE userID = '".$email."'";
            $resultU = mysqli_query($con,$sql);
          }

          $arr = array(
              "admin"=>$row['admin'].'',
              "allMotes"=>$row['allMotes'].'',
              "mqtt_passw"=>$mqtt_passw.''
          );
          echo json_encode($arr);
      }

      mysqli_close($con);
    } else {
      header("HTTP/1.0 404 Not Found");
      missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
      exit();
    }
  } else {
    // Invalid ID token
    header("HTTP/1.0 404 Not Found");
    missing404($_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
    exit();
  }
}
?>
