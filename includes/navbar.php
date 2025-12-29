<?php
require_once __DIR__ . '/../Includes/Functions.php';
?>

<?php
// get current page for active link styling
$CurrentPage = basename($_SERVER['PHP_SELF']);

$LogoLinkTo = "/currencyTransferApp/Public/Index.php";

if (isset($_SESSION['userID'])) {
  $LogoLinkTo = "/currencyTransferApp/LoggedIn/User/Dashboard.php";
  
} elseif (isset($_SESSION['adminID'])) {
  $LogoLinkTo = "/currencyTransferApp/LoggedIn/Admin/Dashboard.php";
}
?>

<header class="navbar">
  <div class="nav-container">
        <!-- logo button, link is defined above based on session -->
    <a href="<?=$LogoLinkTo?>" class="logo">
      <img src="/currencyTransferApp/Images/BankLogo.png" alt="Riverloot Logo">
    </a>

    <!-- right navigation -->
    <nav class="nav-right">

      <!-- users 'dashboard' button -->
      <?php if (isset($_SESSION['userID'])): ?>

        <?php if ($CurrentPage !== 'UserDashboard.php'): ?>
          <a href="/currencyTransferApp/LoggedIn/User/Dashboard.php">Dashboard</a>
        <?php endif; ?>

        <!-- users 'logout' button -->
        <a href="/currencyTransferApp/Logout/Logout.php">Logout</a>

        <!-- admins 'dashboard' button -->
      <?php elseif (isset($_SESSION['adminID'])): ?>

        <?php if ($CurrentPage !== 'AdminDashboard.php'): ?>
          <a href="/currencyTransferApp/LoggedIn/Admin/Dashboard.php">Dashboard</a>
        <?php endif; ?>

        <!-- admins 'logout' button -->
        <a href="/currencyTransferApp/Logout/Logout.php">Logout</a>

      <?php else: ?>

        <!-- if youre not in sign up page, show link to it -->
        <?php if ($CurrentPage !== 'NameAndDOB.php'): ?>
          <a href="/currencyTransferApp/Onboarding/NameAndDOB.php">Sign up</a>
        <?php endif; ?>
        <!-- if youre not in login page, show link to it -->
        <?php if ($CurrentPage !== 'Login.php'): ?>
          <a href="/currencyTransferApp/Public/Login.php">Log in</a>
        <?php endif; ?>

      <?php endif; ?>
    </nav>
  </div>
</header>