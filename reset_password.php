<?php
date_default_timezone_set('Asia/Karachi');
include 'components/connect.php';

// Set MySQL timezone to match PHP timezone
$conn->exec("SET time_zone = '+05:00'");

$message = [];
$valid_token = false;
$token = '';

// Debug: Check what's being received
error_log("GET parameters: " . print_r($_GET, true));

// Check if token is provided
if(isset($_GET['token']) && !empty($_GET['token'])){
   $token = $_GET['token'];
   // Don't filter the token - it should be exactly as generated
   // $token = filter_var($token, FILTER_SANITIZE_STRING); // This can modify the token!
   
   // Debug: Show token info
   error_log("Received raw token: " . $token);
   error_log("Token length: " . strlen($token));
   
   // First, let's create the password_resets table if it doesn't exist
   $create_table = $conn->prepare("
      CREATE TABLE IF NOT EXISTS `password_resets` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `email` varchar(255) NOT NULL,
         `token` varchar(255) NOT NULL,
         `expires_at` datetime NOT NULL,
         `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
         PRIMARY KEY (`id`),
         UNIQUE KEY `email` (`email`),
         KEY `token` (`token`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
   ");
   $create_table->execute();
   
   // Get current datetime in the correct timezone
   $current_time = date('Y-m-d H:i:s');
   
   // Verify token exists first
   $select_token = $conn->prepare("SELECT * FROM `password_resets` WHERE token = ? LIMIT 1");
   $select_token->execute([$token]);
   
   if($select_token->rowCount() > 0){
      $token_data = $select_token->fetch(PDO::FETCH_ASSOC);
      
      // Debug information (remove in production)
      error_log("Token expires at: " . $token_data['expires_at']);
      error_log("Current time: " . $current_time);
      error_log("Comparison: " . ($token_data['expires_at'] > $current_time ? 'Valid' : 'Expired'));
      
      // Check if token is still valid
      if($token_data['expires_at'] > $current_time) {
         $valid_token = true;
      } else {
         $message[] = 'Reset token has expired. Please request a new password reset.';
      }
   } else {
      // Debug: Show what tokens exist in the database
      $debug_tokens = $conn->prepare("SELECT token, expires_at, email FROM `password_resets` ORDER BY created_at DESC LIMIT 5");
      $debug_tokens->execute();
      error_log("Tokens in database:");
      while($debug_row = $debug_tokens->fetch(PDO::FETCH_ASSOC)) {
         error_log("DB Token: " . substr($debug_row['token'], 0, 10) . "... | Email: " . $debug_row['email'] . " | Expires: " . $debug_row['expires_at']);
      }
      error_log("Looking for token: " . substr($token, 0, 10) . "...");
      
      $message[] = 'Invalid reset token. Please check the link or request a new password reset.';
   }
} else {
   $message[] = 'Invalid reset link.';
}

// Handle password reset form submission
if(isset($_POST['submit']) && $valid_token){
   
   $new_password = $_POST['new_password'];
   $new_password = filter_var($new_password, FILTER_SANITIZE_STRING);
   $confirm_password = $_POST['confirm_password'];
   $confirm_password = filter_var($confirm_password, FILTER_SANITIZE_STRING);
   
   // Validate password match
   if($new_password !== $confirm_password){
      $message[] = 'Passwords do not match.';
   } else if(strlen($new_password) < 6){
      $message[] = 'Password must be at least 6 characters long.';
   } else {
      
      // Hash the new password
      $hashed_password = sha1($new_password);
      
      // Get email from token
      $get_email = $conn->prepare("SELECT email FROM `password_resets` WHERE token = ? LIMIT 1");
      $get_email->execute([$token]);
      $reset_data = $get_email->fetch(PDO::FETCH_ASSOC);
      $email = $reset_data['email'];
      
      // Update password in users table
      $update_user = $conn->prepare("UPDATE `users` SET password = ? WHERE email = ?");
      $user_updated = $update_user->execute([$hashed_password, $email]);
      
      // Update password in tutors table
      $update_tutor = $conn->prepare("UPDATE `tutors` SET password = ? WHERE email = ?");
      $tutor_updated = $update_tutor->execute([$hashed_password, $email]);
      
      if($user_updated || $tutor_updated){
         // Delete the used token
         $delete_token = $conn->prepare("DELETE FROM `password_resets` WHERE token = ?");
         $delete_token->execute([$token]);
         
         $message[] = 'Password updated successfully! You can now login with your new password.';
         $valid_token = false; // Hide the form
      } else {
         $message[] = 'Failed to update password. Please try again.';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - UniLinker</title>
    <link rel="stylesheet" href="css/login1.css">
    <style>
        .message-success {
            color: green !important;
            background: rgba(76, 175, 80, 0.1);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid rgba(76, 175, 80, 0.3);
            margin-bottom: 15px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.4;
            text-align: center;
        }
        
        .password-requirements {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
            text-align: left;
        }
        
        .password-requirements ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .back-to-login {
            margin-top: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            text-align: center;
        }

        .debug-info {
            background: rgba(255, 255, 0, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
            border: 1px solid rgba(255, 255, 0, 0.3);
        }
    </style>
</head>
<body>
<div class="container">
    <img src="images/logo2.png" alt="uniLinker" class="circular-image">
    
    <h2 style="text-align: center; margin-bottom: 10px;">Reset Password</h2>
    
    <?php
    // Temporary debug info (remove in production)
    if(isset($_GET['debug'])) {
        echo '<div class="debug-info">';
        echo '<strong>Debug Information:</strong><br>';
        echo 'Token from URL: ' . (isset($_GET['token']) ? htmlspecialchars($_GET['token']) : 'NOT SET') . '<br>';
        echo 'Token length: ' . (isset($_GET['token']) ? strlen($_GET['token']) : 0) . '<br>';
        echo 'Current time: ' . date('Y-m-d H:i:s') . '<br>';
        echo 'Valid token: ' . ($valid_token ? 'YES' : 'NO') . '<br>';
        echo '</div>';
    }
    
    if (!empty($message)) {
        foreach ($message as $msg) {
            // Check if it's a success message
            if (strpos($msg, 'successfully') !== false) {
                echo '<div class="message-success">' . $msg . '</div>';
            } else {
                echo '<p style="color: red; text-align: center; background: rgba(244, 67, 54, 0.1); padding: 10px; border-radius: 5px; border: 1px solid rgba(244, 67, 54, 0.3);">' . $msg . '</p>';
            }
        }
    }
    ?>
    
    <?php if($valid_token): ?>
        <p class="subtitle">Enter your new password below.</p>
        
        <div class="password-requirements">
            <strong>Password Requirements:</strong>
            <ul>
                <li>At least 6 characters long</li>
                <li>Must match confirmation</li>
            </ul>
        </div>
        
        <form action="" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
            
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password" minlength="6" required>
            
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm new password" minlength="6" required>
            
            <button type="submit" name="submit" style="color: var(--background);">Update Password</button>
        </form>
    <?php endif; ?>
    
    <div class="extra-links">
        <div class="back-to-login">
            <p><a href="login1.php">Back to Login</a></p>
        </div>
    </div>
</div>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>