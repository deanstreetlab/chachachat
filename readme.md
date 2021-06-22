##ChaChaChat

Re-coded version of the web chat app example in chapter 23 of [PHP and MySQL Web Development](https://app.box.com/s/huqhr79oaxq2w7w0lnzpzs5infu58roj) by Luke Welling.

![Preview](screenshot.jpg)

Only minor modifications from original:

- from mysqli to PDO for MySQL connection
- DB column names and variable names to my personal liking
- CSS stylings to my personal liking
- linking enter key to send button click

###How It Works
Users open chachachat.html and enter messages to be broadcast to others.  
Own messages appear on the right, other messages on the left.

####Organization
- The app is made up of 3 frontend files and 1 PHP file and MySQL.  
- The frontend files comprise 1 html, 1 css, 1 js.  
- The html is a simple interface to enter a message in a form and click send button to submit.  
- The css uses [Bootstrap](https://getbootstrap.com/) to style panel, buttons, and messages.  
- The js connects the form to php by attaching a POST request to the send button and sending a GET request to check messages repeatedly using setTimeout.   
- The MySQL database is named as the app and having a single table named *chatlog*. The table contains four columns: id int(8) auto_increment primary key, message tinytext, sender varchar(50), and timestamp int(11).  
- The PHP connects to DB and provides **send message** and **check message** functions, distingushed by POST and GET requests.  

####The PHP Script
The script opens a session, connects to DB, checks if a request is POST or GET then switch to either a **send message** or **check message** method, and finally returns json to caller.  

User ID assigned using session ID, time standardized to UCT.  

*lastCheckTime* variable is timestamp for the last time messages are polled, which is stored as a session var.  

*action* variable is either a *send* or *check* string based on POST or GET request sent by the js.  

**check message** selects all queries since *lastCheckTime*, modifies the *sender* column to "self" or "nonself" strings, and stores into a *unreadMessages* array var. It then returns success/failure status boolean and *unreadMessages* in json.  

**send messages** takes the entered message, sanitizes it, inserts it into DB using prepared statement, and return success/failture status boolean in json.
 




