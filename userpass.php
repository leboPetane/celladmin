<?php require "db.php"; ?>
<?php
  $msg ='';
  $msgClass='';

  if (filter_has_var(INPUT_POST, "submit1")){
    $email = mysqli_real_escape_string($conn, htmlentities($_POST["email"]));
    //$password = mysqli_real_escape_string($conn, htmlentities($_POST["password"]));
    //$password1 = mysqli_real_escape_string($conn, htmlentities($_POST["password1"]));

    //check if email is not already registered

    $query = "SELECT * FROM cell_leaders WHERE username = \"$email\"";

    $results = mysqli_query($conn, $query);

    if (mysqli_query($conn, $query)){
      $data = mysqli_fetch_assoc($results);
      if ($data != null){
        //send email here
        $msg = "Account found! An email has been sent to ".$email." with a new temporary password." ;
        $msgClass = "success";
      }else{
        $msg = "Account not found - please check email" ;
        $msgClass = "danger";
      }
    }else{
      $msg = "Error: Something went wrong, contact dev team";
      $msgClass = "danger";
    }
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link rel="stylesheet" href="http://localhost/bootstrap/css/bootstrap.min.css">
    <meta charset="utf-8">
    <title>Cell Registration</title>
  </head>
  <body >
    <div class="container" style="padding-top:50px;">

        <div class="jumbotron">
          <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">

              <div class="form-group row">

                <label for="email" class="col-sm-2 col-form-label">Enter your email</label>
                <div class="col-sm-10">
                  <input type="email" class="form-control col-md-4" name="email" value="<?php echo isset($_POST["email"])? $email : "" ?>">
                </div>

              </div>
              <?php if($msg != ""): ?>
                <div class="alert alert-<?php echo $msgClass ?>"><?php echo $msg ?></div>
              <?php endif; ?>
              <button type="submit" class="btn btn-outline-info" name="submit1">Find account</button>
              <a href= "<?php echo ROOT_URL; ?>" class="btn btn-outline-info">Go to log in</a>
          </form>
        </div>
      </div>
    <script type="text/javascript" src="http://localhost/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="http://localhost/bootstrap/js/jquery-3.3.1.js"/></script>
    <script type="text/javascript">
      $('.dropdown-toggle').dropdown();
    </script>
  </body>
</html>
