<?php

include 'components/connect.php';
         
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

// Redirect to login if user is not logged in
if($user_id == ''){
   header('location:login1.php');
   exit();
}

// Get student info and department if logged in
$student_name = '';
$student_dept = '';
if($user_id != ''){
   $select_student = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
   $select_student->execute([$user_id]);
   if($select_student->rowCount() > 0){
      $fetch_student = $select_student->fetch(PDO::FETCH_ASSOC);
      $student_name = isset($fetch_student['name']) ? $fetch_student['name'] : '';
      $student_dept = isset($fetch_student['department']) ? $fetch_student['department'] : '';
   }
}

$select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ?");
$select_likes->execute([$user_id]);
$total_likes = $select_likes->rowCount();

$select_comments = $conn->prepare("SELECT * FROM `comments` WHERE user_id = ?");
$select_comments->execute([$user_id]);
$total_comments = $select_comments->rowCount();

$select_bookmark = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ?");
$select_bookmark->execute([$user_id]);
$total_bookmarked = $select_bookmark->rowCount();

// Pagination setup - Fixed for MariaDB compatibility
$limit = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>UniLinker - Connect, Learn, Excel</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   :root {
   
      --gold: #8E44AD;
      --black: #000000;
      --white: #ffffff;
      --light-gold: #f4e9a1;
      --dark-gold:rgb(112, 80, 181);
   }

   .hero-section {
      
      color: var(--white);
      padding: 4rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
   }

   .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('images/logo2.png') no-repeat center;
      background-size: 200px;
      opacity: 0.1;
      z-index: 1;
   }

   .hero-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      margin: 0 auto;
   }

   .hero-title {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      color: var(--gold);
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
   }

   .hero-subtitle {
      font-size: 1.5rem;
      margin-bottom: 2rem;
      opacity: 0.9;
   }

   .welcome-message {
      background: var(--gold);
      color: var(--white);
      padding: 1rem 2rem;
      border-radius: 50px;
      display: inline-block;
      margin-bottom: 2rem;
      font-weight: bold;
      box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
   }

   .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
      margin: 3rem 0;
   }

   .stat-card {
      background: var(--white);
      padding: 2rem;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 3px solid transparent;
   }

   .stat-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(77, 46, 92, 0.2);
      border-color: var(--gold);
   }

   .stat-icon {
      font-size: 3rem;
      color: var(--gold);
      margin-bottom: 1rem;
   }

   .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: var(--black);
      margin-bottom: 0.5rem;
   }

   .stat-label {
      color: #666;
      font-size: 1.1rem;
   }

   .about-section {
      background: var(--white);
      padding: 4rem 2rem;
   }

   .about-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 3rem;
      max-width: 1200px;
      margin: 0 auto;
   }

   .about-card {
      background: none;
      padding: 2.5rem;
      border-radius: 20px;
      color: var(--black);
      text-align: center;
      box-shadow: 0 15px 35px rgba(190, 113, 178, 0.3);
      transition: transform 0.3s ease;
   }

   .about-card:hover {
      transform: scale(1.05);
   }

   .about-icon {
      font-size: 4rem;
      margin-bottom: 1.5rem;
      color: var(--gold);
   }

   .courses-section {
      background: #f8f9fa;
      padding: 4rem 2rem;
   }

   .section-title {
      text-align: center;
      font-size: 2.5rem;
      color: var(--black);
      margin-bottom: 3rem;
      position: relative;
   }

   .section-title::after {
      content: '';
      width: 100px;
      height: 4px;
      background: var(--gold);
      display: block;
      margin: 1rem auto;
      border-radius: 2px;
   }

   .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
   }

   .course-card {
      background: var(--white);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 2px solid transparent;
   }

   .course-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
      border-color: var(--gold);
   }

   .course-thumb {
      width: 100%;
      height: 200px;
      object-fit: cover;
   }

   .course-content {
      padding: 1.5rem;
   }

   .course-tutor {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
   }

   .tutor-img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      margin-right: 1rem;
      border: 3px solid var(--gold);
   }

   .course-title {
      font-size: 1.3rem;
      color: var(--black);
      margin-bottom: 1rem;
      line-height: 1.4;
   }

   .course-btn {
      background: var(--gold);
      color: var(--white);
      padding: 0.8rem 2rem;
      border: none;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
      display: inline-block;
   }

   .course-btn:hover {
      background: var(--dark-gold);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(128, 83, 173, 0.4);
      color: var(--white);
   }

   .pagination {
      text-align: center;
      margin: 3rem 0;
   }

   .pagination a {
      display: inline-block;
      padding: 0.8rem 1.5rem;
      margin: 0 0.5rem;
      background: var(--white);
      color: var(--black);
      text-decoration: none;
      border-radius: 25px;
      border: 2px solid var(--gold);
      transition: all 0.3s ease;
   }

   .pagination a:hover,
   .pagination a.active {
      background: var(--gold);
      color: var(--black);
      transform: translateY(-2px);
   }

   .activities-section {

      color: var(--black);
      font-weight:bold;
      padding: 4rem 2rem;
   }

   .activity-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      max-width: 1000px;
      margin: 0 auto;
   }

   .activity-card {
      background: rgba(101, 64, 148, 0.1);
      padding: 2rem;
      border-radius: 15px;
      text-align: center;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(95, 64, 133, 0.3);
      transition: transform 0.3s ease;
   }

   .activity-card:hover {
      transform: translateY(-5px);
      background: rgba(103, 63, 131, 0.1);
      
   }

   .activity-icon {
      font-size: 3rem;
      color: var(--gold);
      margin-bottom: 1rem;
   }

   .auth-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 2rem;
      flex-wrap: wrap;
   }

   .auth-btn {
      padding: 1rem 2rem;
      background: var(--black);
      color: var(--white);
      text-decoration: none;
      border-radius: 25px;
      font-weight: bold;
      transition: all 0.3s ease;
      border: 2px solid var(--black);
   }

   .auth-btn:hover {
      background: transparent;
      color: var(--black);
      transform: translateY(-2px);
   }

   @media (max-width: 768px) {
      .hero-title {
         font-size: 2.5rem;
      }
      
      .hero-subtitle {
         font-size: 1.2rem;
      }
      
      .stats-grid {
         grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
         gap: 1rem;
      }
      
      .course-grid {
         grid-template-columns: 1fr;
      }
      
      .auth-buttons {
         flex-direction: column;
         align-items: center;
      }
   }
   </style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
   <div class="hero-content">
      
      <h1 class="hero-title">
         <img src="images/logo2.png" alt="UniLinker" style="height: 60px; vertical-align: middle; margin-right: 1rem;">
         UniLinker
      </h1>
      <p class="hero-subtitle"></p>
   </div>
