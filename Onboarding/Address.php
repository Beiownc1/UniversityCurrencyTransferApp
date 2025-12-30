<?php
require_once __DIR__ . '/../Includes/Functions.php';
// check to see if step 1 + 2 is done, else go back to step 1
if (
  empty($_SESSION['signup']['firstName']) ||
  empty($_SESSION['signup']['username']) ||
  empty($_SESSION['signup']['password']) ||
  empty($_SESSION['signup']['email'])
) {
  header("Location: /currencyTransferApp/Onboarding/NameAndDOB.php");
  exit;
}

$countries = [
  "United Kingdom",
  "France",
  "United States",
  "Germany",
  "Spain",
  "Italy"
];

$countryCurrencyType = [
  "United Kingdom" => "GBP",
  "France" => "EUR",
  "United States" => "USD",
  "Germany" => "EUR",
  "Spain" => "EUR",
  "Italy" => "EUR"
];

$callingCodes = [
  "GB" => "+44",
  "FR" => "+33",
  "US" => "+1",
  "DE" => "+49",
  "ES" => "+34",
  "IT" => "+39",
];

$errors = [];
$signup = $_SESSION['signup'] ?? [];

$country = $city = $street = $postcode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $country = trim($_POST['country'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $street = trim($_POST['street'] ?? '');
  $postcode = trim($_POST['postcode'] ?? '');

  if ($country === '' || $city === '' || $street === '' || $postcode === '') {
    $errors[] = "Please fill in all required fields.";
  }

  if ($country !== '' && !in_array($country, $countries, true)) {
    $errors[] = "Invalid country selection.";
  }

  if (empty($errors)) {
    $pc = $signup['phoneCountry'];
    $local = $signup['phoneLocal'];
    $telephone = ($callingCodes[$pc] ?? '') . ' ' . $local;

    try {
      $pdo->beginTransaction();


      $hashPassword = password_hash($signup['password'], PASSWORD_DEFAULT);

      $stmt = $pdo->prepare("
        INSERT INTO user
        (username, hashPassword, firstName, middleName, lastName, dateOfBirth, email, telephone, country, city, street, postcode, userStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
      ");

      $stmt->execute([
        $signup['username'],
        $hashPassword,
        $signup['firstName'],
        ($signup['middleName'] ?? '') !== '' ? $signup['middleName'] : NULL,
        $signup['lastName'],
        $signup['dateOfBirth'],
        $signup['email'],
        $telephone,
        $country,
        $city,
        $street,
        $postcode
      ]);


      $userID = (int) $pdo->lastInsertId();
      

      do {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $prefix =
          $letters[random_int(0, 25)] .
          $letters[random_int(0, 25)] .
          $letters[random_int(0, 25)];

        $number = random_int(10000000, 99999999);
        $accountNumber = $prefix . $number;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE accountNumber = ?");
        $stmt->execute([$accountNumber]);
        $duplicateCheck = (int) $stmt->fetchColumn();

      } while ($duplicateCheck > 0);

      $stmt = $pdo->prepare("
        INSERT INTO account
        (userID, accountNumber, dailyTransactionLimit, accountStatus)
        VALUES (?, ?, ?, 'active')
      ");
      $stmt->execute([$userID, $accountNumber, 2000]);

      $currencyType = $countryCurrencyType[$country] ?? 'GBP';
      
      $accountID = (int)$pdo->lastInsertId();

      $stmt = $pdo->prepare("
        INSERT INTO wallet
        (accountID, currencyType, balance, walletStatus)
        VALUES (?, ?, 0, 'active')
      ");
      $stmt->execute([$accountID, $currencyType]);
      
      

      $pdo->commit();

      unset($_SESSION['signup']);
      header("Location: /currencyTransferApp/Public/login.php");
      exit;

    } catch (PDOException $e) {
      $pdo->rollBack();
      die($e->getMessage());
    }
  }
}

?>

<h1>Sign Up - Step 3 of 3</h1>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <?php foreach ($errors as $e): ?>
      <p><?= h($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form align="center" method="post">
  <label>Country*</label><br>
  <select name="country" required>
    <option value="">Select…</option>
    <?php foreach ($countries as $c): ?>
      <option value="<?= h($c) ?>" <?= $country === $c ? 'selected' : '' ?>>
        <?= h($c) ?>
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <label>City*</label><br>
  <input name="city" required value="<?= h($city) ?>"><br><br>

  <label>Street*</label><br>
  <input name="street" required value="<?= h($street) ?>"><br><br>

  <label>Postcode*</label><br>
  <input name="postcode" required value="<?= h($postcode) ?>"><br><br>

  <button type="button" onclick="location.href='UsernameAndPassword.php'">Back</button>
  <button type="submit">Create Account</button>
  <button type="button" onclick="location.href='Cancel.php'">Cancel</button>
</form>