<?php
// all pages must include functions
require_once __DIR__ . '/../../Includes/Functions.php';
// if page requires navbar, use this
require_once __DIR__ . '/../../Includes/Navbar.php';
// if page is locked behind login, use this.
require_once __DIR__ . '/../../Auth/User.php';

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

<!-- Include unique title for pages -->

<head>
  <title>User Dashboard</title>
</head>
<!-- Body -->

<body>
    <div class="sidebar">
      <div class="sidebar-brand">
        <div class="brand-flex">
          <img src="img/logo.png" width="30px" alt="logo">

            <div class="brand-icons">
            <span class="las la-bell"></span>
            <span class="las la-user-circle"></span>
        </div>
      </div>
    </div>
    <div class="sidebar-main">
    <img src="img/2.jpg" alt="">
    </div>
    
  </div>




  <main>

    <?php if (!$res): ?>
      <p>No account found for this user</p>
    <?php else: ?>
      <section aria-label="Account balance">
        <p>Account Number: <?= h($res['accountNumber']) ?></p>
        <p>Balance: <?= h($res['currencyType']) ?>   <?= number_format((float) $res['balance'], 2) ?></p>
      </section>
    <?php endif; ?>

  </main>

</body>


</html>