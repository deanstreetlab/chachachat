
$(document).ready( function() {
  checkUnread();  

  $("button").click( function() {
    $(this).toggleClass('active');
  });
});


function checkUnread() {

  $.get ( "chachachat.php", function(result) {
    if( !result.success ) {
     console.log("Error checking server for new messages");
     return;
   }
   
    $.each(result.messages, function() {
      var chatBubble;
      
      if (this.sender == "self") {
        
        chatBubble = $('<div class="row bubble-sent pull-right">' +   //row is BT grid row, pull-right is to float right, bubble-sent is self-defined
                      this.message +
                      '</div><div class="clearfix"></div>');  //clearfix is BT class to clear float
      } else {
        
        chatBubble = $('<div class="row bubble-recv">' +
                      this.message +
                      '</div><div class="clearfix"></div>'); 
      }      

      $("#chatPanel").append(chatBubble);
    });
    
    setTimeout(checkUnread, 2000); //recursive call self 
  });
}

//for sending a msg to the PHP script via POST
$( "#sendMessageBtn" ).click( function(event) {
  event.preventDefault();
  var message = $("#chatMessage").val().toString();
  $.post("chachachat.php", {'message': message}, function(result) {    
    $("#sendMessageBtn").toggleClass('active');
    if (!result.success) {
      alert("Error sending the message");
    } else {
      console.log("Message sent!");
      $("#chatMessage").val('');
    }
  });
});
//linking the enter key to the send button click
$( "#chatMessage" ).keypress( function(event) {
  var keypressed = event.keyCode ? event.keyCode : event.which ;
  if (keypressed == "13") {
    $( "#sendMessageBtn" ).click();
  }
});