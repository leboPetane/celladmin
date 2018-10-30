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
    if($_GET["member"]){
      $id = $_GET["member"];
      $query = "SELECT * FROM members WHERE id= '$id'";
      $results = mysqli_query($conn, $query);
      if (mysqli_query($conn, $query)){
        $data = mysqli_fetch_assoc($results);
        if ($data['cell_group'] != $_SESSION["username"]){
          header("Location: ".WELCOME);
        }
      }else{
        header("Location: ".WELCOME);
      }
    }else{
      header("Location: ".WELCOME);
    }

    $query = "SELECT * FROM members WHERE id= '$id'";
    $results = mysqli_query($conn, $query);
    if (mysqli_query($conn, $query)){
      $data = mysqli_fetch_assoc($results);
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

        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">

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
              <label for="name"> Surname</label>
              <input type="text" class="form-control" name="surname" value = "<?php echo $data["surname"]; ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="leader_name">Email</label>
            <input type="email" class="form-control" name="leader_name" value = "<?php echo $data["email"] ?>">
          </div>

          <div class="form-group">
            <label for="email">Birthday</label>
            <input type="text" class="form-control" name="cell_name" name="email" value = "<?php echo Date('Y F d ', strtotime($data['birthday'])); ?>">
          </div>

          <div class="form-group">
            <label for="leader_surname">Cell Number</label>
            <input type="text" class="form-control" name="leader_surname" name="leader_surname" value = "<?php echo $data["cell_number"] ?>">
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="email">Group</label>
              <input type="text" class="form-control" name="cell_name" name="email" value = "<?php echo $data["group_name"] ?>">
            </div>

            <div class="form-group col-md-6">
              <label for="member_Chapter">Chapter</label>
              <select name="member_Chapter" required class="form-control">
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
                <label for="email">Attended</label>
                <input type="text" class="form-control" name="cell_name" name="email" value = "<?php echo $data["attendance"] ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="email">Invited</label>
                <input type="text" class="form-control" name="cell_name" name="email" value = "<?php echo $data["invites"] ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="email">Joined</label>
                <input type="text" class="form-control" name="cell_name" name="email" value = "<?php echo $data["joined"] ?>">
              </div>
            </div>
          </fieldset>

          <div class="container">
            <button type="submit" name="submit" class="btn btn-outline-info">Update</button>
            <button type="submit" name="submit" class="btn btn-outline-danger">Delete Member</button>
            <a href="<?php echo WELCOME; ?>" class="btn btn-link btn-sm">Go home</a>
          </div>

        </form>
      </div>

<?php include "footer.php" ?>
