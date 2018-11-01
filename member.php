<?php require "db.php"; ?>
<?php
  session_start();
  $msg = "";
  $msgClass = "danger";
  $members = "other";
  if ($_SESSION["logged_in"] == false){
    header("Location: ".ROOT_URL);
  }else{

    //================================GET Member ===============================
    if($_GET["q"]){
      $id = $_GET["q"];
      $query = "SELECT * FROM members WHERE id= '$id'";
      $results = mysqli_query($conn, $query);
      if (mysqli_query($conn, $query)){
        $data = mysqli_fetch_assoc($results);
        if ($data['cell_group'] != $_SESSION["username"]){
          header("Location: ".WELCOME);
        }else {
          $query = "SELECT * FROM members WHERE id= '$id'";
          $results = mysqli_query($conn, $query);
          if (mysqli_query($conn, $query)){
            $data = mysqli_fetch_assoc($results);

            if ( filter_has_var(INPUT_GET, "delete") ){
              echo "deleting";
              $query = "DELETE FROM members WHERE members. id = '$id'";

              if (mysqli_query($conn, $query)){
                header("Location: ".WELCOME);
              }else{
                echo "Error: ".mysqli_error($conn);
              }
            }else if ( filter_has_var(INPUT_GET, "update") ){
              echo "Updating";
              $title = $_GET["title"];
              $name = $_GET["name"];
              $surname = $_GET["surname"];
              $email = $_GET["email"];
              $bday = $_GET["birthday"];
              $number = $_GET["cell_number"];
              $group = $_GET["group"];
              $chapter = $_GET["chapter"];
              $query = "UPDATE members
                        SET title = '$title',
                            name = '$name',
                            surname = '$surname',
                            email = '$email',
                            birthday = '$bday',
                            cell_number = '$number',
                            group_name = '$group',
                            chapter = '$chapter'

                            WHERE members. id = '$id'";

              if (mysqli_query($conn, $query)){
                header("Location: ".WELCOME);
              }else {
                echo "Error: ".mysqli_error($conn);
              }
            }
          }  //---> Query endif
        }
      }else{
        header("Location: ".WELCOME);
      }
    }else{
      header("Location: ".WELCOME);
    }

  }

?>
<?php include "header.php";?>

    <div class=" container" style="max-width: 48rem;">
      <div class="card-body">
        <?php if ($msg != ""): ?>
          <small id="emailHelp" class="form-text text-muted alert alert-<?php echo $msgClass?>">
            <?php echo $msg ?>
            <a href="<?php echo WELCOME; ?>" class="alert-link">Go home</a>
          </small>
        <?php endif; ?>

        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
          <input type="hidden" name="q" value="<?php echo $_GET["q"]; ?>">
          <div class="form-row ">
            <div class="form-group col-md-2">
              <label for="title">Title</label>
              <select name="title" class="form-control">
                <option selected><?php echo $data["title"] ?></option>
                <option>Pastor</option>
                <option>Deacon</option>
                <option>Brother</option>
                <option>Sister</option>
              </select>
            </div>

            <div class="form-group col-md-5">
              <label for="name">Name</label>
              <input type="text" class="form-control" name="name" value = "<?php echo $data["name"]; ?>">
            </div>

            <div class="form-group col-md-5">
              <label for="surname"> Surname</label>
              <input type="text" class="form-control" name="surname" value = "<?php echo $data["surname"]; ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" name="email" value = "<?php echo $data["email"] ?>">
          </div>

          <div class="form-group">
            <label for="birthday">Birthday</label>
            <input type="text" class="form-control"  name="birthday" value = "<?php echo $data['birthday']; ?>">
          </div>

          <div class="form-group">
            <label for="cell_number">Cell Number</label>
            <input type="text" class="form-control" name="cell_number" value = "<?php echo $data["cell_number"] ?>">
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="group">Group</label>
              <input type="text" class="form-control"  name="group" value = "<?php echo $data["group_name"] ?>">
            </div>

            <div class="form-group col-md-6">
              <label for="chapter">Chapter</label>
              <select name="chapter" required class="form-control">
                <option selected><?php echo $data["chapter"] ?></option>
                <?php $groups = array('UCT','UWC', 'Stellenbosch', 'Colleges', 'CPUT TOWN', 'CPUT BELLVILLE', 'OTHER') ?>
                <?php foreach($groups as $group): ?>
                <option><?php echo $group; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <fieldset disabled>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="attended">Attended</label>
                <input type="text" class="form-control" name="attended" value = "<?php echo $data["attendance"] ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="invited">Invited</label>
                <input type="text" class="form-control" name="invited" value = "<?php echo $data["invites"] ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="joined">Joined</label>
                <input type="text" class="form-control" name="joined" value = "<?php echo $data["joined"] ?>">
              </div>
            </div>
          </fieldset>

          <div class="container">
            <button type="submit" name="update" class="btn btn-outline-info">Update</button>
            <button type="submit" name="delete" class="btn btn-outline-danger">Delete Member</button>
            <a href="<?php echo WELCOME; ?>" class="btn btn-link btn-sm">Go home</a>
          </div>

        </form>
      </div>

<?php include "footer.php" ?>
