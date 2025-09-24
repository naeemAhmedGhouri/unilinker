<?php
// Set higher memory limit and execution time for large file uploads
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

include '../components/connect.php';

// Initialize message array
$message = [];

// Debugging
if(isset($message) && !is_array($message)) {
    error_log('Warning: $message is not an array. Type: ' . gettype($message));
    $message = []; // Reset to array if it's not one
}

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:../login1.php');
   exit();
}

// Handle new playlist creation via AJAX
if(isset($_POST['create_playlist']) && isset($_POST['playlist_name'])) {
    $playlist_id = unique_id();
    $playlist_name = $_POST['playlist_name'];
    $playlist_name = filter_var($playlist_name, FILTER_SANITIZE_STRING);
    
    if(!empty($playlist_name)) {
        try {
            // Create a default thumbnail for the playlist
            $default_thumb = 'default_playlist.jpg'; // You can create a default image
            
            $add_playlist = $conn->prepare("INSERT INTO `playlist`(id, tutor_id, title, description, thumb, status, date) VALUES(?,?,?,?,?,?, NOW())");
            $result = $add_playlist->execute([$playlist_id, $tutor_id, $playlist_name, 'Auto-created Folder', $default_thumb, 'active']);
            
            if($result) {
                echo json_encode(['success' => true, 'playlist_id' => $playlist_id, 'playlist_name' => $playlist_name]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create Folder']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Folder name cannot be empty']);
    }
    exit();
}

if(isset($_POST['submit'])){

   $id = unique_id();
   $status = $_POST['status'];
   $status = filter_var($status, FILTER_SANITIZE_STRING);
   $title = $_POST['title'];
   $title = filter_var($title, FILTER_SANITIZE_STRING);
   $description = $_POST['description'];
   $description = filter_var($description, FILTER_SANITIZE_STRING);
   $playlist = $_POST['playlist'] ?? '';
   $playlist = filter_var($playlist, FILTER_SANITIZE_STRING);

   $thumb = $_FILES['thumb']['name'];
   $thumb = filter_var($thumb, FILTER_SANITIZE_STRING);
   $thumb_ext = pathinfo($thumb, PATHINFO_EXTENSION);
   $rename_thumb = unique_id().'.'.$thumb_ext;
   $thumb_size = $_FILES['thumb']['size'];
   $thumb_tmp_name = $_FILES['thumb']['tmp_name'];
   $thumb_folder = '../uploaded_files/'.$rename_thumb;

   $video = $_FILES['video']['name'];
   $video = filter_var($video, FILTER_SANITIZE_STRING);
   $video_ext = pathinfo($video, PATHINFO_EXTENSION);
   $rename_video = unique_id().'.'.$video_ext;
   $video_size = $_FILES['video']['size'];
   $video_tmp_name = $_FILES['video']['tmp_name'];
   $video_folder = '../uploaded_files/'.$rename_video;

   // Check for upload errors
   if($_FILES['thumb']['error'] !== UPLOAD_ERR_OK) {
      $message[] = 'Error uploading thumbnail: ' . getUploadErrorMessage($_FILES['thumb']['error']);
   }
   if($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
      $message[] = 'Error uploading video: ' . getUploadErrorMessage($_FILES['video']['error']);
   }

   // Check file sizes (5MB for thumbnail, 500MB for video)
   if($thumb_size > 5242880){ // 5MB in bytes (corrected calculation)
      $message[] = 'Thumbnail size is too large! Maximum size is 5MB';
   }
   if($video_size > 524288000){ // 500MB in bytes
      $message[] = 'Video size is too large! Maximum size is 500MB';
   }

   // Check if upload directory exists and is writable
   if(!is_dir('../uploaded_files')) {
      mkdir('../uploaded_files', 0777, true);
   }
   if(!is_writable('../uploaded_files')) {
      $message[] = 'Upload directory is not writable!';
   }

   // If no errors, proceed with upload
   if(empty($message)) {
      try {
         // First upload the files
         if(move_uploaded_file($thumb_tmp_name, $thumb_folder) && move_uploaded_file($video_tmp_name, $video_folder)) {
            // Then insert into database - Fixed: Added proper date format
            $add_content = $conn->prepare("INSERT INTO `content`(id, tutor_id, playlist_id, title, description, video, thumb, status, date) VALUES(?,?,?,?,?,?,?,?, NOW())");
            $result = $add_content->execute([$id, $tutor_id, $playlist, $title, $description, $rename_video, $rename_thumb, $status]);
            
            // Check if database insertion was successful
            if($result) {
               $message[] = 'Content uploaded successfully!';
               // Redirect to contents.php after successful upload
               header("Location: contents.php");
               exit();
            } else {
               $message[] = 'Database insertion failed!';
               // Remove uploaded files if database insertion fails
               if(file_exists($thumb_folder)) unlink($thumb_folder);
               if(file_exists($video_folder)) unlink($video_folder);
            }
         } else {
            $message[] = 'Failed to upload files! Please try again.';
         }
      } catch(PDOException $e) {
         $message[] = 'Database error: ' . $e->getMessage();
         // Remove uploaded files if database insertion fails
         if(file_exists($thumb_folder)) unlink($thumb_folder);
         if(file_exists($video_folder)) unlink($video_folder);
      }
   }
}

// Helper function to get upload error messages
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Upload Content</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
   /* Modal styles */
   .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
   }

   .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 300px;
      border-radius: 5px;
      text-align: center;
   }

   .modal-content h3 {
      margin-bottom: 15px;
      color: #333;
   }

   .modal-content input[type="text"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 3px;
      box-sizing: border-box;
   }

   .modal-buttons {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 15px;
   }

   .modal-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      font-size: 14px;
   }

   .modal-btn.create {
      background-color: #4CAF50;
      color: white;
   }

   .modal-btn.cancel {
      background-color: #f44336;
      color: white;
   }

   .modal-btn:hover {
      opacity: 0.8;
   }

   .create-playlist-option {
      color: #007bff;
      font-weight: bold;
   }
   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>
   
