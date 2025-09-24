<?php

include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

// Fetch tutor name
$tutor_name = '';
if ($tutor_id) {
    $get_tutor = $conn->prepare("SELECT name FROM tutors WHERE id = ?");
    $get_tutor->execute([$tutor_id]);
    if ($get_tutor->rowCount() > 0) {
        $tutor_row = $get_tutor->fetch(PDO::FETCH_ASSOC);
        $tutor_name = $tutor_row['name'];
    }
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:contents.php');
}

if(isset($_POST['delete_content'])){

   $delete_id = $_POST['content_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $delete_content_thumb = $conn->prepare("SELECT thumb FROM `content` WHERE id = ? LIMIT 1");
   $delete_content_thumb->execute([$delete_id]);
   $fetch_thumb = $delete_content_thumb->fetch(PDO::FETCH_ASSOC);
   if($fetch_thumb['thumb'] && file_exists('../uploaded_files/'.$fetch_thumb['thumb'])){
      unlink('../uploaded_files/'.$fetch_thumb['thumb']);
   }

   $delete_content_file = $conn->prepare("SELECT video FROM `content` WHERE id = ? LIMIT 1");
   $delete_content_file->execute([$delete_id]);
   $fetch_file = $delete_content_file->fetch(PDO::FETCH_ASSOC);
   if($fetch_file['video'] && file_exists('../uploaded_files/'.$fetch_file['video'])){
      unlink('../uploaded_files/'.$fetch_file['video']);
   }

   $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
   $delete_likes->execute([$delete_id]);
   $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
   $delete_comments->execute([$delete_id]);

   $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
   $delete_content->execute([$delete_id]);
   header('location:contents.php');
    
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
// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to get file type category
function getFileType($filename) {
    $extension = getFileExtension($filename);
    
    $video_types = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v'];
    $document_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'rtf'];
    $image_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
    $audio_types = ['mp3', 'wav', 'ogg', 'aac', 'm4a'];
    
    if (in_array($extension, $video_types)) {
        return 'video';
    } elseif (in_array($extension, $document_types)) {
        return 'document';
    } elseif (in_array($extension, $image_types)) {
        return 'image';
    } elseif (in_array($extension, $audio_types)) {
        return 'audio';
    } else {
        return 'other';
    }
}

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Content Viewer - Enhanced</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- Google Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
   :root {
      --primary-color: #8E44AD ;
      --secondary-color: #1e40af;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --dark-color: #1f2937;
      --light-color: #f8fafc;
      --border-color: #e5e7eb;
      --text-primary: #111827;
      --text-secondary: #6b7280;
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
   }

   * {
      font-family: 'Inter', sans-serif;
   }

   body {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      min-height: 100vh;
   }

   .view-content {
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
   }

   .content-header {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow);
      border: 1px solid var(--border-color);
   }

   .content-viewer-container {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border-color);
      margin-bottom: 2rem;
   }

   .viewer-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 1.5rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
   }

   .viewer-header h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
   }

   .viewer-controls {
      display: flex;
      gap: 1rem;
      align-items: center;
      flex-wrap: wrap;
   }

   .control-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
   }

   .control-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
   }

   .content-display {
      position: relative;
      background: #f8fafc;
   }

   .video-player {
      width: 100%;
      height: auto;
      max-height: 70vh;
      background: #000;
   }

   .document-viewer {
      width: 100%;
      height: 80vh;
      border: none;
      background: white;
   }

   .document-viewer-office {
      width: 100%;
      height: 80vh;
      border: none;
      background: white;
   }

   .image-viewer {
      width: 100%;
      height: auto;
      max-height: 80vh;
      object-fit: contain;
      background: #f8fafc;
   }

   .audio-player {
      width: 100%;
      padding: 2rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
   }

   .audio-player audio {
      width: 100%;
      max-width: 500px;
   }

   .audio-info {
      color: white;
      text-align: center;
   }

   .file-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 4rem 2rem;
      text-align: center;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      min-height: 400px;
   }

   .file-placeholder i {
      font-size: 5rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
      opacity: 0.8;
   }

   .file-placeholder h3 {
      font-size: 1.5rem;
      color: var(--text-primary);
      margin-bottom: 0.5rem;
   }

   .file-placeholder p {
      color: var(--text-secondary);
      margin-bottom: 2rem;
      font-size: 1rem;
   }

   .file-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      justify-content: center;
   }

   .action-btn {
      background: var(--primary-color);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
   }

   .action-btn:hover {
      background: var(--secondary-color);
      transform: translateY(-2px);
      box-shadow: var(--shadow);
   }

   .action-btn.secondary {
      background: var(--success-color);
   }

   .action-btn.secondary:hover {
      background: #059669;
   }

   .action-btn.danger {
      background: var(--danger-color);
   }

   .action-btn.danger:hover {
      background: #dc2626;
   }

   .content-metadata {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow);
      border: 1px solid var(--border-color);
   }

   .metadata-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
   }

   .metadata-item {
      background: var(--light-color);
      padding: 1.5rem;
      border-radius: 12px;
      border: 1px solid var(--border-color);
   }

   .metadata-item h4 {
      margin: 0 0 0.5rem 0;
      color: var(--text-primary);
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
   }

   .metadata-item p {
      margin: 0;
      color: var(--text-secondary);
      font-size: 0.875rem;
   }

   .metadata-item .value {
      color: var(--text-primary);
      font-weight: 500;
      font-size: 1rem;
   }

   .stats-container {
      display: flex;
      gap: 1rem;
      margin: 1.5rem 0;
      flex-wrap: wrap;
   }

   .stat-badge {
      background: var(--primary-color);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
   }

   .description-section {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow);
      border: 1px solid var(--border-color);
   }

   .description-section h3 {
      color: var(--text-primary);
      margin-bottom: 1rem;
      font-weight: 600;
   }

   .description-text {
      color: var(--text-secondary);
      line-height: 1.6;
      font-size: 1rem;
   }

   .comments-section {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: var(--shadow);
      border: 1px solid var(--border-color);
   }

   .comments-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid var(--border-color);
   }

   .comments-header h1 {
      color: var(--text-primary);
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
   }

   .comment-box {
      background: var(--light-color);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
   }

   .comment-box:hover {
      box-shadow: var(--shadow);
      transform: translateY(-2px);
   }

   .comment-user {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
   }

   .comment-user img {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--border-color);
   }

   .comment-user-info h3 {
      margin: 0;
      color: var(--text-primary);
      font-weight: 600;
      font-size: 1rem;
   }

   .comment-user-info span {
      color: var(--text-secondary);
      font-size: 0.875rem;
   }

   .comment-text {
      color: var(--text-secondary);
      line-height: 1.6;
      margin-bottom: 1rem;
   }

   .comment-actions {
      display: flex;
      justify-content: flex-end;
   }

   .delete-comment-btn {
      background: var(--danger-color);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.875rem;
      transition: all 0.3s ease;
   }

   .delete-comment-btn:hover {
      background: #dc2626;
      transform: translateY(-1px);
   }

   .empty-state {
      text-align: center;
      padding: 3rem;
      color: var(--text-secondary);
   }

   .empty-state i {
      font-size: 3rem;
      color: var(--text-secondary);
      margin-bottom: 1rem;
   }

   .fullscreen-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: rgba(0, 0, 0, 0.7);
      color: white;
      border: none;
      padding: 0.5rem;
      border-radius: 6px;
      cursor: pointer;
      z-index: 10;
   }

   .zoom-controls {
      position: absolute;
      top: 1rem;
      left: 1rem;
      display: flex;
      gap: 0.5rem;
      z-index: 10;
   }

   .zoom-btn {
      background: rgba(0, 0, 0, 0.7);
      color: white;
      border: none;
      padding: 0.5rem;
      border-radius: 6px;
      cursor: pointer;
   }

   @media (max-width: 768px) {
      .view-content {
         padding: 1rem;
      }
      
      .viewer-header {
         flex-direction: column;
         gap: 1rem;
         text-align: center;
      }
      
      .viewer-controls {
         width: 100%;
         justify-content: center;
      }
      
      .metadata-grid {
         grid-template-columns: 1fr;
      }
      
      .file-actions {
         flex-direction: column;
         align-items: center;
      }
   }

   /* Loading Animation */
   .loading-spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid var(--primary-color);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 2rem auto;
   }

   @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
   }

   /* Tooltips */
   .tooltip {
      position: relative;
      cursor: help;
   }

   .tooltip:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translateX(-50%);
      background: var(--dark-color);
      color: white;
      padding: 0.5rem;
      border-radius: 6px;
      white-space: nowrap;
      font-size: 0.875rem;
      z-index: 100;
   }
   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="view-content">

   <?php
      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ?");
      $select_content->execute([$get_id, $tutor_id]);
      if($select_content->rowCount() > 0){
         while($fetch_content = $select_content->fetch(PDO::FETCH_ASSOC)){
            $content_id = $fetch_content['id'];
            $file_path = $fetch_content['video']; // This column stores all file types
            $file_type = getFileType($file_path);
            $file_extension = getFileExtension($file_path);
            $file_url = '../uploaded_files/' . $file_path;
            $file_size = file_exists($file_url) ? filesize($file_url) : 0;

            $count_likes = $conn->prepare("SELECT * FROM `likes` WHERE tutor_id = ? AND content_id = ?");
            $count_likes->execute([$tutor_id, $content_id]);
            $total_likes = $count_likes->rowCount();

            $count_comments = $conn->prepare("SELECT * FROM `comments` WHERE tutor_id = ? AND content_id = ?");
            $count_comments->execute([$tutor_id, $content_id]);
            $total_comments = $count_comments->rowCount();
   ?>

   <!-- Content Header -->
   <div class="content-header">
      <h1 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 2rem; font-weight: 700;">
         <?= htmlspecialchars($fetch_content['title']); ?>
      </h1>
      <div class="stats-container">
         <div class="stat-badge">
            <i class="fas fa-heart"></i>
            <span><?= $total_likes; ?> Likes</span>
         </div>
         <div class="stat-badge">
            <i class="fas fa-comment"></i>
            <span><?= $total_comments; ?> Comments</span>
         </div>
         <div class="stat-badge">
            <i class="fas fa-calendar"></i>
            <span><?= $fetch_content['date']; ?></span>
         </div>
      </div>
   </div>

   <!-- Content Viewer -->
   <div class="content-viewer-container">
      <div class="viewer-header">
         <h2>
            <i class="fas fa-<?= $file_type == 'video' ? 'play' : 
                                ($file_type == 'document' ? 'file-alt' : 
                                ($file_type == 'image' ? 'image' : 
                                ($file_type == 'audio' ? 'music' : 'file'))); ?>"></i>
            <?= strtoupper($file_extension); ?> <?= ucfirst($file_type); ?>
         </h2>
         <div class="viewer-controls">
            <a href="<?= $file_url; ?>" class="control-btn" download>
               <i class="fas fa-download"></i> Download
            </a>
            <a href="<?= $file_url; ?>" class="control-btn" target="_blank">
               <i class="fas fa-external-link-alt"></i> Open New Tab
            </a>
            <button class="control-btn" onclick="copyToClipboard('<?= $file_url; ?>')">
               <i class="fas fa-copy"></i> Copy Link
            </button>
         </div>
      </div>

      <div class="content-display">
         <?php if($file_type == 'video'): ?>
            <!-- Video Content -->
            <video controls class="video-player" preload="metadata">
               <source src="<?= $file_url; ?>" type="video/<?= $file_extension; ?>">
               Your browser does not support the video tag.
            </video>
            
         <?php elseif($file_type == 'document'): ?>
            <!-- Document Content -->
            <?php if($file_extension == 'pdf'): ?>
               <iframe src="<?= $file_url; ?>" class="document-viewer" title="PDF Viewer">
                  <div class="file-placeholder">
                     <i class="fas fa-file-pdf"></i>
                     <h3>PDF Document</h3>
                     <p>Your browser doesn't support PDF viewing</p>
                     <div class="file-actions">
                        <a href="<?= $file_url; ?>" class="action-btn" target="_blank">
                           <i class="fas fa-external-link-alt"></i> Open PDF
                        </a>
                     </div>
                  </div>
               </iframe>
            <?php elseif(in_array($file_extension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'])): ?>
               <!-- Microsoft Office Documents -->
               <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $file_url); ?>" 
                       class="document-viewer-office" 
                       title="Office Document Viewer">
                  <div class="file-placeholder">
                     <i class="fas fa-file-<?= $file_extension == 'doc' || $file_extension == 'docx' ? 'word' : 
                                             ($file_extension == 'ppt' || $file_extension == 'pptx' ? 'powerpoint' : 'excel'); ?>"></i>
                     <h3><?= strtoupper($file_extension); ?> Document</h3>
                     <p>Loading document viewer...</p>
                     <div class="file-actions">
                        <a href="<?= $file_url; ?>" class="action-btn" download>
                           <i class="fas fa-download"></i> Download <?= strtoupper($file_extension); ?>
                        </a>
                        <a href="<?= $file_url; ?>" class="action-btn secondary" target="_blank">
                           <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>
                     </div>
                  </div>
               </iframe>
            <?php else: ?>
               <!-- Text and other documents -->
               <div class="file-placeholder">
                  <i class="fas fa-file-alt"></i>
                  <h3><?= strtoupper($file_extension); ?> Document</h3>
                  <p>Text document ready for viewing</p>
                  <div class="file-actions">
                     <a href="<?= $file_url; ?>" class="action-btn" target="_blank">
                        <i class="fas fa-eye"></i> View Document
                     </a>
                     <a href="<?= $file_url; ?>" class="action-btn secondary" download>
                        <i class="fas fa-download"></i> Download
                     </a>
                  </div>
               </div>
            <?php endif; ?>
            
         <?php elseif($file_type == 'image'): ?>
            <!-- Image Content -->
            <div style="position: relative;">
               <img src="<?= $file_url; ?>" alt="<?= htmlspecialchars($fetch_content['title']); ?>" 
                    class="image-viewer" id="mainImage">
               <div class="zoom-controls">
                  <button class="zoom-btn" onclick="zoomImage(1.2)">
                     <i class="fas fa-search-plus"></i>
                  </button>
                  <button class="zoom-btn" onclick="zoomImage(0.8)">
                     <i class="fas fa-search-minus"></i>
                  </button>
                  <button class="zoom-btn" onclick="resetZoom()">
                     <i class="fas fa-undo"></i>
                  </button>
               </div>
               <button class="fullscreen-btn" onclick="toggleFullscreen()">
                  <i class="fas fa-expand"></i>
               </button>
            </div>
            
         <?php elseif($file_type == 'audio'): ?>
            <!-- Audio Content -->
            <div class="audio-player">
               <div class="audio-info">
                  <h3><?= htmlspecialchars($fetch_content['title']); ?></h3>
                  <p>Audio File • <?= strtoupper($file_extension); ?></p>
               </div>
               <audio controls preload="metadata">
                  <source src="<?= $file_url; ?>" type="audio/<?= $file_extension; ?>">
                  Your browser does not support the audio element.
               </audio>
            </div>
            
         <?php else: ?>
            <!-- Other File Types -->
            <div class="file-placeholder">
               <i class="fas fa-file"></i>
               <h3><?= strtoupper($file_extension); ?> File</h3>
               <p>File: <?= htmlspecialchars(basename($file_path)); ?></p>
               <div class="file-actions">
                  <a href="<?= $file_url; ?>" class="action-btn" target="_blank">
                     <i class="fas fa-external-link-alt"></i> Open File
                  </a>
                  <a href="<?= $file_url; ?>" class="action-btn secondary" download>
                     <i class="fas fa-download"></i> Download
                  </a>
               </div>
            </div>
         <?php endif; ?>
      </div>
   </div>

   <!-- Content Metadata -->
   <div class="content-metadata">
      <h3 style="margin: 0 0 1.5rem 0; color: var(--text-primary); font-weight: 600;">
         <i class="fas fa-info-circle"></i> File Information
      </h3>
      <div class="metadata-grid">
         <div class="metadata-item">
            <h4><i class="fas fa-file-alt"></i> File Type</h4>
            <p class="value"><?= strtoupper($file_extension); ?> <?= ucfirst($file_type); ?></p>
         </div>
         <div class="metadata-item">
            <h4><i class="fas fa-hdd"></i> File Size</h4>
            <p class="value"><?= formatFileSize($file_size); ?></p>
         </div>
         <div class="metadata-item">
            <h4><i class="fas fa-calendar-plus"></i> Upload Date</h4>
            <p class="value"><?= $fetch_content['date']; ?></p>
         </div>
         <div class="metadata-item">
            <h4><i class="fas fa-user"></i> Uploaded By</h4>
            <p class="value"><?= htmlspecialchars($tutor_name ?: 'Unknown Tutor'); ?></p>
         </div>
      </div>
   </div>
   </div>

<!-- Description Section -->
<div class="description-section">
   <h3><i class="fas fa-align-left"></i> Description</h3>
   <div class="description-text">
      <?= nl2br(htmlspecialchars($fetch_content['description'])); ?>
   </div>
</div>

<!-- Content Actions -->
<div class="content-metadata">
   <h3 style="margin: 0 0 1.5rem 0; color: var(--text-primary); font-weight: 600;">
      <i class="fas fa-cog"></i> Content Actions
   </h3>
   <form action="" method="post" style="margin: 0;">
      <div class="file-actions">
         <input type="hidden" name="content_id" value="<?= $content_id; ?>">
         <a href="update_content.php?get_id=<?= $content_id; ?>" class="action-btn secondary">
            <i class="fas fa-edit"></i> Update Content
         </a>
         <button type="submit" name="delete_content" class="action-btn danger" 
                 onclick="return confirm('Are you sure you want to delete this content? This action cannot be undone.');">
            <i class="fas fa-trash"></i> Delete Content
         </button>
      </div>
   </form>
</div>

<?php
 }
}else{
   echo '<div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Content Found</h3>
            <p>The requested content could not be found or you don\'t have permission to view it.</p>
            <a href="add_content.php" class="action-btn">
               <i class="fas fa-plus"></i> Add New Content
            </a>
         </div>';
}
?>

</section>

<!-- Comments Section -->
<section class="comments-section" style="margin: 2rem auto; max-width: 1200px;">
<div class="comments-header">
   <h1><i class="fas fa-comments"></i> User Comments</h1>
   <div class="stat-badge">
      <i class="fas fa-comment"></i>
      <span><?= isset($total_comments) ? $total_comments : 0; ?> Comments</span>
   </div>
</div>

<div class="show-comments">
   <?php
      if(isset($get_id) && $get_id != ''){
         $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ? ORDER BY date DESC");
         $select_comments->execute([$get_id]);
         if($select_comments->rowCount() > 0){
            while($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)){   
               $select_commentor = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
               $select_commentor->execute([$fetch_comment['user_id']]);
               if($select_commentor->rowCount() > 0){
                  $fetch_commentor = $select_commentor->fetch(PDO::FETCH_ASSOC);
   ?>
   <div class="comment-box">
      <div class="comment-user">
         <img src="../uploaded_files/<?= $fetch_commentor['image']; ?>" 
              alt="<?= htmlspecialchars($fetch_commentor['name']); ?>">
         <div class="comment-user-info">
            <h3><?= htmlspecialchars($fetch_commentor['name']); ?></h3>
            <span><?= $fetch_comment['date']; ?></span>
         </div>
      </div>
      <div class="comment-text">
         <?= nl2br(htmlspecialchars($fetch_comment['comment'])); ?>
      </div>
      <div class="comment-actions">
         <form action="" method="post" style="margin: 0;">
            <input type="hidden" name="comment_id" value="<?= $fetch_comment['id']; ?>">
            <button type="submit" name="delete_comment" class="delete-comment-btn" 
                    onclick="return confirm('Are you sure you want to delete this comment?');">
               <i class="fas fa-trash"></i> Delete Comment
            </button>
         </form>
      </div>
   </div>
   <?php
               }
            }
         }else{
            echo '<div class="empty-state">
                     <i class="fas fa-comment-slash"></i>
                     <h3>No Comments Yet</h3>
                     <p>Be the first to share your thoughts on this content!</p>
                  </div>';
         }
      }else{
         echo '<div class="empty-state">
                  <i class="fas fa-exclamation-triangle"></i>
                  <h3>Invalid Content</h3>
                  <p>No content ID provided for comment display.</p>
               </div>';
      }
   ?>
