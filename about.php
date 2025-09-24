<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About UniLinker</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- about section starts  -->

<section class="about">

   <div class="row">

      <div class="image">
         <img src="images/about-img.svg" alt="">
      </div>

      <div class="content">
         <h3>Why Choose UniLinker?</h3>
         <p>UniLinker is your ultimate educational companion - a unified platform that brings together lecture notes, study materials, and resources from teachers all in one place. Say goodbye to the frantic midnight messages to your class representative before exams! With UniLinker, you have 24/7 access to all your course materials, organized and ready when you need them. We bridge the gap between students and educators, creating a seamless learning experience where knowledge flows freely and education becomes truly accessible.</p>
         <a href="courses.html" class="inline-btn">Explore Resources</a>
      </div>

   </div>

   <div class="box-container">

      <div class="box">
         <i class="fas fa-book-open"></i>
         <div>
            <h3>Unified Platform</h3>
            <span>All lecture notes & materials in one place</span>
         </div>
      </div>

      <div class="box">
         <i class="fas fa-clock"></i>
         <div>
            <h3>24/7 Access</h3>
            <span>Study anytime, anywhere - no more last-minute panic</span>
         </div>
      </div>

      <div class="box">
         <i class="fas fa-users"></i>
         <div>
            <h3>Direct Connection</h3>
            <span>Bridge between students and teachers</span>
         </div>
      </div>

      <div class="box">
         <i class="fas fa-shield-alt"></i>
         <div>
            <h3>Reliable Resources</h3>
            <span>Authentic materials from verified educators</span>
         </div>
      </div>

   </div>

</section>

<!-- about section ends -->

<!-- features section starts -->

<section class="reviews">

   <h1 class="heading">What Makes UniLinker Special</h1>

   <div class="box-container">

      <div class="box">
         <div class="feature-icon">
            <i class="fas fa-graduation-cap"></i>
         </div>
         <h3>No More Begging</h3>
         <p>Eliminate those desperate late-night messages to classmates. Get instant access to all lecture notes and study materials whenever you need them.</p>
      </div>

      <div class="box">
         <div class="feature-icon">
            <i class="fas fa-folder-open"></i>
         </div>
         <h3>Organized Learning</h3>
         <p>All your course materials are systematically organized by subject, topic, and date. Find what you need in seconds, not hours.</p>
      </div>

      <div class="box">
         <div class="feature-icon">
            <i class="fas fa-chalkboard-teacher"></i>
         </div>
         <h3>Teacher-Student Bridge</h3>
         <p>Direct connection with educators. Teachers can upload materials instantly, and students can access them immediately - creating a seamless educational flow.</p>
      </div>

      <div class="box">
         <div class="feature-icon">
            <i class="fas fa-mobile-alt"></i>
         </div>
         <h3>Always Available</h3>
         <p>Whether you're on your phone, tablet, or computer - UniLinker ensures your educational resources are always at your fingertips.</p>
      </div>

      <div class="box">
         <div class="feature-icon">
            <i class="fas fa-search"></i>
         </div>
         <h3>Smart Search</h3>
         <p>Powerful search functionality helps you find specific topics, lectures, or materials across all your courses instantly.</p>
      </div>

      <div class="box">
         <div class="feature-icon">
            <i class="fas fa-heart"></i>
         </div>
         <h3>Stress-Free Learning</h3>
         <p>Focus on understanding and learning rather than hunting for materials. UniLinker takes care of the organization so you can focus on education.</p>
      </div>

   </div>

</section>

<!-- features section ends -->

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>