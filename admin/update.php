<?php

   include '../components/connect.php';

   if(isset($_COOKIE['tutor_id'])){
      $tutor_id = $_COOKIE['tutor_id'];
   }else{
      $tutor_id = '';
      header('location:login.php');
   }

// Always fetch current tutor profile for displaying defaults
$select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
$select_tutor->execute([$tutor_id]);
$fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $prev_pass = $fetch_tutor['password'];
   $prev_image = $fetch_tutor['image'];

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $gender = $_POST['gender'] ?? '';
   $gender = filter_var($gender, FILTER_SANITIZE_STRING);
   $university = $_POST['university'] ?? '';
   $university = filter_var($university, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   if(!empty($name) && $name !== ($fetch_tutor['name'] ?? '')){
      $update_name = $conn->prepare("UPDATE `tutors` SET name = ? WHERE id = ?");
      $update_name->execute([$name, $tutor_id]);
      $message[] = ' Name updated successfully!';
   }

 
   if(!empty($gender) && $gender !== ($fetch_tutor['gender'] ?? '')){
      $update_gender = $conn->prepare("UPDATE `tutors` SET gender = ? WHERE id = ?");
      $update_gender->execute([$gender, $tutor_id]);
      $message[] = 'gender updated successfully!';
   }

   if(!empty($university) && $university !== ($fetch_tutor['university'] ?? '')){
      $update_university = $conn->prepare("UPDATE `tutors` SET university = ? WHERE id = ?");
      $update_university->execute([$university, $tutor_id]);
      $message[] = 'university updated successfully!';
   }

   if(!empty($email) && $email !== ($fetch_tutor['email'] ?? '')){
      $select_email = $conn->prepare("SELECT id FROM `tutors` WHERE email = ? AND id != ?");
      $select_email->execute([$email, $tutor_id]);
      if($select_email->rowCount() > 0){
         $message[] = 'email already taken!';
      }else{
         $update_email = $conn->prepare("UPDATE `tutors` SET email = ? WHERE id = ?");
         $update_email->execute([$email, $tutor_id]);
         $message[] = 'email updated successfully!';
      }
   }

   // Faculty (BS/BE) multi-select to CSV
   $faculty = '';
   if(isset($_POST['faculty']) && is_array($_POST['faculty'])){
      $faculty_list = array_map(function($v){ return filter_var($v, FILTER_SANITIZE_STRING); }, $_POST['faculty']);
      $faculty = implode(',', $faculty_list);
   }
   if($faculty !== '' && $faculty !== ($fetch_tutor['faculty'] ?? '')){
      $update_faculty = $conn->prepare("UPDATE `tutors` SET faculty = ? WHERE id = ?");
      $update_faculty->execute([$faculty, $tutor_id]);
      $message[] = 'programs updated successfully!';
   }

   // Departments multi-select to CSV
   $departments = '';
   if(isset($_POST['department']) && is_array($_POST['department'])){
      $dept_list = array_map(function($v){ return filter_var($v, FILTER_SANITIZE_STRING); }, $_POST['department']);
      $departments = implode(',', $dept_list);
   }
   if($departments !== '' && $departments !== ($fetch_tutor['departments'] ?? '')){
      $update_departments = $conn->prepare("UPDATE `tutors` SET departments = ? WHERE id = ?");
      $update_departments->execute([$departments, $tutor_id]);
      $message[] = 'departments updated successfully!';
   }

   // Batch multi-select to CSV
   $batch = '';
   if(isset($_POST['batch']) && is_array($_POST['batch'])){
      $batch_list = array_map(function($v){ return filter_var($v, FILTER_SANITIZE_STRING); }, $_POST['batch']);
      $batch = implode(',', $batch_list);
   }
   if($batch !== '' && $batch !== ($fetch_tutor['batch'] ?? '')){
      $update_batch = $conn->prepare("UPDATE `tutors` SET batch = ? WHERE id = ?");
      $update_batch->execute([$batch, $tutor_id]);
      $message[] = 'batch updated successfully!';
   }

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);
   $rename = unique_id().'.'.$ext;
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_files/'.$rename;

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'image size too large!';
      }else{
         $update_image = $conn->prepare("UPDATE `tutors` SET `image` = ? WHERE id = ?");
         $update_image->execute([$rename, $tutor_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         if($prev_image != '' AND $prev_image != $rename){
            unlink('../uploaded_files/'.$prev_image);
         }
         $message[] = 'image updated successfully!';
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   if($old_pass != $empty_pass){
      if($old_pass != $prev_pass){
         $message[] = 'old password not matched!';
      }elseif($new_pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         if($new_pass != $empty_pass){
            $update_pass = $conn->prepare("UPDATE `tutors` SET password = ? WHERE id = ?");
            $update_pass->execute([$cpass, $tutor_id]);
            $message[] = 'password updated successfully!';
         }else{
            $message[] = 'please enter a new password!';
         }
      }
   }

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
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
input[type="radio"],
input[type="checkbox"] {
   transform: scale(1.3);   
   margin-right: 6px;  
   justify-content: center;
   align-items: center;
   
   float: left;
   cursor: pointer;         /* hand pointer */
}

/* Label text ko thoda align aur readable banane ke liye */
label {
   display: flex;
   align-items: center;
   gap: 4px;
   font-size: 1rem; /* ya jitna tumhe bada chahiye */
}

   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<!-- register section starts  -->

<section class="form-container" style="min-height: calc(100vh - 19rem);">

   <form class="register" action="" method="post" enctype="multipart/form-data">
      <h3>update profile</h3>
      <div class="flex">
         <div class="col">
            <p>Teacher Name</p>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_tutor['name'] ?? '') ?>" maxlength="100" class="box" placeholder="Enter Teacher Name">


            <div class="gender-box">
               <h3>Gender</h3>
               <div class="gender-option" style="display:flex; gap:1rem;">
                  <label><input type="radio" name="gender" value="Male" <?= (isset($fetch_tutor['gender']) && $fetch_tutor['gender']==='Male')?'checked':''; ?>> Male</label>
                  <label><input type="radio" name="gender" value="Female" <?= (isset($fetch_tutor['gender']) && $fetch_tutor['gender']==='Female')?'checked':''; ?>> Female</label>
               </div>
            </div>

            <p>Your Email</p>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_tutor['email'] ?? '') ?>" maxlength="100" class="box" placeholder="Enter your email">

            <div class="gender-box">
               <h3>University</h3>
               <div class="university-option" style="display:flex; gap:1rem; flex-wrap:wrap; justify-content:center;">
   <?php $u = $fetch_tutor['university'] ?? ''; ?>
   <label><input type="radio" name="university" value="Quest" <?= $u==='Quest'?'checked':''; ?>> Quest</label>
   <label><input type="radio" name="university" value="SBBU" <?= $u==='SBBU'?'checked':''; ?>> SBBU</label>
   <label><input type="radio" name="university" value="Mehran" <?= $u==='Mehran'?'checked':''; ?>> Mehran</label>
</div>

            </div>
         </div>
         <div class="col">
            <p>old password :</p>
            <input type="password" name="old_pass" placeholder="enter your old password" maxlength="20"  class="box">
            <p>new password :</p>
            <input type="password" name="new_pass" placeholder="enter your new password" maxlength="20"  class="box">
            <p>confirm password :</p>
            <input type="password" name="cpass" placeholder="confirm your new password" maxlength="20"  class="box">

            <p>Program (Select multiple)</p>
            <?php $faculty_vals = isset($fetch_tutor['faculty']) ? explode(',', $fetch_tutor['faculty']) : []; ?>
            <div class="checkbox-group" style="display:flex; gap:1rem; flex-wrap:wrap;">
               <label><input type="checkbox" name="faculty[]" value="BS" <?= in_array('BS', $faculty_vals)?'checked':''; ?> onchange="showDepartments()"> BS</label>
               <label><input type="checkbox" name="faculty[]" value="BE" <?= in_array('BE', $faculty_vals)?'checked':''; ?> onchange="showDepartments()"> BE</label>
            </div>

            <?php $dept_vals = isset($fetch_tutor['departments']) ? explode(',', $fetch_tutor['departments']) : []; ?>
            <div id="bs-department" class="input-box" style="display: none; margin-top:10px;">
               <label>Department (BS) - Select multiple</label>
               <div class="checkbox-group" style="display:flex; gap:1rem; flex-wrap:wrap;">
                  <?php foreach(['Information Technology','Computer Science','Data Science','Cyber Security','Artificial Intelligence','Mathematics'] as $dep): ?>
                     <label><input type="checkbox" name="department[]" value="<?= $dep; ?>" <?= in_array($dep, $dept_vals)?'checked':''; ?>> <?= $dep; ?></label>
                  <?php endforeach; ?>
               </div>
            </div>

            <div id="be-department" class="input-box" style="display: none; margin-top:10px;">
               <label>Department (BE) - Select multiple</label>
               <div class="checkbox-group" style="display:flex; gap:1rem; flex-wrap:wrap;">
                  <?php foreach(['Software Engineering','Electrical Engineering','Mechanical Engineering','Civil Engineering'] as $dep): ?>
                     <label><input type="checkbox" name="department[]" value="<?= $dep; ?>" <?= in_array($dep, $dept_vals)?'checked':''; ?>> <?= $dep; ?></label>
                  <?php endforeach; ?>
               </div>
            </div>

            <?php $batch_vals = isset($fetch_tutor['batch']) ? explode(',', $fetch_tutor['batch']) : []; ?>
            <p style="margin-top:10px;">Batch</p>
            <div class="checkbox-group" style="display:flex; gap:1rem; flex-wrap:wrap;">
               <?php foreach(['2021','2022','2023'] as $b): ?>
                  <label><input type="checkbox" name="batch[]" value="<?= $b; ?>" <?= in_array($b, $batch_vals)?'checked':''; ?>> <?= $b; ?></label>
               <?php endforeach; ?>
            </div>
         </div>
      </div>
      <p>update pic :</p>
      <input type="file" name="image" accept="image/*"  class="box">
      <input type="submit" name="submit" value="update now" class="btn">
   </form>

</section>


<?php include '../components/footer.php'; ?>

<script src="../js/admin_script.js"></script>
<script>
function showDepartments() {
  const bsChecked = document.querySelector('input[name="faculty[]"][value="BS"]').checked;
  const beChecked = document.querySelector('input[name="faculty[]"][value="BE"]').checked;
  const bs = document.getElementById('bs-department');
  const be = document.getElementById('be-department');
  bs.style.display = bsChecked ? 'block' : 'none';
  be.style.display = beChecked ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', showDepartments);
</script>
   
</body>
</html>