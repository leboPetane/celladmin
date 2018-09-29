<?php require "db.php"; ?>
<?php
  session_start();
  $msg = "";
  $msgClass = "danger";
  $members = "other";
  if ($_SESSION["logged_in"] == false){
    header("Location: ".ROOT_URL);
  }else{
    $member_total = 0;
    $report_total = 0;
    $cell_name = $_SESSION["username"];
    $query = "SELECT * FROM members WHERE cell_group = \"$cell_name\"";
    $results = mysqli_query($conn, $query);
    if (mysqli_query($conn, $query)){
      //Get Members
      $members = mysqli_fetch_all($results, MYSQLI_ASSOC);
      $member_total = count($members);

    }else{
      echo "Error: query not - i dont know: ".mysqli_error($conn);
    }

    $email = $_SESSION["user"];
    $query = "SELECT * FROM cell_leaders WHERE username = \"$email\"";
    $results = mysqli_query($conn, $query);
    if (mysqli_query($conn, $query)){
      //Get Members
      $leader = mysqli_fetch_assoc($results);
      $report_total = $leader["reports"];

    }else{
      echo "Error: query not - i dont know: ".mysqli_error($conn);
    }

//======================================================= Insert new member===================================================

    if (filter_has_var(INPUT_POST, "new_member")){

      //+++++++++++++++++++++++++++++++++++++++++++ Get DATA for new member +++++++++++++++++++++++++++++++++++++++++++++++++++

      $member_title = mysqli_real_escape_string($conn, htmlentities($_POST["title"]));
      $member_name = mysqli_real_escape_string($conn, htmlentities($_POST["member_Name"]));
      $member_name = filter_var( $member_name , FILTER_SANITIZE_STRING );
      $member_surname = mysqli_real_escape_string($conn, htmlentities($_POST["member_Surname"]));
      $member_surname = filter_var( $member_surname , FILTER_SANITIZE_STRING );
      $member_number = mysqli_real_escape_string($conn, htmlentities($_POST["member_Cell"]));
      $member_number = filter_var( $member_number , FILTER_SANITIZE_STRING );
      $member_cell = $_SESSION["username"];
      $member_group = mysqli_real_escape_string($conn, htmlentities($_POST["member_Group"]));
      $member_chapter = mysqli_real_escape_string($conn, htmlentities($_POST["member_Chapter"]));
      $member_birthday = $_POST["member_birthday"];
      $member_invite = mysqli_real_escape_string($conn, htmlentities($_POST["member_invite"]));

      if (filter_var($_POST["member_Email"], FILTER_VALIDATE_EMAIL)){
        $member_email = mysqli_real_escape_string($conn, htmlentities($_POST["member_Email"]));
        $member_email = filter_var( $member_email , FILTER_SANITIZE_EMAIL);

        //+++++++++++++++++++++++++++++++++++++++++ Put data of new member into db  +++++++++++++++++++++++++++++++++++++++++++++

        $query = "INSERT INTO members (name, surname, email, cell_number, cell_group, group_name, chapter, birthday) VALUES('$member_name', '$member_surname', '$member_email', '$member_number', '$cell_name', '$member_group', '$member_chapter', '$member_birthday')";
        if (mysqli_query($conn, $query)){

          //++++++++++++++++++ Handle invited by  ++++++++++++++++++++++

          if ($member_invite != "Cell leader"){

            $query = "SELECT * FROM members WHERE concat(members.name, ' ', members.surname) = '$member_invite'";
            $invitor = mysqli_fetch_assoc(mysqli_query($conn, $query));
            $invites = $invitor["invites"] + 1;

            $query = "UPDATE members
                      SET invites = $invites
                      WHERE concat(members.name, ' ', members.surname) = '$member_invite'";
            $results = mysqli_query($conn, $query);
            if (mysqli_query($conn, $query)){
            }else{
              echo "Error: query not - i dont know: ".mysqli_error($conn);
            }
          }

          //++++++++++++++++++++ Update display now that the new member has been added ++++++++++++++++++++

          $query = "SELECT * FROM members WHERE cell_group = \"$cell_name\"";
          $results = mysqli_query($conn, $query);
          if (mysqli_query($conn, $query)){
            //Get Members
            $members = mysqli_fetch_all($results, MYSQLI_ASSOC);
            $member_total = count($members);
          }else{
            echo "Error: query not - i dont know: ".mysqli_error($conn);
          }

          $msg = $member_title." ".$member_name." has been sucessfully added to"." ". $_SESSION['username'];
          $msgClass = "success";
        }else{
          $msg = "Error: ".mysqli_error($conn)." \nplease contact technical team :(";
          $msgClass = "danger";
        }

      }else{
        $msg = "Member not added: Invalid email - please re enter email";
        $msgClass = "danger";
      }

    }

//========================================================== GEt Reports ===================================================

  $query = "SELECT * FROM reports WHERE cell_name = '$cell_name'";
  $results = mysqli_query($conn, $query);
  if (mysqli_query($conn, $query)){
    $cell_reports = mysqli_fetch_all($results, MYSQLI_ASSOC);
  }else{
    echo "Error: : ".mysqli_error($conn);
  }

//========================================================== Get Birthday list for the month ===================================================

  $current_month = Date("F");
  $birthday_list = 0;
  foreach($members as $member) {
    if (date("F", strtotime($member["birthday"])) == $current_month){
      $birthday_list++;
    }
  }

//========================================================== Get Ministry Materials ===================================================


  }

 ?>

