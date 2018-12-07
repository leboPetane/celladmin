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


    $sql = "SELECT * FROM members WHERE cell_group = :cell_group";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cell_group' => $cell_name]);

    if ($stmt)
    {
      $members = $stmt->fetchAll();
      $member_total = $stmt->rowCount();
    }
    else
    {

      echo "Error: Unable to retrieve members for your cell ";

    }

    $sql = "SELECT * FROM cell_leaders WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $email]);

    if ($stmt)
    {
      //Get Leader Details
      $leader = $stmt->fetch();
      $report_total = $leader->reports;

    }
    else
    {
      echo "Error: Unable to retrieve cell leaders details ";
    }

    /*                    -------------------------------------------------                                         */
    /*                    +               Inserting a new member          +                                              */
    /*                    -------------------------------------------------                                         */

    if (filter_has_var(INPUT_POST, "new_member"))
    {

      //Variable to store new member data
      $member_title =  htmlentities($_POST["title"]);
      $member_name = htmlentities($_POST["member_Name"]);
      $member_name = filter_var( $member_name , FILTER_SANITIZE_STRING );
      $member_surname = htmlentities($_POST["member_Surname"]);
      $member_surname = filter_var( $member_surname , FILTER_SANITIZE_STRING );
      $member_number = htmlentities($_POST["member_Cell"]);
      $member_number = filter_var( $member_number , FILTER_SANITIZE_STRING );
      $member_cell = $_SESSION["username"];
      $member_group = htmlentities($_POST["member_Group"]);
      $member_chapter = htmlentities($_POST["member_Chapter"]);
      $member_birthday = $_POST["member_birthday"];
      $member_invite = htmlentities($_POST["member_invite"]);

      if (filter_var($_POST["member_Email"], FILTER_VALIDATE_EMAIL))
      {
        $member_email = htmlentities($_POST["member_Email"]);
        $member_email = filter_var( $member_email , FILTER_SANITIZE_EMAIL);
        //insert member to DB

        $sql = "INSERT INTO members (title, name, surname, email, cell_number, cell_group, group_name, chapter, birthday) VALUES(:title, :name, :surname, :email, :cell_number, :cell_group, :group_name, :chapter, :birthday)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          'title' => $member_title,
          'name' => $member_name,
          'surname' => $member_surname,
          'email' => $member_email,
          'cell_number' => $member_number,
          'cell_group' => $cell_name,
          'group_name' => $member_group,
          'chapter' => $member_chapter,
          'birthday' => $member_birthday
        ]);

        if ($stmt)
        {

          //Update number of members on DB
          $sql = "SELECT * FROM cell_leaders WHERE cell_name = :cell_name";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['cell_name' => $cell_name]);

          $leader_temp = $stmt->fetch();
          $members_of_leader = $leader_temp->members + 1;

          $sql = "UPDATE cell_leaders
                    SET members = :members
                    WHERE cell_name = :cell_name";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([
            'members' => $members_of_leader,
            'cell_name' => $cell_name
          ]);



          //           Handling invited by
          //        1. The DB keeps track of each members invites, so that
          //        2. When a new member is inserted to the cell
          //        3. The cell leader can track each member on how many people they have invited in the cell


          if ($member_invite != "Cell leader")
          {
            $sql = "SELECT * FROM members WHERE concat(members. name, ' ', members. surname) = '$member_invite'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $invitor = $stmt->fetch();

            $invites = $invitor->invites + 1;

            $sql = "UPDATE members
                      SET invites = :invites
                      WHERE concat(members. name, ' ', members. surname) = '$member_invite'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
              'invites' => $invites
            ]);

          }

          //          Updating the display on the left menu now that the member has been added on DB
          //          1. This is the number at the end of the text 'Members'

          $sql = "SELECT * FROM members WHERE cell_group = :cell_group";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['cell_group'=>$cell_name]);

          //Get Members
          $members = $stmt->fetchAll();
          $member_total = $stmt->rowCount();

          $msg = $member_title." ".$member_name." has been sucessfully added to"." ". $_SESSION['username'];
          $msgClass = "success";

        }
        else
        {
          $msg = "Something went wrong : Error W209 :(";
          $msgClass = "danger";
        }

        //header("Location: ".WELCOME);

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

  $sql = "SELECT * FROM reports WHERE cell_name = :cell_name";
  $stmt = $pdo->prepare($sql);

  $stmt->execute(['cell_name' => $cell_name]);
  if ($stmt)
  {
    $cell_reports = $stmt->fetchAll();
    $total_attendence = 0;
    $total_converts = 0;
    $total_first_timers = 0;
    $weekly_attendence = 0;
    $average_first_timers = 0;

    //these are number of all reports under the cell - note each report = a cell that held.
    $total_r = $stmt-> rowCount();

    foreach ($cell_reports as $report)
    {
      $total_attendence = $total_attendence + $report->attendance;
      $total_converts = $total_converts + $report->new_converts;
      $total_first_timers = $total_first_timers + $report->first_timers;
    }
    if ($total_r >= 1) //this is to prevent zero division
    {
      $weekly_attendence = floor($total_attendence / $total_r);
      $average_first_timers = floor($total_first_timers / $total_r);
    }

  }else
  {
    echo "Something went wrong : Error W261";
  }

  /*                    -------------------------------------------------                                         */
  /*                    +       Handling the birthday list display        +                                      */
  /*                    -------------------------------------------------                                         */

  $current_month = Date("F");
  $birthday_list = 0;
  foreach($members as $member)
  {
    if (date("F", strtotime($member->birthday)) == $current_month)
    {
      $birthday_list++; //this is the variable used to store the number of people who have bdays on te current month (Date("F"))
    }
  }

  /*                    -------------------------------------------------                                         */
  /*                    +    Handling the tracking stats of the cell   +                                      */
  /*                    -------------------------------------------------                                         */


  $sql = "SELECT * FROM reports WHERE cell_name = :cell_name";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['cell_name' => $cell_name]);

  if ($stmt)
  {
    $cell_reports = $stmt->fetchAll();
  }
  else
  {
    echo "Error: Something went wrong : W295";
  }


 }

