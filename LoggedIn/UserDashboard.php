<?php
require_once __DIR__ . '/../Includes/Functions.php';
require_once '../Includes/Navbar.php';
require_once '../Auth/User.php'; //this page requires a login, so we include this



$accountID = trim($_GET['accountID'] ?? '');
$userID = trim($_GET['userID'] ?? '');
$accountNumber = trim($_GET['accountNumber'] ?? '');
$accountCreationDate = trim($_GET['accountCreationDate'] ?? '');
$balance = trim($_GET['balance'] ?? '');
$currencyType = trim($_GET['currencyType'] ?? '');
$dailyTransactionLimit = trim($_GET['dailyTransactionLimit'] ?? '');
$accountStatus = trim($_GET['accountStatus'] ?? '');

$sql = "SELECT * FROM account WHERE 1=1";
$params = [];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$res = $stmt;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>user_Dashboard</title>
</head>
<body>

<main class="dashboard">
<?php while ($r = $res->fetch(PDO::FETCH_ASSOC)): ?>
  <section class="balance-card" aria-label="Account balance">
  
    <h2 class="balance-title"><?= h($r['accountNumber']) ?></h2>
    <p class="balance-amount"><?= number_format(h($r['balance']),2) ?></p>
    <p class="balance-subtext">Available balance</p>
    
    
  </section>
  
  <?php endwhile; ?>
</main>


  
</body>
</html>