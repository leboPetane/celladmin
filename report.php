<?php require "db.php"; ?>
<?php
  session_start();
  $msg = "";
  $msgClass = "danger";
  $members = "other";
  if ($_SESSION["logged_in"] == false){
    header("Location: ".ROOT_URL);
  }else{

    //================================GET REPORT ===============================

    $id = $_GET["report"];

    $query = "SELECT * FROM reports WHERE id= '$id'";
    $results = mysqli_query($conn, $query);
    if (mysqli_query($conn, $query)){
      $report = mysqli_fetch_assoc($results);
    }

  }

?>
<?php include "header.php";?>

    <div class="container pad-top">
      <h5 class="display-4 alert-light"> Cell Report: <?php echo $report["topic"]; ?></h5>
    </div>

    <div class="container my-report">
      <ul>
        <li>Cell Leader <span class="report-value"> <?php echo $_SESSION["leader"]; ?> </span> </li>
        <li>Cell Name <span class="report-value"> <?php echo $report["cell_name"]; ?> </span> </li>
        <li>Date Of Cell <span class="report-value"> <?php echo Date("Y F d | h:ia", strtotime($report["date"])); ?> </span></li>
        <li>Topic <span class="report-value"> <?php echo $report["topic"]; ?> </span></li>
        <li>Summary <span class="report-value">  <?php echo $report["summary"]; ?> </span></li>
        <li>Location <span class="report-value">  <?php echo $report["location"]; ?> </span></li>
        <li>Attendance <span class="report-value">  <?php echo $report["attendance"]; ?> </span></li>
        <li>First Timers <span class="report-value">  <?php echo $report["first_timers"]; ?> </span></li>
        <li>New Converts <span class="report-value">  <?php echo $report["new_converts"]; ?> </span></li>
        <li>Holy Ghost Filled <span class="report-value">  <?php echo $report["holy_ghost_filled"]; ?> </span></li>
        <li>Offering <span class="report-value"> <?php echo $report["offering"]; ?> </span></li>
        <a href="<?php echo WELCOME; ?>" class="btn btn-outline-info">Go home</a>
      </ul>
    </div>
  </body>

<?php include "footer.php" ?>
