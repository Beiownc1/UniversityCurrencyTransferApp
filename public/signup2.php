<?php
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['signup']['firstName']) || empty($_SESSION['signup']['lastName'])) {
  header("Location: signup1.php");
  exit;
}

$countries = [
  "GB" => "United Kingdom",
  "FR" => "France",
  "US" => "United States",
  "DE" => "Germany",
  "ES" => "Spain",
  "IT" => "Italy",
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = (string) ($_POST['password'] ?? '');
  $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $confirmEmail = trim($_POST['confirmEmail'] ?? '');
  $phoneCountry = trim($_POST['phoneCountry'] ?? '');
  $phoneLocal = trim($_POST['phoneLocal'] ?? '');

  if ($username === '' || $password === '' || $confirmPassword === '' || $email === '' || $confirmEmail === '' || $phoneCountry === '' || $phoneLocal === '') {
    $errors[] = "Please fill in all required fields.";
  }

  if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
  }

  if (strcasecmp($email, $confirmEmail) !== 0) {
    $errors[] = "Emails do not match.";
  }

  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  }

  if ($phoneCountry !== '' && !array_key_exists($phoneCountry, $callingCodes)) {
    $errors[] = "Invalid phone country selection.";
  }

  $phoneLocalClean = preg_replace('/[^0-9]/', '', $phoneLocal);
  if ($phoneLocal !== '' && $phoneLocalClean === '') {
    $errors[] = "Phone number must contain digits.";
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
    $stmt->execute([$username]);
    if ((int) $stmt->fetchColumn() > 0)
      $errors[] = "Username unavailable.";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ((int) $stmt->fetchColumn() > 0)
      $errors[] = "Email unavailable.";
  }

  if (empty($errors)) {
    $_SESSION['signup'] = array_merge($signup, [
      'username' => $username,
      'password' => $password,
      'email' => $email,
      'phoneCountry' => $phoneCountry,
      'phoneLocal' => $phoneLocalClean,
    ]);
    header("Location: signup3.php");
    exit;
  }
}

$username = $signup['username'] ?? '';
$email = $signup['email'] ?? '';
$phoneCountry = $signup['phoneCountry'] ?? '';
$phoneLocal = $signup['phoneLocal'] ?? '';
?>

<h1>Sign Up — Step 2 of 3</h1>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <?php foreach ($errors as $e): ?>
      <p><?= h($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post">
  <label>Username*</label><br>
  <input name="username" required value="<?= h($username) ?>"><br><br>

  <label>Password*</label><br>
  <input type="password" name="password" required><br><br>

  <label>Confirm password*</label><br>
  <input type="password" name="confirmPassword" required><br><br>

  <label>Email*</label><br>
  <input type="email" name="email" required value="<?= h($email) ?>"><br><br>

  <label>Confirm email*</label><br>
  <input type="email" name="confirmEmail" required value="<?= h($email) ?>"><br><br>

  <label>Phone country*</label><br>
  <select name="phoneCountry" required>
    <option value="">Select…</option>
    <?php foreach ($callingCodes as $code => $dial): ?>
      <option value="<?= h($code) ?>" <?= $phoneCountry === $code ? 'selected' : '' ?>>
        <?= h($countries[$code] ?? $code) ?> (<?= h($dial) ?>)
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <label>Phone number*</label><br>
  <input name="phoneLocal" required value="<?= h($phoneLocal) ?>" placeholder="digits only"><br><br>

  <button type="button" onclick="location.href='signup1.php'">Back</button>
  <button type="submit">Next</button>
  <button type="button" onclick="location.href='signupCancel.php'">Cancel</button>
</form>