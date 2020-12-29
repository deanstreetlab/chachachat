<?php 

  error_reporting(E_ALL);  
  session_start(); //start a new session or resume existing one
  ob_start(); //start an output buffer
  
  header("Content-Type: application/json"); //header mime
  
  date_default_timezone_set("UCT"); //set default timezone used by all datetime in this script
  
  //database connection
  require(__DIR__.'/config.php');
  $dsn = "mysql:host=$host;dbname=chachachat;port=$port";
  try {
    $dbh = new PDO($dsn, $user, $password);
  } catch (PDOException $ex) {
    print("Database connection failure: ".$ex->getMessage());
    exit();
  }  
  
  try {
    $currentTime = time(); //Unix timestamp for now
    $userID = session_id(); //get session id as user id

    //assign lastCheckTime to either session var if one exists or now if doesn't
    $lastCheckTime = isset( $_SESSION["lastCheckTime"] ) ? $_SESSION["lastCheckTime"] : $currentTime ;
    
    //for GET request, assign check; for POST requests, assign send
    $action = ( isset( $_SERVER["REQUEST_METHOD"] ) &&  ($_SERVER["REQUEST_METHOD"] == "POST") ) 
            ? "send" : "check"; 

    //switching operations based on send or check
    switch($action) {

      case "check": //retrieve unread messages and send to caller

        $query = 'SELECT * FROM chatlog WHERE timestamp >= :lastCheckTime '; 
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':lastCheckTime', $lastCheckTime, PDO::PARAM_INT); //the timestamp column is type int
        $stmt->execute(); 

        $resultset = $stmt->fetchAll(); //assign all rows into a variable for convenience
        $unreadMessages = []; //var to return to caller, it's $resultset with the sender column modified
        
        foreach ($resultset as $row) { //looping through each row 

          if ( $userID == $row["sender"] ) { //modify sender to either self or nonself
            $row["sender"] = "self";
          } else {
            $row["sender"] = "nonself";
          }

          array_push($unreadMessages, $row); //appending the modified row
        }

        $_SESSION["lastCheckTime"] = $currentTime; //update var to now

        $output_data = ["success" => TRUE, "messages" => $unreadMessages]; //return success and new messages to caller
        $output_json = json_encode($output_data) ; //json
        print($output_json);
        exit();


      case "send": //receive message and add to db
        
        $message = isset( $_POST["message"] ) ? $_POST["message"] : "" ;
        $message =  strip_tags( trim($message) ); //strip off html and php tags
        $query = "INSERT INTO chatlog (message, sender, timestamp) VALUES (:message, :sender, :timestamp)";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR); //the message column is type tinytext
        $stmt->bindParam(':sender', $userID, PDO::PARAM_STR); //the sender column is type varchar
        $stmt->bindParam(':timestamp', $currentTime, PDO::PARAM_INT); //the timestamp column is type int
        $stmt->execute();
        
        $output_data = ["success" => TRUE];
        $output_json = json_encode($output_data);
        print($output_json);
        exit();        

      }
  }

  catch (Exception $ex) {
    $output = json_encode( ["success" => FALSE, "error" => $ex->getMessage()] );
    print($output);
  }


?>