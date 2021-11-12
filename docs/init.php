<!-- REG: 1805098 -->

<!-- NOTE: This file runs every time a page is refreshed. -->

<?php
  #If the user has edited a project before this...
  if (isset($_COOKIE["PROJECT_EDIT_NAME"]) and isset($_COOKIE["PROJECT_EDIT_NEW_NAME"])) {
    #Access the necessary cookies.
    $project_name = $_COOKIE["PROJECT_EDIT_NAME"];
    $new_name = $_COOKIE["PROJECT_EDIT_NEW_NAME"];
    #Update the edited project.
	if (strlen($new_name) <= 64) {
      $result = mysqli_query($conn, "UPDATE T_PROJECT SET name = '$new_name' WHERE name = '$project_name'") or die("1 : " . mysqli_error($conn));
	}
    #Delete the accessed cookies.
    unset_cookie("PROJECT_EDIT_NAME");
    unset_cookie("PROJECT_EDIT_NEW_NAME");
  }
  #The task date's regex constraint. (YYYY-MM-DD hh:mm:ss)
  $date_pattern = '/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/';
  #If the user has edited a task before this...
  if (isset($_COOKIE["TASK_EDIT_NAME"]) and isset($_COOKIE["TASK_EDIT_PROJECT"]) and isset($_COOKIE["TASK_EDIT_NEW_NAME"]) and isset($_COOKIE["TASK_EDIT_NEW_DESCRIPTION"]) and isset($_COOKIE["TASK_EDIT_NEW_DATE"]) and isset($_COOKIE["TASK_EDIT_NEW_PRIORITY"])) {
    #Access the necessary cookies.
    $restrict_set = "";
    $task_name = $_COOKIE["TASK_EDIT_NAME"];
    $project_name = $_COOKIE["TASK_EDIT_PROJECT"];
    $new_name = $_COOKIE["TASK_EDIT_NEW_NAME"];
    $new_description = $_COOKIE["TASK_EDIT_NEW_DESCRIPTION"];
    $new_date = $_COOKIE["TASK_EDIT_NEW_DATE"];
	#Check if the new name is reasonably small.
    if (strlen($new_name) <= 64) {
      $restrict_set .= "name = '$new_name'";
    }
	#Check if the new description is reasonably small.
    if (strlen($new_description) <= 1024) {
	  if ($restrict_set) {
        $restrict_set .= ", description = '$new_description'";
	  }
	  else {
		$restrict_set .= "description = '$new_description'";
	  }
    }
    #Check if the new date would be syntactically correct.
    if (preg_match($date_pattern, $new_date)) {
	  if ($restrict_set) {
        $restrict_set .= ", dueDate = '$new_date'";
	  }
	  else {
		$restrict_set .= "dueDate = '$new_date'";
	  }
    }
    #Check if the new priority is between 1 and 99 (inclusive).
    $new_priority = $_COOKIE["TASK_EDIT_NEW_PRIORITY"];
    if (ctype_digit($new_priority) and $new_priority == max(1, min($new_priority, 99))) {
	  if ($restrict_set) {
        $restrict_set .= ", priority = $new_priority";
	  }
	  else {
		$restrict_set .= "priority = $new_priority";
	  }
    }
    #Update the edited task.
	if ($restrict_set) {
	  $result = mysqli_query($conn, "UPDATE T_TASK SET $restrict_set WHERE name = '$task_name' AND project = '$project_name'");
	}
    #Delete the accessed cookies.
    unset_cookie("TASK_EDIT_NAME");
    unset_cookie("TASK_EDIT_PROJECT");
    unset_cookie("TASK_EDIT_NEW_NAME");
    unset_cookie("TASK_EDIT_NEW_DESCRIPTION");
    unset_cookie("TASK_EDIT_NEW_DATE");
    unset_cookie("TASK_EDIT_NEW_PRIORITY");
  }
?>