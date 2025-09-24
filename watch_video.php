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

if(isset($_POST['like_content'])){

   if($user_id != ''){

      $content_id = $_POST['content_id'];
      $content_id = filter_var($content_id, FILTER_SANITIZE_STRING);

      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $select_content->execute([$content_id]);
      $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);

      $tutor_id = $fetch_content['tutor_id'];

      $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
      $select_likes->execute([$user_id, $content_id]);

      if($select_likes->rowCount() > 0){
         $remove_likes = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
         $remove_likes->execute([$user_id, $content_id]);
         $message[] = 'removed from likes!';
      }else{
         $insert_likes = $conn->prepare("INSERT INTO `likes`(user_id, tutor_id, content_id) VALUES(?,?,?)");
         $insert_likes->execute([$user_id, $tutor_id, $content_id]);
         $message[] = 'added to likes!';
      }

   }else{
      $message[] = 'please login first!';
   }

}

if(isset($_POST['add_comment'])){

   if($user_id != ''){

      $id = unique_id();
      $comment_box = $_POST['comment_box'];
      $comment_box = filter_var($comment_box, FILTER_SANITIZE_STRING);
      $content_id = $_POST['content_id'];
      $content_id = filter_var($content_id, FILTER_SANITIZE_STRING);

      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $select_content->execute([$content_id]);
      $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);

      $tutor_id = $fetch_content['tutor_id'];

      if($select_content->rowCount() > 0){

         $select_comment = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ? AND user_id = ? AND tutor_id = ? AND comment = ?");
         $select_comment->execute([$content_id, $user_id, $tutor_id, $comment_box]);

         if($select_comment->rowCount() > 0){
            $message[] = 'comment already added!';
         }else{
            $insert_comment = $conn->prepare("INSERT INTO `comments`(id, content_id, user_id, tutor_id, comment) VALUES(?,?,?,?,?)");
            $insert_comment->execute([$id, $content_id, $user_id, $tutor_id, $comment_box]);
            $message[] = 'new comment added!';
         }

      }else{
         $message[] = 'something went wrong!';
      }

   }else{
      $message[] = 'please login first!';
   }

}

