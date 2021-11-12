<!-- REG: 1805098 -->

<!-- Initialize a list. -->
<ul>
  <?php
    #Start a session.
    if (!isset($_SESSION)) {
      session_start();
    }
    #Retrieve the selected project.
    $user = $_SESSION['USER'];
	$result = mysqli_query($conn, "SELECT selectedProject FROM T_USER WHERE name = '$user'");
	$project = mysqli_fetch_assoc($result)['selectedProject'];
    #If there is a task selected...
    if (!($project == "")) {
      #Retrieve how the tasks are ordered and filtered within the selected project.
      $result = mysqli_query($conn, "SELECT orderby, showTasks FROM T_PROJECT WHERE name = '$project' AND user = '$user'");
	  $row = mysqli_fetch_assoc($result);
	  $orderby = $row['orderby'];
	  $showtask = $row['showTasks'];
      #Retrieve how many tasks the selected project has.
      $result = mysqli_query($conn, "SELECT COUNT(name) FROM T_TASK WHERE project = '$project' AND user = '$user'");
      $task_count = mysqli_fetch_assoc($result)['COUNT(name)'];
      #Retrieve the current date and the date a week from now.
	  $current_date = date("Y-m-d H:i:s");
	  $close_date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + (86400 * 7));
      #Set additional query constraints based on filtering.
	  switch ($showtask) {
	    case "all": $constraint = ""; break;
		case "complete": $constraint = "AND complete = 1"; break;
		case "close": $constraint = "AND dueDate <= '$close_date' AND dueDate > '$current_date' AND complete = 0"; break;
		case "overdue": $constraint = "AND dueDate <= '$current_date' AND complete = 0"; break;
		default: $constraint = ""; break;
	  }
      #Retrieve the list of tasks based on the project's filtering.
	  $result = mysqli_query($conn, "SELECT * FROM T_TASK WHERE project = '$project' AND user = '$user' $constraint ORDER BY $orderby");
    }
	if (!($result or $project == "")) {
	  //echo "<li class='error'>" . __LINE__ . " : " . mysqli_error($conn) . " : " . "SELECT * FROM T_TASK WHERE project = '$project' AND user = '$user' ORDER BY $orderby" . "</li>";
	}
	else {
	  $i = 1;
      #Loop through each of the selected project's tasks.
	  while ($row = mysqli_fetch_assoc($result)) {
        #Store the task's attributes.
		$name = $row['name'];
		$description = $row['description'];
        $priority = $row['priority'];
		$complete = $row['complete'];
		$expanded = $row['expanded'];
		$editing = $row['editing'];
        switch ($editing) {
          case 0: $hlist_x = '95'; $cbox_y = '37px'; break;
          case 1: $hlist_x = '92.5'; $cbox_y = '42px'; break;
        }
        #Retrieve the task's due date and calculate the difference between that and the current datetime.
		$date = $row['dueDate'];
		$date_diff = date_diff(date_create($date), date_create(date("Y-m-d H:i:s")));
		$date_diff_int = strtotime($date) - strtotime(date("Y-m-d H:i:s"));
		$almost_due = false;
		$overdue = false;
        #If the due date is within a week, set it to almost due.
		if (($date_diff_int / 86400) < 7) {
		  $almost_due = true;
		}
        #If the due date has passed, set it to overdue.
		if ($date_diff_int < 0) {
		  $overdue = true;
		}
        #Set the task box style based on that.
		$class = "task";
		if ($complete) {
		  $class = "completed " . $class;
		}
		elseif ($overdue) {
		  $class = "overdue " . $class;
		}
		elseif ($almost_due) {
		  $class = "almost-due " . $class;
		}
        #Draw the task box.
		echo "<li class='$class' style='left: 8px' draggable='true'>";
        #Draw the task's name. If it has no name, create a buffer space. If it is being edited, draw a textbox instead.
		switch ($editing) {
		  case 0:
			if ($name) {
			  echo $name;
			}
			else {
			  echo "<br>";
			};
		  break;
		  case 1:
			echo "<form id='name-edit'>";
			echo "<input type='text' name='name_task_$i' value='$name'>";
			echo "</form>";
		  break;
		}
        #Draw the arrow button that expands or contracts the task.
		switch ($expanded) {
		  case true: $expand_class = "expand-up"; break;
		  case false: $expand_class = "expand-down"; break;
		}
		echo "<div class='hidden-box button $expand_class' style='top: 2px; left: calc($hlist_x% - 25px); text-align: center'><a href='?expand_task_$i' style='height: 12px'></a></div>";
		#If editing, draw a input number box with priority inside it. If not, draw the actual priority within its own box.
        switch ($editing) {
          case 0: 
		    echo "<div class='task-box' style='top: 2px; text-align: center'>" . $priority . "</div>";
		  break;
          case 1:
			echo "<form id='priority-edit'>";
			echo "<input type='number' name='priority_task_$i' value='$priority' min='1' max='99' style='width: 32px; position: absolute; right: 8px; top: 5px'>";
			echo "</form>";
          break;
        }
        #If the task is expanded...
		if ($expanded) {
          #Create a divider.
		  echo "<hr>";
          #If the task is not being edited, draw the due date and whether the task's complete or overdue.
		  switch ($editing) {
			case 0:
			  if ($complete) {
				echo "COMPLETE (" . $date . ")";
			  }
			  elseif ($overdue) {
				echo "OVERDUE (" . $date . ")";
			  }
			  else {
                #If the task is neither complete or overdue, draw the amount of time until the due date hits.
				echo $date_diff->format('%y years, %m months, %d days') . " (" . $date . ")";
			  }
			break;
            #If it is being edited, create the date input box. Its tooltip tells the user the format.
			case 1:
			  echo "<form id='date-edit'>";
			  echo "<input type='text' name='date_task_$i' value='$date' pattern='$date_pattern' title='YYYY-MM-DD hh:mm:ss'>";
			  echo "</form>";
            break;
		  }
          #Draw the button to complete the task. It acts as a slot for the 'complete', 'overdue' and 'almost due' icons.
		  echo "<div class='task-box completed' style='top: $cbox_y'><a href='?complete_task_$i' style='height: 6px'></a></div>";
		  #If the task is complete, draw the green 'complete' icon inside the button slot.
          if ($complete) {
			echo "<div class='check completed' style='top: calc($cbox_y + 2px)'></div>";
		  }
          #If the task is overdue, draw the red 'overdue' icon inside the button slot.
		  elseif ($overdue) {
			echo "<div class='check overdue' style='top: calc($cbox_y + 2px)'></div>";
		  }
          #If the task is due within a week, draw the orange 'almost due' icon inside the button slot.
		  elseif ($almost_due) {
			echo "<div class='check almost-due' style='top: calc($cbox_y + 2px)'></div>";
		  }
          #Create a divider.
		  echo "<hr>";
          #Draw the task's description. If it has no description, create a buffer space. If it is being edited, draw a textbox instead.
		  switch ($editing) {
			case 0:
			  if ($description) {
				echo $description;
			  }
			  else {
				echo "<br>";
			  };
			break;
			case 1:
			  echo "<form id='description-edit'>";
			  echo "<textarea name='description_task_$i' form='description-edit' rows='2' style='width: calc(100% - 6px)'>$description</textarea>";
			  echo "</form>";
			break;
		  }
		}
        #Create a list containing the save button if editing the task or the edit button if not, and the delete button. These buttons will
        #only appear if the task is being hovered over.
		echo "<ul class='hidden'>";
		echo "  <li class='button red-button task-button delete' style='top: 3px; left: calc($hlist_x% - 45px)'><a href='?delete_task_$i'></a></li>";
		switch ($editing) {
		  case 0: echo "<li class='button yellow-button task-button edit' style='top: 3px; left: calc($hlist_x% - 65px)'><a href='?edit_task_$i'></a></li>"; break;
		  case 1: echo "<li class='button cyan-button task-button save' style='top: 3px; left: calc($hlist_x% - 65px)'><a href='?save_task_$i' onclick='submit_task_form()'></a></li>"; break;
		}
		echo "</ul>";
        #Check if the URL has the 'delete_task' command. If so, delete the task.
		if (isset($_GET["delete_task_$i"])) {
		  delete_task($conn, $name, $project);
		}
        #Check if the URL has the 'complete_task' command. If so, complete the task.
		elseif (isset($_GET["complete_task_$i"])) {
		  complete_task($conn, $name, $project);
		}
        #Check if the URL has the 'expand_task' command. If so, expand the task.
		elseif (isset($_GET["expand_task_$i"])) {
		  expand_task($conn, $name, $project);
		}
        #Check if the URL has the 'edit_task' command. If so, set the task to editing mode.
		elseif (isset($_GET["edit_task_$i"])) {
		  edit_task($conn, $name, $project);
		}
        #Check if the URL has the 'save_task' command. If so, save the task.
		elseif (isset($_GET["save_task_$i"])) {
		  save_task($conn, $name, $project);
		}
		echo "</li>";
		$i += 1;
	  }
      #Retrieve all tasks from the selected project, regardless of filtering.
	  $result = mysqli_query($conn, "SELECT * FROM T_TASK WHERE project = '$project' AND user = '$user' ORDER BY $orderby");
	  $project_completed = 0;
      $project_overdue = 0;
      #Check which tasks are complete and which are overdue.
	  while ($row = mysqli_fetch_assoc($result)) {
		$complete = $row['complete'];
		$date = $row['dueDate'];
		$date_diff = date_diff(date_create($date), date_create(date("Y-m-d H:i:s")));
		$date_diff_int = strtotime($date) - strtotime(date("Y-m-d H:i:s"));
		$almost_due = false;
		$overdue = false;
		if (($date_diff_int / 86400) < 7) {
		  $almost_due = true;
		}
		if ($date_diff_int < 0) {
		  $overdue = true;
		}
        #For each project that is complete or overdue, increase the project's percentages by a regular fraction of the whole.
		if ($complete) {
          $project_completed += 1.0 / $task_count;
		}
		elseif ($overdue) {
          $project_overdue += 1.0 / $task_count;
		}
	  }
      #Alter the project's overdue and completed values based on this information.
      update_complete_count($conn, $project, $project_completed, 3);
      update_overdue_count($conn, $project, $project_overdue, 3);
	}
  ?>
<!-- End the list. -->
</ul>