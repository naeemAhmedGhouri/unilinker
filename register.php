<?php


include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}
$message = []; 

if(isset($_POST['submit'])){
    // Collect form data
    $id = unique_id();
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $password = sha1($_POST['password']);
    $password = filter_var($password, FILTER_SANITIZE_STRING);
    $batch_roll_no = $_POST['batch_roll_no'];
    $university = $_POST['university'];
    $program = $_POST['program'];
    $department = $_POST['department'];
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = unique_id().'.'.$ext;
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_files/'.$rename;
 
   // check email in BOTH tables (users + tutors)
// pehle fixed admin email check karo
if (strtolower($email) === "admin@gmail.com") {
   $message[] = 'Email already exists! Please use a different email.';
} else {
   // check email in BOTH tables (users + tutors)
   $check_email = $conn->prepare("
      SELECT 1 FROM users WHERE email = ?
      UNION
      SELECT 1 FROM tutors WHERE email = ?
      LIMIT 1
   ");
   $check_email->execute([$email, $email]);

   if ($check_email->fetch()) {
      $message[] = 'Email already exists! Please use a different email.';
   } else {
      // email free hai -> insert karo
      $insert_user = $conn->prepare("INSERT INTO users (id, fulL_name, gender,  email, password, batch_roll_no, university, program, department, image) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $insert_user->execute([$id, $full_name, $gender, $email, $password, $batch_roll_no, $university, $program, $department, $rename]);
      move_uploaded_file($image_tmp_name, $image_folder);

      $verify_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
      $verify_user->execute([$email, $password]);
      $row = $verify_user->fetch(PDO::FETCH_ASSOC);

      if($row){
         setcookie('user_id', $row['id'], time() + 60*60*24*30, '/');
         header('location:home.php');
         exit;
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
  <title>Student Signup</title>
  <link rel="stylesheet" href="css/s_register.css">
</head>
<body>
  <section class="container">
    <header>Students Signup</header>
    <?php if(!empty($message)): ?>
  <div class="alert error">
    <?php foreach ($message as $m) { echo htmlspecialchars($m); break; } ?>
  </div>
<?php endif; ?>
    <form class="register" action="" method="post" enctype="multipart/form-data">
      <div class="input-box">
        <label>Full Name</label>
        <input type="text" name="full_name" placeholder="Enter your full name" required>
      </div>
      <div class="gender-box">
        <h3>Gender</h3>
        <div class="gender-option">
          <label><input type="radio" name="gender" value="Male" checked> Male</label>
          <label><input type="radio" name="gender" value="Female"> Female</label>
        </div>
      </div>
      <div class="input-box">
        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="input-box">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <div class="input-box">
        <label>Batch & Roll No</label>
        <input type="text" name="batch_roll_no" placeholder="e.g., 45BCS32" required>
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
        <label>Program</label>
        <select id="program" name="program" onchange="showDepartments()" required>
          <option value="">Select Program</option>
          <option value="BS">BS</option>
          <option value="BE">BE</option>
        </select>
      </div>

      <div id="bs-department" class="input-box" style="display: none;">
        <label>Department (BS)</label>
        <select name="department">
          <option value="Information Technology">Information Technology</option>
          <option value="Computer Science">Computer Science</option>
          <option value="Data Science">Data Science</option>
          <option value="Cyber Security">Cyber Security</option>
          <option value="Artificial Intelligence">Artificial Intelligence</option>
          <option value="Mathematics">Mathematics</option>
        </select>
      </div>

      <div id="be-department" class="input-box" style="display: none;">
        <label>Department (BE)</label>
        <select name="department">
          <option value="Software Engineering">Software Engineering</option>
          <option value="Electrical Engineering">Electrical Engineering</option>
          <option value="Mechanical Engineering">Mechanical Engineering</option>
          <option value="Civil Engineering">Civil Engineering</option>
        </select>
      </div>

      <div class="input-box">
        <button type="submit" name="submit" class="logins-button">Register Now</button>
      </div>
</form>

      <div class="text">
          <h3>Already have an account? <a href="Login1.php">Login now</a></h3>
        </div>
    </form>
  </section>
  <script>
    function showDepartments() {
      const program = document.getElementById("program").value;
      document.getElementById("bs-department").style.display = program === "BS" ? "block" : "none";
      document.getElementById("be-department").style.display = program === "BE" ? "block" : "none";
    }
  </script>
</body>
</html>
