<?php
require_once __DIR__ . '/../includes/functions.php';
?>

<?php
// get current page for active link styling
$currentPage = basename($_SERVER['PHP_SELF']);

// check login state
$isLoggedIn = isset($_SESSION['userID']) || isset($_SESSION['adminID']);


$logoLinkTo = "/currencyTransferApp/public/index.php";

if (isset($_SESSION['userID'])) {
  $logoLinkTo = "/currencyTransferApp/private/user_dashboard.php";
  
} elseif (isset($_SESSION['adminID'])) {
  $logoLinkTo = "/currencyTransferApp/private/admin_dashboard.php";
}


?>

<header class="navbar">
  <div class="nav-container">

        <!-- logo button, link is defined above based on session -->
    <a href="<?=$logoLinkTo?>" class="logo">
      <img src="/currencyTransferApp/_img/bankLogo.png" alt="Bank Logo">
    </a>

    <!-- right navigation -->
    <nav class="nav-right">

      <!-- users 'dashboard' button -->
      <?php if (isset($_SESSION['userID'])): ?>

        <?php if ($currentPage !== 'user_dashboard.php'): ?>
          <a href="/currencyTransferApp/private/user_dashboard.php">Dashboard</a>
        <?php endif; ?>

        <!-- users 'logout' button -->
        <a href="/currencyTransferApp/public/logout.php">Logout</a>


        <!-- admins 'dashboard' button -->
      <?php elseif (isset($_SESSION['adminID'])): ?>

        <?php if ($currentPage !== 'admin_dashboard.php'): ?>
          <a href="/currencyTransferApp/private/admin_dashboard.php">Admin</a>
        <?php endif; ?>

        <!-- admins 'logout' button -->
        <a href="/currencyTransferApp/public/logout.php">Logout</a>



      <?php else: ?>


        <!-- if youre not in sign up page, show link to it -->
        <?php if ($currentPage !== 'signup1.php'): ?>
          <a href="/currencyTransferApp/public/signup1.php">Sign up</a>
        <?php endif; ?>
        <!-- if youre not in login page, show link to it -->
        <?php if ($currentPage !== 'login.php'): ?>
          <a href="/currencyTransferApp/public/login.php">Log in</a>
        <?php endif; ?>

      <?php endif; ?>
    </nav>


  </div>
</header>