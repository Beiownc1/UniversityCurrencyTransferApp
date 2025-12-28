<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['signup']);
header("Location: index.php");
exit;
