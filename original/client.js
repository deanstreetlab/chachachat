
//call pollServer at ready and toggle the active class on click
$(document).ready( function() {
  pollServer();  

  $("button").click( function() {
    $(this).toggleClass('active');
  });

});


//the pollServer function does two things: an asynchronous GET to the PHP script to request new messages, then setTimeout() to call again in 5s
//The GET is triggered and completed, and the closure passed into .get executed. The closure checks the result, loop each msg, and add to the web interface
function pollServer() {
  
  $.get ( "chat.php", function(result) {
    
    if( !result.success ) {
     console.log("Error polling server for new messages");
     return;
   }
   
    $.each(result.messages, function() {
      var chatBubble;
      
      if (this.sender == "self") {
        
        chatBubble = $('<div class="row bubble-sent pull-right">' +
                      this.message +
                      '</div><div class="clearfix"></div>');
      } else {
        
        chatBubble = $('<div class="row bubble-recv">' +
                      this.message +
                      '</div><div class="clearfix"></div>');
      }
      

      $("#chatPanel").append(chatBubble);
    });

    setTimeout(pollServer, 10000);
  });

}

//for sending a msg to the PHP script via POST
$( "#sendMessageBtn" ).click( function(event) {
  event.preventDefault();
  var message = $("#chatMessage").val();
  $.post("chat.php", {'message' : message}, function(result) {
    $("#sendMessageBtn").toggleClass('active');
    if (!result.success) {
      alert("Error sending the message");
    } else {
      console.log("Message sent!");
      $("#chatMessage").val('');
    }
  });    
});

