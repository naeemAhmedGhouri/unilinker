<?php

include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

if(isset($_POST['delete_video'])){
   $delete_id = $_POST['video_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   $verify_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ? LIMIT 1");
   $verify_video->execute([$delete_id, $tutor_id]);
   if($verify_video->rowCount() > 0){
      $delete_video_thumb = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $delete_video_thumb->execute([$delete_id]);
      $fetch_thumb = $delete_video_thumb->fetch(PDO::FETCH_ASSOC);
      if($fetch_thumb['thumb']) {
         $thumb_path = '../uploaded_files/'.$fetch_thumb['thumb'];
         if(file_exists($thumb_path)) {
            unlink($thumb_path);
         }
      }
      $delete_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $delete_video->execute([$delete_id]);
      $fetch_video = $delete_video->fetch(PDO::FETCH_ASSOC);
      if($fetch_video['video']) {
         $video_path = '../uploaded_files/'.$fetch_video['video'];
         if(file_exists($video_path)) {
            unlink($video_path);
         }
      }
      if($fetch_video['pdf']) {
         $pdf_path = '../uploaded_files/'.$fetch_video['pdf'];
         if(file_exists($pdf_path)) {
            unlink($pdf_path);
         }
      }
      $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
      $delete_likes->execute([$delete_id]);
      $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
      $delete_comments->execute([$delete_id]);
      $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
      $delete_content->execute([$delete_id]);
      $message[] = 'content deleted!';
   }else{
      $message[] = 'content already deleted!';
   }
}

if(isset($_POST['delete_playlist'])){
   $delete_id = $_POST['playlist_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   $verify_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND tutor_id = ? LIMIT 1");
   $verify_playlist->execute([$delete_id, $tutor_id]);
   if($verify_playlist->rowCount() > 0){
      $delete_playlist_thumb = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? LIMIT 1");
      $delete_playlist_thumb->execute([$delete_id]);
      $fetch_thumb = $delete_playlist_thumb->fetch(PDO::FETCH_ASSOC);
      if($fetch_thumb['thumb']) {
         $thumb_path = '../uploaded_files/'.$fetch_thumb['thumb'];
         if(file_exists($thumb_path)) {
            unlink($thumb_path);
         }
      }
      $delete_bookmark = $conn->prepare("DELETE FROM `bookmark` WHERE playlist_id = ?");
      $delete_bookmark->execute([$delete_id]);
      $delete_playlist = $conn->prepare("DELETE FROM `playlist` WHERE id = ?");
      $delete_playlist->execute([$delete_id]);
      $message[] = 'Folder deleted!';
   }else{
      $message[] = 'Folder already deleted!';
   }
}

$file = '../uploaded_files/default_playlist.jpg';
if (file_exists($file)) {
    unlink($file);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
    :root {
  /* Primary Colors */
  --primary-color: #8E44AD;
  --primary-light: #A569BD;
  --primary-dark: #7D3C98;
  --primary-hover: #9B59B6;
  
  /* Secondary Colors */
  --secondary-color: #3498DB;
  --secondary-light: #5DADE2;
  --secondary-dark: #2E86C1;
  
  /* Neutral Colors */
  --white: #FFFFFF;
  --light-gray: #F8F9FA;
  --gray: #E9ECEF;
  --medium-gray: #6C757D;
  --dark-gray: #495057;
  --black: #212529;
  
  /* Status Colors */
  --success: #27AE60;
  --warning: #F39C12;
  --danger: #E74C3C;
  --info: #17A2B8;
  
  /* Background Colors */
  --bg-primary: var(--white);
  --bg-secondary: var(--light-gray);
  --bg-card: var(--white);
  --bg-overlay: rgba(142, 68, 173, 0.1);
  
  /* Shadow */
  --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.1);
  --shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.15);
  --shadow-heavy: 0 8px 32px rgba(0, 0, 0, 0.2);
  
  /* Border Radius */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-xxl: 3rem;
  
  /* Transitions */
  --transition-fast: 0.2s ease;
  --transition-normal: 0.3s ease;
  --transition-slow: 0.5s ease;
}

/* Contents Section */
.contents {
  padding: var(--spacing-xl);
  background: var(--bg-secondary);
  min-height: calc(100vh - 120px);
}

.contents .heading {
  font-size: 2.5rem;
  color: var(--primary-color);
  margin-bottom: var(--spacing-xl);
  text-align: center;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  position: relative;
}

.contents .heading::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
  border-radius: var(--radius-sm);
}