</div>
</section>

<?php include '../components/footer.php'; ?>

<script>
// Image zoom functionality
let currentZoom = 1;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;

function zoomImage(factor) {
const image = document.getElementById('mainImage');
if (image) {
   currentZoom *= factor;
   currentZoom = Math.max(0.1, Math.min(5, currentZoom)); // Limit zoom between 0.1x and 5x
   image.style.transform = `scale(${currentZoom})`;
   image.style.cursor = currentZoom > 1 ? 'move' : 'default';
}
}

function resetZoom() {
const image = document.getElementById('mainImage');
if (image) {
   currentZoom = 1;
   image.style.transform = 'scale(1)';
   image.style.cursor = 'default';
}
}

function toggleFullscreen() {
const image = document.getElementById('mainImage');
if (image) {
   if (document.fullscreenElement) {
      document.exitFullscreen();
   } else {
      image.requestFullscreen().catch(err => {
         console.log('Error attempting to enable fullscreen:', err);
      });
   }
}
}

// Copy to clipboard function
function copyToClipboard(text) {
navigator.clipboard.writeText(text).then(function() {
   // Show success message
   showNotification('Link copied to clipboard!', 'success');
}, function(err) {
   console.error('Could not copy text: ', err);
   showNotification('Failed to copy link', 'error');
});
}