<?php include "header.php";?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-3" style="padding-left:0pt;">
      <div class="list-group list-group-flush" id="list-tab" role="tablist">
        <a class="list-group-item list-group-item-info active" id="list-home-list" data-toggle="list" href="#list-home" role="tab" aria-controls="home">
          Welcome <?php echo $_SESSION["leader"]; ?>
        </a>
        <a class="list-group-item list-group-item-info" id="list-members-list" data-toggle="list" href="#list-members" role="tab" aria-controls="home" >
          Members
          <span class="badge badge-warning badge-pill"><?php echo $member_total; ?></span>
        </a>
        <a class="list-group-item list-group-item-info" id="list-reports-list" data-toggle="list" href="#list-reports" role="tab" aria-controls="profile">
          Reports <span class="badge badge-warning badge-pill"><?php echo $report_total; ?></span>
        </a>
        <a class="list-group-item list-group-item-info" id="list-birthdays-list" data-toggle="list" href="#list-birthdays" role="tab" aria-controls="profile">
          View Birthday List <span class="badge badge-warning badge-pill"><?php echo $birthday_list; ?></span>
        </a>
        <a class="list-group-item list-group-item-info" id="list-attendance-list" data-toggle="list" href="#list-attendance" role="tab" aria-controls="profile">
          Track attendance <span class="badge badge-warning badge-pill"><?php echo "a/w: ".($member_total+5); ?></span>
        </a>
        <a class="list-group-item list-group-item-info" id="list-materials-list" data-toggle="list" href="#list-materials" role="tab" aria-controls="profile">
          Track Ministry Materials <span class="badge badge-warning badge-pill"><?php echo ($member_total+57); ?></span>
        </a>
        <button type="button" class="list-group-item list-group-item-secondary btn btn-outline-info">Start Cell</button>
      </div>
    </div>
    <div class="col-8">
<!-- |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| Content for titles ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| -->
<!-- ///////////////////////////////////////////////////////////////// Home //////////////////////////////////////////////////-->
      <div class="tab-content" id="nav-tabContent">
          <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
            <div class="container" style="padding-top:30px;">
              <?php if ($msg != ""): ?>
                <div class="alert alert-<?php echo $msgClass?> alert-dismissible fade show" role="alert">
                  <small>
                    <?php echo $msg ?>
                  </small>
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              <?php endif; ?>
              <p>Will display ROR and notes here</p>
            </div>
          </div>

