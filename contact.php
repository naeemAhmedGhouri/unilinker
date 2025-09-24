<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_POST['submit'])){

   $name = $_POST['name']; 
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $email = $_POST['email']; 
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $roll = $_POST['roll']; 
   $roll = filter_var($roll, FILTER_SANITIZE_STRING);

   $university = $_POST['university']; 
   $university = filter_var($university, FILTER_SANITIZE_STRING);

   $msg = $_POST['msg']; 
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);

   // check if same message already exists
   $select_contact = $conn->prepare("SELECT * FROM `contact` WHERE name = ? AND email = ? AND roll = ? AND university = ? AND message = ?");
   $select_contact->execute([$name, $email, $roll, $university, $msg]);

   if($select_contact->rowCount() > 0){
      $message[] = 'Message already sent!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `contact`(name, email, roll, university, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$name, $email, $roll, $university, $msg]);
      $message[] = 'Message sent successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact - UniLinker</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- contact section starts  -->

<section class="contact">

   <div class="row">

      <div class="image">
         <img src="images/contact-img.svg" alt="Contact UniLinker">
      </div>

      <form action="" method="post">
         <h3>Get in Touch</h3>
         <input type="text" placeholder="Enter your name" required maxlength="100" name="name" class="box">
         <input type="email" placeholder="Enter your email" required maxlength="100" name="email" class="box">
         <select name="role" class="box" required>
      <option value="" disabled selected>Select your role</option>
      <option value="Student">Student</option>
      <option value="Teacher">Teacher</option>
   </select>


         <input type="text" placeholder="Enter your university name" required maxlength="150" name="university" class="box">
         <textarea name="msg" class="box" placeholder="Enter your message" required cols="30" rows="10" maxlength="1000"></textarea>
         <input type="submit" value="Send Message" class="inline-btn" name="submit">
      </form>

   </div>

   <div class="box-container">

      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>Phone</h3>
         <a href="tel:+923001112233">+92 300 111 2233</a>
         <a href="tel:+923001112244">+92 300 111 2244</a>
      </div>

      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>Email</h3>
         <a href="mailto:support@unlinker.com">support@unlinker.com</a>
         <a href="mailto:info@unlinker.com">info@unlinker.com</a>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>Office</h3>
         <a href="#">UniLinker HQ, Nawabshah, Sindh, Pakistan</a>
      </div>

   </div>

</section>

<!-- contact section ends -->

<?php include 'components/footer.php'; ?>  

<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>
