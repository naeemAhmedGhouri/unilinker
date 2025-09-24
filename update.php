<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
   exit();
}

// Initialize message array to prevent foreach error
$message = [];

// Helper function to add messages safely
function addMessage(&$messageArray, $msg) {
   if(!is_array($messageArray)) {
      $messageArray = [];
   }
   $messageArray[] = $msg;
}

// --- user data pehle hi fetch kar lo (har jagah use hoga)
$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   if($fetch_user){
      $prev_pass = $fetch_user['password'];
      $prev_image = $fetch_user['image'];
   }else{
      $prev_pass = '';
      $prev_image = '';
   }

   // Update Full Name - only if different from current value
   $full_name = $_POST['full_name'] ?? '';
   $full_name = filter_var($full_name, FILTER_SANITIZE_STRING);
   if(!empty($full_name) && (!isset($fetch_user['full_name']) || $full_name != $fetch_user['full_name'])){
      $update_full_name = $conn->prepare("UPDATE `users` SET full_name = ? WHERE id = ?");
      $update_full_name->execute([$full_name, $user_id]);
      // $message[] = 'Full name updated successfully!';
   }

   // Update Gender - only if different from current value
   $gender = $_POST['gender'] ?? '';
   if(!empty($gender) && in_array($gender, ['Male', 'Female']) && (!isset($fetch_user['gender']) || $gender != $fetch_user['gender'])){
      $update_gender = $conn->prepare("UPDATE `users` SET gender = ? WHERE id = ?");
      $update_gender->execute([$gender, $user_id]);
      $message[] = 'Gender updated successfully!';
   }


   // Update Email - only if different from current value
   $email = $_POST['email'] ?? '';
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   if(!empty($email) && (!isset($fetch_user['email']) || $email != $fetch_user['email'])){
      // Check if email already exists (excluding current user)
      $select_email = $conn->prepare("SELECT email FROM `users` WHERE email = ? AND id != ?");
      $select_email->execute([$email, $user_id]);
      if($select_email->rowCount() > 0){
         $message[] = 'Email already taken!';
      }else{
         $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id = ?");
         $update_email->execute([$email, $user_id]);
         $message[] = 'Email updated successfully!';
      }
   }

   // Update Batch & Roll No - only if different from current value
   $batch_roll_no = $_POST['batch_roll_no'] ?? '';
   $batch_roll_no = filter_var($batch_roll_no, FILTER_SANITIZE_STRING);
   if(!empty($batch_roll_no) && (!isset($fetch_user['batch_roll_no']) || $batch_roll_no != $fetch_user['batch_roll_no'])){
      $update_batch = $conn->prepare("UPDATE `users` SET batch_roll_no = ? WHERE id = ?");
      $update_batch->execute([$batch_roll_no, $user_id]);
      $message[] = 'Batch & Roll No updated successfully!';
   }

   // Update University - only if different from current value
   $university = $_POST['university'] ?? '';
   if(!empty($university) && in_array($university, ['Quest', 'SBBU', 'Mehran']) && (!isset($fetch_user['university']) || $university != $fetch_user['university'])){
      $update_university = $conn->prepare("UPDATE `users` SET university = ? WHERE id = ?");
      $update_university->execute([$university, $user_id]);
      $message[] = 'University updated successfully!';
   }

   // Update Program - only if different from current value
   $program = $_POST['program'] ?? '';
   if(!empty($program) && in_array($program, ['BS', 'BE']) && (!isset($fetch_user['program']) || $program != $fetch_user['program'])){
      $update_program = $conn->prepare("UPDATE `users` SET program = ? WHERE id = ?");
      $update_program->execute([$program, $user_id]);
      $message[] = 'Program updated successfully!';
   }

   // Update Department - only if different from current value
   $department = $_POST['department'] ?? '';
   if(!empty($department) && (!isset($fetch_user['department']) || $department != $fetch_user['department'])){
      $valid_departments = [
         'Information Technology', 'Computer Science', 'Data Science', 
         'Cyber Security', 'Artificial Intelligence', 'Mathematics',
         'Software Engineering', 'Electrical Engineering', 
         'Mechanical Engineering', 'Civil Engineering'
      ];
      if(in_array($department, $valid_departments)){
         $update_department = $conn->prepare("UPDATE `users` SET department = ? WHERE id = ?");
         $update_department->execute([$department, $user_id]);
         $message[] = 'Department updated successfully!';
      }
   }

   // Update Image
   if(!empty($_FILES['image']['name'])){
      $image = $_FILES['image']['name'];
      $image = filter_var($image, FILTER_SANITIZE_STRING);
      $ext = pathinfo($image, PATHINFO_EXTENSION);
      $rename = uniqid().'.'.$ext;
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_files/'.$rename;

      if($image_size > 2000000){
         $message[] = 'Image size too large!';
      }else{
         $update_image = $conn->prepare("UPDATE `users` SET `image` = ? WHERE id = ?");
         $update_image->execute([$rename, $user_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         if($prev_image != '' && $prev_image != $rename && file_exists('uploaded_files/'.$prev_image)){
            unlink('uploaded_files/'.$prev_image);
         }
         $message[] = 'Image updated successfully!';
      }
   }

   // Update Password
   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $old_pass = sha1($_POST['old_pass'] ?? '');
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass'] ?? '');
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] ?? '');
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   if($old_pass != $empty_pass){
      if($old_pass != $prev_pass){
         $message[] = 'Old password not matched!';
      }elseif($new_pass != $cpass){
         $message[] = 'Confirm password not matched!';
      }else{
         if($new_pass != $empty_pass){
            $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $update_pass->execute([$cpass, $user_id]);
            $message[] = 'Password updated successfully!';
         }else{
            $message[] = 'Please enter a new password!';
         }
      }
   }

   // Re-fetch user data to show updated values
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
   $select_user->execute([$user_id]);
   $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   .gender-box, .university-box {
      margin: 1rem 0;
   }
   .gender-box h3, .university-box h3 {
      margin-bottom: 0.5rem;
      color: var(--black);
      font-size: 1.8rem;
   }
   .gender-option, .university-option {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
   }
   .gender-option label, .university-option label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
      font-size: 1.6rem;
   }
   .form-container .flex {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
      gap: 2rem;
   }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container" style="min-height: calc(100vh - 19rem);">

   <?php if(!empty($message)): ?>
      <div class="message">
         <?php 
         // Ensure $message is always an array
         if(is_array($message)) {
            foreach($message as $msg): ?>
               <span><?= htmlspecialchars($msg); ?></span>
            <?php endforeach; 
         } else {
            // If $message is a string, display it directly
            echo '<span>' . htmlspecialchars($message) . '</span>';
         }
         ?>
      </div>
   <?php endif; ?>

   <form action="" method="post" enctype="multipart/form-data">
      <h3>Update Profile</h3>
      <div class="flex">
         <div class="col">
            <p>Full Name</p>
            <input type="text" name="full_name" value="<?= isset($fetch_user['full_name']) ? htmlspecialchars($fetch_user['full_name']) : ''; ?>" maxlength="100" class="box">
            
            <div class="gender-box">
               <h3>Gender</h3>
               <div class="gender-option">
                  <label>
                     <input type="radio" name="gender" value="Male" 
                     <?= (isset($fetch_user['gender']) && $fetch_user['gender'] == 'Male') ? 'checked' : ''; ?>>
                     Male
                  </label>
                  <label>
                     <input type="radio" name="gender" value="Female"
                     <?= (isset($fetch_user['gender']) && $fetch_user['gender'] == 'Female') ? 'checked' : ''; ?>>
                     Female
                  </label>
               </div>
            </div>

            
            <p>Email</p>
            <input type="email" name="email" value="<?= isset($fetch_user['email']) ? htmlspecialchars($fetch_user['email']) : ''; ?>" maxlength="100" class="box">
            
            <p>Batch & Roll No</p>
            <input type="text" name="batch_roll_no" value="<?= isset($fetch_user['batch_roll_no']) ? htmlspecialchars($fetch_user['batch_roll_no']) : ''; ?>" maxlength="20" class="box" placeholder="e.g., 45BCS32">
            
            <p>Update Profile Picture</p>
            <input type="file" name="image" accept="image/*" class="box">
         </div>
         
         <div class="col">
            <div class="university-box">
               <h3>University</h3>
               <div class="university-option">
                  <label>
                     <input type="radio" name="university" value="Quest" 
                     <?= (isset($fetch_user['university']) && $fetch_user['university'] == 'Quest') ? 'checked' : ''; ?>>
                     Quest
                  </label>
                  <label>
                     <input type="radio" name="university" value="SBBU"
                     <?= (isset($fetch_user['university']) && $fetch_user['university'] == 'SBBU') ? 'checked' : ''; ?>>
                     SBBU
                  </label>
                  <label>
                     <input type="radio" name="university" value="Mehran"
                     <?= (isset($fetch_user['university']) && $fetch_user['university'] == 'Mehran') ? 'checked' : ''; ?>>
                     Mehran
                  </label>
               </div>
            </div>

            <p>Program</p>
            <select name="program" class="box" onchange="showDepartments()" id="program">
               <option value="">Select Program</option>
               <option value="BS" <?= (isset($fetch_user['program']) && $fetch_user['program'] == 'BS') ? 'selected' : ''; ?>>BS</option>
               <option value="BE" <?= (isset($fetch_user['program']) && $fetch_user['program'] == 'BE') ? 'selected' : ''; ?>>BE</option>
            </select>

            <div id="bs-department" style="display: none;">
               <p>Department (BS)</p>
               <select name="department" class="box">
                  <option value="">Select Department</option>
                  <option value="Information Technology" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                  <option value="Computer Science" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                  <option value="Data Science" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Data Science') ? 'selected' : ''; ?>>Data Science</option>
                  <option value="Cyber Security" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Cyber Security') ? 'selected' : ''; ?>>Cyber Security</option>
                  <option value="Artificial Intelligence" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Artificial Intelligence') ? 'selected' : ''; ?>>Artificial Intelligence</option>
                  <option value="Mathematics" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
               </select>
            </div>

            <div id="be-department" style="display: none;">
               <p>Department (BE)</p>
               <select name="department" class="box">
                  <option value="">Select Department</option>
                  <option value="Software Engineering" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                  <option value="Electrical Engineering" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                  <option value="Mechanical Engineering" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                  <option value="Civil Engineering" <?= (isset($fetch_user['department']) && $fetch_user['department'] == 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
               </select>
            </div>

            <p>Old Password</p>
            <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="50" class="box">
            <p>New Password</p>
            <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="50" class="box">
            <p>Confirm Password</p>
            <input type="password" name="cpass" placeholder="Confirm your new password" maxlength="50" class="box">
         </div>
      </div>
      <input type="submit" name="submit" value="Update Profile" class="btn">
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script>
function showDepartments() {
   const program = document.getElementById("program").value;
   const bsDept = document.getElementById("bs-department");
   const beDept = document.getElementById("be-department");
   
   // Hide both first
   bsDept.style.display = "none";
   beDept.style.display = "none";
   
   // Show relevant department
   if (program === "BS") {
      bsDept.style.display = "block";
   } else if (program === "BE") {
      beDept.style.display = "block";
   }
}

// Show appropriate department on page load
document.addEventListener('DOMContentLoaded', function() {
   showDepartments();
});
</script>

<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>