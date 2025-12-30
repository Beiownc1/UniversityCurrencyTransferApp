<?php
require_once __DIR__ . '/../Includes/Functions.php';
require_once __DIR__ . '/../Includes/Navbar.php';
require_once __DIR__ . '/../Auth/User.php';

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

  // FROM -> GBP -> TO
  $gbp = $amount / $rates[$from];
  $out = $gbp * $rates[$to];
  return round($out, 2);
}

$userID = (int)$_SESSION['userID'];

$stmt = $pdo->prepare("
  SELECT a.accountID, a.accountNumber, u.country
  FROM account a
  JOIN user u ON u.userID = a.userID
  WHERE a.userID = :userID
  LIMIT 1
");
$stmt->execute([':userID' => $userID]);
$sender = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sender) {
  die("No account found.");
}

$senderAccountID = (int)$sender['accountID'];
$senderCountry = (string)$sender['country'];
$senderBaseCurrency = $countryCurrencyType[$senderCountry] ?? 'GBP';

$stmt = $pdo->prepare("
  SELECT walletID, currencyType, balance
  FROM wallet
  WHERE accountID = :accountID
  ORDER BY currencyType
");
$stmt->execute([':accountID' => $senderAccountID]);
$senderWallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$walletByCurrency = [];
foreach ($senderWallets as $w) {
  $walletByCurrency[$w['currencyType']] = $w;
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
  $receiverAccountNumber = trim($_POST['receiverAccountNumber'] ?? '');
  $sendCurrency = strtoupper(trim($_POST['sendCurrency'] ?? ''));
  $sendAmount = (float)($_POST['sendAmount'] ?? 0);

  if ($receiverAccountNumber === '' || $sendCurrency === '' || $sendAmount <= 0) {
    $errors[] = "Please fill in all fields with a valid amount.";
  }

  if (!isset($walletByCurrency[$sendCurrency])) {
    $errors[] = "You don't have a wallet in $sendCurrency.";
  }

  if (!isset($rates[$sendCurrency])) {
    $errors[] = "Currency $sendCurrency is not supported by rates yet.";
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare("
      SELECT a.accountID, u.country
      FROM account a
      JOIN user u ON u.userID = a.userID
      WHERE a.accountNumber = :accountNumber
      LIMIT 1
    ");
    $stmt->execute([':accountNumber' => $receiverAccountNumber]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$receiver) {
      $errors[] = "Receiver account not found.";
    } else {
      $receiverAccountID = (int)$receiver['accountID'];

      if ($receiverAccountID === $senderAccountID) {
        $errors[] = "You can't send money to your own account.";
      }
    }
  }

  if (empty($errors)) {
    $receiverCountry = (string)$receiver['country'];
    $receiverBaseCurrency = $countryCurrencyType[$receiverCountry] ?? 'GBP';

    if (!isset($rates[$receiverBaseCurrency])) {
      $errors[] = "Receiver base currency $receiverBaseCurrency is not supported by rates yet.";
    }
  }

  if (empty($errors)) {
    $receiverGets = convertAmount($sendAmount, $sendCurrency, $receiverBaseCurrency, $rates);

    if ($receiverGets <= 0) {
      $errors[] = "Conversion failed (check hardcoded rates).";
    }
  }

  if (empty($errors)) {
    try {
      $pdo->beginTransaction();

      $stmt = $pdo->prepare("
        SELECT walletID, balance
        FROM wallet
        WHERE accountID = :accountID AND currencyType = :currency
        LIMIT 1
      ");
      $stmt->execute([
        ':accountID' => $senderAccountID,
        ':currency' => $sendCurrency
      ]);
      $senderWallet = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$senderWallet) {
        throw new Exception("Sender wallet not found.");
      }

      if ((float)$senderWallet['balance'] < $sendAmount) {
        throw new Exception("Insufficient funds in $sendCurrency wallet.");
      }
      $stmt = $pdo->prepare("
        SELECT walletID
        FROM wallet
        WHERE accountID = :accountID AND currencyType = :currency
        LIMIT 1
      ");
      $stmt->execute([
        ':accountID' => $receiverAccountID,
        ':currency' => $receiverBaseCurrency
      ]);
      $receiverWallet = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$receiverWallet) {
        $stmt = $pdo->prepare("
          INSERT INTO wallet (accountID, currencyType, balance, walletCreatedAt, walletStatus)
          VALUES (:accountID, :currency, 0, CURRENT_TIMESTAMP, 'active')
        ");
        $stmt->execute([
          ':accountID' => $receiverAccountID,
          ':currency' => $receiverBaseCurrency
        ]);
        $receiverWalletID = (int)$pdo->lastInsertId();
      } else {
        $receiverWalletID = (int)$receiverWallet['walletID'];
      }

      $stmt = $pdo->prepare("
        UPDATE wallet
        SET balance = balance - :amount
        WHERE walletID = :walletID AND balance >= :amount
      ");
      $stmt->execute([
        ':amount' => $sendAmount,
        ':walletID' => (int)$senderWallet['walletID']
      ]);
      if ($stmt->rowCount() !== 1) {
        throw new Exception("Failed to deduct funds (balance changed).");
      }

      $stmt = $pdo->prepare("
        UPDATE wallet
        SET balance = balance + :amount
        WHERE walletID = :walletID
      ");
      $stmt->execute([
        ':amount' => $receiverGets,
        ':walletID' => $receiverWalletID
      ]);

      $stmt = $pdo->prepare("
        INSERT INTO transactions (senderAccountID, receiverAccountID, amount, transactionsStatus)
        VALUES (:senderAccountID, :receiverAccountID, :amount, 'complete')
      ");
      $stmt->execute([
        ':senderAccountID' => $senderAccountID,
        ':receiverAccountID' => $receiverAccountID,
        ':amount' => $receiverGets
      ]);

      $pdo->commit();

      $success = "Sent " . number_format($sendAmount, 2) . " $sendCurrency. Receiver got " . number_format($receiverGets, 2) . " $receiverBaseCurrency.";
      header("Location: UserTransactions.php?ok=" . urlencode($success));
      exit;

    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $errors[] = $e->getMessage();
    }
  }
}

if (!empty($_GET['ok'])) {
  $success = $_GET['ok'];
}
?>

  <title>Send Money</title>



<body>
<main class="dash">
  <aside class="dash__sidebar" aria-label="Dashboard navigation">
    <div class="dash__side-top">
      <p class="dash__side-title">Menu</p>
    </div>

    <nav class="dash__nav">
      <a href="/currencyTransferApp/LoggedIn/User/Dashboard.php" class="dash__link">Overview</a>
      <a href="/currencyTransferApp/Transaction/UserTransactions.php" class="dash__link dash__link--active">Send money</a>
      <a href="/currencyTransferApp/Exchange/ExchangeCurrency.php" class="dash__link">Exchange</a>
      <a href="#" class="dash__link">Transactions</a>
      <a href="#" class="dash__link">Cards</a>
      <a href="#" class="dash__link">Settings</a>
      <a href="#" class="dash__link">Support</a>
    </nav>
  </aside>


    
    




<main class="dash" style="max-width: 900px; margin: 2rem auto; padding: 0 1rem;">
  <article class="card card--padded">
    <h1 class="h2">Send money</h1>

    <p class="muted">
      Your base currency: <strong><?= h($senderBaseCurrency) ?></strong>
    </p>

    <?php if (!empty($success)): ?>
      <p class="ok"><?= h($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="err">
        <?php foreach ($errors as $e): ?>
          <p><?= h($e) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="ex-row">
        <label class="ex-label">Receiver account number</label>
        <input class="ex-input" name="receiverAccountNumber" required placeholder="e.g. AMB72072198">
      </div>

      <div class="ex-row">
        <label class="ex-label">Send from wallet</label>
        <select class="ex-select" name="sendCurrency" required>
          <option value="">Select currency</option>
          <?php foreach ($senderWallets as $w): ?>
            <option value="<?= h($w['currencyType']) ?>">
              <?= h($w['currencyType']) ?> (<?= number_format((float)$w['balance'], 2) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="ex-row">
        <label class="ex-label">Amount</label>
        <input class="ex-input" type="number" name="sendAmount" step="0.01" min="0.01" required>
      </div>

      <button class="btn" type="submit" name="send" value="1">Send</button>
    </form>
  </article>
</main>

</body>
</html>