// Notification function
function showNotification(message, type = 'info') {
const notification = document.createElement('div');
notification.className = `notification ${type}`;
notification.innerHTML = `
   <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}"></i>
   <span>${message}</span>
`;

// Add notification styles
notification.style.cssText = `
   position: fixed;
   top: 20px;
   right: 20px;
   background: ${type === 'success' ? 'var(--success-color)' : type === 'error' ? 'var(--danger-color)' : 'var(--primary-color)'};
   color: white;
   padding: 1rem 1.5rem;
   border-radius: 8px;
   box-shadow: var(--shadow-lg);
   z-index: 1000;
   display: flex;
   align-items: center;
   gap: 0.5rem;
   animation: slideIn 0.3s ease;
`;

document.body.appendChild(notification);

// Remove notification after 3 seconds
setTimeout(() => {
   notification.style.animation = 'slideOut 0.3s ease';
   setTimeout(() => {
      document.body.removeChild(notification);
   }, 300);
}, 3000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
@keyframes slideIn {
   from {
      transform: translateX(100%);
      opacity: 0;
   }
   to {
      transform: translateX(0);
      opacity: 1;
   }
}

@keyframes slideOut {
   from {
      transform: translateX(0);
      opacity: 1;
   }
   to {
      transform: translateX(100%);
      opacity: 0;
   }
}
`;
document.head.appendChild(style);

// Image dragging functionality for zoomed images
document.addEventListener('DOMContentLoaded', function() {
const image = document.getElementById('mainImage');
if (image) {
   image.addEventListener('mousedown', function(e) {
      if (currentZoom > 1) {
         isDragging = true;
         startX = e.pageX - image.offsetLeft;
         startY = e.pageY - image.offsetTop;
         image.style.cursor = 'grabbing';
      }
   });
   
   document.addEventListener('mousemove', function(e) {
      if (isDragging && currentZoom > 1) {
         e.preventDefault();
         const x = e.pageX - startX;
         const y = e.pageY - startY;
         image.style.left = x + 'px';
         image.style.top = y + 'px';
      }
   });
   
   document.addEventListener('mouseup', function() {
      if (isDragging) {
         isDragging = false;
         image.style.cursor = currentZoom > 1 ? 'move' : 'default';
      }
   });
   
   // Prevent image dragging on non-zoomed images
   image.addEventListener('dragstart', function(e) {
      e.preventDefault();
   });
}
});

// Enhanced video controls
document.addEventListener('DOMContentLoaded', function() {
const videos = document.querySelectorAll('video');
videos.forEach(video => {
   video.addEventListener('loadedmetadata', function() {
      console.log('Video loaded:', this.duration + ' seconds');
   });
   
   video.addEventListener('error', function() {
      console.error('Video failed to load');
      showNotification('Video failed to load', 'error');
   });
});

// Enhanced audio controls
const audioElements = document.querySelectorAll('audio');
audioElements.forEach(audio => {
   audio.addEventListener('loadedmetadata', function() {
      console.log('Audio loaded:', this.duration + ' seconds');
   });
   
   audio.addEventListener('error', function() {
      console.error('Audio failed to load');
      showNotification('Audio failed to load', 'error');
   });
});
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
if (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'textarea') {
   return; // Don't trigger shortcuts when typing in inputs
}

switch(e.key) {
   case 'f':
   case 'F':
      toggleFullscreen();
      break;
   case '+':
   case '=':
      zoomImage(1.2);
      break;
   case '-':
      zoomImage(0.8);
      break;
   case '0':
      resetZoom();
      break;
   case 'Escape':
      if (document.fullscreenElement) {
         document.exitFullscreen();
      }
      break;
}
});

// Loading states for iframes
document.addEventListener('DOMContentLoaded', function() {
const iframes = document.querySelectorAll('iframe');
iframes.forEach(iframe => {
   iframe.addEventListener('load', function() {
      console.log('Document loaded successfully');
   });
   
   iframe.addEventListener('error', function() {
      console.error('Document failed to load');
      showNotification('Document failed to load', 'error');
   });
});
});
</script>

<script src="../js/admin_script.js"></script>

</body>
</html>