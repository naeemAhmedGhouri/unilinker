<?php

include '../components/connect.php';

// Initialize message array
$message = array();

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:dashboard.php');
}

// Function to get file type based on extension
function getFileType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $video_exts = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
    $doc_exts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'rtf'];
    $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    
    if (in_array($ext, $video_exts)) return 'video';
    if (in_array($ext, $doc_exts)) return 'document';
    if (in_array($ext, $image_exts)) return 'image';
    return 'other';
}

// Function to validate file type
function isValidFileType($filename) {
    $allowed_exts = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'rtf'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed_exts);
}

if(isset($_POST['update'])){

   $content_id = $_POST['content_id'];
   $content_id = filter_var($content_id, FILTER_SANITIZE_STRING);
   $status = $_POST['status'];
   $status = filter_var($status, FILTER_SANITIZE_STRING);
   $title = $_POST['title'];
   $title = filter_var($title, FILTER_SANITIZE_STRING);
   $description = $_POST['description'];
   $description = filter_var($description, FILTER_SANITIZE_STRING);
   $playlist = $_POST['playlist'];
   $playlist = filter_var($playlist, FILTER_SANITIZE_STRING);

   // Flag to track if update was successful
   $update_successful = true;

   // Update basic content information
   $update_content = $conn->prepare("UPDATE `content` SET title = ?, description = ?, status = ? WHERE id = ?");
   $update_content->execute([$title, $description, $status, $content_id]);

   // Update playlist if provided
   if(!empty($playlist)){
      $update_playlist = $conn->prepare("UPDATE `content` SET playlist_id = ? WHERE id = ?");
      $update_playlist->execute([$playlist, $content_id]);
   }

   // Handle thumbnail update
   $old_thumb = $_POST['old_thumb'];
   $old_thumb = filter_var($old_thumb, FILTER_SANITIZE_STRING);
   $thumb = $_FILES['thumb']['name'];
   $thumb = filter_var($thumb, FILTER_SANITIZE_STRING);
   
   if(!empty($thumb)){
      $thumb_ext = pathinfo($thumb, PATHINFO_EXTENSION);
      $rename_thumb = unique_id().'.'.$thumb_ext;
      $thumb_size = $_FILES['thumb']['size'];
      $thumb_tmp_name = $_FILES['thumb']['tmp_name'];
      $thumb_folder = '../uploaded_files/'.$rename_thumb;

      if($thumb_size > 2000000){
         $message[] = 'Thumbnail size is too large! (Max: 2MB)';
         $update_successful = false;
      }else{
         $update_thumb = $conn->prepare("UPDATE `content` SET thumb = ? WHERE id = ?");
         $update_thumb->execute([$rename_thumb, $content_id]);
         move_uploaded_file($thumb_tmp_name, $thumb_folder);
         if($old_thumb != '' AND $old_thumb != $rename_thumb){
            unlink('../uploaded_files/'.$old_thumb);
         }
      }
   }

   // Handle main file update (video/document)
   $old_file = $_POST['old_file'];
   $old_file = filter_var($old_file, FILTER_SANITIZE_STRING);
   $main_file = $_FILES['main_file']['name'];
   $main_file = filter_var($main_file, FILTER_SANITIZE_STRING);

   if(!empty($main_file)){
      if(!isValidFileType($main_file)){
         $message[] = 'Invalid file type! Supported: MP4, AVI, MOV, PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT';
         $update_successful = false;
      }else{
         $file_ext = pathinfo($main_file, PATHINFO_EXTENSION);
         $rename_file = unique_id().'.'.$file_ext;
         $file_tmp_name = $_FILES['main_file']['tmp_name'];
         $file_folder = '../uploaded_files/'.$rename_file;
         $file_size = $_FILES['main_file']['size'];
         
         // Set file size limits based on type
         $max_size = 50000000; // 50MB default
         $file_type = getFileType($main_file);
         
         if($file_type == 'video'){
            $max_size = 100000000; // 100MB for videos
         }elseif($file_type == 'document'){
            $max_size = 25000000; // 25MB for documents
         }
         
         if($file_size > $max_size){
            $message[] = 'File size is too large! Max allowed: ' . ($max_size / 1000000) . 'MB';
            $update_successful = false;
         }else{
           $update_file = $conn->prepare("UPDATE `content` SET video = ? WHERE id = ?");
$update_file->execute([$rename_file, $content_id]);
            move_uploaded_file($file_tmp_name, $file_folder);
            
            // Remove old file if it exists and is different
            if($old_file != '' AND $old_file != $rename_file){
               unlink('../uploaded_files/'.$old_file);
            }
         }
      }
   }

   // If update was successful, redirect to view_content.php
   if($update_successful){
      header('location:view_content.php?get_id='.$content_id);
      exit();
   }else{
      $message[] = 'Content update failed due to errors above.';
   }
}

