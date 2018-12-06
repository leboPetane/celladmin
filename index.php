<?php require "db.php"; ?>

<?php

  session_start();

  /* This handles a case where the user has loggged in and closes their window */

  if(count($_SESSION) <= 1) //user has never logged in as there are three Session variables created on login
  {
    $_SESSION["logged_in"] = false;
  }
  else
  {
    if ($_SESSION["logged_in"] == true) //user does not need to log in again, count($_SESSION) will be > 1
    {
      header("Location: ".WELCOME);
    }
    else
    {
      //echo "User has logged out and must log in again\n";
    }
  }

  /* Variables for error notifications to the user */
  $msg = "";
  $msgClass = "danger";

  if (filter_has_var(INPUT_POST, "submit"))
  {

    $email = htmlentities($_POST["email"]);
    $pass = htmlentities($_POST["password"]);

    //validate email later
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $msg = "Email is invalid.";
      $msgClass = "danger";
    }
    //get the user
    //$query = "SELECT * FROM cell_leaders WHERE username = \"$email\"";

    $sql = "SELECT * FROM cell_leaders WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $email]);
    $leader = $stmt-> fetch();


    if($leader){
      if (!password_verify($pass, $leader->password))
      {
        $msg = "Incorrect password.";
        $msgClass = "danger";
      }
      else
      {
        $_SESSION["logged_in"] = true;
        $_SESSION["username"] = $leader->cell_name;
        $_SESSION["user"] = $leader->username;
        $_SESSION["leader"] = $leader->title." ".$leader->name;
        define(LOGGED,"true");
        header("Location: ".WELCOME);
      }
    }else{
      $msg = "Cell leader not registered, please register your cell.";
      $msgClass = "danger";
    }

  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <!-- TO do : bootstrap CDN -->
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
