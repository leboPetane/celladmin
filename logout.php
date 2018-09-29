<?php require "db.php"; ?>

<?php
  session_start();
  $_SESSION["logged_in"] = false;
  session_destroy();
  mysqli_close($conn);
  header("Location: ".ROOT_URL);
?>
