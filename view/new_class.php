<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
  <title>TA Help Queue - New Course</title>
  <?php include('./view/head.html'); ?>
  <script src="./src/logout.js"></script>
  <script src="./src/create_class.js"></script>
</head>

	<body>

		<nav class="navbar navbar-default">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand">
                        <img alt="Brand" src="./resources/img/UHz.png">
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-left">
                        <li>
                            <a href="./about">About</a>
                        </li>
                        <li>
                            <a href="./tutorial" target="_blank">Tutorial</a>
                        </li>
                        <li>
                            <a href="../swagger/index.html" target="_blank">Public API</a>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="nav-item dropdown active" id="admin_menu">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Admin
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                <ul>
                                    <li>
                                        <a class="dropdown-item" href="new_class">Create Course</a>
                                    </li>
                                    <!--<li>-->
                                    <!--<a class="dropdown-item" href="edit_class">Edit Course</a>-->
                                    <!--</li>-->
                                </ul>
                            </div>
                        </li>
                        <li>
                            <a href="classes">Courses</a>
                        </li>
                        <li>
                            <a href="#" onclick="logout();">Logout <script> document.write(localStorage.first_name)</script></a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>  

		<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
			<div align="center" style="margin-top:  -40px; margin-bottom: -20px">	
				<h1 style="color: #404040; text-shadow: 2px 2px #000000;">New Course</h1>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<div id ="class_table">
						<div class="panel panel-primary">
						<!-- Default panel contents -->
							<div class="panel-heading">
								<h3 class="panel-title" style="font-size:20px; text-align: center">Create New Course</h3>
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
                                    <input type="text" id="depart_prefix" placeholder="e.g. &quot;CS&quot;" maxlength="16" required>

                                    <label>Course Number</label>
                                    <input type="text" id="course_num" placeholder="e.g. &quot;4400&quot;" maxlength="16" required>
                                </div>
                                <div class="col-sm-6 padding-top-15 padding-left-0 padding-right-0">
                                    <label>Instructor Username</label>
                                    <input type="text" id="professor" placeholder="username of instructor" maxlength="128" required>

                                    <label>LDAP Group</label>
                                    <input type="text" id="ldap_group" placeholder="TA membership group" maxlength="256" required>

                                    <label>Access Code</label>
                                    <input type="text" id="acc_code" placeholder="(optional) 16 character max" maxlength="16">
                                </div>
                                <div class="col-sm-12 padding-left-0 padding-right-0">
                                    <label>Description</label>
                                    <textarea id="description" placeholder="(optional)" style="height:100px" ></textarea>
                                </div>
                                <row>
                                    <button class="btn btn-success" type="submit">Create Course</button>
                                    <a class="padding-left-30" style="color: #e8002b; text-align: right" href="https://webhandin.eng.utah.edu/groupmodify" target="_blank">Update LDAP groups here</a>
                                    <span>or send an email to </span><span style="font-style: italic">opers@eng.utah.edu</span>
                                </row>
                            </form>
						</div><!--panel-->
					</div><!--id-->
				</div><!--col-sm-12-->
			</div><!--row-->
		</div><!--container-->
	</body>
</html>
