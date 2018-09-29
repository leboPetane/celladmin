<?php require "db.php"; ?>

<?php

  session_start();

  if(count($_SESSION) <= 1){ //user has never logged in
    $_SESSION["logged_in"] = false; //user must log in
    //echo "never logged in\n";
  }else{
    //echo "logged in before\n";
    if ($_SESSION["logged_in"] == true){
      //echo "User has not logged out and is in log in page\n";
      header("Location: ".WELCOME); //user does not need to log in again, count($_SESSION) will br > 1
    }else{
      //echo "User has logged out and must log in again\n";
    }
  }

  $msg = "";
  $msgClass = "danger";

  if (filter_has_var(INPUT_POST, "submit")){
    $email = mysqli_real_escape_string($conn, htmlentities($_POST["email"]));
    $pass = mysqli_real_escape_string($conn, htmlentities($_POST["password"]));

    //validate email later

    //get the user
    $query = "SELECT * FROM cell_leaders WHERE username = \"$email\"";

    $results = mysqli_query($conn, $query);

    if (mysqli_query($conn, $query)){
      $data = mysqli_fetch_assoc($results);
      if ($data == null){
        $msg = "Cell leader not registered, please register your cell.";
        $msgClass = "danger";
      }else{
        if ($data["password"] != $pass){
          $msg = "Incorrect password.";
          $msgClass = "danger";
        }else{
          $_SESSION["logged_in"] = true;
          $_SESSION["username"] = $data["cell_name"];
          $_SESSION["user"] = $data["username"];
          $_SESSION["leader"] = $data["title"]." ".$data["name"];
          define(LOGGED,"true");
          header("Location: ".WELCOME);
        }
      }
    }else{
      $msg = "Error: Unable to log in";
      $msgClass = "danger";
    }
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="http://localhost/bootstrap/css/bootstrap.min.css">
    <title>Cell Ministry</title>
  </head>
  <body style="padding-top:10%; background:url('images/img.png'); background-size: 90%; background-repeat: no-repeat">
    <div class="card container" style="width: 18rem;">
      <div class="card-body">
        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
          <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" class="form-control" name="email" >
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" name="password" >
          </div>
          <button type="submit" name="submit" class="btn btn-outline-info">Log in</button>
          <a href="register.php" class="btn btn-outline-info">Register</a>
          <a href="userpass.php" class="btn btn-link btn-sm">Forgot password</a>
          <?php if ($msg != ""): ?>
            <small id="emailHelp" class="form-text text-muted <?php echo $msgClass?>"><?php echo $msg ?></small>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </body>
</html>
