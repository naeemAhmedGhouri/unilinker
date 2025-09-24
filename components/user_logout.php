<?php

   include 'connect.php';

   // Clear server-side session
   $_SESSION = array();
   if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
   }
   session_destroy();

   // Clear client-side remember cookies
   setcookie('user_id', '', time() - 3600, '/');
   setcookie('remember_me', '', time() - 3600, '/');

   header('location:../login1.php');
   exit();

?>