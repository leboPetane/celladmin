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
      //$query = "SELECT * FROM members WHERE id= '$id'";
      $sql = "SELECT * FROM members WHERE id= :id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(['id' => $id]);
      $member = $stmt-> fetch();

      if ($member->cell_group != $_SESSION["username"] || $member == false){
        header("Location: ".WELCOME);
      }else{
        if ( filter_has_var(INPUT_GET, "delete") ){
          echo "deleting";
          $sql = "DELETE FROM members WHERE members. id = :id";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['id' => $id]);
          header("Location: ".WELCOME);
        }else if ( filter_has_var(INPUT_GET, "update") ){
          echo "Updating";
          $title = htmlentities($_GET["title"]);
          $name = htmlentities($_GET["name"]);
          $surname = htmlentities($_GET["surname"]);
          $email = htmlentities($_GET["email"]);
          $bday = htmlentities($_GET["birthday"]);
          $number = htmlentities($_GET["cell_number"]);
          $group = htmlentities($_GET["group"]);
          $chapter = htmlentities($_GET["chapter"]);
          $sql = "UPDATE members
                    SET title = :title,
                        name = :name,
                        surname = :surname,
                        email = :email,
                        birthday = :birthday,
                        cell_number = :cell_number,
                        group_name = :group_name,
                        chapter = :chapter

                        WHERE members. id = :id";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([
            'title' => $title,
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'birthday' => $bday,
            'cell_number' => $number,
            'group_name' => $group,
            'chapter' => $chapter,
            'id' => $id
          ]);
          if ($stmt){
            header("Location: ".WELCOME);
          }
        }
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
                <option selected><?php echo $member->title; ?></option>
                <option>Pastor</option>
                <option>Deacon</option>
                <option>Brother</option>
                <option>Sister</option>
              </select>
            </div>

            <div class="form-group col-md-5">
              <label for="name">Name</label>
              <input type="text" class="form-control" name="name" value = "<?php echo $member->name; ?>">
            </div>

            <div class="form-group col-md-5">
              <label for="surname"> Surname</label>
              <input type="text" class="form-control" name="surname" value = "<?php echo $member->surname; ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" name="email" value = "<?php echo $member->email; ?>">
          </div>

          <div class="form-group">
            <label for="birthday">Birthday</label>
            <input type="text" class="form-control"  name="birthday" value = "<?php echo $member->birthday; ?>">
          </div>

          <div class="form-group">
            <label for="cell_number">Cell Number</label>
            <input type="text" class="form-control" name="cell_number" value = "<?php echo $member->cell_number; ?>">
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="group">Group</label>
              <input type="text" class="form-control"  name="group" value = "<?php echo $member->group_name; ?>">
            </div>

            <div class="form-group col-md-6">
              <label for="chapter">Chapter</label>
              <select name="chapter" required class="form-control">
                <option selected><?php echo $member->chapter ?></option>
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
                <input type="text" class="form-control" name="attended" value = "<?php echo $member->attendance; ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="invited">Invited</label>
                <input type="text" class="form-control" name="invited" value = "<?php echo $member->invites; ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="joined">Joined</label>
                <input type="text" class="form-control" name="joined" value = "<?php echo $member->joined; ?>">
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
