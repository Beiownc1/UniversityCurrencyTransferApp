<?php
// all pages must include functions
require_once __DIR__ . '/../../Includes/Functions.php';
// if page requires navbar, use this
require_once __DIR__ . '/../../Includes/Navbar.php';
// if page is locked behind login, use this.
require_once __DIR__ . '/../../Auth/User.php';


$stmt = $pdo->prepare("
  SELECT accountID, accountNumber, dailyTransactionLimit
  FROM account
  WHERE userID = :uid
  LIMIT 1
");
$stmt->execute([':uid' => (int)$_SESSION['userID']]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) { die("No account found."); }

$accountID = (int)$account['accountID'];

$stmt = $pdo->prepare("
  SELECT balance
  FROM wallet
  WHERE accountID = :aid AND currencyType = 'GBP'
  LIMIT 1
");
$stmt->execute([':aid' => $accountID]);
$gbpBalance = (float)($stmt->fetchColumn() ?? 0);


$stmt = $pdo->prepare("
  SELECT walletID, currencyType, balance
  FROM wallet
  WHERE accountID = :aid
  ORDER BY currencyType
");
$stmt->execute([':aid' => $accountID]);
$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$wallets) {
  $wallets = [
    ['walletID' => 0, 'currencyType' => 'GBP', 'balance' => 0]
  ];
}

$stmt = $pdo->prepare("
  SELECT
    u.firstName ||
      CASE
        WHEN u.middleName IS NOT NULL AND u.middleName != '' THEN ' ' || u.middleName || '. '
        ELSE ' '
      END ||
      u.lastName AS fullName,
    u.country,
    u.telephone,
    u.email
  FROM user u
  WHERE u.userID = :uid
  LIMIT 1
");
$stmt->execute([':uid' => (int)$_SESSION['userID']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard</title>
</head>

<body>
<main class="dash">
  <aside class="dash__sidebar" aria-label="Dashboard navigation">
    <div class="dash__side-top">
      <p class="dash__side-title">Menu</p>
    </div>

    <nav class="dash__nav">
      <a href="#" class="dash__link dash__link--active">Overview</a>
      <a href="/currencyTransferApp/Transaction/UserTransactions.php" class="dash__link">Send money</a>
      <a href="#" class="dash__link">Exchange</a>
      <a href="#" class="dash__link">Transactions</a>
      <a href="#" class="dash__link">Cards</a>
      <a href="#" class="dash__link">Settings</a>
      <a href="#" class="dash__link">Support</a>
    </nav>
  </aside>

  <section class="dash__content">
    <!-- Header / main balance box -->
    <header class="dash__header card card--padded">
      <div class="dash__header-left">
        <p class="muted">Account Number</p>
        <p class="mono"><?= h($account['accountNumber']) ?></p>

        <h1 class="balance" id="balanceTitle">
          <span class="balance__ccy">GBP</span>
          <span class="balance__amt"><?= number_format($gbpBalance, 2) ?></span>
        </h1>

        <p class="muted">Available balance</p>
      </div>
    </header>

    <!-- Grid of boxes to fill later -->
    <section class="dash__grid" aria-label="Dashboard widgets">
      <article class="card card--padded">
        <h2 class="h2">Quick actions</h2>
        <div class="placeholder">Buttons later</div>
      </article>

      <article class="card card--padded">
        <h2 class="h2">Recent transactions</h2>
        <div class="placeholder">List later</div>
      </article>

      <article class="card card--padded">
        <h2 class="h2">Spending insights</h2>
        <div class="placeholder">Chart.js later</div>
      </article>

      <article class="card card--padded">
        <h2 class="h2">Limits & status</h2>
        <p class="muted">Daily limit: <?= number_format((float)($account['dailyTransactionLimit'] ?? 0), 2) ?></p>
        <div class="placeholder">Status later</div>
      </article>
    </section>

  </section>
</main>
</body>
</html>
