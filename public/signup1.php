<?php
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$signup = $_SESSION['signup'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstName = trim($_POST['firstName'] ?? '');
  $middleName = trim($_POST['middleName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');

  if ($firstName === '' || $lastName === '' || $dateOfBirth === '') {
    $errors[] = "Please fill in all required fields.";
  }

  if ($dateOfBirth !== '' && $dateOfBirth > date('Y-m-d')) {
    $errors[] = "Date of birth cannot be in the future.";
  }

  if (empty($errors)) {
    $_SESSION['signup'] = array_merge($signup, [
      'firstName' => $firstName,
      'middleName' => $middleName,
      'lastName' => $lastName,
      'dateOfBirth' => $dateOfBirth,
    ]);
    header("Location: signup2.php");
    exit;
  }
}

$firstName = $signup['firstName'] ?? '';
$middleName = $signup['middleName'] ?? '';
$lastName = $signup['lastName'] ?? '';
$dateOfBirth = $signup['dateOfBirth'] ?? '';
?>

<h1>Sign Up — Step 1 of 3</h1>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <?php foreach ($errors as $e): ?>
      <p><?= h($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post">
  <label>First name*</label><br>
  <input name="firstName" required value="<?= h($firstName) ?>"><br><br>

  <label>Middle name (optional)</label><br>
  <input name="middleName" value="<?= h($middleName) ?>"><br><br>

  <label>Last name*</label><br>
  <input name="lastName" required value="<?= h($lastName) ?>"><br><br>

  <label>Date of birth*</label><br>
  <input type="date" name="dateOfBirth" required max="<?= date('Y-m-d') ?>" value="<?= h($dateOfBirth) ?>"><br><br>

  <button type="submit">Next</button>
  <button type="button" onclick="location.href='signupCancel.php'">Cancel</button>
</form>