
<?php

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin']) || !isset($_SESSION['adminID'])) {
    header("Location: /currencyTransferApp/Public/login.php");
    exit;
}