<?php

$pdo = new PDO('sqlite:' . dirname(__DIR__) . '/Zdatabase/database.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON'); // enforce foreign keys
?>
