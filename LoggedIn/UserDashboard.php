<?php
require_once __DIR__ . '/../Includes/Functions.php';
require_once __DIR__ . '/../Includes/Navbar.php';
require_once __DIR__ . '/../Auth/User.php';

$myUserID = (int) ($_SESSION['userID'] ?? 0);

$sql = "
  SELECT
  a.userID,
  a.accountID,
  a.accountNumber,
  a.dailyTransactionLimit,
  a.balance,
  u.firstName ||
  CASE
  WHEN u.middleName IS NOT NULL AND u.middleName != '' THEN ' ' || u.middleName || '. '
  ELSE ' '
  END ||
  u.lastName AS fullName,
  a.currencyType,
  u.country AS country,
  u.telephone AS telephone,
  u.email AS email
  FROM account a
  JOIN user u ON a.userID = u.userID
  WHERE a.userID = :myUserID
  LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':myUserID' => $myUserID]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
</head>
<body>

<main class="dashboard">

<?php if (!$res): ?>
<p>No account found for this user</p>
<?php else: ?>
<section aria-label="Account balance">
<h2><?= h($res['accountNumber']) ?></h2>
<p><?= number_format((float)$res['balance'], 2) ?> <?= h($res['currencyType']) ?></p>
<p>Available balance</p>
</section>
<?php endif; ?>

</main>

</body>
</html>
