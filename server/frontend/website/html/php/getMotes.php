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

    $sql="SELECT * FROM users WHERE userID = '".$q."'";
    $userResult = mysqli_query($con,$sql);
    if (mysqli_num_rows($userResult)) {
        $row = mysqli_fetch_array($userResult);

        echo '<?xml version="1.0" encoding="ISO-8859-1"?>
        <data>';
        
        echo "<allMotes>" . $row['allMotes'] . "</allMotes>";

        echo '<moteTypes>';

            $sql="SELECT * FROM moteTypes";
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
                        echo "<floorLevel>" . $row['floorLevel'] . "</floorLevel>";
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
                        echo "<physical_id>" . $row['physical_id'] . "</physical_id>";
                        echo "<virtual_id>" . $row['virtual_id'] . "</virtual_id>";
                        echo "<gateway_ip>" . $row['gateway_ip'] . "</gateway_ip>";
                        echo "<gateway_ttyid>" . $row['gateway_ttyid'] . "</gateway_ttyid>";
                        echo "<gateway_port>" . $row['gateway_port'] . "</gateway_port>";
                        echo "<coordinates>" . $row['coordinates'] . "</coordinates>";
                        echo "<moteTypes_moteTypeID>" . $row['moteTypes_moteTypeID'] . "</moteTypes_moteTypeID>";
                        echo "<clusters_clusterID>" . $row['clusters_clusterID'] . "</clusters_clusterID>";
                        //echo "<status>" . $row['status'] . "</status>";
                        echo "<status>" . 1 . "</status>";
                    echo "</mote>";
                }
            }

        echo "</motes>";

        echo "</data>";

        mysqli_close($con);
    }
    else
    {
        mysqli_close($con);
        
        die('Could not connect: ' . mysqli_error($con));
    }
}
?>