<?php
require_once __DIR__ . '/../Includes/Functions.php';
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['exchange'])) {
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode("Invalid request."));
  exit;
}

$toCurrency = strtoupper(trim($_POST['toCurrency'] ?? ''));
$fromAmount = (float)($_POST['fromAmount'] ?? 0);

if ($toCurrency === '' || $fromAmount <= 0) {
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode("Invalid input."));
  exit;
}

if (!isset($rates[$toCurrency])) {
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode("Unsupported currency."));
  exit;
}

$stmt = $pdo->prepare("
  SELECT a.accountID, u.country
  FROM account a
  JOIN user u ON u.userID = a.userID
  WHERE a.userID = :userID
  LIMIT 1
");
$stmt->execute([':userID' => $userID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode("No account found."));
  exit;
}

$accountID = (int)$row['accountID'];
$country = (string)$row['country'];
$baseCurrency = $countryCurrencyType[$country] ?? 'GBP';

if ($toCurrency === $baseCurrency) {
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode("Cannot exchange to the same currency."));
  exit;
}

$toAmount = convertAmount($fromAmount, $baseCurrency, $toCurrency, $rates);
if ($toAmount <= 0) {
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode("Conversion failed."));
  exit;
}

try {
  $pdo->beginTransaction();


  $stmt = $pdo->prepare("
    SELECT walletID, balance
    FROM wallet
    WHERE accountID = :accountID AND currencyType = :currency
    LIMIT 1
  ");
  $stmt->execute([':accountID' => $accountID, ':currency' => $baseCurrency]);
  $baseWallet = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$baseWallet) {
    throw new Exception("Base wallet not found.");
  }

  if ((float)$baseWallet['balance'] < $fromAmount) {
    throw new Exception("Insufficient funds.");
  }


  $stmt = $pdo->prepare("
    SELECT walletID
    FROM wallet
    WHERE accountID = :accountID AND currencyType = :currency
    LIMIT 1
  ");
  $stmt->execute([':accountID' => $accountID, ':currency' => $toCurrency]);
  $targetWallet = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$targetWallet) {
    $stmt = $pdo->prepare("
      INSERT INTO wallet (accountID, currencyType, balance, walletCreatedAt, walletStatus)
      VALUES (:accountID, :currency, 0, CURRENT_TIMESTAMP, 'active')
    ");
    $stmt->execute([':accountID' => $accountID, ':currency' => $toCurrency]);
    $targetWalletID = (int)$pdo->lastInsertId();
  } else {
    $targetWalletID = (int)$targetWallet['walletID'];
  }


  $stmt = $pdo->prepare("
    UPDATE wallet
    SET balance = balance - :amount
    WHERE walletID = :walletID AND balance >= :amount
  ");
  $stmt->execute([':amount' => $fromAmount, ':walletID' => (int)$baseWallet['walletID']]);
  if ($stmt->rowCount() !== 1) {
    throw new Exception("Failed to deduct balance.");
  }

  $stmt = $pdo->prepare("
    UPDATE wallet
    SET balance = balance + :amount
    WHERE walletID = :walletID
  ");
  $stmt->execute([':amount' => $toAmount, ':walletID' => $targetWalletID]);

  $pdo->commit();

  $msg = "Exchanged " . number_format($fromAmount, 2) . " $baseCurrency → " . number_format($toAmount, 2) . " $toCurrency.";
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?ok=" . urlencode($msg));
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  header("Location: /currencyTransferApp/Exchange/ExchangeCurrency.php?err=" . urlencode($e->getMessage()));
  exit;
}
