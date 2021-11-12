<!-- REG: 1805098 -->

<!-- Create the container for the buttons. -->
<div class='within' style='position: relative; float: right; width: calc(20% - 48px); height: 96px; top: 104px; left: calc(20% - 54px); text-align: center'>
  <!-- Draw the title and line divider. -->
  Show Tasks<hr>
  <!-- Initialize a list. -->
  <ul style='position: relative; top: -28px; display: inline-block'>
  <?php
    #Check how the project's tasks are filtered.
    $result = mysqli_query($conn, "SELECT showTasks FROM T_PROJECT WHERE name = '$project' AND user = '$user'");
    $show = mysqli_fetch_assoc($result)['showTasks'];
    #Cycle through the task filter types, creating each of their buttons.
    $showlist = [ [ "all", "all" ], [ "completed", "complete" ], [ "almostdue", "close" ], [ "overdue", "overdue" ] ];
    for ($i = 0; $i < sizeof($showlist); $i++) {
      #If the project filters its tasks in this way... outline its button in white.
      switch ($show == $showlist[$i][1]) {
        case true: $selected = 'selected'; break;
        case false: $selected = ''; break;
      }
      #Draw the button list item.
      echo "<li class='button magenta-button $selected show-" . $showlist[$i][0] . "' style='position: absolute; left: calc(" . ($i * 40) . "px - 60px); width: 32px; height: 32px'>";
	  echo "  <a href='?show_$i' style='opacity: 0'>X</a>";
	  echo "</li>";
      #Check if the URL has the 'show' command. If so, change how the project's tasks are filtered.
      if (isset($_GET["show_$i"])) {
        set_tasks_shown($conn, $project, $showlist[$i][1]);
      }
    }
  ?>
  </ul>
</div>