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
          <li><a href="./about">About</a></li>
          <li><a href="./help" target="_blank">Help</a></li>
          <li><a href="../swagger/index.html" target="_blank">Public API</a></li>
        </ul>

        <ul class="nav navbar-nav navbar-right">
        <?php require_once('./model/auth.php'); 
        if( isset($_SESSION["username"]) ){ 
          if( is_admin($_SESSION["username"]) ){ ?>
            <li class="nav-item dropdown" id="admin_menu">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Admin
              </a>
              <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <li><a href="new_course">Create Course</a></li>
                <li><a href="group_mod?type=admin">Admins</a></li>
              </ul>
            </li>
          <?php } ?>
          <li><a href="courses">Courses</a></li>
          <li><a href="#" onclick="logout();">Logout <?php echo $_SESSION["username"] ?></a></li>
        <?php }else{ ?>
          <li><a href="/">Home</a></li>
        <?php } ?>
  
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>  
