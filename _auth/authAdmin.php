
<?php

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin']) || !isset($_SESSION['adminID'])) {
    header("Location: login.php");
    exit;
}