/* --------------------------------------  DEALING WITH START CELL BUTTON ---------------------------- */

  if ( filter_has_var(INPUT_POST, "submit_start_cell") ){

    $topic = htmlentities($_POST['topic']);
    $description = htmlentities($_POST['description']);
    $attendees = $_POST['attendance'];
    $cell_attendance = count($attendees);
    $first_timers = htmlentities($_POST['first_timers']);
    $new_converts = htmlentities($_POST['new_converts']);
    $holy_ghost_filled = htmlentities($_POST['holy_ghost_filled']);
    $offering = htmlentities($_POST['offering']);
    $duration = $_POST['duration'];

    //Increment Number of Reports on db

    $number_of_reports = 1 + $leader->reports;
    $sql = "UPDATE cell_leaders
              SET reports = :reports
              WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      'reports' => $number_of_reports,
      'id' => $leader->id
    ]);

    // Add report to DB

    $sql = "INSERT INTO reports (cell_name, attendance, first_timers, new_converts, topic, location, holy_ghost_filled, offering, summary, duration) VALUES (:cell_name, :attendance, :first_timers, :new_converts, :topic, :location, :holy_ghost_filled, :offering, :summary, :duration)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      'cell_name' => $leader->cell_name,
      'attendance' => $cell_attendance,
      'first_timers' => $first_timers,
      'new_converts' => $new_converts,
      'topic' => $topic,
      'location' => $leader->location,
      'holy_ghost_filled' => $holy_ghost_filled,
      'offering' => $offering,
      'summary' => $description,
      'duration' => $duration
    ]);

    //Increment Number of Attendace for each member_

    foreach ($attendees as $attendee_id ) {
      $sql = "SELECT * FROM members WHERE id = :id ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(['id' => $attendee_id]);
      $attendee = $stmt->fetch();
      $attendee_attendance = 1 + $attendee->attendance;

      $sql = "UPDATE members SET attendance = :attendance WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        'attendance' => $attendee_attendance,
        'id' => $attendee_id
      ]);


    }

    $msg = "Report Created succesfully";
    $msgClass = "success";
    //header("Location: ".WELCOME);

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


