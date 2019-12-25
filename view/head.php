  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="google" content="notranslate" />
  <meta http-equiv="Content-Language" content="en">
  
  <meta name="AUTHOR"      content="Ryan Welling, Blake Burton, Zane Zakraisek">
  <meta name="keywords"    content="University of Utah, 2017-2018, College of Engineering">
  <meta name="description" content="Senior Project">
  <meta name="theme-color" content="#646a72">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!--App Manifest-->
  <link rel="manifest" href="./resources/manifest.json">  

  <!--U icon for browser tabs-->
  <link rel="icon" type="image/png" href="./resources/img/favicon-32x32.png">
  <link rel="icon" type="image/png" href="./resources/img/favicon-16x16.png">
  <link rel="icon" type="image/png" href="./resources/img/favicon.ico">

  <!-- ALL CSS FILES -->
  <?php
    $include   = './resources/CSS/global.css';
    $filemtime = filemtime($include);
    $source    = $include.'?ver='.$filemtime;
    echo "<link rel='stylesheet' type='text/css' href='".$source."'>";
  ?>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

  <!-- jQuery CDN -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha384-vk5WoKIaW/vJyUAd9n/wmopsmNhiy+L2Z+SBxGYnUkunIxVxAv/UtMOhba/xskxh" crossorigin="anonymous"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha384-Dziy8F2VlJQLMShA6FHWNul/veM9bCkRUaLqr199K94ntO5QUrLJBEbYegdSkkqX" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" integrity="sha384-Nlo8b0yiGl7Dn+BgLn4mxhIIBU6We7aeeiulNCjHdUv/eKHx59s3anfSUjExbDxn" crossorigin="anonymous">

  <!-- Latest compiled and minified JavaScript -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
  <!-- Cloudflare buttons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
