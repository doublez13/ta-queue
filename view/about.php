<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - About</title>
  <?php include('./view/head.html'); ?>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

  <div class="jumbotron jumbotron-billboard" style="margin-top: -20px; opacity: 0.75;">
    <div style="margin-top:  -40px; margin-bottom: -40px; text-align: center;">
      <h1 style="color: #404040; text-shadow: 2px 2px #000000;">Team Suzie Queue</h1>
      <img src="./resources/img/Untitled-1.png" alt="Suzie Queue Logo" style="height:70px;">
    </div>
  </div>

  <div class="container">
    <div class="col-sm-5">
      <h1 style="color: #cc0000">Team Members</h1>
      <h3 style="text-decoration: underline;">Blake Burton</h3> <br>
        <p style="font-size: 17px">
          -API Design and Implementation <br>
          -UI / UX Polishing <br>
          -Quality Assurance <br>
          -Documentation <br>
          <br>
          Email: <a href="mailto:blakesterburt@gmail.com">blakesterburt@gmail.com</a>
        </p>
            
      <h3 style="text-decoration: underline;">Ryan Welling</h3> <br>
        <p style="font-size: 17px">
          -Front-end Design and Implementation<br>
          -UI / UX Polishing <br>
          -Reports<br>
          <br>
          Email: <a href="mailto:welling.ryan@gmail.com">welling.ryan@gmail.com</a>
        </p>
          
      <h3 style="text-decoration: underline;">Zane Zakraisek</h3> <br>
        <p style="font-size: 17px">
          -DevOps <br>
          -Back-end Design and Implementation<br>
          -Database Design and Implementation<br>
          -API Design and Implementation <br>
          -Security Validation <br>
          -Front-End Queue Implementation <br>
          -UI / UX Polishing <br>
          <br>
          Email: <a href="mailto:zz@eng.utah.edu">zz@eng.utah.edu</a>
        </p>
    </div><!--col-sm-5-->
    
    <div class="col-sm-7">
      <img src="./resources/img/SuzieQueueTeamPhoto.jpg" alt="Team Photo" style="width:450px; height:300px; margin-top:50px;">
      <p>
      <br>
      Team Members L-R: Blake Burton, Ryan Welling, Matt Damon, Zane Zakraisek
      </p>
      
      <p style="font-size: 17px">
        This queue was built as our senior project for the 2017/2018 year. When we started this project, none of us had any web experience, but were vaguely familiar with some common web practices. We decided early on that we'd employ the common MVC web architecture for our project. One choice (mistake?) that separated us from the other web oriented teams was writing everything from scratch. Instead of using a common framwork like Ruby on Rails, we wrote the entire project from scratch, purely in PHP. Although this was a fantastic learning experience, a majority of time was spent writing boiler plate framework code. In fact, the entire backend was written using nothing more than the standard PHP libraries.
      </p>
      
      <h3 style="text-decoration: underline;">Queue Components</h3>
      <p style="font-size: 17px">
        -<b>View</b>: All HTML, CSS, Javascript on the frontend<br> 
        -<b>Controllers</b>: Parse all RESTfull API requests and call the appropriate model functions<br>
        -<b>Model</b>: All logic and DB/LDAP interaction is done here.<br>
        -<b>Router</b>: Every request comes through here. It either fetches the appropriate page, or sends the request on to the appropriate model
      </p>

      <p style="font-size: 17px">Please feel free to submit pull requests <a href="https://github.com/doublez13/suzie-queue">here</a>! </p>

    </div>
  </div><!--container-->
</body>
</html>
