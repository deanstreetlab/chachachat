<?php 
//script for a basic chat app exercise in chapter23 PHP and MySQL Web Development by Luke Welling

  session_start(); //start a session
  ob_start(); //start an output buffer

  header("Content-Type: application/json"); //header mime

  date_default_timezone_set("UCT"); //set default timezone used by all datetime in this script

  //database connection to a newly created port 3307 and root user for this example
  $db = mysqli_connect('127.0.0.1', 'USERNAME HERE', 'PASSWORD HERE', 'chat', 3307);  
  
  if ( mysqli_connect_errno() ) {
    echo "database connection error"; 
    exit();
  }  
  
  try {
    $currentTime = time(); //Unix timestamp for now
    $session_id = session_id(); //get session id

    //assign lastPoll to currentTime if not set
    $lastPoll = isset( $_SESSION["last_poll"] ) ? $_SESSION["last_poll"] : $currentTime ;
    
    //for HTTP GET request, retrieve messages for display = poll; for POST requests, accept messages for broadcast = send
    $action = ( isset( $_SERVER["REQUEST_METHOD"] ) &&  ($_SERVER["REQUEST_METHOD"] == "POST") ) 
            ? "send" : "poll"; 

    //two switches on poll or send, both return json 
    switch($action) {

      case "poll":

        $query = "SELECT * FROM chatlog where timestamp >= ? "; //query db for records above lastPoll for messages not yet seen
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $lastPoll); 
        $stmt->execute(); 

        $stmt->bind_result($id, $message, $session_id, $timestamp); //bind the column values to each var
        $result = $stmt->get_result(); 

        $newChats = [];
        while ( $chat = $result->fetch_assoc() ) { //while records exist

          if ( $session_id == $chat["sender"] ) { //modify sender to either self or other
            $chat["sender"] = "self";
          } else {
            $chat["sender"] = "other";
          }

          array_push($newChats, $chat);
        }

        $_SESSION["last_poll"] = $currentTime; //update lastPoll to now

        $output_data = ["success" => TRUE, "messages" => $newChats];
        $output_json = json_encode($output_data);
        print($output_json);

        exit();


      case "send":

        $message = isset( $_POST["message"] ) ? $_POST["message"] : "" ;
        $message =  strip_tags($message); //strip off all html and php tags
        $query = "INSERT INTO chatlog (message, sender, timestamp) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param('ssi', $message, $session_id, $currentTime);
        $stmt->execute();

        $output_data = ["success" => true];
        $output_json = json_encode($output_data);
        print($output_json);
        exit();

      }
  }

  catch (Exception $e) {

    $output_data = ["success" => FALSE, "error" => $e->getMessage()];
    $output_json = json_encode($output_data);
    print($output_json);

  }


?>
