<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Course Admin</title>
  <?php include('./view/head.html'); ?>
  <script src="./src/create_class.js"></script>
</head>
<body>
  <?php include('./view/navbar.php'); ?>

	<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
    <div style="margin-top: -40px; margin-bottom: -20px; text-align: center;">	
      <h1 id="page_title" style="color: #404040; text-shadow: 2px 2px #000000;"></h1>
    </div>
  </div>

		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<div id ="class_table">
						<div class="panel panel-primary">
						<!-- Default panel contents -->
							<div class="panel-heading">
								<h3 id="panel_title" class="panel-title" style="font-size:20px; text-align: center">Create New Course</h3>
                            </div>

                            <style>
                            input[type=text], select, textarea {
                                width: 100%; /* Full width */
                                padding: 12px; /* Some padding */
                                border: 1px solid #ccc; /* Gray border */
                                border-radius: 4px; /* Rounded borders */
                                box-sizing: border-box; /* Make sure that padding and width stays in place */
                                margin-top: 6px; /* Add a top margin */
                                margin-bottom: 16px; /* Bottom margin */
                                resize: vertical /* Allow the user to vertically resize the textarea (not horizontally) */
                            }
                            </style>

                            <form id="create_class" class="padding-left-15 padding-right-15">
                                
                                <div class="col-sm-6 padding-top-15 padding-left-0">
                                    <label>Course Name</label>
                                    <input type="text" id="course_name" placeholder="e.g. &quot;CS 4400: Computer Systems&quot;" maxlength="128" required>

                                    <label>Department</label>
                                    <input type="text" id="depart_pref" placeholder="e.g. &quot;CS&quot;" maxlength="16" required>

                                    <label>Course Number</label>
                                    <input type="text" id="course_num" placeholder="e.g. &quot;4400&quot;" maxlength="16" required>
                                </div>
                                <div class="col-sm-6 padding-top-15 padding-left-0 padding-right-0">
                                    <label>Instructor Username</label>
                                    <input type="text" id="professor" placeholder="username of instructor" maxlength="128" required>

                                    <label>LDAP Group</label>
                                    <input type="text" id="ldap_group" placeholder="TA membership group" maxlength="256" required>

                                    <label>Access Code</label>
                                    <input type="text" id="access_code" placeholder="(optional) 16 character max" maxlength="16">
                                </div>
                                <div class="col-sm-12 padding-left-0 padding-right-0">
                                    <label>Description</label>
                                    <textarea id="description" placeholder="Course description" style="height:100px" required></textarea>
                                </div>
                                <div class="padding-bottom-10">
                                  <button id="create_class_button" class="btn btn-success" type="submit"></button>
                                  <button id="delete_class_button" class="btn btn-warning" type="submit">Delete Course</button>
                                  <a class="padding-left-10" style="color: #e8002b; text-align: right" href="https://webhandin.eng.utah.edu/groupmodify" target="_blank">Update LDAP groups here</a>
                                  <span>or send an email to </span><span style="font-style: italic">opers@eng.utah.edu</span>
                                </div>
                            </form>
						</div><!--panel-->
					</div><!--id-->
				</div><!--col-sm-12-->
			</div><!--row-->
		</div><!--container-->
	</body>
</html>
