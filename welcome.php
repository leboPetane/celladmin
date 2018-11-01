<?php require "db.php"; ?>
<?php

  session_start();

  $msg = "";
  $msgClass = "danger";
  $members = "other";

  if ($_SESSION["logged_in"] == false)
  {
    header("Location: ".ROOT_URL);
  }
  else
  {

    $member_total = 0;
    $report_total = 0;
    $cell_name = $_SESSION["username"];
    $email = $_SESSION["user"];

    $query = "SELECT * FROM members WHERE cell_group = \"$cell_name\"";
    $results = mysqli_query($conn, $query);

    if (mysqli_query($conn, $query))
    {

      $members = mysqli_fetch_all($results, MYSQLI_ASSOC);
      $member_total = count($members);

    }
    else
    {

      echo "Error: Unable to retrieve members for your cell - contact media with this error: ".mysqli_error($conn);

    }


    $query = "SELECT * FROM cell_leaders WHERE username = \"$email\"";
    $results = mysqli_query($conn, $query);

    if (mysqli_query($conn, $query))
    {
      //Get Leader Details
      $leader = mysqli_fetch_assoc($results);
      $report_total = $leader["reports"];

    }
    else
    {
      echo "Error: Unable to retrieve cell leaders details - contact media with this error: ".mysqli_error($conn);
    }

    /*                    -------------------------------------------------                                         */
    /*                    +               Inserting a new member          +                                              */
    /*                    -------------------------------------------------                                         */

    if (filter_has_var(INPUT_POST, "new_member"))
    {

      //Variable to store new member data
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

      if (filter_var($_POST["member_Email"], FILTER_VALIDATE_EMAIL))
      {
        $member_email = mysqli_real_escape_string($conn, htmlentities($_POST["member_Email"]));
        $member_email = filter_var( $member_email , FILTER_SANITIZE_EMAIL);
        //insert member to DB
        $query = "INSERT INTO members (title, name, surname, email, cell_number, cell_group, group_name, chapter, birthday) VALUES('$member_title', '$member_name', '$member_surname', '$member_email', '$member_number', '$cell_name', '$member_group', '$member_chapter', '$member_birthday')";

        if (mysqli_query($conn, $query))
        {

          //Update number of members on DB
          $query = "SELECT * FROM cell_leaders WHERE cell_name = '$cell_name'";
          $leader_temp = mysqli_fetch_assoc(mysqli_query($conn, $query));
          $members_of_leader = $leader_temp["members"] + 1;

          $query = "UPDATE cell_leaders
                    SET members = $members_of_leader
                    WHERE cell_name = '$cell_name'";

          $results = mysqli_query($conn, $query);

          if (mysqli_query($conn, $query))
          {
            //updated succesfully
          }
          else
          {
            echo "Error: Unable to update member addition on number of members for cell leader - please contact media with error: ".mysqli_error($conn);
          }

          //           Handling invited by
          //        1. The DB keeps track of each members invites, so that
          //        2. When a new member is inserted to the cell and that new member has invited by an existing member.
          //        3. The cell leader can track each member on how many people they have invited in the cell


          if ($member_invite != "Cell leader")
          {
            $query = "SELECT * FROM members WHERE concat(members.name, ' ', members.surname) = '$member_invite'";
            $invitor = mysqli_fetch_assoc(mysqli_query($conn, $query));
            $invites = $invitor["invites"] + 1;

            $query = "UPDATE members
                      SET invites = $invites
                      WHERE concat(members.name, ' ', members.surname) = '$member_invite'";
            $results = mysqli_query($conn, $query);

            if (mysqli_query($conn, $query))
            {
              //succesfully updated the the number of invites on the member.
            }
            else
            {
              echo "Error: Unable to update the invitor of a new member - please contact media with this error: ".mysqli_error($conn);
            }

          }

          //          Updating the display on the left menu now that the member has been added on DB
          //          1. This is the number at the end of the text 'Members'

          $query = "SELECT * FROM members WHERE cell_group = \"$cell_name\"";
          $results = mysqli_query($conn, $query);

          if (mysqli_query($conn, $query))
          {
            //Get Members
            $members = mysqli_fetch_all($results, MYSQLI_ASSOC);
            $member_total = count($members);
          }
          else
          {
            echo "Error: Unable to retrieve members data: ".mysqli_error($conn);
          }

          $msg = $member_title." ".$member_name." has been sucessfully added to"." ". $_SESSION['username'];
          $msgClass = "success";

        }
        else
        {
          $msg = "Error: ".mysqli_error($conn)." \n please contact technical team :(";
          $msgClass = "danger";
        }

        header("Location: ".WELCOME);

      }
      else
      {
        $msg = "Member not added: Invalid email - please re enter email";
        $msgClass = "danger";
      }

    }

  /*                    -------------------------------------------------                                         */
  /*                    +       Handling reports and the display        +                                      */
  /*                    -------------------------------------------------                                         */

  $query = "SELECT * FROM reports WHERE cell_name = '$cell_name'";
  $results = mysqli_query($conn, $query);

  if (mysqli_query($conn, $query))
  {
    $cell_reports = mysqli_fetch_all($results, MYSQLI_ASSOC);
    $total_attendence = 0;
    $total_converts = 0;
    $total_first_timers = 0;
    $weekly_attendence = 0;
    $average_first_timers = 0;

    //these are number of all reports under the cell - note each report = a cell that held.
    $total_r = count($cell_reports);

    foreach ($cell_reports as $report)
    {
      $total_attendence = $total_attendence + $report["attendance"];
      $total_converts = $total_converts + $report["new_converts"];
      $total_first_timers = $total_first_timers + $report["first_timers"];
    }
    if ($total_r >= 1) //this is to prevent zero division
    {
      $weekly_attendence = floor($total_attendence / $total_r);
      $average_first_timers = floor($total_first_timers / $total_r);
    }

  }else
  {
    echo "Error: ".mysqli_error($conn);
  }

  /*                    -------------------------------------------------                                         */
  /*                    +       Handling the birthday list display        +                                      */
  /*                    -------------------------------------------------                                         */

  $current_month = Date("F");
  $birthday_list = 0;
  foreach($members as $member)
  {
    if (date("F", strtotime($member["birthday"])) == $current_month)
    {
      $birthday_list++; //this is the variable used to store the number of people who have bdays on te current month (Date("F"))
    }
  }

  /*                    -------------------------------------------------                                         */
  /*                    +    Handling the tracking stats of the cell   +                                      */
  /*                    -------------------------------------------------                                         */



  $query = "SELECT * FROM reports WHERE cell_name = '$cell_name'";
  $results = mysqli_query($conn, $query);

  if (mysqli_query($conn, $query))
  {
    $cell_reports = mysqli_fetch_all($results, MYSQLI_ASSOC);
  }
  else
  {
    echo "Error: ".mysqli_error($conn);
  }


 }

 ?>

