<!-- REG: 1805098 -->

<!-- Create the container for the buttons. -->
<div class='within' style='position: relative; float: right; width: calc(20% - 48px); height: 96px; right: 16px; text-align: center'>
  <!-- Draw the title and line divider. -->
  Order By<hr>
  <!-- Initialize a list. -->
  <ul style='position: relative; top: -28px; display: inline-block'>
  <?php
    #Check how the project's tasks are ordered.
    $result = mysqli_query($conn, "SELECT orderby FROM T_PROJECT WHERE name = '$project' AND user = '$user'");
    $orderby = mysqli_fetch_assoc($result)['orderby'];
    #Cycle through the order types, creating each of their buttons.
    $orderlist = [ [ "date", "dueDate" ], [ "name", "name" ], [ "priority", "priority" ] ];
    for ($i = 0; $i < sizeof($orderlist); $i++) {
      #If the project is ordered in this way... outline its button in white.
      switch ($orderby == $orderlist[$i][1]) {
        case true: $selected = 'selected'; break;
        case false: $selected = ''; break;
      }
      #Draw the button list item.
      echo "<li class='button magenta-button $selected task-button order-by-" . $orderlist[$i][0] . "' style='position: absolute; left: calc(" . ($i * 40) . "px - 40px); width: 32px; height: 32px'>";
	  echo "  <a href='?orderby_$i' style='opacity: 0'>X</a>";
	  echo "</li>";
      #Check if the URL has the 'orderby' command. If so, change the project's task order.
      if (isset($_GET["orderby_$i"])) {
        set_task_order($conn, $project, $orderlist[$i][1]);
      }
    }
  ?>
  </ul>
</div>