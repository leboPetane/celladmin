<?php require "db.php"; ?>
<?php
  session_start();
  if ($_SESSION["logged_in"] == false){
    header("Location: ".ROOT_URL);
  }
  //Setting up existing user details
  $msg = "";
  $msgClass = "danger";
  $user = $_SESSION["user"];

  $sql = "SELECT * FROM cell_leaders WHERE username = :username";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['username' => $user]);

  if ($stmt) {
    $data = $stmt->fetch();
  }else{
    $msg = "Error: ".mysqli_error($conn)." Please contact dev team.";
    $msgClass = "danger";
  }

  if (filter_has_var(INPUT_POST, "submit")){
      $cell_name = $_SESSION["username"];
      $title = htmlentities($_POST["title"]);
      $name = htmlentities($_POST["leader_name"]);
      $surname = htmlentities($_POST["leader_surname"]);
      $password = htmlentities($_POST["password"]);
      $password1 = htmlentities($_POST["password1"]);

      if ($password != $password1){
        $msg = " Password does not match, please retype password.";
        $msgClass = "danger";
      }else{
        //update username
        if ($password!=""){

          $sql = "UPDATE cell_leaders
                    SET password = :password,
                        title = :title,
                        name = :name,
                        surname = :surname
                        WHERE username = :username";
          $hash = password_hash($password, PASSWORD_BCRYPT);
          $stmt = $pdo-> prepare($sql);
          $stmt -> execute([
            'password'=> $hash,
            'title' => $title,
            'name' => $name,
            'surname' => $surname,
            'username' => $user
          ]);

        }else {

          $sql = "UPDATE cell_leaders
                    SET title = :title,
                        name = :name,
                        surname = :surname
                        WHERE username = :username";
          $stmt = $pdo->prepare($sql);
          $stmt -> execute([
            'title' => $title,
            'name' => $name,
            'surname' => $surname,
            'username' => $user
          ]);
        }

        if ($stmt){

          $sql = "SELECT * FROM cell_leaders WHERE username = :username";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['username' => $user]);

          if ($stmt){
            $data = $stmt->fetch();
          }

          $_SESSION["leader"] = $data->title." ".$data->name;
          $cell_name = $_SESSION["username"];
          $msg = $cell_name." has been succesfully updated. ";
          $msgClass = "success";

        }else{
          $msg = "Error: ".mysqli_error($conn)." \nplease contact technical team :(";
          $msgClass = "danger";
        }
      }
  }
 ?>
<?php include "header.php";?>


<div class=" container" style="max-width: 28rem;">
  <div class="card-body">
    <?php if ($msg != ""): ?>
      <small id="emailHelp" class="form-text text-muted alert alert-<?php echo $msgClass?>">
        <?php echo $msg ?>
        <a href="<?php echo WELCOME; ?>" class="alert-link">Go home</a>
      </small>
    <?php endif; ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
      <fieldset disabled>
      <div class="form-group">
        <label for="cell_name">Cell Name</label>
        <input type="text" class="form-control" name="cell_name" value = "<?php echo $data->cell_name; ?>">
      </div>
      </fieldset>
      <div class="form-group">
        <label for="title">Title</label>
        <select name="title" class="form-control">
          <option selected><?php echo $data->title; ?></option>
          <option>Pastor</option>
          <option>Deacon</option>
          <option>Brother</option>
          <option>Sister</option>
        </select>
      </div>

      <div class="form-group">
        <label for="leader_name">Name</label>
        <input type="text" class="form-control" name="leader_name" value = "<?php echo $data->name; ?>">
      </div>
      <div class="form-group">
        <label for="leader_surname">Surname</label>
        <input type="text" class="form-control" name="leader_surname" name="leader_surname" value = "<?php echo $data->surname; ?>">
      </div>
      <fieldset disabled>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" class="form-control" name="cell_name" name="email" value = "<?php echo $data->username; ?>">
        </div>
      </fieldset>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="password">Change password</label>
          <input type="password" class="form-control" name="password" value="">
        </div>
        <div class="form-group col-md-6">
          <label for="password1">Confirm New Password</label>
          <input type="password" class="form-control" name = "<?php echo "password1" ?>" value="">
        </div>
      </div>
      <button type="submit" name="submit" class="btn btn-outline-info">Update</button>
      <a href="<?php echo WELCOME; ?>" class="btn btn-link btn-sm">Go home</a>
    </form>
  </div>


<?php include "footer.php";?>
