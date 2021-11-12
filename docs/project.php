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
    #List the user's projects.
	$result = mysqli_query($conn, "SELECT * FROM T_PROJECT WHERE user = '$user' ORDER BY priority");
    #Check if the SQL query was successful...
	if (!$result) {
      #Display an error message. 
	  echo "<li class='error'>" . __LINE__ . " : " . mysqli_error($conn) . "</li>";
	}
	else {
	  $i = 1;
      #Loop through each of the projects.
	  while ($row = mysqli_fetch_assoc($result)) {
        #Store the project's attributes.
		$name = $row['name'];
        $progress = $row['progress'];
        $overdue = $row['overdueCount'];
		$expanded = $row['expanded'];
        $editing = $row['editing'];
        #Check whether the project has been selected. If so, outline it in white.
		switch (isset($_GET["project_$i"]) or $project == $name) {
		  case true: $class = "nav-button error"; break;
		  case false: $class = "nav-button"; break;
		}
		switch ($class) {
		  case "": echo "<li style='left: -12%; width: calc(100% - 4px)'>"; break;
		  default: echo "<li class='$class' style='left: -12%; width: calc(100% - 4px)'>"; break;
		}
        #Make sure the project can't be selected when it's being edited.
        switch ($editing) {
		  case 0: echo "<a href='?project_$i'>"; break;
          case 1: echo "<a>"; break;
        }
        #Draw the project's name. If it has no name, create a buffer space. If it is being edited, draw a textbox instead.
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
			echo "<form id='project-edit'>";
			echo "<input type='text' name='name_project_$i' value='$name' style='width: 85%'>";
			echo "</form>";
		  break;
		}
		echo "</a>";
        #Draw the arrow button that expands or contracts the project.
		switch ($expanded) {
		  case true: $expand_class = "expand-up"; break;
		  case false: $expand_class = "expand-down"; break;
		}
		echo "<div class='hidden-box button $expand_class' style='top: 2px; left: 85%; text-align: center'><a href='?expand_project_$i' style='height: 12px'></a></div>";
		#Create a list containing the save button if editing the project or the edit button if not, and the delete button. These buttons will
        #only appear if the project is being hovered over.
        echo "<ul class='hidden'>";
		echo "  <li class='button red-button task-button delete' style='top: 3px; left: calc(85% - 23px)'><a href='?delete_project_$i'></a></li>";
        switch ($editing) {
		  case 0: echo "<li class='button yellow-button task-button edit' style='top: 3px; left: calc(85% - 43px)'><a href='?edit_project_$i'></a></li>"; break;
		  case 1: echo "<li class='button cyan-button task-button save' style='top: 3px; left: calc(85% - 43px)'><a href='?save_project_$i' onclick='submit_project_form()'></a></li>"; break;
		}
        echo "</ul>";
		echo "</li>";
        #If the project is expanded, draw the complete and overdue bars.
		if ($expanded) {
		  echo "<div class='back-bar'><div class='progress-bar' style='width: calc(100% * $progress); background-color: #62B44B'>" . min(100, 100 * $progress) . "%</div></div>";
		  echo "<div class='back-bar'><div class='progress-bar' style='width: calc(100% * $overdue); background-color: #AC3E12'>" . min(100, 100 * $overdue) . "%</div></div>";
		}
        #Check if the URL has the 'project' command. If so, change the project.
		if (isset($_GET["project_$i"])) {
		  change_project($conn, $row['name']);
		}
        #Check if the URL has the 'delete_project' command. If so, delete the project.
		elseif (isset($_GET["delete_project_$i"])) {
		  delete_project($conn, $row['name']);
		}
        #Check if the URL has the 'expand_project' command. If so, expand the project.
		elseif (isset($_GET["expand_project_$i"])) {
		  expand_project($conn, $row['name']);
		}
        #Check if the URL has the 'edit_project' command. If so, edit the project.
        elseif (isset($_GET["edit_project_$i"])) {
		  edit_project($conn, $name);
		}
        #Check if the URL has the 'save_project' command. If so, save the project.
		elseif (isset($_GET["save_project_$i"])) {
		  save_project($conn, $name);
		}
		$i += 1;
	  }
	}
  ?>
<!-- End the list. -->
</ul>