<!-- REG: 1805098 -->

<!-- Stall the sending of the headers, create the session and import the functions. -->
<?php
  ob_start();
  require("docs/functions.php");
  if (!isset($_SESSION)) {
    session_start();
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <!-- Import all of the styles needed for the website. -->
    <?php
	  require("styles.html");
	?>
  </head>
  <body>
    <!-- Establish the connection to the SQL server and check for cookies. -->
    <?php
      require("docs/conn.php");
	  require("docs/init.php");
	?>
	<!-- Draw the title. -->
    <div class="header background">
      <h1 class="header">
        Task Manager
      </h1>
    </div>
	<!-- Draw the login section. -->
    <div class="content background" style="width: 49.25%; float: left; text-align: center">
      <h2 class="subheader">
        Login
      </h2>
      <hr>
	  <!-- Login form that redirects back to login.php (uses POST). -->
      <form action="login.php" method="POST">
      <br>
      Username
      <br>
	  <!-- Create a username textbox. -->
      <input style="width: 320px" type="text" name="username" value="">
      <br><br>
      Password
      <br>
	  <!-- Create a password textbox. -->
      <input style="width: 320px" type="password" name="password" value="">
      <br>
      <br>
	  <!-- Create the reset button. -->
      <input style="width: 160px; position: relative; top: 16px" type="reset" value="Reset" />
      <br>
	  <!-- Create the submit button. -->
      <input style="position: relative; top: 20px; width: 160px" type="submit" value="Submit" />
      </form>
	  <!-- Log the user in. -->
      <?php
	    #If there is a username and password filled out in the login section when the page is refreshed...
        if (isset($_POST['username']) and isset($_POST['password'])) {
		  #If both the username and password are filled out...
          if (!($_POST['username'] == "" or $_POST['password'] == "")) {
			#Store the post values for the username and password.
            $username = $_POST['username'];
            $password = md5($_POST['password']);
			#If that user exists...
            $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(name) FROM T_USER WHERE name = '$username'"))['COUNT(name)'];
            if ($count > 0) {
			  #Check if their credentials match.
              $result = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM T_USER WHERE name = '$username' AND password = '$password'"));
			  #If the query succeeded...
              if ($result) {
				#Log the user in.
                $_SESSION['USER'] = $username;
			    unset_cookie('LOGIN_NOTICE');
                refresh_page('home.php');
              }
			  #Else refresh the page.
              else {
			    setcookie('LOGIN_NOTICE', 0);
			    refresh_page();
              }
            }
			#Else refresh the page.
            else {
			  setcookie('LOGIN_NOTICE', 2);
			  refresh_page();
			}
          }
		  #Else refresh the page.
          else {
			setcookie('LOGIN_NOTICE', 1);
			refresh_page();
          }
		  #Unset the used cookies and post values.
		  unset_cookie('REGISTER_NOTICE');
          unset($_POST['username']);
          unset($_POST['password']);
        }
		#If there has been a notice code called, determine what it means and display the appropriate notice.
		if (isset($_COOKIE['LOGIN_NOTICE'])) {
		  switch ($_COOKIE['LOGIN_NOTICE']) {
		    case 0: $class = "error"; $message = "Invalid username or password."; break;
		    case 1: $class = "warning"; $message = "Please enter both a username and password."; break;
            case 2: $class = "warning"; $message = "That user does not exist."; break;
		  }
		  echo "<p class='$class message' style='position: relative; top: -64px'>$message</p>";
	    }
      ?>
    </div>
    <div class="content background" style="width: 49.25%; float: left; text-align: center">
      <h2 class="subheader">
        Register
      </h2>
      <hr>
	  <!-- Register form that redirects back to login.php (uses POST). -->
	  <form action="login.php" method="POST">
      <br>
      Username
      <br>
	  <!-- Create a username textbox. -->
      <input style="width: 320px" type="text" name="new-username" value="">
      <br><br>
      Password
      <br>
	  <!-- Create a password textbox. -->
      <input style="width: 320px" type="password" name="new-password" value="">
      <br>
      <br>
	  <!-- Create the reset button. -->
      <input style="width: 160px; position: relative; top: 16px" type="reset" value="Reset" />
      <br>
	  <!-- Create the submit button. -->
      <input style="position: relative; top: 20px; width: 160px" type="submit" value="Submit" />
      </form>
	  <!-- Register a new user. -->
	  <?php
	    #If there is a username and password filled out in the register section when the page is refreshed...
        if (isset($_POST['new-username']) and isset($_POST['new-password'])) {
		  #If both the username and password are filled out...
          if (!($_POST['new-username'] == "" or $_POST['new-password'] == "")) {
			#Store the post values for the username and password.
            $username = $_POST['new-username'];
            $password = md5($_POST['new-password']);
			#If there is no one using the site with those credentials...
			$count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(name) FROM T_USER WHERE name = '$username'"))['COUNT(name)'];
			if ($count == 0) {
			  #Register the user with that username and password.
              $result = mysqli_query($conn, "INSERT INTO T_USER VALUES ('$username', '$password', null)");
			  #If the query succeeded...
              if ($result) {
				#Tell them that they have been registered.
			    setcookie('REGISTER_NOTICE', 3);
                refresh_page();
              }
			  #Else refresh the page.
              else {
			    setcookie('REGISTER_NOTICE', 0);
			    refresh_page();
              }
			}
			#Else refresh the page.
			else {
			  setcookie('REGISTER_NOTICE', 2);
			  refresh_page();
			}
          }
		  #Else refresh the page.
          else {
			setcookie('REGISTER_NOTICE', 1);
			refresh_page();
          }
		  #Unset the used cookies and post values.
		  unset_cookie('LOGIN_NOTICE');
          unset($_POST['new-username']);
          unset($_POST['new-password']);
        }
		#If there has been a notice code called, determine what it means and display the appropriate notice.
		if (isset($_COOKIE['REGISTER_NOTICE'])) {
		  switch ($_COOKIE['REGISTER_NOTICE']) {
		    case 0: $class = "error"; $message = "Invalid username or password."; break;
		    case 1: $class = "warning"; $message = "Please enter both a username and password."; break;
			case 2: $class = "warning"; $message = "That username already exists."; break;
			case 3: $class = "ok"; $message = "Registration complete."; break;
		  }
		  echo "<p class='$class message' style='position: relative; top: -64px'>$message</p>";
	    }
	  ?>
    </div>
	<!-- Draw the footer, telling the user if their session has expired. -->
    <div class="footer background">
      <h1 class="header">
        <?php
		  if (isset($_COOKIE['S_EXPIRED'])) {
			echo "Your session has expired. Please login again.";
			unset_cookie("S_EXPIRED");
		  }
          else if (isset($_COOKIE['S_SERVER'])) {
			echo "There was an internal server error.";
			unset_cookie("S_SERVER");
		  }
		  else {
			echo "Made by Charles, Reg. 1805098";
		  }
		?>
      </h1>
    </div>
  </body>
</html>
<!-- Close the connection and send all of the headers through. -->
<?php
  mysqli_close($conn);
  ob_flush();
?>