<section class="video-form">

   <h1 class="heading">upload content</h1>

   <?php 
   if(!empty($message) && is_array($message)): 
      foreach($message as $msg): 
   ?>
      <div class="message">
         <span><?= $msg; ?></span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
   <?php 
      endforeach; 
   endif; 
   ?>

   <form action="" method="post" enctype="multipart/form-data">
      <p>content status <span>*</span></p>
      <select name="status" class="box" required>
         <option value="" selected disabled>-- select status</option>
         <option value="active">active</option>
         <option value="deactive">deactive</option>
      </select>
      <p>content title <span>*</span></p>
      <input type="text" name="title" maxlength="100" required placeholder="enter content title" class="box">
      <p>content description <span>*</span></p>
      <textarea name="description" class="box" required placeholder="write description" maxlength="1000" cols="30" rows="10"></textarea>
      <p>content Folder</p>
      <select name="playlist" class="box" id="playlistSelect">
         <option value="">--select Folder(optional)</option>
         <option value="create_new" class="create-playlist-option">+ Create New Folder</option>
         <?php
         $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ?");
         $select_playlists->execute([$tutor_id]);
         if($select_playlists->rowCount() > 0){
            while($fetch_playlist = $select_playlists->fetch(PDO::FETCH_ASSOC)){
         ?>
         <option value="<?= $fetch_playlist['id']; ?>"><?= $fetch_playlist['title']; ?></option>
         <?php
            }
         }
         ?>
      </select>
      <p>select thumbnail <span>*</span> <small>(max size: 5MB)</small></p>
      <input type="file" name="thumb" accept="image/*" required class="box">
      <p>select content file <span>*</span> <small>(max size: 500MB - video/pdf/ppt)</small></p>
      <input type="file" name="video" accept="video/*,.pdf,.ppt,.pptx,.docx" required class="box">
      <input type="submit" value="upload content" name="submit" class="btn">
   </form>

</section>

<!-- Modal for creating new playlist -->
<div id="createPlaylistModal" class="modal">
   <div class="modal-content">
      <h3>Create New Folder</h3>
      <input type="text" id="playlistNameInput" placeholder="Enter Folder name" maxlength="100">
      <div class="modal-buttons">
         <button type="button" class="modal-btn create" onclick="createPlaylist()">Create</button>
         <button type="button" class="modal-btn cancel" onclick="closeModal()">Cancel</button>
      </div>
   </div>
</div>

<?php include '../components/footer.php'; ?>

<script src="../js/admin_script.js"></script>

<script>

document.getElementById('playlistSelect').addEventListener('change', function() {
   if (this.value === 'create_new') {
      document.getElementById('createPlaylistModal').style.display = 'block';
      document.getElementById('playlistNameInput').focus();
    
      this.value = '';
   }
});


function closeModal() {
   document.getElementById('createPlaylistModal').style.display = 'none';
   document.getElementById('playlistNameInput').value = '';
}


function createPlaylist() {
   const playlistName = document.getElementById('playlistNameInput').value.trim();
   
   if (playlistName === '') {
      alert('Please enter a Folder name');
      return;
   }

   const formData = new FormData();
   formData.append('create_playlist', '1');
   formData.append('playlist_name', playlistName);


   fetch(window.location.href, {
      method: 'POST',
      body: formData
   })
   .then(response => response.json())
   .then(data => {
      if (data.success) {
      
         const select = document.getElementById('playlistSelect');
         const newOption = document.createElement('option');
         newOption.value = data.playlist_id;
         newOption.textContent = data.playlist_name;
         newOption.selected = true;
         
        
         const createOption = select.querySelector('option[value="create_new"]');
         select.insertBefore(newOption, createOption);
         
         // Close modal
         closeModal();
         
         // Show success message
         showMessage('Playlist created successfully!', 'success');
      } else {
         alert('Error: ' + data.message);
      }
   })
   .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while creating the Folder');
   });
}


document.getElementById('playlistNameInput').addEventListener('keypress', function(e) {
   if (e.key === 'Enter') {
      createPlaylist();
   }
});


window.onclick = function(event) {
   const modal = document.getElementById('createPlaylistModal');
   if (event.target === modal) {
      closeModal();
   }
}

// Function to show messages
function showMessage(message, type) {
   const messageDiv = document.createElement('div');
   messageDiv.className = 'message';
   messageDiv.innerHTML = `
      <span>${message}</span>
      <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
   `;
   
 
   const heading = document.querySelector('.heading');
   heading.insertAdjacentElement('afterend', messageDiv);
   
  
   setTimeout(() => {
      if (messageDiv.parentNode) {
         messageDiv.remove();
      }
   }, 3000);
}
</script>

</body>
</html>