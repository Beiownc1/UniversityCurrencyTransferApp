<?php

$pdo = new PDO('sqlite:' . dirname(__DIR__) . '/database/database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON'); // enforce foreign keys


//helper
function h($s) 
{
    return htmlspecialchars($s);

}

function stupidPhoneNumberRegexSyntax($s)
{
  return !preg_match('/^[0-9+\-\s]+$/', $s);
}
?>
