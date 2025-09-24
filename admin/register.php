<?php

include '../components/connect.php';
if(isset($_COOKIE['user_id'])){
  $user_id = $_COOKIE['user_id'];
}else{
  $user_id = '';
}
if(isset($_POST['submit'])){

   $id = unique_id();
   $username = $_POST['username'];
   $username = filter_var($username, FILTER_SANITIZE_STRING);
   $university = $_POST['university'];
   $university= filter_var($university, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $password = sha1($_POST['password']);
   $password = filter_var($password, FILTER_SANITIZE_STRING);
   $gender = ($_POST['gender']);
   $gender = filter_var($gender, FILTER_SANITIZE_STRING);
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   
   // Handle multiple faculty
   $faculty = '';
   if(isset($_POST['faculty']) && is_array($_POST['faculty'])){
      $faculty_array = array_map(function($item) {
         return filter_var($item, FILTER_SANITIZE_STRING);
      }, $_POST['faculty']);
      $faculty = implode(',', $faculty_array);
   }
   
   // Handle multiple departments
   $departments = '';
   if(isset($_POST['department']) && is_array($_POST['department'])){
      $department_array = array_map(function($item) {
         return filter_var($item, FILTER_SANITIZE_STRING);
      }, $_POST['department']);
      $departments = implode(',', $department_array);
   }
   
   // Handle batch (multiple selections)
   $batch = '';
   if(isset($_POST['batch']) && is_array($_POST['batch'])){
      $batch_array = array_map(function($item) {
         return filter_var($item, FILTER_SANITIZE_STRING);
      }, $_POST['batch']);
      $batch = implode(',', $batch_array);
   }

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);
   $rename = unique_id().'.'.$ext;
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_files/'.$rename;

if($email == "admin@gmail.com"){
   $message[] = 'Email already exists! Please use a different email!';
}else{
   // Check if email exists in users table (active users)
   $check_users = $conn->prepare("SELECT id FROM users WHERE email = ?");
   $check_users->execute([$email]);
   
   // Check if email exists in tutors table with different statuses
   $check_tutors = $conn->prepare("SELECT id, status FROM tutors WHERE email = ?");
   $check_tutors->execute([$email]);
   
   if($check_users->rowCount() > 0){
      // Email exists in users table (active user)
      $message[] = 'Email already exists! Please use a different email!';
   }elseif($check_tutors->rowCount() > 0){
      $tutor_data = $check_tutors->fetch(PDO::FETCH_ASSOC);
      if($tutor_data['status'] == 'Pending'){
         // Email exists but registration is still pending
         $message[] = 'Registration request is already pending for this email. Please wait for admin approval or contact support.';
      }elseif($tutor_data['status'] == 'Active'){
         // Email exists and tutor is already active
         $message[] = 'Email already exists! Please use a different email!';
      }else{
         // Email exists but status is something else (like rejected)
         $message[] = 'Email already exists! Please use a different email!';
      }
   }else{
      // Email doesn't exist anywhere, proceed with registration
      $insert_tutor = $conn->prepare("INSERT INTO `tutors`
         (id, name, username, university, email, batch, gender, password, faculty, departments, image, status) 
         VALUES(?,?,?,?,?,?,?,?,?,?,?,'Pending')");
      $insert_tutor->execute([$id, $name, $username, $university, $email, $batch, $gender, $password, $faculty, $departments, $rename]);
      move_uploaded_file($image_tmp_name, $image_folder);
      $message[] = 'Registration successful! Your request is pending admin approval. You will be notified once approved.';
   }
}
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Signup</title>
  <link rel="stylesheet" href="../css/tutor_register.css">
</head>
<body>
  <section class="container">
    <header>Teacher Signup</header>
  <?php if(isset($message)){ 
      foreach($message as $msg){
         // Determine alert type based on message content
         if(strpos($msg, 'successful') !== false || strpos($msg, 'pending admin approval') !== false){
            $alert_class = 'success';
         }elseif(strpos($msg, 'pending') !== false){
            $alert_class = 'warning';
         }else{
            $alert_class = 'error';
         }
         echo "<div class='alert-box $alert_class'><p>$msg</p></div>";
      }
   } ?>
    <form class="register" action="" method="post" enctype="multipart/form-data">
      <div class="input-box">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" required />
      </div>

      <div class="input-box">
        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required />
      </div>

      <div class="input-box">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required />
      </div>

      <div class="gender-box">
        <h3>Gender</h3>
        <div class="radio-group">
          <label><input type="radio" name="gender" value="Male" required /> Male</label>
          <label><input type="radio" name="gender" value="Female" required /> Female</label>
        </div>
      </div>

      <div class="input-box">
        <label>Teacher Name</label>
        <input type="text" name="name" placeholder="Enter Teacher Name" required />
      </div>

      <div class="image-box">
        <p>select pic <span>*</span></p>
        <input type="file" name="image" accept="image/*" required class="box">
      </div>

      <div class="gender-box">
        <h3>University</h3>
        <div class="university-option">
          <label><input type="radio" name="university" value="Quest" checked> Quest</label>
          <label><input type="radio" name="university" value="SBBU"> SBBU</label>
          <label><input type="radio" name="university" value="Mehran"> Mehran</label>
        </div>
      </div>

      <div class="input-box">
        <label>Program (Select multiple)</label>
        <div class="checkbox-group">
          <label><input type="checkbox" name="faculty[]" value="BS" onchange="showDepartments()"> BS</label>
          <label><input type="checkbox" name="faculty[]" value="BE" onchange="showDepartments()"> BE</label>
        </div>
      </div>

      <div id="bs-department" class="input-box" style="display: none;">
        <label>Department (BS) - Select multiple</label>
        <div class="checkbox-group">
          <label><input type="checkbox" name="department[]" value="Information Technology"> Information Technology</label>
          <label><input type="checkbox" name="department[]" value="Computer Science"> Computer Science</label>
          <label><input type="checkbox" name="department[]" value="Data Science"> Data Science</label>
          <label><input type="checkbox" name="department[]" value="Cyber Security"> Cyber Security</label>
          <label><input type="checkbox" name="department[]" value="Artificial Intelligence"> Artificial Intelligence</label>
          <label><input type="checkbox" name="department[]" value="Mathematics"> Mathematics</label>
        </div>
      </div>

      <div id="be-department" class="input-box" style="display: none;">
        <label>Department (BE) - Select multiple</label>
        <div class="checkbox-group">
          <label><input type="checkbox" name="department[]" value="Software Engineering"> Software Engineering</label>
          <label><input type="checkbox" name="department[]" value="Electrical Engineering"> Electrical Engineering</label>
          <label><input type="checkbox" name="department[]" value="Mechanical Engineering"> Mechanical Engineering</label>
          <label><input type="checkbox" name="department[]" value="Civil Engineering"> Civil Engineering</label>
        </div>
      </div>

      <div class="input-box">
        <label>Batch</label>
        <div class="checkbox-group">
          <label><input type="checkbox" name="batch[]" value="2021" /> 2021</label>
          <label><input type="checkbox" name="batch[]" value="2022" /> 2022</label>
          <label><input type="checkbox" name="batch[]" value="2023" /> 2023</label>
        </div>
      </div>

      <div class="input-box">
        <button type="submit" name="submit" class="logins-button">Register Now</button>
        <div class="text">
          <h3>Already have an account? <a href="../login1.php">Login now</a></h3>
        </div>
      </div>
    </form>
  </section>

  <script>
    function showDepartments() {
      const bsCheckbox = document.querySelector('input[name="faculty[]"][value="BS"]');
      const beCheckbox = document.querySelector('input[name="faculty[]"][value="BE"]');
      const bsDepartment = document.getElementById('bs-department');
      const beDepartment = document.getElementById('be-department');

      // Show/hide BS departments
      if (bsCheckbox.checked) {
        bsDepartment.style.display = 'block';
      } else {
        bsDepartment.style.display = 'none';
        // Uncheck all BS department checkboxes
        const bsDeptCheckboxes = bsDepartment.querySelectorAll('input[type="checkbox"]');
        bsDeptCheckboxes.forEach(checkbox => checkbox.checked = false);
      }

      // Show/hide BE departments
      if (beCheckbox.checked) {
        beDepartment.style.display = 'block';
      } else {
        beDepartment.style.display = 'none';
        // Uncheck all BE department checkboxes
        const beDeptCheckboxes = beDepartment.querySelectorAll('input[type="checkbox"]');
        beDeptCheckboxes.forEach(checkbox => checkbox.checked = false);
      }
    }
  </script>
</body>
</html>