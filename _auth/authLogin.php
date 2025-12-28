<?php
// include at the top of any page that requires a login
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect if not logged in, but this cant be applied to log in page, so this same file exists in authLog.php but without this If statement

// List of public pages
$publicPages = [
    'index.php',
    'signup1.php',
    'login.php'
    
];

// Get current script name
$currentPage = basename($_SERVER['PHP_SELF']);
// include user below


// If page is NOT public, require login
if (!in_array($currentPage, $publicPages)) {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit;
    }
}

?>
<script>
    // This event fires when the page is shown, including from the back/forward cache
    window.addEventListener('pageshow', function(event) {
        // If persisted is true, the page was loaded from the cache
        if (event.persisted) {
            window.location.reload(); 
        }
    });
</script>