<?php
session_start();
session_unset();
session_destroy();
session_cache_expire();
header("Location: index.php");
exit;

?>