</section>

<!-- Student Stats -->
<section class="about-section">
   <div class="stats-grid">
      <div class="stat-card">
         <div class="stat-icon"><i class="fas fa-heart"></i></div>
         <div class="stat-number"><?= $total_likes; ?></div>
         <div class="stat-label">Liked Lectures</div>
         <a href="likes.php" class="course-btn" style="margin-top: 1rem;">View Likes</a>
      </div>
      <div class="stat-card">
         <div class="stat-icon"><i class="fas fa-comments"></i></div>
         <div class="stat-number"><?= $total_comments; ?></div>
         <div class="stat-label">Comments Made</div>
         <a href="comments.php" class="course-btn" style="margin-top: 1rem;">View Comments</a>
      </div>
      <div class="stat-card">
         <div class="stat-icon"><i class="fas fa-bookmark"></i></div>
         <div class="stat-number"><?= $total_bookmarked; ?></div>
         <div class="stat-label">Saved Content</div>
         <a href="bookmark.php" class="course-btn" style="margin-top: 1rem;">View Bookmarks</a>
      </div>
   </div>
</section>

<!-- About UniLinker -->
<section class="about-section">
   <h2 class="section-title">What is UniLinker?</h2>
   <div class="about-grid">
      <div class="about-card">
         <div class="about-icon"><i class="fas fa-university"></i></div>
         <h3 style="margin-bottom: 1rem;">Quest University Hub</h3>
         <p>Your centralized platform for accessing all Quest University lectures, courses, and educational resources in one convenient location.</p>
      </div>
      <div class="about-card">
         <div class="about-icon"><i class="fas fa-users"></i></div>
         <h3 style="margin-bottom: 1rem;">Connect & Collaborate</h3>
         <p>Engage with fellow students, share insights, comment on lectures, and build a collaborative learning community.</p>
      </div>
      <div class="about-card">
         <div class="about-icon"><i class="fas fa-graduation-cap"></i></div>
         <h3 style="margin-bottom: 1rem;">Excel Academically</h3>
         <p>Access department-specific content, bookmark important lectures, and track your learning progress throughout your academic journey.</p>
      </div>
   </div>
