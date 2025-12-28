<?php
require_once __DIR__ . '/../includes/functions.php';

$errorMessage = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $hashPassword = (string)($_POST['hashPassword'] ?? '');

    if ($username === '' || $hashPassword === '') {
        $errorMessage[] = "Username and password are required";
    } else {

        $stmt = $pdo->prepare("
            SELECT userID, username, hashPassword
            FROM user
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($hashPassword, $user['hashPassword'])) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['userID'] = (int)$user['userID'];
            unset($_SESSION['admin'], $_SESSION['adminID']);

            header("Location: ../private/user_dashboard.php");
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT adminID, username, hashPassword
            FROM admin
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($hashPassword, $admin['hashPassword'])) {
            $_SESSION['admin'] = $admin['username'];
            $_SESSION['adminID'] = (int)$admin['adminID'];
            unset($_SESSION['user'], $_SESSION['userID']);

            header("Location: ../private/admin_dashboard.php");
            exit;
        }

        $errorMessage[] = "Invalid username or password";
    }
}
?>


<h1>Login</h1>

<?php if (!empty($errorMessage)): ?>
    <p style="color:red; font-weight:bold;">
        <?= h($errorMessage[0]) ?>
    </p>
<?php endif; ?>

<form method="post">
    <table>
        <tr>
            <td>Username*</td>
            <td>
                <input type="text" name="username"
                       placeholder="Username" required>
            </td>
        </tr>

        <tr>
            <td>Password*</td>
            <td>
                <input type="password" name="hashPassword"
                       placeholder="Password" required>
            </td>
        </tr>
    </table>

    <button type="submit">Login</button>
    <button type="button" onclick="location.href='index.php'">Cancel</button>
</form>

<br>
<div>
    <a href="forgotPassword.php">Can’t login?</a>
</div>
