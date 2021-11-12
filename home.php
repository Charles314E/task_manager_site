<!-- REG: 1805098 -->

<!-- Stall the sending of the headers, create the session and import the functions. If the session has timed out, redirect to the login page. -->
<?php
  ob_start();
  require("docs/functions.php");
  load_session();
  if (!(isset($_SESSION) and isset($_SESSION['USER']))) {
	setcookie('S_EXPIRED', true);
    unset_cookie('LOGIN_NOTICE');
    unset_cookie('REGISTER_NOTICE');
    refresh_page('login.php');
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
	<!-- Functions to check there is data in the various task and project editing forms, and to store that data in cookies. -->
	<script>
	  function submit_task_form() {
		var new_name = document.getElementById("name-edit").elements[0].value;
	    var new_description = document.getElementById("description-edit").elements[0].value;
		var new_date = document.getElementById("date-edit").elements[0].value;
        var new_priority = document.getElementById("priority-edit").elements[0].value;
	    document.cookie = "TASK_EDIT_NEW_NAME=" + new_name;
	    document.cookie = "TASK_EDIT_NEW_DESCRIPTION=" + new_description;
		document.cookie = "TASK_EDIT_NEW_DATE=" + new_date;
        document.cookie = "TASK_EDIT_NEW_PRIORITY=" + new_priority;
	  }
      function submit_project_form() {
		var new_name = document.getElementById("project-edit").elements[0].value;
	    document.cookie = "PROJECT_EDIT_NEW_NAME=" + new_name;
	  }
	</script>
	<!-- Draw the title. -->
    <div class="header background">
      <h1 class="header">
	    <?php
          echo "Hello, " . $_SESSION['USER'];
		?>
      </h1>
	  <!-- Draw the logout button. -->
      <div class='button task-button green-button logout' style='position: absolute; top: 13px; right: 32px; width: 32px; height: 32px'>
        <a href='?logout' style='opacity: 0'>X</a>
      </div>
	  <!-- Check if the user has pressed the logout button. -->
      <?php
        if (isset($_GET["logout"])) {
          unset($_SESSION['USER']);
          unset_cookie('LOGIN_NOTICE');
          unset_cookie('REGISTER_NOTICE');
          refresh_page('login.php');
        }
      ?>
    </div>
	<!-- Draw the navigation background. -->
    <div class="navigation background">
      <h2 class="subheader">
        Projects
      </h2>
      <hr>
	  <!-- Make the scrollable container for the project tabs, and draw the list of projects (see project.php). -->
      <?php
        $offset = '40px';
        echo "<div class='scrollable' style='left: $offset; width: calc(80% - $offset + 8px); height: 65%'>";
		require("docs/project.php");
	  ?>
      </div>
	  <!-- Check if the number of projects has been maximised, and draw the project creation button, discolouring it based on that. -->
      <?php
        $max_project = 10;
        $result = mysqli_query($conn, "SELECT COUNT(name) FROM T_PROJECT WHERE user = '$user'") or die(mysqli_error($conn));
        $count = mysqli_fetch_assoc($result)['COUNT(name)'];
        create_new_item_button("New Project", "add_project", $max_project, $count, 0);
        if ($count < $max_project) {
          if (isset($_GET["add_project"])) {
            add_project($conn);
          }
        }
      ?>
    </div>
    <div class="content background">
      <h2 class="subheader">
        Tasks
      </h2>
      <hr>
	  <!-- Draw the orderby and filter buttons if there is a project selected. -->
	  <?php
        if (get_selected_project($conn)) {
          require("docs/orderby.php");
		  require("docs/showtasks.php");
        }
      ?>
	  <!-- If there is a project selected, make the scrollable container for the tasks, and draw the list of tasks (see task.php). -->
      <?php
        $offset = '40px';
		switch (get_selected_project($conn)) {
		  case true: $top = -101; break;
		  case false: $top = 0; break;
		}
        echo "<div class='scrollable' style='position: relative; top: " . strval($top) . "px; left: $offset; width: calc(80% - $offset + 8px); height: 65%'>";
		require("docs/task.php");
      ?>
      </div>
	  <!-- Check if the number of tasks within the selected project have been maximised, and draw the task creation button, discolouring it based on that. -->
      <?php
	    if (get_selected_project($conn)) {
          $max_task = 10;
          $result = mysqli_query($conn, "SELECT COUNT(name) FROM T_TASK WHERE project = '$project' AND user = '$user'") or die(mysqli_error($conn));
          $count = mysqli_fetch_assoc($result)['COUNT(name)'];
	      create_new_item_button("New Task", "add_task_$i", $max_task, $count, 103);
          if ($count < $max_task) {
            if (isset($_GET["add_task_$i"])) {
              add_task($conn, $project);
            }
		  }
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