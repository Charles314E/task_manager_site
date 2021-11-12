<!-- REG: 1805098 -->

<!-- NOTE: Many of these functions and methods refresh the page on completion. -->

<?php
  #Start the session, because many of these functions and methods require the 'user' session variable.
  if (!isset($_SESSION)) {
    session_start();
  }
  #Save session variables into cookies.
  function save_session() {
    if (isset($_SESSION))
    {
      foreach ($_SESSION as $id => $value) {
        $_COOKIE['S_' . $id] = $value;
      }
      return true;
    }
    else {
      return false;
    }
  }
  #Load session variables from cookies.
  function load_session() {
    foreach ($_COOKIE as $id => $value) {
      if (substr($id, 0, 2) == "S_") {
        $new_id = explode("S_", $id, 2)[0];
        $_SESSION[$new_id] = $value;
      }
    }
  }
  #Refresh or redirect page, based on $resource.
  function refresh_page($resource = null) {
    save_session();
    if (isset($resource)) {
      header("Refresh: 0.1; url=$resource");
    }
    else {
      $resource = preg_split("/[?#]/", $_SERVER["REQUEST_URI"], 2)[0];
      header("Refresh: 0.1; url=$resource");
    }
    exit();
  }
  #Delete cookie.
  function unset_cookie($name) {
	$date = time() - 3600;
    if (isset($_COOKIE[$name])) {
	  setcookie($name, "", $date);
      unset($_COOKIE[$name]);
    }
  }
  #Make a button with a 'plus' icon that creates a new item (task or project).
  function create_new_item_button($text, $href, $max, $count, $top) {
	switch ($count < $max) {
	  case true: $color = "green"; break;
	  case false: $color = "black"; break;
	}
	echo "<div class='group'><ul>";
	echo "  <li style='position: relative; width: 11.125%; float: left; top: -10%; bottom: " . strval($top) . "px; left: -5px; margin-right: 0px' class='" . $color . "-button button add-new'><a><br></a></li>";
	echo "  <li style='position: relative; width: 68.875%; float: left; top: -10%; bottom: " . strval($top) . "px; left: -5px; margin-left: 0px'><a href='?$href'>$text</a></li>";
	echo "</ul></div>";
  }
  #Change the selected project for a logged in user.
  function change_project($conn, $project) {
    $user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT * FROM T_USER WHERE name = '$user'");
	if ($result) {
	  $result = mysqli_query($conn, "UPDATE T_USER SET selectedProject = '$project' WHERE name = '$user'") or die(mysqli_error($conn));
	}
	else {
	  $result = mysqli_query($conn, "INSERT INTO T_USER VALUES ('$user', '$project')");
	}
	refresh_page();
  }
  #Create a new project in editing mode.
  function add_project($conn) {
    $user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT COUNT(name) FROM T_PROJECT WHERE user = '$user'") or die(mysqli_error($conn));
	$count = mysqli_fetch_assoc($result)['COUNT(name)'];
	$random = rand(0, 100) / 100.0;
	$name = $user . " Project 1";
	$result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM T_PROJECT WHERE name = '$name'"))['name'];
	$done = false;
	$i = 1;
	while ($done == false) {
	  if ($result != $name) {
		$done = true;
	  }
	  else {
	    $name = $user . " Project " . strval($i + 1);
	    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM T_PROJECT WHERE name = '$name'"))['name'];
	    $i += 1;
	  }
    }
    $result = mysqli_query($conn, "UPDATE T_PROJECT SET editing = 0") or die(mysqli_error($conn));
	$result = mysqli_query($conn, "INSERT INTO T_PROJECT ( name, user, priority, progress, overdueCount, expanded, editing, orderby, showTasks ) VALUES ( '$name', '$user', $count + 1, 0, 0, 0, 1, 'priority', 'all' )") or die(mysqli_error($conn));
	refresh_page();
  }
  #Delete particular project.
  function delete_project($conn, $name) {
    $user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT selectedProject FROM T_USER WHERE name = '$user'") or die(mysqli_error($conn));
	$row = mysqli_fetch_assoc($result);
	if ($row['selectedProject'] == $name) {
	  $result = mysqli_query($conn, "UPDATE T_USER SET selectedProject = null WHERE name = '$user'") or die(mysqli_error($conn));
	}
	$result = mysqli_query($conn, "DELETE FROM T_TASK WHERE project = '$name' AND user = '$user'") or die(mysqli_error($conn));
	$result = mysqli_query($conn, "DELETE FROM T_PROJECT WHERE name = '$name' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Check which project is currently selected.
  function get_selected_project($conn) {
    $user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT * FROM T_USER WHERE name = '$user'");
	return mysqli_fetch_assoc($result)['selectedProject'];
  }
  #Expand/contract a particular project to see/hide its notes.
  function expand_project($conn, $name) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT expanded FROM T_PROJECT WHERE name = '$name' AND user = '$user'") or die(mysqli_error($conn));
	$expanded = mysqli_fetch_assoc($result)['expanded'];
	switch ($expanded) {
	  case 1: $expanded = 0; break;
	  case 0: $expanded = 1; break;
	}
	$result = mysqli_query($conn, "UPDATE T_PROJECT SET expanded = $expanded WHERE name = '$name' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Switch which project is being edited.
  function edit_project($conn, $name) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "UPDATE T_PROJECT SET editing = 0 WHERE user = '$user'") or die(mysqli_error($conn));
	$result = mysqli_query($conn, "UPDATE T_PROJECT SET editing = 1 WHERE name = '$name' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Save a particular project.
  function save_project($conn, $name) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "UPDATE T_PROJECT SET editing = 0 WHERE user = '$user'") or die(mysqli_error($conn));
	setcookie('PROJECT_EDIT_NAME', $name);
	refresh_page();
  }
  #Create a new task in editing mode.
  function add_task($conn, $project) {
    $user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT COUNT(name) FROM T_TASK WHERE project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	$count = mysqli_fetch_assoc($result)['COUNT(name)'];
	$random = rand(0, 100) / 100.0;
	$check_name = "New Task";
	$result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM T_TASK WHERE name = '$check_name' AND project = '$project' AND user = '$user'"))['name'];
	$done = false;
	$i = 1;
	while ($done == false) {
	  if ($result != $check_name) {
		$done = true;
	  }
	  else {
	    $check_name = "New Task (" . strval($i) . ")";
	    $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM T_TASK WHERE name = '$check_name' AND project = '$project' AND user = '$user'"))['name'];
	    $i += 1;
	  }
    }
	$name = $check_name;
	$time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + (86400 * 15));
	$result = mysqli_query($conn, "UPDATE T_TASK SET editing = 0") or die(mysqli_error($conn));
	$result = mysqli_query($conn, "INSERT INTO T_TASK VALUES ('$name', '$project', '$user', 'Hello.', ($count + 1), 0, '$time', 1, 1)") or die("1 : " . mysqli_error($conn));
	refresh_page();
  }
  #Delete particular task within a project.
  function delete_task($conn, $name, $project) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "DELETE FROM T_TASK WHERE name = '$name' AND project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Change whether a particular task is completed or not.
  function complete_task($conn, $name, $project) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT complete FROM T_TASK WHERE name = '$name' AND project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	$complete = mysqli_fetch_assoc($result)['complete'];
	switch ($complete) {
	  case 1: $complete = 0; break;
	  case 0: $complete = 1; break;
	}
	$result = mysqli_query($conn, "UPDATE T_TASK SET complete = $complete WHERE name = '$name' AND project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Expand/contract a particular task to see/hide its notes.
  function expand_task($conn, $name, $project) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT expanded FROM T_TASK WHERE name = '$name' AND project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	$expanded = mysqli_fetch_assoc($result)['expanded'];
	switch ($expanded) {
	  case 1: $expanded = 0; break;
	  case 0: $expanded = 1; break;
	}
	$result = mysqli_query($conn, "UPDATE T_TASK SET expanded = $expanded WHERE name = '$name' AND project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Switch which task within a project is being edited.
  function edit_task($conn, $name, $project) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "UPDATE T_TASK SET editing = 0") or die(mysqli_error($conn));
	$result = mysqli_query($conn, "UPDATE T_TASK SET editing = 1 WHERE name = '$name' AND project = '$project' AND user = '$user'") or die(mysqli_error($conn));
	refresh_page();
  }
  #Save a particular task.
  function save_task($conn, $name, $project) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "UPDATE T_TASK SET editing = 0 WHERE user = '$user'") or die(mysqli_error($conn));
	setcookie('TASK_EDIT_NAME', $name);
	setcookie('TASK_EDIT_PROJECT', $project);
	refresh_page();
  }
  #Reorder the tasks within a project.
  function set_task_order($conn, $name, $orderby) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "UPDATE T_PROJECT SET orderby = '$orderby' WHERE name = '$name' AND user = '$user'");
    refresh_page();
  }
  #Change which tasks are shown for a particular task.
  function set_tasks_shown($conn, $name, $show) {
	$user = $_SESSION['USER'];
	$result = mysqli_query($conn, "UPDATE T_PROJECT SET showTasks = '$show' WHERE name = '$name' AND user = '$user'");
    refresh_page();
  }
  #Change a particular project's completion percentage (green bar).
  function update_complete_count($conn, $project, $n, $precision) {
    $n = round($n, $precision);
	$user = $_SESSION['USER'];
    $result = mysqli_query($conn, "SELECT progress FROM T_PROJECT WHERE name = '$project' AND user = '$user'");
    $progress = round(mysqli_fetch_assoc($result)["progress"], $precision);
    if (!($progress == $n)) {
      $result = mysqli_query($conn, "UPDATE T_PROJECT SET progress = $n WHERE name = '$project' AND user = '$user'");
      refresh_page();
    }
  }
  #Change a particular project's overdue percentage (red bar).
  function update_overdue_count($conn, $project, $n, $precision) {
    $n = round($n, $precision);
	$user = $_SESSION['USER'];
    $result = mysqli_query($conn, "SELECT overdueCount FROM T_PROJECT WHERE name = '$project' AND user = '$user'");
    $overdue = round(mysqli_fetch_assoc($result)["overdueCount"], $precision);
    if (!($overdue == $n)) {
      $result = mysqli_query($conn, "UPDATE T_PROJECT SET overdueCount = $n WHERE name = '$project' AND user = '$user'");
      refresh_page();
    }
  }
?>