<?php include "header.php";?>




<!--                    #################################################                                        -->
<!--                    #################################################                                        -->
<!--                    #################################################                                        -->
<!--                    +          MAIN CONTENT - END OF LOGIC          +                                     -->
<!--                    #################################################                                        -->
<!--                   #################################################                                        -->
<!--                    #################################################                                        -->






<!--                    LEFT MENU TILES                               -->
<div class="container-fluid">
  <div class="row">
    <div class="col-md-3" style="padding-left:0pt;" id="left-nav">
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
          Track My Cell <span class="badge badge-warning badge-pill"><?php echo "a/w: ".$weekly_attendence; ?></span>
        </a>

        <button type="button" class="list-group-item list-group-item-secondary mybtn">Start Cell</button>
      </div>
    </div>

    <!--                    END OF LEFT MENU TILES                                         -->




    <!--                    ||||||||||||||||||||||||||||||||||                                         -->
    <!--                    DISPLAY AREA ON THE RIGHT OF TILES                                         -->
    <!--                    |||||||||||||||||||||||||||||||||||                                         -->
    <div class="col-8" id="alterClass">

      <div class="tab-content" id="nav-tabContent">

          <!-- /////////////////////////////// WELCOME TILE ////////////////////////////////////////////////////////-->
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
          <div id="myToggle" class="hideme"><</div>
          <!-- ////////////////////////////END OF WELCOME TILE ////////////////////////////////////////////////////////-->


          <!-- /////////////////////////////// HOME TILE ////////////////////////////////////////////////////////-->
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
                        <td> <a href="member.php?q=<?php echo $member["id"]; ?>"><?php echo ($member["name"])." ".($member["surname"]); ?></a></td>
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
          <!-- /////////////////////////////// WELCOME TILE ////////////////////////////////////////////////////////-->


          <!-- /////////////////////////////// REPORTS TILE ////////////////////////////////////////////////////////-->
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
        <!-- /////////////////////////////// END OF REPORTS TILE ////////////////////////////////////////////////////////-->


        <!-- /////////////////////////////// BIRTHDAY LIST TILE ////////////////////////////////////////////////////////-->
        <div class="tab-pane fade" id="list-birthdays" role="tabpanel" aria-labelledby="list-birthdays-list">
          <div class="container" style="padding-top:30px; margin: auto;">
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
                  <td> <a href="member.php?q=<?php echo $member["id"]; ?>"><?php echo ($member["name"])." ".($member["surname"]); ?></a></td>
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
        <!-- /////////////////////////////// END OF BIRTHDAY LIST TILE ////////////////////////////////////////////////////////-->


        <!-- /////////////////////////////// TRACK CELL TILE ////////////////////////////////////////////////////////-->
        <div class="tab-pane fade" id="list-attendance" role="tabpanel" aria-labelledby="list-attendance-list">
          <div class="container" style="padding-top:30px;">
            <h5 class="display-4 alert-light">Attendance</h5>
            <hr>

            <div class="row">
              <div class="col-md-6 col-xs-12">
                <h4 class="track-heading">Total Attendance</h4>
                <h5 class="track-value"><?php echo $total_attendence; ?></h5>
              </div>

              <div class="col-md-6 col-xs-12">
                <h4 class="track-heading">Total Converts</h4>
                <h5 class="track-value"><?php echo $total_converts; ?></h5>
              </div>

              <div class="col-md-6 col-xs-12">
                <h4 class="track-heading">Weekly Attendance</h4>
                <h5 class="track-value"><?php echo $weekly_attendence; ?></h5>
              </div>

              <div class="col-md-6 col-xs-12">
                <h4 class="track-heading">Average First Timers</h4>
                <h5 class="track-value"><?php echo $average_first_timers; ?></h5>
              </div>

            </div>

          </div> <!-- end of content container -->
        </div>  <!-- end of fade -->
        <!-- /////////////////////////////// END OF TRACK MY CELL TILE ////////////////////////////////////////////////////////-->
      </div>
      <!-- ################################################## END OF CONTENT ######################################################### -->

    </div>
  </div> <!-- end of row-->
</div> <!-- end of main container-->

<script type="text/javascript">
  var mytoggle = document.getElementById("myToggle");
  myToggle.addEventListener("click", doToggle);

  function doToggle(){
    var hidethis = document.getElementById("left-nav");
    var changethis = document.getElementById("alterClass");
    if (hidethis.style.display != "none"){
      mytoggle.innerHTML = ">";
      hidethis.style.display = "none";
      changethis.classList.remove("col-md-8");
      changethis.classList.add("col-md-12");
    }else{
      hidethis.style.display = "block";
      mytoggle.innerHTML = "<";
      changethis.classList.remove("col-md-12");
      changethis.classList.add("col-md-8");
    }
  }
</script>
<?php include "footer.php";?>
