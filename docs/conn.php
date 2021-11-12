<!-- REG: 1805098 -->

<?php
  #Load configuration settings for the connection.
  $config = require("config.php");
  #Launch the MySQL connection.
  $conn = mysqli_connect($config['HOST'], $config['USERNAME'], $config['PASSWORD']);
  #State if there has been an error in the connection.
  if (mysqli_connect_errno()) {
    echo "<li class='error'>[" . mysqli_connect_errno() . "]: " . mysqli_connect_error() . "</li>";
  }
  elseif (!$conn) {
    echo "<li class='error'>" . mysqli_connect_error() . "</li>";
  }
  #Try and connect to the configured database.
  $db = mysqli_select_db($conn, $config['DATABASE']);
  #Run the MySQL startup file, executing each query line by line.
  $file = explode("\n", file_get_contents("docs/create_database.sql"));
  if ($file) {
    foreach ($file as $line) {
  	  $line = str_replace(";", "", $line);
	  if (strlen($line) != 0) {
	    if (substr($line, 0, 2) != "--") {
		  $result = mysqli_query($conn, $line) or die(mysqli_error($conn));
	    }
	  }
    }
  }
?>