if(isset($_POST['delete_comment'])){

   $delete_id = $_POST['comment_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ?");
   $verify_comment->execute([$delete_id]);

   if($verify_comment->rowCount() > 0){
      $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
      $delete_comment->execute([$delete_id]);
      $message[] = 'comment deleted successfully!';
   }else{
      $message[] = 'comment already deleted!';
   }

}

if(isset($_POST['update_now'])){

   $update_id = $_POST['update_id'];
   $update_id = filter_var($update_id, FILTER_SANITIZE_STRING);
   $update_box = $_POST['update_box'];
   $update_box = filter_var($update_box, FILTER_SANITIZE_STRING);

   $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND comment = ?");
   $verify_comment->execute([$update_id, $update_box]);

   if($verify_comment->rowCount() > 0){
      $message[] = 'comment already added!';
   }else{
      $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
      $update_comment->execute([$update_box, $update_id]);
      $message[] = 'comment edited successfully!';
   }

}

// Function to get file type and determine how to display it
function getFileDisplayInfo($filename) {
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $file_types = [
        // Video files
        'mp4' => ['type' => 'video', 'display' => 'video'],
        'avi' => ['type' => 'video', 'display' => 'video'], 
        'mov' => ['type' => 'video', 'display' => 'video'],
        'wmv' => ['type' => 'video', 'display' => 'video'],
        'flv' => ['type' => 'video', 'display' => 'video'],
        'webm' => ['type' => 'video', 'display' => 'video'],
        'mkv' => ['type' => 'video', 'display' => 'video'],
        'ogg' => ['type' => 'video', 'display' => 'video'],
        
        // Document files
        'pdf' => ['type' => 'document', 'display' => 'iframe'],
        'doc' => ['type' => 'document', 'display' => 'download'],
        'docx' => ['type' => 'document', 'display' => 'download'],
        'ppt' => ['type' => 'document', 'display' => 'download'],
        'pptx' => ['type' => 'document', 'display' => 'download'],
        'xls' => ['type' => 'document', 'display' => 'download'],
        'xlsx' => ['type' => 'document', 'display' => 'download'],
        'txt' => ['type' => 'document', 'display' => 'text'],
        
        // Image files
        'jpg' => ['type' => 'image', 'display' => 'image'],
        'jpeg' => ['type' => 'image', 'display' => 'image'],
        'png' => ['type' => 'image', 'display' => 'image'],
        'gif' => ['type' => 'image', 'display' => 'image'],
        'bmp' => ['type' => 'image', 'display' => 'image'],
        'svg' => ['type' => 'image', 'display' => 'image'],
        'webp' => ['type' => 'image', 'display' => 'image'],
        
        // Audio files
        'mp3' => ['type' => 'audio', 'display' => 'audio'],
        'wav' => ['type' => 'audio', 'display' => 'audio'],
        'ogg' => ['type' => 'audio', 'display' => 'audio'],
        'aac' => ['type' => 'audio', 'display' => 'audio'],
        'flac' => ['type' => 'audio', 'display' => 'audio'],
        
        // Archive files
        'zip' => ['type' => 'archive', 'display' => 'download'],
        'rar' => ['type' => 'archive', 'display' => 'download'],
        '7z' => ['type' => 'archive', 'display' => 'download'],
    ];
    
    return $file_types[$file_extension] ?? ['type' => 'unknown', 'display' => 'download'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Content</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   /* Additional styles for different file types */
   .file-viewer {
      width: 100%;
      min-height: 400px;
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
   }
   
   .video {
      width: 100%;
      max-height: 500px;
      border-radius: 10px;
   }
   
   .pdf-viewer {
      width: 100%;
      height: 600px;
      border: none;
      border-radius: 10px;
   }
   
   .image-viewer {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
   }
   
   .audio-player {
      width: 100%;
      margin: 20px 0;
   }
   
   .text-viewer {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #ddd;
      font-family: 'Courier New', monospace;
      white-space: pre-wrap;
      max-height: 500px;
      overflow-y: auto;
   }
   
   .download-section {
      text-align: center;
      padding: 40px;
      background: #f8f9fa;
      border-radius: 10px;
      border: 2px dashed #ddd;
   }
   
   .download-icon {
      font-size: 64px;
      color: #666;
      margin-bottom: 20px;
   }
   
   .download-btn {
      display: inline-block;
      background: #8E44AD;
      color: white;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 25px;
      margin: 10px;
      transition: background 0.3s ease;
   }
   
   .download-btn:hover {
      background:rgb(67, 6, 91);
   }
   
   .file-info-card {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 20px;
   }
   
   .file-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      flex-wrap: wrap;
   }
   
   .file-type-badge {
      background:rgb(147, 43, 192);
      color: white;
      padding: 5px 12px;
      border-radius: 5% !important;
      font-size: 12px;
      text-transform: uppercase;
   }
   
   .file-size {
      color: #666;
      font-size: 14px;
   }
   </style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php
   if(isset($_POST['edit_comment'])){
      $edit_id = $_POST['comment_id'];
      $edit_id = filter_var($edit_id, FILTER_SANITIZE_STRING);
      $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? LIMIT 1");
      $verify_comment->execute([$edit_id]);
      if($verify_comment->rowCount() > 0){
         $fetch_edit_comment = $verify_comment->fetch(PDO::FETCH_ASSOC);
?>
<section class="edit-comment">
   <h1 class="heading">Edit Comment</h1>
   <form action="" method="post">
      <input type="hidden" name="update_id" value="<?= $fetch_edit_comment['id']; ?>">
      <textarea name="update_box" class="box" maxlength="1000" required placeholder="please enter your comment" cols="30" rows="10"><?= $fetch_edit_comment['comment']; ?></textarea>
      <div class="flex">
         <a href="watch_video.php?get_id=<?= $get_id; ?>" class="inline-option-btn">cancel edit</a>
         <input type="submit" value="update now" name="update_now" class="inline-btn">
      </div>
   </form>
</section>
<?php
      }else{
         $message[] = 'comment was not found!';
      }
   }
?>

<!-- watch content section starts  -->

<section class="watch-video">

   <?php
      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND status = ?");
      $select_content->execute([$get_id, 'active']);
      if($select_content->rowCount() > 0){
         while($fetch_content = $select_content->fetch(PDO::FETCH_ASSOC)){
            $content_id = $fetch_content['id'];

            $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE content_id = ?");
            $select_likes->execute([$content_id]);
            $total_likes = $select_likes->rowCount();  

            $verify_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
            $verify_likes->execute([$user_id, $content_id]);

            $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
            $select_tutor->execute([$fetch_content['tutor_id']]);
            $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
            
            // Get file information
            $file_path = "uploaded_files/" . $fetch_content['video'];
            $file_info = getFileDisplayInfo($fetch_content['video']);
            $file_size = file_exists($file_path) ? formatBytes(filesize($file_path)) : 'Unknown';
   ?>
   
   <!-- File Information Card -->
   <div class="file-info-card">
      <div class="file-meta">
         <div>
            <h2><?= $fetch_content['title']; ?></h2>
            <span class="file-type-badge"><?= strtoupper($file_info['type']); ?></span>
         </div>
         <div class="file-size">Size: <?= $file_size; ?></div>
      </div>
   </div>

   <div class="video-details">
      <div class="file-viewer">
         <?php
         // Display content based on file type
         switch($file_info['display']) {
            case 'video':
               echo '<video src="' . $file_path . '" class="video" poster="uploaded_files/' . $fetch_content['thumb'] . '" controls preload="metadata">';
               echo '<source src="' . $file_path . '" type="video/' . pathinfo($file_path, PATHINFO_EXTENSION) . '">';
               echo 'Your browser does not support the video tag.';
               echo '</video>';
               break;
               
            case 'iframe':
               echo '<iframe src="' . $file_path . '" class="pdf-viewer" frameborder="0">';
               echo '<p>Your browser does not support iframes. <a href="' . $file_path . '" target="_blank">Download the file</a></p>';
               echo '</iframe>';
               break;
               
            case 'image':
               echo '<div style="text-align: center;">';
               echo '<img src="' . $file_path . '" class="image-viewer" alt="' . $fetch_content['title'] . '">';
               echo '</div>';
               break;
               
            case 'audio':
               echo '<div style="text-align: center; padding: 40px;">';
               echo '<i class="fas fa-music" style="font-size: 64px; color: #666; margin-bottom: 20px;"></i>';
               echo '<audio controls class="audio-player">';
               echo '<source src="' . $file_path . '" type="audio/' . pathinfo($file_path, PATHINFO_EXTENSION) . '">';
               echo 'Your browser does not support the audio element.';
               echo '</audio>';
               echo '</div>';
               break;
               
            case 'text':
               if(file_exists($file_path)) {
                  $text_content = file_get_contents($file_path);
                  echo '<div class="text-viewer">' . htmlspecialchars($text_content) . '</div>';
               } else {
                  echo '<div class="download-section">';
                  echo '<i class="fas fa-file-alt download-icon"></i>';
                  echo '<p>Text file not found!</p>';
                  echo '</div>';
               }
               break;
               
            case 'download':
            default:
               $icon_class = '';
               switch($file_info['type']) {
                  case 'document':
                     $icon_class = 'fas fa-file-alt';
                     break;
                  case 'archive':
                     $icon_class = 'fas fa-file-archive';
                     break;
                  default:
                     $icon_class = 'fas fa-download';
               }
               
               echo '<div class="download-section">';
               echo '<i class="' . $icon_class . ' download-icon"></i>';
               echo '<h3>Download Required</h3>';
               echo '<p>This file type needs to be downloaded to view properly.</p>';
               echo '<a href="' . $file_path . '" download class="download-btn">';
               echo '<i class="fas fa-download"></i> Download File';
               echo '</a>';
               echo '<a href="' . $file_path . '" target="_blank" class="download-btn">';
               echo '<i class="fas fa-external-link-alt"></i> Open in New Tab';
               echo '</a>';
               echo '</div>';
               break;
         }
         ?>
      </div>

      <div class="tutor">
         <img src="uploaded_files/<?= $fetch_tutor['image']; ?>" alt="">
         <div>
            <h3><?= $fetch_tutor['name']; ?></h3>
            <span><?= $fetch_tutor['profession']; ?></span>
         </div>
      </div>
      
      <form action="" method="post" class="flex">
         <input type="hidden" name="content_id" value="<?= $content_id; ?>">
         <a href="playlist.php?get_id=<?= $fetch_content['playlist_id']; ?>" class="inline-btn">view Folder</a>
         <?php
            if($verify_likes->rowCount() > 0){
         ?>
         <button type="submit" name="like_content"><i class="fas fa-heart"></i><span>liked (<?= $total_likes; ?>)</span></button>
         <?php
         }else{
         ?>
         <button type="submit" name="like_content"><i class="far fa-heart"></i><span>like (<?= $total_likes; ?>)</span></button>
         <?php
            }
         ?>
      </form>
      
      <div class="description"><p><?= $fetch_content['description']; ?></p></div>
   </div>
   
   <?php
         }
      }else{
         echo '<p class="empty">No content found!</p>';
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

</section>

<!-- watch content section ends -->

<!-- comments section starts  -->

<section class="comments">

   <h1 class="heading">add a comment</h1>

   <form action="" method="post" class="add-comment">
      <input type="hidden" name="content_id" value="<?= $get_id; ?>">
      <textarea name="comment_box" required placeholder="write your comment..." maxlength="1000" cols="30" rows="10"></textarea>
      <input type="submit" value="add comment" name="add_comment" class="inline-btn">
   </form>

   <h1 class="heading">user comments</h1>

   <div class="show-comments">
      <?php
         $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ? ORDER BY date DESC");
         $select_comments->execute([$get_id]);
         if($select_comments->rowCount() > 0){
            while($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)){   
               $select_commentor = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
               $select_commentor->execute([$fetch_comment['user_id']]);
               $fetch_commentor = $select_commentor->fetch(PDO::FETCH_ASSOC);
      ?>
      <div class="box" style="<?php if($fetch_comment['user_id'] == $user_id){echo 'order:-1;';} ?>">
         <div class="user">
            <img src="uploaded_files/<?= $fetch_commentor['image']; ?>" alt="">
            <div>
               <h3><?= $fetch_commentor['full_name']; ?></h3>
               <span><?= $fetch_comment['date']; ?></span>
            </div>
         </div>
         <p class="text"><?= $fetch_comment['comment']; ?></p>
         <?php
            if($fetch_comment['user_id'] == $user_id){ 
         ?>
         <form action="" method="post" class="flex-btn">
            <input type="hidden" name="comment_id" value="<?= $fetch_comment['id']; ?>">
            <button type="submit" name="edit_comment" class="inline-option-btn">edit comment</button>
            <button type="submit" name="delete_comment" class="inline-delete-btn" onclick="return confirm('delete this comment?');">delete comment</button>
         </form>
         <?php
         }
         ?>
      </div>
      <?php
       }
      }else{
         echo '<p class="empty">no comments added yet!</p>';
      }
      ?>
   </div>
   
</section>

<!-- comments section ends -->

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
// Additional JavaScript for better user experience
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Image click to fullscreen
    const images = document.querySelectorAll('.image-viewer');
    images.forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', function() {
            const fullscreen = document.createElement('div');
            fullscreen.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                cursor: zoom-out;
            `;
            
            const fullImg = document.createElement('img');
            fullImg.src = this.src;
            fullImg.style.cssText = `
                max-width: 90vw;
                max-height: 90vh;
                object-fit: contain;
            `;
            
            fullscreen.appendChild(fullImg);
            document.body.appendChild(fullscreen);
            
            fullscreen.addEventListener('click', function() {
                document.body.removeChild(fullscreen);
            });
        });
    });
});
</script>
   
</body>
</html>