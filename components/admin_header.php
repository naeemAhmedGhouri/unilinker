<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

// Count unread messages for notification badge
$unread_count = 0;
if (isset($conn) && isset($tutor_id)) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM `messages` WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$tutor_id]);
    $unread_count = $stmt->fetchColumn();
}
?>             

<header class="header">

   <section class="flex">

      <a href="dashboard.php" class="logo"><?= isset($page_title) ? $page_title : 'Teacher.'; ?></a>

      <?php if (empty($hide_search_bar)): ?>
      <form action="search_page.php" method="post" class="search-form">
         <input type="text" name="search" placeholder="search here..." required maxlength="100">
         <button type="submit" class="fas fa-search" name="search_btn"></button>
      </form>
      <?php endif; ?>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="search-btn" class="fas fa-search"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <a href="chat-list.php" id="chat-btn" title="Chats" style="color:inherit; position:relative;">
            <i class="fas fa-comments"></i>
            <?php if ($unread_count > 0): ?>
               <span class="chat-notification-badge"><?= $unread_count; ?></span>
            <?php endif; ?>
         </a>
         <div id="toggle-btn" class="fas fa-sun"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
            $select_profile->execute([$tutor_id]);
            if($select_profile->rowCount() > 0){
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="../uploaded_files/<?= $fetch_profile['image']; ?>" alt="">
         <h3><?= $fetch_profile['name']; ?></h3>
        

         <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">logout</a>
         <?php
            }else{
         ?>
         <h3>please login or register</h3>
          <div class="flex-btn">
            <a href="login.php" class="option-btn">login</a>
            <a href="register.php" class="option-btn">register</a>
         </div>
         <?php
            }
         ?>
      </div>

   </section>

</header>

<!-- header section ends -->
<!-- side bar section starts  -->

<div class="side-bar">

   <div class="close-side-bar">
      <i class="fas fa-times"></i>
   </div>

   <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
            $select_profile->execute([$tutor_id]);
            if($select_profile->rowCount() > 0){
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="../uploaded_files/<?= $fetch_profile['image']; ?>" alt="">
         <h3><?= $fetch_profile['name']; ?></h3>
         <a href="update.php" class="btn">Edit Profile</a>
         <?php
            }else{
         ?>
         <h3>please login or register</h3>
          <div class="flex-btn">
            <a href="login.php" class="option-btn">login</a>
            <a href="register.php" class="option-btn">register</a>
         </div>
         <?php
            }
         ?>
      </div>

   <nav class="navbar">
      <a href="dashboard.php"><i class="fas fa-home"></i><span>home</span></a>
      <a href="other_teacher.php"><i class="fa-solid fa-bars-staggered"></i><span>Other Teacher</span></a>
      <a href="contents.php"><i class="fas fa-graduation-cap"></i><span>contents</span></a>
      <a href="comments.php"><i class="fas fa-comment"></i><span>comments</span></a>
      <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');"><i class="fas fa-right-from-bracket"></i><span>logout</span></a>
   </nav>

</div>

<!-- side bar section ends -->

<style>
.icons #chat-btn {
    display: inline-block;
    margin: 0 8px;
    position: relative;
}

.icons #chat-btn:hover,
.icons #chat-btn:focus {
    color:#2C3E50;
    text-decoration: none;
}
.chat-notification-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #e53935;
    color: #fff;
    font-size: 0.75rem;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(229,57,53,0.15);
    pointer-events: none;
    z-index: 2;
}
#chat-btn:hover i {
background:#2C3E50;
color:white;

font-size:2rem;
}
#chat-btn i{
    background:#EEEEEE;
    padding:12px;
    font-size:2rem;
   border-radius:10%;
 }
</style>