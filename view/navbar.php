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
        <?php if( isset($_SESSION["username"]) ){ 
          if( isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] ){ ?>
            <li class="nav-item dropdown" id="admin_menu">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Admin
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <ul>
                  <li>
                    <a class="dropdown-item" href="new_class">Create Course</a>
                  </li>
                </ul>
              </div>
            </li>
          <?php } ?>
          <li>
            <a href="classes">Courses</a>
          </li>
          <li>
            <a href="#" onclick="logout();">Logout <script> document.write(localStorage.first_name)</script></a>
          </li>
        <?php }else{ ?>
          <li>
            <a href="/">Home</a>
          </li>
        <?php } ?>
  
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>  
