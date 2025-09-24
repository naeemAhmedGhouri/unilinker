<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:home.php');
}

if(isset($_POST['save_list'])){

   if($user_id != ''){
      
      $list_id = $_POST['list_id'];
      $list_id = filter_var($list_id, FILTER_SANITIZE_STRING);

      $select_list = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
      $select_list->execute([$user_id, $list_id]);

      if($select_list->rowCount() > 0){
         $remove_bookmark = $conn->prepare("DELETE FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
         $remove_bookmark->execute([$user_id, $list_id]);
         $message[] = 'Folder removed!';
      }else{
         $insert_bookmark = $conn->prepare("INSERT INTO `bookmark`(user_id, playlist_id) VALUES(?,?)");
         $insert_bookmark->execute([$user_id, $list_id]);
         $message[] = 'Folder saved!';
      }

   }else{
      $message[] = 'please login first!';
   }

}

// Function to get file type and appropriate icon
function getFileTypeInfo($filename) {
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $file_types = [
        // Video files
        'mp4' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        'avi' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        'mov' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        'wmv' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        'flv' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        'webm' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        'mkv' => ['type' => 'video', 'icon' => 'fas fa-play', 'class' => 'video-file'],
        
        // Document files
        'pdf' => ['type' => 'document', 'icon' => 'fas fa-file-pdf', 'class' => 'pdf-file'],
        'doc' => ['type' => 'document', 'icon' => 'fas fa-file-word', 'class' => 'doc-file'],
        'docx' => ['type' => 'document', 'icon' => 'fas fa-file-word', 'class' => 'doc-file'],
        'ppt' => ['type' => 'document', 'icon' => 'fas fa-file-powerpoint', 'class' => 'ppt-file'],
        'pptx' => ['type' => 'document', 'icon' => 'fas fa-file-powerpoint', 'class' => 'ppt-file'],
        'xls' => ['type' => 'document', 'icon' => 'fas fa-file-excel', 'class' => 'excel-file'],
        'xlsx' => ['type' => 'document', 'icon' => 'fas fa-file-excel', 'class' => 'excel-file'],
        'txt' => ['type' => 'document', 'icon' => 'fas fa-file-alt', 'class' => 'text-file'],
        
        // Image files
        'jpg' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        'jpeg' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        'png' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        'gif' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        'bmp' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        'svg' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        'webp' => ['type' => 'image', 'icon' => 'fas fa-image', 'class' => 'image-file'],
        
        
        // Archive files
        'zip' => ['type' => 'archive', 'icon' => 'fas fa-file-archive', 'class' => 'archive-file'],
        'rar' => ['type' => 'archive', 'icon' => 'fas fa-file-archive', 'class' => 'archive-file'],
        '7z' => ['type' => 'archive', 'icon' => 'fas fa-file-archive', 'class' => 'archive-file'],
    ];
    
    return $file_types[$file_extension] ?? ['type' => 'unknown', 'icon' => 'fas fa-file', 'class' => 'unknown-file'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Folder</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   /* Additional styles for different file types */
   .file-type-indicator {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0,0,0,0.7);
      color: white;
      padding: 5px 8px;
      border-radius: 15px;
      font-size: 12px;
      text-transform: uppercase;
   }
   
   .video-file .file-type-indicator { background:#8E44AD; }
   .pdf-file .file-type-indicator { background:rgb(142, 81, 169) }
   .doc-file .file-type-indicator { background:#8E44AD; }
   .ppt-file .file-type-indicator { background:rgb(13, 41, 76); }
   .excel-file .file-type-indicator { background: #27ae60; }
   .image-file .file-type-indicator { background:rgb(83, 41, 99); }
   .audio-file .file-type-indicator { background: #f39c12; }
   .archive-file .file-type-indicator { background: #34495e; }
   
   .box {
      position: relative;
   }
   
   .file-info {
      margin-top: 10px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 5px;
   }
   
   .file-size, .file-type {
      font-size: 12px;
      color: #666;
      margin: 2px 0;
   }
   </style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- playlist section starts  -->

<section class="playlist">

   <h1 class="heading">Folder details</h1>

   <div class="row">

      <?php
         $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? and status = ? LIMIT 1");
         $select_playlist->execute([$get_id, 'active']);
         if($select_playlist->rowCount() > 0){
            $fetch_playlist = $select_playlist->fetch(PDO::FETCH_ASSOC);

            $playlist_id = $fetch_playlist['id'];

            $count_content = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
            $count_content->execute([$playlist_id]);
            $total_content = $count_content->rowCount();

            // Count different file types
            $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? AND (video LIKE '%.mp4' OR video LIKE '%.avi' OR video LIKE '%.mov' OR video LIKE '%.wmv' OR video LIKE '%.flv' OR video LIKE '%.webm' OR video LIKE '%.mkv')");
            $count_videos->execute([$playlist_id]);
            $total_videos = $count_videos->rowCount();

            $count_documents = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? AND (video LIKE '%.pdf' OR video LIKE '%.doc' OR video LIKE '%.docx' OR video LIKE '%.ppt' OR video LIKE '%.pptx' OR video LIKE '%.xls' OR video LIKE '%.xlsx' OR video LIKE '%.txt')");
            $count_documents->execute([$playlist_id]);
            $total_documents = $count_documents->rowCount();

            $count_images = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? AND (video LIKE '%.jpg' OR video LIKE '%.jpeg' OR video LIKE '%.png' OR video LIKE '%.gif' OR video LIKE '%.bmp' OR video LIKE '%.svg' OR video LIKE '%.webp')");
            $count_images->execute([$playlist_id]);
            $total_images = $count_images->rowCount();

            $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
            $select_tutor->execute([$fetch_playlist['tutor_id']]);
            $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);

            $select_bookmark = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
            $select_bookmark->execute([$user_id, $playlist_id]);

      ?>

      <div class="col">
         <form action="" method="post" class="save-list">
            <input type="hidden" name="list_id" value="<?= $playlist_id; ?>">
            <?php
               if($select_bookmark->rowCount() > 0){
            ?>
            <button type="submit" name="save_list"><i class="fas fa-bookmark"></i><span>saved</span></button>
            <?php
               }else{
            ?>
               <button type="submit" name="save_list"><i class="far fa-bookmark"></i><span>save Folder</span></button>
            <?php
               }
            ?>
         </form>
         <div class="thumb">
            <span><?= $total_content; ?> files</span>
            <div class="file-stats" style="font-size: 12px; margin-top: 5px;">
               <?php if($total_videos > 0): ?>
                  <div><i class="fas fa-play"></i> <?= $total_videos; ?> videos</div>
               <?php endif; ?>
               <?php if($total_documents > 0): ?>
                  <div><i class="fas fa-file-alt"></i> <?= $total_documents; ?> documents</div>
               <?php endif; ?>
               <?php if($total_images > 0): ?>
                  <div><i class="fas fa-image"></i> <?= $total_images; ?> images</div>
               <?php endif; ?>
            </div>
            <img src="uploaded_files/<?= $fetch_playlist['thumb']; ?>" alt="">
         </div>
      </div>

      <div class="col">
         <div class="tutor">
            <img src="uploaded_files/<?= $fetch_tutor['image']; ?>" alt="">
            <div>
               <h3><?= $fetch_tutor['name']; ?></h3>
               <span><?= $fetch_tutor['profession']; ?></span>
            </div>
         </div>
         <div class="details">
            <h3><?= $fetch_playlist['title']; ?></h3>
            <p><?= $fetch_playlist['description']; ?></p>
            <div class="date"><i class="fas fa-calendar"></i><span><?= $fetch_playlist['date']; ?></span></div>
         </div>
      </div>

      <?php
         }else{
            echo '<p class="empty">this Folder was not found!</p>';
         }  
      ?>

   </div>

</section>

<!-- playlist section ends -->

<!-- content container section starts  -->
<section class="videos-container">

   <h1 class="heading">Folder Contents</h1>

   <!-- Filter buttons -->
   <div class="filter-buttons" style="margin-bottom: 20px; text-align: center;">
      <button class="filter-btn active" data-filter="all">All Files</button>
      <button class="filter-btn" data-filter="video">Videos</button>
      <button class="filter-btn" data-filter="document">Documents</button>
      <button class="filter-btn" data-filter="image">Images</button>
   </div>

   <div class="box-container">

      <?php
         $select_content = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? AND status = ? ORDER BY date DESC");
         $select_content->execute([$get_id, 'active']);
         if($select_content->rowCount() > 0){
            while($fetch_content = $select_content->fetch(PDO::FETCH_ASSOC)){
               $file_path = "uploaded_files/" . $fetch_content['video'];
               $file_info = getFileTypeInfo($fetch_content['video']);
               
               // Get file size if possible
               $file_size = file_exists($file_path) ? formatBytes(filesize($file_path)) : 'Unknown';
      ?>
      <a href="watch_video.php?get_id=<?= $fetch_content['id']; ?>" class="box <?= $file_info['class']; ?>" data-type="<?= $file_info['type']; ?>">
         <i class="<?= $file_info['icon']; ?>"></i>
         <div class="file-type-indicator"><?= strtoupper($file_info['type']); ?></div>
         <img src="uploaded_files/<?= $fetch_content['thumb']; ?>" alt="">
         <h3><?= $fetch_content['title']; ?></h3>
         <div class="file-info">
            <div class="file-type">Type: <?= ucfirst($file_info['type']); ?></div>
            <div class="file-size">Size: <?= $file_size; ?></div>
         </div>
      </a>
      <?php
            }
         }else{
            echo '<p class="empty">No content added yet!</p>';
         }
         
         // Function to format file size
         function formatBytes($size, $precision = 2) {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            
            for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
               $size /= 1024;
            }
            
            return round($size, $precision) . ' ' . $units[$i];
         }
      ?>

   </div>

</section>

<!-- content container section ends -->

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const boxes = document.querySelectorAll('.box');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filterType = this.getAttribute('data-filter');
            
            boxes.forEach(box => {
                if (filterType === 'all' || box.getAttribute('data-type') === filterType) {
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';
                }
            });
        });
    });
});
</script>

<style>
/* Filter button styles */
.filter-buttons {
    margin: 20px 0;
}

.filter-btn {
    background: #f8f9fa;
    border: 2px solid #ddd;
    color: #666;
    padding: 10px 20px;
    margin: 0 5px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-btn:hover, .filter-btn.active {
    background:#8E44AD;
    color: white;
    border-color:1 px solid black;
}
</style>
   
</body>
</html>