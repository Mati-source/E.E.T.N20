<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Datos del Personal</title>
  <link rel="stylesheet" href="assets/css/fontawesome.css" />
  <link rel="stylesheet" href="assets/css/templatemo-grad-school.css" />
  <link rel="stylesheet" href="assets/css/owl.css" />
  <link rel="stylesheet" href="assets/css/lightbox.css" />
  <link rel="stylesheet" href="assets/css/add.css" />
  <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css" />
  <style>
    body {
      background-image: url('assets/images/fondo.jpg');
      min-height: 100vh;
      margin: 0;
      padding: 0;
      position: relative;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #F5F5F5;
      z-index: 1;
      pointer-events: none;
    }

    .login {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 400px;
      margin: 100px auto 0 auto;
      background-color: #F5F5F5;
      text-align: center;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
    }

    .login-header {
      margin-bottom: 10px;
      font-size: 24px;
      color: #333;
    }

    .welcome-icon {
      font-size: 50px;
      color: rgb(255, 0, 217);
    }

    .action-button {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 16px;
    }

    nav.navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      width: 100vw;
      z-index: 10;
      border-radius: 0;
      margin: 0;
    }

    @media (max-width: 600px) {
      .login {
        max-width: 95vw;
        padding: 20px 5vw;
        margin-top: 60px;
      }

      .welcome-icon {
        font-size: 40px;
      }

      .login-header {
        font-size: 20px;
      }

      .action-button {
        font-size: 14px;
        padding: 8px;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-default navbar-fixed-top">
    <?php include('nav.php'); ?>
  </nav>


  <div class="login">
    <div class="welcome-icon">👋</div>
    <h2 class="login-header">Bienvenido </h2>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>