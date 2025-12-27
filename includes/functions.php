<?php
session_start();
require_once __DIR__ . '/../config/dbConnect.php';

//Input functions
function h($s) 
{
    return htmlspecialchars($s);

}

function phoneNumber($s)
{
  return !preg_match('/^[0-9+\-\s]+$/', $s);
}





?>
<head>
<link rel="stylesheet" href="style.css">
</head>

</html>
