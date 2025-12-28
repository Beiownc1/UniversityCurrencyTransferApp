<?php
session_start();
session_unset();
session_destroy();
header("Location: /currencyTransferApp/public/index.php");
exit;

?>
