<?php 
date_default_timezone_set('Asia/Karachi');
include 'components/connect.php';
require 'vendor/autoload.php';

// Set MySQL timezone to match PHP timezone
$conn->exec("SET time_zone = '+05:00'");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = [];

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);

   // Check if email exists in users or tutors table
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
   $select_user->execute([$email]);

   $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ? LIMIT 1");
   $select_tutor->execute([$email]);

   if($select_user->rowCount() > 0 || $select_tutor->rowCount() > 0){

      // Generate reset token
      $reset_token = bin2hex(random_bytes(32));
      
      // Create expiration time (1 hour from now) in the correct timezone
      $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
      
      // Debug information
      error_log("Current time: " . date('Y-m-d H:i:s'));
      error_log("Expires at: " . $expires_at);
      error_log("Timezone: " . date_default_timezone_get());

      // Insert or update token in database
      $check_existing = $conn->prepare("SELECT * FROM `password_resets` WHERE email = ?");
      $check_existing->execute([$email]);

      if($check_existing->rowCount() > 0){
         $update_token = $conn->prepare("UPDATE `password_resets` SET token = ?, expires_at = ?, created_at = NOW() WHERE email = ?");
         $token_saved = $update_token->execute([$reset_token, $expires_at, $email]);
         error_log("Token updated in database: " . ($token_saved ? 'SUCCESS' : 'FAILED'));
      } else {
         $insert_token = $conn->prepare("INSERT INTO `password_resets` (email, token, expires_at) VALUES (?, ?, ?)");
         $token_saved = $insert_token->execute([$email, $reset_token, $expires_at]);
         error_log("Token inserted in database: " . ($token_saved ? 'SUCCESS' : 'FAILED'));
      }
      
      // Verify the token was actually saved
      $verify_save = $conn->prepare("SELECT * FROM `password_resets` WHERE email = ? ORDER BY created_at DESC LIMIT 1");
      $verify_save->execute([$email]);
      if($verify_save->rowCount() > 0) {
         $saved_data = $verify_save->fetch(PDO::FETCH_ASSOC);
         error_log("Verified token in DB: " . $saved_data['token']);
         error_log("Verified expires_at in DB: " . $saved_data['expires_at']);
      } else {
         error_log("ERROR: No token found in database after save attempt");
      }

      // Use your actual domain instead of localhost in production
      $reset_link = "http://localhost/nighteen_aug/reset_password.php?token=" . $reset_token;
      
      // Debug: Log the generated link
      error_log("Generated reset link: " . $reset_link);
      error_log("Token length: " . strlen($reset_token));

      // Send email using PHPMailer
      $mail = new PHPMailer(true);

      try {
          // Enable verbose debug output for troubleshooting
          $mail->SMTPDebug = SMTP::DEBUG_SERVER;

          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com';
          $mail->SMTPAuth   = true;
          $mail->Username   = 'bisma.sarwar.malik@gmail.com';       // <-- your Gmail
          $mail->Password   = 'mqee gbit tphv akks';         // <-- Gmail App Password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port       = 587;

          $mail->setFrom('bisma.sarwar.malik@gmail.com', 'UniLinker app');
          $mail->addAddress($email);

          $mail->isHTML(true);
          $mail->Subject = 'Password Reset - UniLinker';
          $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Password Reset Request</h2>
                <p>You have requested to reset your password for UniLinker.</p>
                <p>Click the button below to reset your password:</p>
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='$reset_link' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </div>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 5px;'>$reset_link</p>
                <p><strong>Important:</strong> This link will expire in 1 hour for security reasons.</p>
                <p>If you didn't request this password reset, you can safely ignore this email.</p>
            </div>
          ";

          $mail->send();
          $message[] = 'A password reset link has been sent to your email address. Please check your inbox and spam folder.';

      } catch (Exception $e) {
          $message[] = 'Failed to send reset email. Please try again later.';
          // Log the error for debugging (remove in production)
          error_log('Mail Error: ' . $mail->ErrorInfo);
      }

   } else {
      $message[] = 'Email address not found in our records.';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - UniLinker</title>
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
        
        .back-to-login {
            margin-top: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="images/logo2.png" alt="uniLinker" class="circular-image">
    
    <h2 style="text-align: center; margin-bottom: 10px;">Forgot Password</h2>
    <p class="subtitle">Enter your email address and we'll send you a link to reset your password.</p>
    
    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            // Check if it's a success message
            if (strpos($msg, 'sent') !== false) {
                echo '<div class="message-success">' . $msg . '</div>';
            } else {
                echo '<p style="color: red; text-align: center; background: rgba(244, 67, 54, 0.1); padding: 10px; border-radius: 5px; border: 1px solid rgba(244, 67, 54, 0.3);">' . $msg . '</p>';
            }
        }
    }
    ?>
    
    <form action="" method="post">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter your email address" maxlength="50" required>
        
        <button type="submit" name="submit" style="color: var(--background);">Send Reset Link</button>
    </form>
    
    <div class="extra-links">
        <div class="back-to-login">
            <p>Remember your password? <a href="login1.php">Back to Login</a></p>
        </div>
    </div>
</div>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>