/* Box Container */
.box-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: var(--spacing-xl);
  max-width: 1400px;
  margin: 0 auto;
}

/* Add Content Box */
.add-content-box {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  color: var(--white);
  text-align: center;
  box-shadow: var(--shadow-medium);
  transition: var(--transition-normal);
  border: 2px solid transparent;
  max-width: 500px;
  margin: 0 auto var(--spacing-xl) auto;
}

.add-content-box:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-heavy);
  border-color: var(--primary-dark);
}

.add-content-box h3 {
  font-size: 1.5rem;
  margin-bottom: var(--spacing-lg);
  font-weight: 600;
}

.add-content-actions {
  display: flex;
  gap: var(--spacing-md);
  justify-content: center;
}

.add-content-actions .btn {
  background: var(--white);
  color: var(--primary-color);
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  transition: var(--transition-fast);
  box-shadow: var(--shadow-light);
  border: none;
  text-decoration: none;
}

.add-content-actions .btn:hover {
  background: var(--primary-dark);
  color: var(--white);
  transform: scale(1.1);
}

/* Content Box */
.content-box {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-light);
  overflow: hidden;
  transition: var(--transition-normal);
  border: 1px solid var(--gray);
}

.content-box:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-medium);
  border-color: var(--primary-light);
}

/* Content Header */
.content-header {
  padding: var(--spacing-lg);
  background: var(--bg-overlay);
  border-bottom: 2px solid var(--gray);
  position: relative;
}

.content-header.playlist {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
  color: var(--white);
  border-bottom-color: var(--primary-dark);
}

