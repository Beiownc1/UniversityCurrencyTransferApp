<?php
session_start();
require_once __DIR__ . '/../Config/DBConnect.php';
require_once __DIR__ . '/../Includes/Header.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}



// XSS attack check
function h($s) 
{
    return htmlspecialchars($s);

}

// checks if phonenumber field is only numbers / manually should add the +44
function phoneNumber($s)
{
  return !preg_match('/^[0-9+\-\s]+$/', $s);
}

// alphabetic check
function validateName(string $value): bool {
    return preg_match('/^[A-Za-z]+( [A-Za-z]+)*$/', $value) === 1;
}

// keeps old input data on error thrown
function old(string $key): string {
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key], ENT_QUOTES, 'UTF-8') : '';
}

// null field check
function validateRequiredFields(array $fields): bool {
    foreach ($fields as $value) {
        if ($value === '') {
            return false;
        }
    }
    return true;
}



?>