<!-- Modal -->

  <div id="query-modal" class="modal">
      <div class="modal-content">
        <div class="container">
          <p><span id="closeBtn" class="closeBtn">&times;</span></p>
          <form id="my-form" method="post" action=welcome.php>

            <p class="text-center" ><i id="top-text">Please add all first timers as your cell members before you start the cell* </i> </p>

            <div class="form-group">
                <label for="topic">Topic</label>
                <input type="text" class="form-control" name="topic" value="">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea type="textarea" name="description" ></textarea>

            </div>


              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="attendance">Attendance : </label>
                  <?php foreach ($members as $member): ?>
                    <div class="checkbox">

                      <input type="checkbox" name="attendance[]" value="<?php echo $member->id; ?>"> <?php echo $member->name." ".$member->surname; ?></input>
                    </div>
                  <?php endforeach; ?>
                </div>

              </div>

              <div class="form-row">

                <div class="form-group col-md-3">
                  <label for="first_timers">First Timers</label>
                  <input required type="number" class="form-control" name="first_timers" value="">
                </div>
                <div class="form-group col-md-3">
                  <label for="new_converts">New Converts</label>
                  <input required type="number" class="form-control" name = "new_converts" value="">
                </div>

                <div class="form-group col-md-3">
                  <label for="holy_ghost_filled">Holy Ghost Filled</label>
                  <input required type="number" class="form-control" name="holy_ghost_filled" value="">
                </div>
                <div class="form-group col-md-3">
                  <label for="offering">Offering</label>
                  <input required type="number" class="form-control" name = "offering" value="">
                </div>
              </div>

              <input type="text" name="duration" hidden value="0hrs 20mins 3sec" id="duration">
              <button type="submit" class="btn btn-outline-info" name="submit_start_cell" id="submit_start_cell">Finish Cell</button>
              <div class="btn btn-outline-info" id="cell_started">Start Cell</div>

          </form> <!-- end of form -->
        </div>
      </div>
  </div>

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

        <button type="button" class="list-group-item list-group-item-secondary mybtn" id="startCell">Start Cell</button>
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

                  <a href="http://localhost/celladmin/welcome.php" class="close">&times;</a>
                  
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
                              <option><?php echo $member->name." ".$member->surname; ?></option>
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
                        <td> <a href="member.php?q=<?php echo $member->id; ?>"><?php echo ($member->name)." ".($member->surname); ?></a></td>
                        <td><?php echo $member->email; ?></td>
                        <td><?php echo $member->cell_number; ?></td>
                        <td><?php echo $member->group_name; ?></td>
                        <td><?php echo $member->chapter; ?></td>
                        <td><?php echo $member->attendance; ?></td>
                        <td><?php echo $member->invites; ?></td>
                        <td><?php echo date("Y F d", strtotime($member->joined)); ?></td>
                        <td><?php echo date("F d", strtotime($member->birthday)); ?></td>
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
                <div class="card text-center shadow p-3 mb-5 bg-white rounded">
                  <div class="card-header">
                    <?php echo Date("Y F d", strtotime($report->date)); ?>
                  </div>
                  <div class="card-body">
                    <h5 class="card-title"><?php echo $report->topic ?></h5>
                    <p class="card-text"><?php echo substr(($report->summary),0,100)."..." ?></p>
                    <a href="report.php?report=<?php echo $report->id; ?>" class="btn btn-outline-info my-2 my-sm-0">View Report</a>
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
                <?php if (date("F", strtotime($member->birthday)) == $current_month): ?>
                <tr>
                  <td> <a href="member.php?q=<?php echo $member->id; ?>"><?php echo ($member->name)." ".($member->surname); ?></a></td>
                  <td><?php echo $member->email; ?></td>
                  <td><?php echo $member->cell_number; ?></td>
                  <td><?php echo $member->group_name; ?></td>
                  <td><?php echo $member->chapter; ?></td>
                  <td><?php echo $member->attendance; ?></td>
                  <td><?php echo $member->invites; ?></td>
                  <td><?php echo date("Y F d", strtotime($member->joined)); ?></td>
                  <td><?php echo date("F d", strtotime($member->birthday)); ?></td>
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

<script>
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

  /* -------------HANDLING START CELL BUTTON EVENT ---------*/

  var start_cell = document.getElementById("startCell");
  var modal = document.getElementById('query-modal');
  var closeBtn = document.getElementById('closeBtn');
  var duration = document.getElementById('duration');




  closeBtn.addEventListener("click", function(){
    modal.style.display = "none";
  });

  start_cell.addEventListener("click", start_the_cell);


  function start_the_cell(){
    modal.style.display = "block";

    var cell_started = document.getElementById("cell_started");
    var submit_start_cell = document.getElementById("submit_start_cell");
    submit_start_cell.style.display = "none";
    var top_text = document.getElementById("top-text");

    var sec = 0;
    var min = 0;
    var hrs = 0;

    cell_started.addEventListener("click", function(){

      closeBtn.style.display = "none";
      setInterval(function(){
        if(sec==59){
          sec = 0;
          if(min==59){
            min = 0;
            if(hrs==23){
              hrs=0;
            }else{
              hrs++;
            }
          }else{
            min++;
          }
        }else{
          sec++;
        }
        top_text.innerHTML = "<h4 class='warning'>Cell in progress | " + hrs + "hr(s) " + min + "min(s) " + sec + "sec </h4>" ;
        duration.value = hrs + "hr(s) " + min + "min(s) " + sec + "sec";
      }, 1000);

      cell_started.style.display = "none";
      submit_start_cell.style.display = "block";
    });


  }

</script>
<?php include "footer.php";?>
