<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

// Pagination settings
$limit = 16;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of active courses for pagination
$count_courses = $conn->prepare("SELECT COUNT(*) FROM `playlist` WHERE status = ?");
$count_courses->execute(['active']);
$total_courses = $count_courses->fetchColumn();
$total_pages = ceil($total_courses / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>courses</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <!-- <link rel="stylesheet" href="css/style.css">  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   /* Pagination Styles */
   .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 3rem 0;
      gap: 1rem;
      flex-wrap: wrap;
   }

   .pagination a,
   .pagination span {
      padding: 1rem 1.5rem;
      border: 0.1rem solid var(--light-color);
      background: var(--white);
      color: var(--black);
      text-decoration: none;
      border-radius: 0.5rem;
      font-size: 1.6rem;
      transition: all 0.3s ease;
   }

   .pagination a:hover {
      background: var(--main-color);
      color: var(--white);
      border-color: var(--main-color);
   }

   .pagination .current {
      background: var(--main-color);
      color: var(--white);
      border-color: var(--main-color);
   }

   .pagination .disabled {
      opacity: 0.5;
      cursor: not-allowed;
   }

   .pagination .disabled:hover {
      background: var(--white);
      color: var(--black);
      border-color: var(--light-color);
   }

   .pagination-info {
      text-align: center;
      margin: 2rem 0;
      font-size: 1.6rem;
      color: var(--light-color);
   }
   </style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- courses section starts  -->

<section class="courses">

   <h1 class="heading">all courses</h1>

   <?php if($total_courses > 0): ?>
   <div class="pagination-info">
      Showing <?= min($offset + 1, $total_courses) ?> - <?= min($offset + $limit, $total_courses) ?> of <?= $total_courses ?> courses
   </div>
   <?php endif; ?>

   <div class="box-container">

      <?php
         $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC LIMIT $limit OFFSET $offset");
         $select_courses->execute(['active']);
         if($select_courses->rowCount() > 0){
            while($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)){
               $course_id = $fetch_course['id'];

               $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
               $select_tutor->execute([$fetch_course['tutor_id']]);
               $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
      ?>
      <div class="box">
         <div class="tutor">
            <img src="uploaded_files/<?= $fetch_tutor['image']; ?>" alt="">
            <div>
               <h3><?= $fetch_tutor['name']; ?></h3>
               <span><?= $fetch_course['date']; ?></span>
            </div>
         </div>
         <img src="uploaded_files/<?= $fetch_course['thumb']; ?>" class="thumb" alt="">
         <h3 class="title"><?= $fetch_course['title']; ?></h3>
         <a href="playlist.php?get_id=<?= $course_id; ?>" class="inline-btn">view Content</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no courses added yet!</p>';
      }
      ?>

   </div>

   <?php if($total_pages > 1): ?>
   <!-- Pagination section starts -->
   <div class="pagination">
      <?php
      // Previous button
      if($page > 1) {
         echo '<a href="?page=' . ($page - 1) . '" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>';
      } else {
         echo '<span class="prev disabled"><i class="fas fa-chevron-left"></i> Previous</span>';
      }

      // Page numbers
      $start_page = max(1, $page - 2);
      $end_page = min($total_pages, $page + 2);

      // Show first page if not in range
      if($start_page > 1) {
         echo '<a href="?page=1">1</a>';
         if($start_page > 2) {
            echo '<span>...</span>';
         }
      }

      // Show page numbers in range
      for($i = $start_page; $i <= $end_page; $i++) {
         if($i == $page) {
            echo '<span class="current">' . $i . '</span>';
         } else {
            echo '<a href="?page=' . $i . '">' . $i . '</a>';
         }
      }

      // Show last page if not in range
      if($end_page < $total_pages) {
         if($end_page < $total_pages - 1) {
            echo '<span>...</span>';
         }
         echo '<a href="?page=' . $total_pages . '">' . $total_pages . '</a>';
      }

      // Next button
      if($page < $total_pages) {
         echo '<a href="?page=' . ($page + 1) . '" class="next">Next <i class="fas fa-chevron-right"></i></a>';
      } else {
         echo '<span class="next disabled">Next <i class="fas fa-chevron-right"></i></span>';
      }
      ?>
   </div>
   <!-- Pagination section ends -->
   <?php endif; ?>

</section>

<!-- courses section ends -->

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>