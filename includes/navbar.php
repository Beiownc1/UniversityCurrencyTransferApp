<?php
require_once __DIR__ . '/../includes/functions.php';
?>

<?php
// get current page for active link styling
$currentPage = basename($_SERVER['PHP_SELF']);

// check login state
$isLoggedIn = isset($_SESSION['userID']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body class="tempBackground" align="center">

<header class="navbar">
  <div class="nav-container">
  
      <a href="index.php" class="logo">
      <img src="../_img/bankLogo.png" alt="Hotel Logo">
      </a>

    <!-- right navigation -->
    <nav class="nav-right">
      <?php if ($isLoggedIn): ?>
      <?php if ($currentPage !== 'dashboard.php'): ?>
      <a href="dashboard.php">Dashboard</a>
      <?php endif; ?>

      <a href="logout.php">Logout</a>

      <?php else: ?>
      <?php if ($currentPage !== 'signup1.php'): ?>
      <a href="signup1.php">Sign up</a>
      <?php endif; ?>

      <?php if ($currentPage !== 'login.php'): ?>
      <a href="login.php">Log in</a>
      <?php endif; ?><?php endif; ?>
    </nav>
    
  </div>
</header>




</body>

</html>