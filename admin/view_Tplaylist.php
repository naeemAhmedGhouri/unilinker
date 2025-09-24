<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
    $tutor_id = $_COOKIE['tutor_id'];
}else{
    $tutor_id = '';
    header('location:../login1.php');
    exit();
}

// Teacher ID
if(isset($_GET['teacher_id'])){
    $teacher_id = $_GET['teacher_id'];
}else{
    header('location:other_teacher.php');
    exit();
}

// Playlist ID
if(isset($_GET['playlist_id'])){
    $playlist_id = $_GET['playlist_id'];
}else{
    header("location:teacher_profile.php?teacher_id=$teacher_id");
    exit();
}

// Get playlist info
$select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND tutor_id = ? LIMIT 1");
$select_playlist->execute([$playlist_id, $teacher_id]);
if($select_playlist->rowCount() == 0){
    echo "Playlist not found!";
    exit();
}
$playlist = $select_playlist->fetch(PDO::FETCH_ASSOC);

// Get content of this playlist
$select_content = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? ORDER BY date DESC");
$select_content->execute([$playlist_id]);
$contents = $select_content->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= htmlspecialchars($playlist['title']); ?> - Folder</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="playlist-details">
   <h1 class="heading">Folder Details</h1>

   <div class="row">
      <div class="thumb">
         <img src="../uploaded_files/<?= $playlist['thumb']; ?>" alt="">
      </div>
      <div class="details">
         <h3 class="title"><?= htmlspecialchars($playlist['title']); ?></h3>
         <div class="date"><i class="fas fa-calendar"></i><span><?= $playlist['date']; ?></span></div>
         <div class="description"><?= $playlist['description']; ?></div>
      </div>
   </div>
</section>

<section class="contents">
   <h1 class="heading">Folder Content</h1>
   <div class="box-container">
      <?php if(count($contents) > 0): ?>
         <?php foreach($contents as $content): ?>
            <?php
               $file_extension = pathinfo($content['video'], PATHINFO_EXTENSION);
               $video_extensions = ['mp4','avi','mov','wmv','flv','webm','mkv'];
               $is_video = in_array(strtolower($file_extension), $video_extensions);
            ?>
            <div class="box">
               <div class="flex">
                  <div><i class="fas fa-calendar"></i><span><?= $content['date']; ?></span></div>
                  <div><i class="fas fa-file"></i><span><?= strtoupper($file_extension); ?></span></div>
               </div>
               
               <?php if($content['thumb'] && file_exists('../uploaded_files/'.$content['thumb'])): ?>
                  <img src="../uploaded_files/<?= $content['thumb']; ?>" class="thumb" alt="">
               <?php else: ?>
                  <div class="thumb" style="background:#f0f0f0; display:flex;align-items:center;justify-content:center;min-height:200px;">
                     <i class="fas fa-file-alt" style="font-size:3rem;color:#666;"></i>
                  </div>
               <?php endif; ?>

               <h3 class="title"><?= $content['title']; ?></h3>
               <p class="description"><?= substr($content['description'],0,100); ?>...</p>

               <?php if($is_video): ?>
                  <a href="view_content.php?get_id=<?= $content['id']; ?>" class="btn">Watch Video</a>
               <?php else: ?>
                  <a href="../uploaded_files/<?= $content['video']; ?>" target="_blank" class="btn">Download File</a>
               <?php endif; ?>
            </div>
         <?php endforeach; ?>
      <?php else: ?>
         <p class="empty">No content in this folder yet.</p>
      <?php endif; ?>
   </div>
</section>

<?php include '../components/footer.php'; ?>
<script src="../js/admin_script.js"></script>
</body>
</html>
