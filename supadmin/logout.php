<?php
session_start();

// Destroy all session data
session_destroy();

// Clear any cookies if they exist
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Clear any other authentication cookies you might have
// For example, if you have a "remember_me" cookie:
// if (isset($_COOKIE['remember_me'])) {
//     setcookie('remember_me', '', time()-42000, '/');
// }

// Redirect to login page or home page
header("Location: ../login1.php"); // Adjust path as needed
exit();
?>