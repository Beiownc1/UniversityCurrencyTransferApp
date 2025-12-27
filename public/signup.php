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
        $username === '' || $hashPassword === '' || $firstName === '' ||
        $lastName === '' || $dateOfBirth === '' || $email === '' ||
        $telephone === '' || $country === '' || $city === '' ||
        $street === '' || $postcode === ''
    ) {
        $errorMessage[] = "All fields must be filled";
    }

    if ($firstName !== '' && !ctype_alpha($firstName)) {
        $errorMessage[] = "Names can only contain letters";
    }

    if ($middleName !== '' && !ctype_alpha($middleName)) {
        $errorMessage[] = "Names can only contain letters";
    }

    if ($lastName !== '' && !ctype_alpha($lastName)) {
        $errorMessage[] = "Names can only contain letters";
    }

    if ($dateOfBirth !== '' && $dateOfBirth > date('Y-m-d')) {
        $errorMessage[] = "Date of birth cannot be in the future";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage[] = "Enter a valid email address";
    }

    if ($telephone !== '' && phoneNumber($telephone)) {
        $errorMessage[] = "Phone number can only contain numbers, spaces, '+' or '-'";
    }

    if (empty($errorMessage)) {
        $dup = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
        $dup->execute([$email]);
        if ($dup->fetchColumn() > 0) {
            $errorMessage[] = "Email unavailable";
        }

        $dup = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
        $dup->execute([$username]);
        if ($dup->fetchColumn() > 0) {
            $errorMessage[] = "Username unavailable";
        }
    }

    if (empty($errorMessage)) {
        try {
            $pdo->beginTransaction();

            $hashPassword = password_hash($hashPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO user
                (username, hashPassword, firstName, middleName, lastName, dateOfBirth, email, telephone, country, city, street, postcode, userStatus)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");

            $stmt->execute([
                $username,
                $hashPassword,
                $firstName,
                $middleName !== '' ? $middleName : NULL,
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
            $errorMessage[] = $e->getMessage();
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
    <td>Username*</td>
    <td><input type="text" name="username" value="<?= h($username) ?>" required></td>
</tr>

<tr>
    <td>Password*</td>
    <td><input type="password" name="hashPassword" required></td>
</tr>

<tr>
    <td>First Name*</td>
    <td><input type="text" name="firstName" value="<?= h($firstName) ?>" required></td>
</tr>

<tr>
    <td>Middle Name</td>
    <td><input type="text" name="middleName" value="<?= h($middleName) ?>"></td>
</tr>

<tr>
    <td>Last Name*</td>
    <td><input type="text" name="lastName" value="<?= h($lastName) ?>" required></td>
</tr>

<tr>
    <td>Date Of Birth*</td>
    <td><input type="date" name="dateOfBirth" value="<?= h($dateOfBirth) ?>" max="<?= date('Y-m-d') ?>" required></td>
</tr>

<tr>
    <td>Email*</td>
    <td><input type="email" name="email" value="<?= h($email) ?>" required></td>
</tr>

<tr>
    <td>Telephone*</td>
    <td><input type="text" name="telephone" value="<?= h($telephone) ?>" required></td>
</tr>

<tr>
    <td>Country*</td>
    <td><input type="text" name="country" value="<?= h($country) ?>" required></td>
</tr>

<tr>
    <td>City*</td>
    <td><input type="text" name="city" value="<?= h($city) ?>" required></td>
</tr>

<tr>
    <td>Street*</td>
    <td><input type="text" name="street" value="<?= h($street) ?>" required></td>
</tr>

<tr>
    <td>Postcode*</td>
    <td><input type="text" name="postcode" value="<?= h($postcode) ?>" required></td>
</tr>
</table>

<button type="submit" name="save" value="1">Save</button>
<button type="button" onclick="location.href='index.php'">Cancel</button>
</form>
