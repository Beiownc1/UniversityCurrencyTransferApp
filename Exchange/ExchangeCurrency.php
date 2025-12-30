<?php
require_once __DIR__ . '/../Includes/Functions.php';
require_once __DIR__ . '/../Includes/Navbar.php';
require_once __DIR__ . '/../Auth/User.php';

$userID = (int)$_SESSION['userID'];


$countryCurrencyType = [
  "United Kingdom" => "GBP",
  "France" => "EUR",
  "United States" => "USD",
  "Germany" => "EUR",
  "Spain" => "EUR",
  "Italy" => "EUR"
];

$rates = [
  'GBP' => 1.0,
  'EUR' => 1.15,
  'USD' => 1.25
];


function convertAmount(float $amount, string $from, string $to, array $rates): float {
  if ($from === $to) return round($amount, 2);
  if (!isset($rates[$from], $rates[$to])) return 0.0;
  $gbp = $amount / $rates[$from];
  return round($gbp * $rates[$to], 2);
}


$stmt = $pdo->prepare("
  SELECT a.accountID, a.accountNumber, u.country
  FROM account a
  JOIN user u ON u.userID = a.userID
  WHERE a.userID = :userID
  LIMIT 1
");
$stmt->execute([':userID' => $userID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) die("No account found.");

$accountID = (int)$row['accountID'];
$country = (string)$row['country'];
$baseCurrency = $countryCurrencyType[$country] ?? 'GBP';


$stmt = $pdo->prepare("
  SELECT walletID, currencyType, balance
  FROM wallet
  WHERE accountID = :accountID
  ORDER BY currencyType
");
$stmt->execute([':accountID' => $accountID]);
$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);


$baseBalance = 0.0;
foreach ($wallets as $w) {
  if ($w['currencyType'] === $baseCurrency) {
    $baseBalance = (float)$w['balance'];
    break;
  }
}

$allCurrencies = array_keys($rates);

$ok = $_GET['ok'] ?? '';
$err = $_GET['err'] ?? '';
?>

  <title>Exchange Currency</title>

<body>
<main class="dash">
  <aside class="dash__sidebar" aria-label="Dashboard navigation">
    <div class="dash__side-top">
      <p class="dash__side-title">Menu</p>
    </div>

    <nav class="dash__nav">
      <a href="/currencyTransferApp/LoggedIn/User/Dashboard.php" class="dash__link">Overview</a>
      <a href="/currencyTransferApp/Transaction/UserTransactions.php" class="dash__link">Send money</a>
      <a href="/currencyTransferApp/Exchange/ExchangeCurrency.php" class="dash__link dash__link--active">Exchange</a>
      <a href="#" class="dash__link">Transactions</a>
      <a href="#" class="dash__link">Cards</a>
      <a href="#" class="dash__link">Settings</a>
      <a href="#" class="dash__link">Support</a>
    </nav>
  </aside>

<main class="dash" style="max-width: 900px; margin: 2rem auto; padding: 0 1rem;">
  <article class="card card--padded">
    <h1 class="h2">Exchange</h1>

    <p class="muted">
      Base currency (from your country): <strong><?= h($baseCurrency) ?></strong><br>
      Available in base wallet: <strong><?= number_format($baseBalance, 2) ?></strong>
    </p>

    <?php if ($ok): ?>
      <p class="ok"><?= h($ok) ?></p>
    <?php endif; ?>

    <?php if ($err): ?>
      <p class="err"><?= h($err) ?></p>
    <?php endif; ?>

   <form method="post" action="/currencyTransferApp/Exchange/ExchangeProcess.php">
  <div class="ex-row">
    <label class="ex-label">From</label>
    <select class="ex-select" name="fromCurrency" required>
      <option value="">Select wallet</option>
      <?php foreach ($wallets as $w): ?>
        <option value="<?= h($w['currencyType']) ?>">
          <?= h($w['currencyType']) ?> (<?= number_format((float)$w['balance'], 2) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="ex-row">
    <label class="ex-label">To</label>
    <select class="ex-select" name="toCurrency" required>
      <option value="">Select currency</option>
      <?php foreach ($allCurrencies as $cur): ?>
        <option value="<?= h($cur) ?>"><?= h($cur) ?></option>
      <?php endforeach; ?>
    </select>
    <p class="tiny muted" style="margin:.25rem 0 0;">
      (You can’t exchange to the same currency as “From”.)
    </p>
  </div>

  <div class="ex-row">
    <label class="ex-label">Amount</label>
    <input class="ex-input" type="number" name="fromAmount" step="0.01" min="0.01" required>
  </div>

  <button class="btn" type="submit" name="exchange" value="1">Exchange</button>

  <p class="tiny muted" style="margin-top:.75rem;">
    This will create a new wallet automatically if you don’t have the target currency yet.
  </p>
</form>

  </article>

  <article class="card card--padded" style="margin-top:1rem;">
    <h2 class="h2">Your wallets</h2>
    <?php if (!$wallets): ?>
      <p class="muted">No wallets found.</p>
    <?php else: ?>
      <ul style="margin:0; padding-left:1.25rem;">
        <?php foreach ($wallets as $w): ?>
          <li>
            <?= h($w['currencyType']) ?> — <?= number_format((float)$w['balance'], 2) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </article>
</main>

</body>
</html>