<!-- ///////////////////////////////////////////////////////////////// Members //////////////////////////////////////////////////-->

          <div class="tab-pane fade" id="list-members" role="tabpanel" aria-labelledby="list-members-list">
            <div class="container" style="padding-top:30px;">
              <h5 class="display-4 alert-light">Cell Members</h5>
              <hr>
                <?php if ($msg != "" && $msgClass == "danger"): ?>
                  <small id="emailHelp" class="form-text text-muted alert alert-<?php echo $msgClass?>">
                    <?php echo $msg ?>
                  </small>
                <?php endif; ?>
                  <a class="btn btn-light" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                    + Add member
                  </a>

                  <!-- Form for inserting a new member -->
                  <div class="collapse" id="collapseExample">
                    <div class="card card-body">
                      <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="post">
                        <?php
                          $input_fields = array('Name', 'Surname', 'Email', 'Cell number' );
                        ?>
                        <div class="form-row">
                          <div class="col-md-2">
                            <label for="title">Title</label>
                            <select name="title" class="form-control">
                              <option selected>Pastor</option>
                              <option>Deacon</option>
                              <option>Brother</option>
                              <option>Sister</option>
                            </select>
                          </div>
                        <?php for($i=0; $i<=1; $i++): ?>
                          <div class="col-md-5">
                            <label for="member_<?php echo $input_fields[$i]; ?>"><?php echo $input_fields[$i]; ?></label>
                            <input type="<?php echo($i==6)? "date":"text" ?>" required class="form-control" name="member_<?php echo $input_fields[$i]; ?>">
                          </div>
                        <?php endfor; ?>
                        </div>
                        <div class="form-row">
                        <?php for($i=2; $i<=3; $i++): ?>
                          <div class="col-md-3">
                            <?php if($i==3): ?>
                            <label for="member_Cell"><?php echo $input_fields[$i]; ?></label>
                            <input type="text" required class="form-control" name="member_Cell">
                          <?php else: ?>
                            <label for="member_Email"><?php echo $input_fields[$i]; ?></label>
                            <?php $var = $input_fields[$i]; ?>
                            <input type="email" required class="form-control" name="member_Email">
                          <?php endif; ?>
                          </div>
                        <?php endfor; ?>
                          <div class="col-md-3">
                            <label for="member_Group">Group</label>
                            <select name="member_Group" required class="form-control">
                              <option selected>I</option>
                              <?php $groups = array('A','B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'OTHER') ?>
                              <?php foreach($groups as $group): ?>
                              <option><?php echo $group; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label for="member_Chapter">Chapter</label>
                            <select name="member_Chapter" required class="form-control">
                              <option selected>UCT</option>
                              <?php $groups = array('UCT','UWC', 'Stellenbosch', 'Colleges', 'CPUT TOWN', 'CPUT BELLVILLE', 'OTHER') ?>
                              <?php foreach($groups as $group): ?>
                              <option><?php echo $group; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        <div class="form-row">
                        <?php for($i=6; $i<count($input_fields); $i++): ?>
                          <div class="col-md-4">
                            <label for="member_<?php echo $input_fields[$i]; ?>"><?php echo $input_fields[$i]; ?></label>
                            <input type="<?php echo($i==6)? "date":"text" ?>" required class="form-control" name="member_<?php echo $input_fields[$i]; ?>" >
                          </div>
                        <?php endfor; ?>
                          <div class="col-md-4">
                            <label for="member_birthday">Birthday</label>
                            <input type="date" name="member_birthday" required class="form-control" >
                          </div>
                          <div class="col-md-4">
                            <label for="member_name">Invited By</label>
                            <select name="member_invite" required class="form-control">
                              <option selected>Cell leader</option>
                              <?php foreach ($members as $member): ?>
                              <option><?php echo $member['name']." ".$member['surname']; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label for="member_name"><cite>*All fields are required</cite></label>
                            <input type="submit" class="btn btn-outline-info" name="new_member" >
                          </div>
                        </div>
                      </form>
                    </div>
                  </div> <!-- End of collapse -->

                  <!-- Table of members -->

                  <table class="table">
                    <thead class="thead-light">
                      <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Cell Number</th>
                        <th scope="col">Group</th>
                        <th scope="col">Chapter</th>
                        <th scope="col">Attended</th>
                        <th scope="col">Invites</th>
                        <th scope="col">Joined</th>
                        <th scope="col">Birthday</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($members as $member): ?>
                      <tr>
                        <td> <a href="#"><?php echo ($member["name"])." ".($member["surname"]); ?></a></td>
                        <td><?php echo $member["email"]; ?></td>
                        <td><?php echo $member["cell_number"]; ?></td>
                        <td><?php echo $member["group_name"]; ?></td>
                        <td><?php echo $member["chapter"]; ?></td>
                        <td><?php echo $member["attendance"]; ?></td>
                        <td><?php echo $member["invites"]; ?></td>
                        <td><?php echo date("Y F d", strtotime($member["joined"])); ?></td>
                        <td><?php echo date("F d", strtotime($member["birthday"])); ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>

            </div> <!-- end of content container -->
          </div> <!-- end of fade -->

<!-- ///////////////////////////////////////////////////////////////// Reports //////////////////////////////////////////////////-->

        <div class="tab-pane fade" id="list-reports" role="tabpanel" aria-labelledby="list-reports-list">
          <div class="container" style="padding-top:30px;">
            <h5 class="display-4 alert-light">Cell Reports</h5>
            <hr>

            <div class="row">
              <?php foreach ($cell_reports as $report): ?>
              <div class="col-md-4">
                <div class="card text-right shadow p-3 mb-5 bg-white rounded">
                  <div class="card-header">
                    <?php echo Date("Y F d", strtotime($report["date"])); ?>
                  </div>
                  <div class="card-body">
                    <h5 class="card-title"><?php echo $report["topic"] ?></h5>
                    <p class="card-text"><?php echo $report["summary"] ?></p>
                    <a href="report.php?report=<?php echo $report["id"]; ?>" class="btn btn-outline-info my-2 my-sm-0">View Report</a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>

            </div>

          </div> <!-- end of content container -->
        </div>  <!-- end of fade -->

<!-- ///////////////////////////////////////////////////////////////// Birthdays //////////////////////////////////////////////////-->

        <div class="tab-pane fade" id="list-birthdays" role="tabpanel" aria-labelledby="list-birthdays-list">
          <div class="container" style="padding-top:30px;">
            <h5 class="display-4 alert-light">Birthdays<small> <span class="badge badge-info badge-pill"><?php echo $current_month; ?></span></small></h5>
            <hr>

            <table class="table">
              <thead class="thead-light">
                <tr>
                  <th scope="col">Name</th>
                  <th scope="col">Email</th>
                  <th scope="col">Cell Number</th>
                  <th scope="col">Group</th>
                  <th scope="col">Chapter</th>
                  <th scope="col">Attended</th>
                  <th scope="col">Invites</th>
                  <th scope="col">Joined</th>
                  <th scope="col">Birthday</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($members as $member): ?>
                <?php if (date("F", strtotime($member["birthday"])) == $current_month): ?>
                <tr>
                  <td> <a href="#"><?php echo ($member["name"])." ".($member["surname"]); ?></a></td>
                  <td><?php echo $member["email"]; ?></td>
                  <td><?php echo $member["cell_number"]; ?></td>
                  <td><?php echo $member["group_name"]; ?></td>
                  <td><?php echo $member["chapter"]; ?></td>
                  <td><?php echo $member["attendance"]; ?></td>
                  <td><?php echo $member["invites"]; ?></td>
                  <td><?php echo date("Y F d", strtotime($member["joined"])); ?></td>
                  <td><?php echo date("F d", strtotime($member["birthday"])); ?></td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>

          </div> <!-- end of content container -->
        </div>  <!-- end of fade -->

<!-- ///////////////////////////////////////////////////////////////// Attendance //////////////////////////////////////////////////-->

        <div class="tab-pane fade" id="list-attendance" role="tabpanel" aria-labelledby="list-attendance-list">
          <div class="container" style="padding-top:30px;">
            <h5 class="display-4 alert-light">Attendance</h5>
            <hr>

            Attend please
            <div class="rounded-circle border border-info">
              67
            </div>


          </div> <!-- end of content container -->
        </div>  <!-- end of fade -->

<!-- ///////////////////////////////////////////////////////////////// Materials //////////////////////////////////////////////////-->
        <div class="tab-pane fade" id="list-materials" role="tabpanel" aria-labelledby="list-materials-list">
          <div class="container" style="padding-top:30px;">
            <h5 class="display-4 alert-light">Materials</h5>
            <hr>
          </div> <!-- end of content container -->
        </div>  <!-- end of fade -->

<!-- ################################################## END OF CONTENT ######################################################### -->

      </div> <!--end of content-->
    </div>
  </div> <!-- end of row-->
</div> <!-- end of main container-->


<?php include "footer.php";?>
