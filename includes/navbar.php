<?php
require_once __DIR__ . '/../Includes/Functions.php';
?>

<?php
// get current page for active link styling
$currentPage = basename($_SERVER['PHP_SELF']);

// check login state
$isLoggedIn = isset($_SESSION['userID']) || isset($_SESSION['adminID']);


$logoLinkTo = "/currencyTransferApp/Public/Index.php";

if (isset($_SESSION['userID'])) {
  $logoLinkTo = "/currencyTransferApp/LoggedUser/Dashboard.php";
  
} elseif (isset($_SESSION['adminID'])) {
  $logoLinkTo = "/currencyTransferApp/LoggedAdmin/Dashboard.php";
}

?>

<header class="navbar">
  <div class="nav-container">

        <!-- logo button, link is defined above based on session -->
    <a href="<?=$logoLinkTo?>" class="logo">
      <img src="/currencyTransferApp/Images/BankLogo.png" alt="Riverloot Logo">
    </a>

    <!-- right navigation -->
    <nav class="nav-right">

      <!-- users 'dashboard' button -->
      <?php if (isset($_SESSION['userID'])): ?>

        <?php if ($currentPage !== 'Dashboard.php'): ?>
          <a href="/currencyTransferApp/LoggedUser/Dashboard.php">Dashboard</a>
        <?php endif; ?>

        <!-- users 'logout' button -->
        <a href="/currencyTransferApp/Logout/Logout.php">Logout</a>


        <!-- admins 'dashboard' button -->
      <?php elseif (isset($_SESSION['adminID'])): ?>

        <?php if ($currentPage !== 'Dashboard.php'): ?>
          <a href="/currencyTransferApp/LoggedAdmin/Dashboard.php">Admin</a>
        <?php endif; ?>

        <!-- admins 'logout' button -->
        <a href="/currencyTransferApp/Logout/Logout.php">Logout</a>



      <?php else: ?>


        <!-- if youre not in sign up page, show link to it -->
        <?php if ($currentPage !== 'NameAndDOB.php'): ?>
          <a href="/currencyTransferApp/Onboarding/NameAndDOB.php">Sign up</a>
        <?php endif; ?>
        <!-- if youre not in login page, show link to it -->
        <?php if ($currentPage !== 'login.php'): ?>
          <a href="/currencyTransferApp/Public/Login.php">Log in</a>
        <?php endif; ?>

      <?php endif; ?>
    </nav>


  </div>
</header>