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

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <div class="container">
      <?php foreach ($report as $item): ?>
        <p><?php echo $item; ?></p>
      <?php endforeach; ?>

    </div>
    <a href="<?php echo WELCOME; ?>" class="btn btn-link btn-sm">Go home</a>


  </body>
<?php include "footer.php" ?>