if(isset($_POST['delete_content'])){

   $delete_id = $_POST['content_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   // Delete thumbnail file
   $delete_thumb = $conn->prepare("SELECT thumb FROM `content` WHERE id = ? LIMIT 1");
   $delete_thumb->execute([$delete_id]);
   $fetch_thumb = $delete_thumb->fetch(PDO::FETCH_ASSOC);
   if($fetch_thumb['thumb'] && file_exists('../uploaded_files/'.$fetch_thumb['thumb'])){
      unlink('../uploaded_files/'.$fetch_thumb['thumb']);
   }

   // Delete main file (video/document)
   $delete_file = $conn->prepare("SELECT video FROM `content` WHERE id = ? LIMIT 1");
   $delete_file->execute([$delete_id]);
   $fetch_file = $delete_file->fetch(PDO::FETCH_ASSOC);
   if($fetch_file['video'] && file_exists('../uploaded_files/'.$fetch_file['video'])){
      unlink('../uploaded_files/'.$fetch_file['video']);
   }

   // Delete related records
   $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
   $delete_likes->execute([$delete_id]);
   $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
   $delete_comments->execute([$delete_id]);

   // Delete content record
   $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
   $delete_content->execute([$delete_id]);
   header('location:contents.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Content</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      .file-preview {
         margin: 1rem 0;
         padding: 1rem;
         border: 1px solid #ddd;
         border-radius: 0.5rem;
         background-color: #f9f9f9;
      }
      .file-preview h4 {
         margin: 0 0 0.5rem 0;
         color: #333;
      }
      .file-icon {
         font-size: 3rem;
         margin: 1rem 0;
         text-align: center;
      }
      .pdf-icon { color: #d32f2f; }
      .doc-icon { color: #1976d2; }
      .ppt-icon { color: #d84315; }
      .xls-icon { color: #388e3c; }
      .txt-icon { color: #616161; }
      .video-icon { color: #7b1fa2; }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>
   
<section class="video-form">

   <h1 class="heading">Update Content</h1>

   <?php
   if(isset($message) && is_array($message) && count($message) > 0){
      foreach($message as $msg){
         echo '<p class="message">'.$msg.'</p>';
      }
   }
   ?>

   <?php
      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ?");
      $select_content->execute([$get_id, $tutor_id]);
      if($select_content->rowCount() > 0){
         while($fetch_content = $select_content->fetch(PDO::FETCH_ASSOC)){ 
            $content_id = $fetch_content['id'];
            $content_type = isset($fetch_content['content_type']) ? $fetch_content['content_type'] : 'video';
            $file_ext = pathinfo($fetch_content['video'], PATHINFO_EXTENSION);
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="content_id" value="<?= $fetch_content['id']; ?>">
      <input type="hidden" name="old_thumb" value="<?= $fetch_content['thumb']; ?>">
      <input type="hidden" name="old_file" value="<?= $fetch_content['video']; ?>">
      
      <p>Update Status <span>*</span></p>
      <select name="status" class="box" required>
         <option value="<?= $fetch_content['status']; ?>" selected><?= $fetch_content['status']; ?></option>
         <option value="active">Active</option>
         <option value="deactive">Deactive</option>
      </select>
      
      <p>Update Title <span>*</span></p>
      <input type="text" name="title" maxlength="100" required placeholder="Enter content title" class="box" value="<?= $fetch_content['title']; ?>">
      
      <p>Update Description <span>*</span></p>
      <textarea name="description" class="box" required placeholder="Write description" maxlength="1000" cols="30" rows="10"><?= $fetch_content['description']; ?></textarea>
      
      <p>Update Folder</p>
      <select name="playlist" class="box">
         <option value="<?= $fetch_content['playlist_id']; ?>" selected>--Select Folder--</option>
         <?php
         $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ?");
         $select_playlists->execute([$tutor_id]);
         if($select_playlists->rowCount() > 0){
            while($fetch_playlist = $select_playlists->fetch(PDO::FETCH_ASSOC)){
         ?>
         <option value="<?= $fetch_playlist['id']; ?>"><?= $fetch_playlist['title']; ?></option>
         <?php
            }
         }else{
            echo '<option value="" disabled>No Folder created yet!</option>';
         }
         ?>
      </select>
      
      <!-- Thumbnail Section -->
      <?php if($fetch_content['thumb']): ?>
      <img src="../uploaded_files/<?= $fetch_content['thumb']; ?>" alt="Thumbnail" style="max-width: 200px; margin: 1rem 0;">
      <?php endif; ?>
      <p>Update Thumbnail</p>
      <input type="file" name="thumb" accept="image/*" class="box">
      
      <!-- Current File Preview -->
      <div class="file-preview">
         <h4>Current File: <?= $fetch_content['video']; ?></h4>
         <?php
         $current_file_type = getFileType($fetch_content['video']);
         if($current_file_type == 'video'):
         ?>
            <video src="../uploaded_files/<?= $fetch_content['video']; ?>" controls style="max-width: 100%; height: auto;"></video>
         <?php elseif($current_file_type == 'document'): ?>
            <div class="file-icon">
               <?php
               switch(strtolower($file_ext)){
                  case 'pdf':
                     echo '<i class="fas fa-file-pdf pdf-icon"></i>';
                     break;
                  case 'doc':
                  case 'docx':
                     echo '<i class="fas fa-file-word doc-icon"></i>';
                     break;
                  case 'ppt':
                  case 'pptx':
                     echo '<i class="fas fa-file-powerpoint ppt-icon"></i>';
                     break;
                  case 'xls':
                  case 'xlsx':
                     echo '<i class="fas fa-file-excel xls-icon"></i>';
                     break;
                  case 'txt':
                  case 'rtf':
                     echo '<i class="fas fa-file-alt txt-icon"></i>';
                     break;
                  default:
                     echo '<i class="fas fa-file"></i>';
               }
               ?>
            </div>
            <p><strong>File Type:</strong> <?= strtoupper($file_ext); ?> Document</p>
            <p><a href="../uploaded_files/<?= $fetch_content['video']; ?>" target="_blank" class="btn">View/Download File</a></p>
         <?php endif; ?>
      </div>
      
      <p>Update File (Video/Document)</p>
      <input type="file" name="main_file" accept="video/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.rtf" class="box">
      <small style="color: #666; display: block; margin-top: 0.5rem;">
         Supported formats: MP4, AVI, MOV, WMV, FLV, WebM, MKV (videos) | PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, RTF (documents)
      </small>
      
      <input type="submit" value="Update Content" name="update" class="btn">
      <div class="flex-btn">
         <a href="view_content.php?get_id=<?= $content_id; ?>" class="option-btn">View Content</a>
         <input type="submit" value="Delete Content" name="delete_content" class="delete-btn" onclick="return confirm('Are you sure you want to delete this content?');">
      </div>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">Content not found! <a href="add_content.php" class="btn" style="margin-top: 1.5rem;">Add Content</a></p>';
      }
   ?>

</section>

<?php include '../components/footer.php'; ?>

<script src="../js/admin_script.js"></script>

</body>
</html>