.content-header.video {
  background: linear-gradient(135deg, var(--danger) 0%, #FF6B6B 100%);
  color: var(--white);
  border-bottom-color: #C0392B;
}

.content-header.pdf {
  background: linear-gradient(135deg, var(--warning) 0%, #F7DC6F 100%);
  color: var(--white);
  border-bottom-color: #D68910;
}

.content-header.image {
  background: linear-gradient(135deg, var(--success) 0%, #58D68D 100%);
  color: var(--white);
  border-bottom-color: #229954;
}

.content-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: var(--spacing-sm);
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.content-title i {
  font-size: 1.1rem;
  opacity: 0.9;
}

.content-meta {
  font-size: 0.9rem;
  opacity: 0.8;
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.content-meta i {
  font-size: 0.8rem;
}

/* Content Body */
.content-body {
  padding: var(--spacing-lg);
}

.content-thumb {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: var(--radius-md);
  margin-bottom: var(--spacing-md);
  border: 2px solid var(--gray);
  transition: var(--transition-fast);
}

.content-thumb:hover {
  border-color: var(--primary-color);
  transform: scale(1.02);
}

.content-description {
  color: var(--medium-gray);
  line-height: 1.6;
  margin-bottom: var(--spacing-lg);
  font-size: 0.95rem;
}

/* Content Actions */
.content-actions {
  display: flex;
  gap: var(--spacing-sm);
  align-items: center;
}

.content-actions .btn,
.content-actions .option-btn,
.content-actions .delete-btn {
  padding: var(--spacing-sm) var(--spacing-md);
  border-radius: var(--radius-md);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: var(--transition-fast);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  text-align: center;
  justify-content: center;
}

.content-actions .btn {
  background: var(--primary-color);
  color: var(--white);
  flex: 1;
}

.content-actions .btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.content-actions .option-btn {
  background: var(--secondary-color);
  color: var(--white);
  flex: 1;
}

.content-actions .option-btn:hover {
  background: var(--secondary-dark);
  transform: translateY(-1px);
}

.content-actions .delete-btn {
  background: var(--danger);
  color: var(--white);
  flex: 1;
}

.content-actions .delete-btn:hover {
  background: #C0392B;
  transform: translateY(-1px);
}

.content-actions form {
  flex: 1;
  margin: 0;
}

/* Empty State */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: var(--spacing-xxl);
  color: var(--medium-gray);
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-light);
}

.empty-state i {
  font-size: 4rem;
  color: var(--primary-light);
  margin-bottom: var(--spacing-lg);
  opacity: 0.7;
}

.empty-state p {
  font-size: 1.1rem;
  margin-bottom: var(--spacing-sm);
}

.empty-state p:first-of-type {
  font-weight: 600;
  color: var(--dark-gray);
}

/* Responsive Design */
@media (max-width: 768px) {
  .contents {
    padding: var(--spacing-md);
  }
  
  .contents .heading {
    font-size: 2rem;
    margin-bottom: var(--spacing-lg);
  }
  
  .box-container {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-md);
  }
  
  .content-actions {
    flex-direction: column;
  }
  
  .content-actions .btn,
  .content-actions .option-btn,
  .content-actions .delete-btn {
    width: 100%;
  }
  
  .add-content-actions {
    flex-direction: row;
    justify-content: center;
  }
  
  .add-content-box {
    max-width: 100%;
    margin-bottom: var(--spacing-lg);
  }
}

@media (max-width: 480px) {
  .contents .heading {
    font-size: 1.5rem;
  }
  
  .content-header {
    padding: var(--spacing-md);
  }
  
  .content-body {
    padding: var(--spacing-md);
  }
  
  .content-title {
    font-size: 1.1rem;
  }
  
  .content-meta {
    font-size: 0.8rem;
  }
  
  .box-container {
    grid-template-columns: 2fr;
    gap: var(--spacing-sm);
  }
}

/* Loading Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.content-box {
  animation: fadeInUp 0.6s ease forwards;
}

.content-box:nth-child(even) {
  animation-delay: 0.1s;
}

.content-box:nth-child(3n) {
  animation-delay: 0.2s;
}

/* Focus States for Accessibility */
.btn:focus,
.option-btn:focus,
.delete-btn:focus {
  outline: 3px solid var(--primary-light);
  outline-offset: 2px;
}

/* Print Styles */
@media print {
  .contents {
    background: var(--white);
    padding: var(--spacing-md);
  }
  
  .content-box {
    break-inside: avoid;
    box-shadow: none;
    border: 1px solid var(--medium-gray);
  }
  
  .content-actions {
    display: none;
  }
}
   </style>
</head>
<body>
<?php include '../components/admin_header.php'; ?>
<section class="contents">
   <h1 class="heading">Your Contents</h1>
   <div class="add-content-box">
         <h3>Create New Content</h3>
         <div class="add-content-actions">
<a href="add_content.php" class="btn" title="Add Content">
   <i class="fas fa-plus"></i>
</a>
<a href="add_playlist.php" class="btn" title="Add Folder">
   <i class="fas fa-folder-plus"></i>
</a>

         </div>
      </div>

   <div class="box-container">
      

      <?php
         // Get playlists only once and collect IDs at the same time
         $existing_playlist_ids = [];
         $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ? ORDER BY date DESC");
         $select_playlists->execute([$tutor_id]);
         $total_playlists = $select_playlists->rowCount();
         
         if($total_playlists > 0){
            while($fetch_playlist = $select_playlists->fetch(PDO::FETCH_ASSOC)){
               $playlist_id = $fetch_playlist['id'];
               // Collect playlist IDs for later use
               $existing_playlist_ids[] = $playlist_id;
               
               $count_content = $conn->prepare("SELECT COUNT(*) as total FROM `content` WHERE playlist_id = ?");
               $count_content->execute([$playlist_id]);
               $content_count = $count_content->fetch(PDO::FETCH_ASSOC);
               $total_content = $content_count['total'];
      ?>
         <div class="content-box">
            <div class="content-header playlist">
               <div class="content-title">
                  <i class="fas fa-folder"></i>
                  <?= htmlspecialchars($fetch_playlist['title']); ?>
               </div>
               <div class="content-meta">
                  <i class="fas fa-calendar"></i> <?= $fetch_playlist['date']; ?> • 
                  <i class="fas fa-file"></i> <?= $total_content; ?> items
               </div>
            </div>
            <div class="content-body">
               <?php if($fetch_playlist['thumb']): ?>
               <img src="../uploaded_files/<?= $fetch_playlist['thumb']; ?>" alt="Folder Thumbnail" class="content-thumb">
               <?php endif; ?>
               
               <div class="content-description">
                  <?= htmlspecialchars($fetch_playlist['description']); ?>
               </div>

               <div class="content-actions">
                  <a href="update_playlist.php?get_id=<?= $playlist_id; ?>" class="option-btn">
                     <i class="fas fa-edit"></i> Update
                  </a>
                  <form action="" method="post" style="flex: 1;">
                     <input type="hidden" name="playlist_id" value="<?= $playlist_id; ?>">
                     <input type="submit" value="Delete" class="delete-btn" onclick="return confirm('Delete this folder and all its contents?');" name="delete_playlist">
                  </form>
                  <a href="view_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">
                     <i class="fas fa-eye"></i> View Folder
                  </a>
               </div>
            </div>
         </div>
      <?php
            }
         }
         
         // Now display standalone content (content not in any playlist)
         if(!empty($existing_playlist_ids)){
            $placeholders = implode(',', array_fill(0, count($existing_playlist_ids), '?'));
            $query = "SELECT * FROM `content` WHERE tutor_id = ? AND (playlist_id IS NULL OR playlist_id = '' OR playlist_id = '0' OR playlist_id NOT IN ($placeholders)) ORDER BY date DESC";
            $params = array_merge([$tutor_id], $existing_playlist_ids);
         } else {
            $query = "SELECT * FROM `content` WHERE tutor_id = ? ORDER BY date DESC";
            $params = [$tutor_id];
         }
         
         $select_standalone_content = $conn->prepare($query);
         $select_standalone_content->execute($params);
         $total_standalone = $select_standalone_content->rowCount();
         
         if($total_standalone > 0){
            while($fetch_content = $select_standalone_content->fetch(PDO::FETCH_ASSOC)){ 
               $content_id = $fetch_content['id'];
               $file_extension = '';
               $icon_class = 'fas fa-file';
               $header_class = 'content-header';
               
               if($fetch_content['video']){
                  $file_extension = strtolower(pathinfo($fetch_content['video'], PATHINFO_EXTENSION));
                  if(in_array($file_extension, ['mp4', 'avi', 'mov', 'wmv', 'flv'])){
                     $icon_class = 'fas fa-video';
                     $header_class = 'content-header video';
                  } elseif(in_array($file_extension, ['pdf'])){
                     $icon_class = 'fas fa-file-pdf';
                     $header_class = 'content-header pdf';
                  } elseif(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])){
                     $icon_class = 'fas fa-image';
                     $header_class = 'content-header image';
                  } elseif(in_array($file_extension, ['mp3', 'wav', 'ogg'])){
                     $icon_class = 'fas fa-music';
                     $header_class = 'content-header';
                  } elseif(in_array($file_extension, ['doc', 'docx', 'txt'])){  
                     $icon_class = 'fas fa-file-word';
                     $header_class = 'content-header';
                  }
               }
      ?>
         <div class="content-box">
            <div class="<?= $header_class; ?>">
               <div class="content-title">
                  <i class="<?= $icon_class; ?>"></i>
                  <?= htmlspecialchars($fetch_content['title']); ?>
               </div>
               <div class="content-meta">
                  <i class="fas fa-calendar"></i> <?= $fetch_content['date']; ?>
               </div>
            </div>
            <div class="content-body">
               <?php if($fetch_content['thumb']): ?>
               <img src="../uploaded_files/<?= $fetch_content['thumb']; ?>" alt="Content Thumbnail" class="content-thumb">
               <?php endif; ?>
               
               <div class="content-description">
                  <?= htmlspecialchars($fetch_content['description']); ?>
               </div>

               <div class="content-actions">
                  <a href="update_content.php?get_id=<?= $content_id; ?>" class="option-btn">
                     <i class="fas fa-edit"></i> Update
                  </a>
                  <form action="" method="post" style="flex: 1;">
                     <input type="hidden" name="video_id" value="<?= $content_id; ?>">
                     <input type="submit" value="Delete" class="delete-btn" onclick="return confirm('Delete this content?');" name="delete_video">
                  </form>
                  <a href="view_content.php?get_id=<?= $content_id; ?>" class="btn">
                     <i class="fas fa-eye"></i> View
                  </a>
               </div>
            </div>
         </div>
      <?php
            }
         }
         if($total_playlists == 0 && $total_standalone == 0){
      ?>
         <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <p>No contents added yet!</p>
            <p>Start by creating your first Folder or adding some content.</p>
         </div>
      <?php
         }
      ?>
   </div>
</section>
<?php include '../components/footer.php'; ?>
<script src="../js/admin_script.js"></script>

</body>
</html>