</section>

<!-- Latest Courses Section -->
<section class="courses-section">
   <h2 class="section-title">
      <?php if($student_dept): ?>
         Latest <?= $student_dept; ?>  Lectures
      <?php else: ?>
         Latest Quest University Lectures
      <?php endif; ?>
   </h2>

   <div class="course-grid">
      <?php
         // Fixed SQL queries for MariaDB compatibility
         if($student_dept != ''){
            $sql = "SELECT * FROM `playlist` WHERE status = ? AND (department = ? OR department IS NULL) ORDER BY date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $select_courses = $conn->prepare($sql);
            $select_courses->execute(['active', $student_dept]);
         } else {
            $sql = "SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $select_courses = $conn->prepare($sql);
            $select_courses->execute(['active']);
         }

         if($select_courses->rowCount() > 0){
            while($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)){
               $course_id = $fetch_course['id'];

               $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
               $select_tutor->execute([$fetch_course['tutor_id']]);
               $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
      ?>
      <div class="course-card">
         <img src="uploaded_files/<?= $fetch_course['thumb']; ?>" class="course-thumb" alt="<?= $fetch_course['title']; ?>">
         <div class="course-content">
            <div class="course-tutor">
               <img src="uploaded_files/<?= $fetch_tutor['image']; ?>" class="tutor-img" alt="<?= $fetch_tutor['name']; ?>">
               <div>
                  <h4 style="margin: 0; color: var(--gold);"><?= $fetch_tutor['name']; ?></h4>
                  <small style="color: #666;"><?= $fetch_course['date']; ?></small>
               </div>
            </div>
            <h3 class="course-title"><?= $fetch_course['title']; ?></h3>
            <a href="playlist.php?get_id=<?= $course_id; ?>" class="course-btn">
               <i class="fas fa-play"></i> Watch Lectures
            </a>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                  <i class="fas fa-graduation-cap" style="font-size: 4rem; color: var(--gold); margin-bottom: 1rem;"></i>
                  <p style="font-size: 1.2rem; color: #666;">No lectures available yet. Check back soon!</p>
               </div>';
      }
      ?>
   </div>

   <!-- Pagination -->
   <?php
   // Get total courses for pagination - Fixed SQL
   if($student_dept != ''){
      $total_courses_query = $conn->prepare("SELECT COUNT(*) FROM `playlist` WHERE status = ? AND (department = ? OR department IS NULL)");
      $total_courses_query->execute(['active', $student_dept]);
   } else {
      $total_courses_query = $conn->prepare("SELECT COUNT(*) FROM `playlist` WHERE status = ?");
      $total_courses_query->execute(['active']);
   }
   $total_courses = $total_courses_query->fetchColumn();
   $total_pages = ceil($total_courses / $limit);

   if($total_pages > 1):
   ?>
   <div class="pagination">
      <?php if($page > 1): ?>
         <a href="?page=<?= $page - 1; ?>"><i class="fas fa-chevron-left"></i> Previous</a>
      <?php endif; ?>
      
      <?php for($i = 1; $i <= $total_pages; $i++): ?>
         <a href="?page=<?= $i; ?>" <?= ($i == $page) ? 'class="active"' : ''; ?>><?= $i; ?></a>
      <?php endfor; ?>
      
      <?php if($page < $total_pages): ?>
         <a href="?page=<?= $page + 1; ?>">Next <i class="fas fa-chevron-right"></i></a>
      <?php endif; ?>
   </div>
   <?php endif; ?>

   <div style="text-align: center; margin-top: 2rem;">
      <a href="courses.php" class="course-btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
         <i class="fas fa-list"></i> View All Courses
      </a>
   </div>
</section>
<!-- Footer section -->
<?php include 'components/footer.php'; ?>

<!-- Keep existing JavaScript functionality intact -->
<script src="js/script.js"></script>

</body>
</html>