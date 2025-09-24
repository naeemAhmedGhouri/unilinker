<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   header('location:home.php');
   exit();
}elseif(isset($_COOKIE['tutor_id'])){
   header('location:admin/dashboard.php');
   exit();
}

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   // check student
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
   $select_user->execute([$email, $pass]);
   $row = $select_user->fetch(PDO::FETCH_ASSOC);
   
   if($select_user->rowCount() > 0){
      setcookie('user_id', $row['id'], 0, '/'); // session cookie
      header('location:home.php');
   }else{
      // check tutor
      $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ? AND password = ? AND status = 'Approved' LIMIT 1");
      $select_tutor->execute([$email, $pass]);
      $row = $select_tutor->fetch(PDO::FETCH_ASSOC);
      
      if($select_tutor->rowCount() > 0){
         setcookie('tutor_id', $row['id'], 0, '/'); // session cookie
         header('location:admin/dashboard.php');
      }else{
         // super admin check
         if($email=='admin@gmail.com' && $_POST['pass']=='1234'){
            header('location:supadmin/home1.php');
         } else {
            $message[] = 'Incorrect email or password!';
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login - UniLinker</title>
   <link rel="stylesheet" href="css/login1.css">
</head>
<body>
<div class="container">
   <img src="images/logo2.png" alt="uniLinker" class="circular-image">

   <?php
   if (isset($message)) {
      foreach ($message as $msg) {
         echo '<p style="color: red; text-align: center;">' . $msg . '</p>';
      }
   }
   ?>

   <form action="" method="post" enctype="multipart/form-data">
      <label>Email</label>
      <input type="email" name="email" placeholder="Enter your email" maxlength="50" required>

      <label>Password</label>
      <input type="password" name="pass" id="password" placeholder="Password" required>

      <div class="show-pass" style="justify-content:left; display: flex;  gap: 15px; ">
      <label for="showPass">Show Password</label>
         <input type="checkbox" id="showPass" onclick="togglePassword()" style="width: 15px;margin: 0; border: none;">

      </div>

      <button type="submit" name="submit" style="color: var(--background);">Login</button>
   </form>

   <div class="extra-links">
      <p><a href="forget_password.php">Forgot Password?</a></p>
      <p>Don't have an account? <a href="javascript:void(0);" onclick="openModal()">Create Account</a></p>
   </div>
</div>

<!-- Modal for Registration Options -->
<div class="modal" id="regModal">
   <div class="modal-content">
      <h2>Choose Registration Type</h2>
      <a href="register.php" id="studentReg" style="color: var(--background);">Student Registration</a>
      <a href="admin/register.php" id="teacherReg" style="color: var(--background);">Teacher Registration</a>
      <button class="btn1" onclick="closeModal()">Cancel</button>
   </div>
</div>

<script>
   // Open Modal
   function openModal() {
      document.getElementById('regModal').style.display = 'flex';
   }

   // Close Modal
   function closeModal() {
      document.getElementById('regModal').style.display = 'none';
   }

   // Show / Hide Password
   function togglePassword() {
      const passField = document.getElementById("password");
      if (passField.type === "password") {
         passField.type = "text";
      } else {
         passField.type = "password";
      }
   }
</script>

</body>
</html>
