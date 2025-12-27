<?php

require_once __DIR__ . '/../includes/functions.php';

$errorMessage = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {

  $username = trim($_POST['username'] ?? '');
  $hashPassword = trim($_POST['hashPassword'] ?? '');
  $firstName = trim($_POST['firstName'] ?? '');
  $middleName = trim($_POST['middleName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telephone = trim($_POST['telephone'] ?? '');
  $country = trim($_POST['country'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $street = trim($_POST['street'] ?? '');
  $postcode = trim($_POST['postcode'] ?? '');

  if (
    !validateRequiredFields([
      $username,
      $hashPassword,
      $firstName,
      $lastName,
      $dateOfBirth,
      $email,
      $telephone,
      $country,
      $city,
      $street,
      $postcode
    ])
  ) {
    $errorMessage[] = "All fields must be filled";
  }

  $names = [
    'First name' => $firstName,
    'Middle name' => $middleName,
    'Last name' => $lastName
  ];

  foreach ($names as $label => $value) {
    if ($value !== '' && !validateName($value)) {
      $errorMessage[] = "$label can only contain letters and spaces";
      break;
    }
  }

  if ($dateOfBirth !== '' && $dateOfBirth > date('Y-m-d')) {
    $errorMessage[] = "Date of birth cannot be in the future";
  }


  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMessage[] = "Enter a valid email address";
  }


  if ($telephone !== '' && !preg_match('/^[0-9+\- ]+$/', $telephone)) {
    $errorMessage[] = "Phone number can only contain numbers, spaces, '+' or '-'";
  }


  if (empty($errorMessage)) {

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `user` WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
      $errorMessage[] = "Email unavailable";
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `user` WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
      $errorMessage[] = "Username unavailable";
    }
  }

  if (empty($errorMessage)) {
    try {
      $pdo->beginTransaction();

      $hashPassword = password_hash($hashPassword, PASSWORD_DEFAULT);

      $stmt = $pdo->prepare("
                INSERT INTO `user`
                (username, hashPassword, firstName, middleName, lastName, dateOfBirth, email, telephone, country, city, street, postcode, userStatus)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");

      $stmt->execute([
        $username,
        $hashPassword,
        $firstName,
        $middleName !== '' ? $middleName : null,
        $lastName,
        $dateOfBirth,
        $email,
        $telephone,
        $country,
        $city,
        $street,
        $postcode
      ]);

      $pdo->commit();
      header('Location: login.php');
      exit;

    } catch (PDOException $e) {
      $pdo->rollBack();
      $errorMessage[] = "Something went wrong. Please try again.";
    }
  }
}
?>

<h1>Create New Account</h1>

<?php if (!empty($errorMessage)): ?>
  <p style="color:red; font-weight:bold;">
    <?= h($errorMessage[0]) ?>
  </p>

<?php endif; ?>

<form method="post">
  <table>
    <tr>
      <td>Create a Username*</td>
      <td><input type="text" name="username" value="<?= old('username') ?>" required>
</td>
    </tr>

    <tr>
      <td>Create a Password*</td>
      <td><input type="password" name="hashPassword" required></td>
    </tr>

    <tr>
      <td>First Name*</td>
      <td><input type="text" name="firstName" value="<?= old('firstName') ?>" required>
</td>
    </tr>

    <tr>
      <td>Middle Name</td>
      <td><input type="text" name="middleName" value="<?= old('middleName') ?>">
</td>
    </tr>

    <tr>
      <td>Last Name*</td>
      <td><input type="text" name="lastName" value="<?= old('lastName') ?>" required>
</td>
    </tr>

    <tr>
      <td>Date Of Birth*</td>
      <td><input type="date" name="dateOfBirth" value="<?= old('dateOfBirth') ?>" required>
</td>
    </tr>

    <tr>
      <td>Email*</td>
      <td><input type="email" name="email" value="<?= old('email') ?>" required>
</td>
    </tr>

    <tr>
      <td>Telephone*</td>
      <td><input type="text" name="telephone" value="<?= old('telephone') ?>" required>
</td>
    </tr>

    <tr>
      <td>Country*</td>
      <td><input type="text" name="country" value="<?= old('country') ?>" required></td>
    </tr>

    <tr>
      <td>City*</td>
      <td><input type="text" name="city" value="<?= old('city') ?>" required></td>
    </tr>

    <tr>
      <td>Street*</td>
      <td><input type="text" name="street" value="<?= old('street') ?>" required></td>
    </tr>

    <tr>
      <td>Postcode*</td>
      <td><input type="text" name="postcode" value="<?= old('postcode') ?>" required></td>
    </tr>
  </table>

  <button type="submit" name="save" value="1">Save</button>
  <button type="button" onclick="location.href='index.php'">Cancel</button>
</form>