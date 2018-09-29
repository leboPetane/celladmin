<?php require "db.php"; ?>
<?php
  $msg ='';
  $msgClass='';

  if (filter_has_var(INPUT_POST, "submit")){

    $name_of_cell = mysqli_real_escape_string($conn, htmlentities($_POST["cellName"]));
    $title = mysqli_real_escape_string($conn, htmlentities($_POST["title"]));
    $leader_name = mysqli_real_escape_string($conn, htmlentities($_POST["name"]));
    $leader_surname = mysqli_real_escape_string($conn, htmlentities($_POST["surname"]));
    $email = mysqli_real_escape_string($conn, htmlentities($_POST["email"]));
    $password = mysqli_real_escape_string($conn, htmlentities($_POST["password"]));
    $password1 = mysqli_real_escape_string($conn, htmlentities($_POST["password1"]));

    //check if email is not already registered

    $query = "SELECT * FROM cell_leaders WHERE username = \"$email\"";

    $results = mysqli_query($conn, $query);

    if (mysqli_query($conn, $query)){
      $data = mysqli_fetch_assoc($results);
      if ($data != null){
        $msg = "This email has already been registered, please log in." ;
        $msgClass = "danger";
      }else{
        //User not found - Form validation

        if ($name_of_cell == "" || $leader_name == "" || $leader_surname == "" ){
          $msg = "All fields are required";
          $msgClass = "danger";
        }else if ($title != "Pastor" && $title != "Deacon" && $title != "Brother" && $title !="Sister"){
          $msg = "Please enter title";
          $msgClass = "danger";
        }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
          $msg = "Email is invalid.";
          $msgClass = "danger";
        }else if ($password != $password1){
          $msg = "Password does not match";
          $msgClass = "danger";
        }else{
          $query = "INSERT INTO cell_leaders (username, password, title, name, surname, cell_name) VALUES('$email', '$password', '$title', '$leader_name', '$leader_surname', '$name_of_cell')";
          if (mysqli_query($conn, $query)){
            $msg = $name_of_cell." by ".$title." ".$leader_name." ".$leader_surname." has been succesfully registered";
            $msgClass = "success";
          }else{
            $msg = "Error: ".mysqli_error($conn)." \nplease contact technical team :(";
            $msgClass = "danger";
          }
        }
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
        <?php if($msg != ""): ?>
          <div class="alert alert-<?php echo $msgClass ?>"><?php echo $msg ?></div>
        <?php endif; ?>
        <div class="jumbotron ">
          <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
              <div class="form-group">
                <label for="cellName">Cell name</label>
                <input type="text" class="form-control" name="cellName" value="<?php echo isset($_POST["cellName"])? $name_of_cell : "" ?>">
              </div>

              <div class="form-row">
                <div class="form-group col-md-2">
                  <label for="title">Title</label>
                  <select name="title" class="form-control">
                    <option selected>Choose...</option>
                    <option>Pastor</option>
                    <option>Deacon</option>
                    <option>Brother</option>
                    <option>Sister</option>
                  </select>
                </div>
                <div class="form-group col-md-4">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" name="name" value="<?php echo isset($_POST["name"])? $leader_name : "" ?>">
                </div>
                <div class="form-group col-md-4">
                  <label for="surname">Surname</label>
                  <input type="text" class="form-control" name = "surname" value="<?php echo isset($_POST["surname"])? $leader_surname : "" ?>">
                </div>

              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" value="<?php echo isset($_POST["email"])? $email : "" ?>">
                </div>
                <div class="form-group col-md-6">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" name = "password" value="<?php echo isset($_POST["password"])? $password : "" ?>">
                </div>
              </div>

              <div class="form-group">
                <label for="password1">Repeat Password</label>
                <input type="password" class="form-control" name = "password1" value="<?php echo isset($_POST["password1"])? $password1 : "" ?>">
              </div>

              <button type="submit" class="btn btn-outline-info" name="submit">Register Cell</button>
              <a href= "<?php echo ROOT_URL; ?>" class="btn btn-outline-info">Log in</a>
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
