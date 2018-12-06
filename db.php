<?php
  require "config.php";
  //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);


  $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;

  //Create PDO instance or object (Cause its a class)

  $pdo = new PDO($dsn, DB_USER, DB_PASS);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ); // defualt fetch mode

  //if (mysqli_connect_errno()){
  //echo "Failed to connect to MySQL".myspli_connect_errno();
  //}
?>
