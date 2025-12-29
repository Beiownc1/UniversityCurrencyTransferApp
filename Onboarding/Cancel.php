<?php
require_once __DIR__ . '/../Includes/Functions.php';
unset($_SESSION['signup']);
header("Location: /currencyTransferApp/Public/Index.php");
exit;
