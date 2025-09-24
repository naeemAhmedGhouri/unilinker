<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
    $tutor_id = $_COOKIE['tutor_id'];
}else{
    $tutor_id = '';
    header('location:../login1.php');
    exit();
}

// Filters and sorting
$search_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$search_university = isset($_GET['university']) ? trim($_GET['university']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'most_content';

// Pagination
$allowed_limits = [5,10,20,50];
$limit_param = isset($_GET['limit']) && ctype_digit($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = in_array($limit_param, $allowed_limits, true) ? $limit_param : 10;
$page = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build WHERE and params
$where = "WHERE t.id != ? AND t.status = 'Approved'";
$params = [$tutor_id];
if ($search_name !== '') {
    $where .= " AND t.name LIKE ?";
    $params[] = "%$search_name%";
}
if ($search_university !== '') {
    $where .= " AND t.university LIKE ?";
    $params[] = "%$search_university%";
}

// Order clause
if ($sort === 'most_likes') {
    $orderBy = " ORDER BY like_count DESC, content_count DESC, comment_count DESC, t.name ASC";
} elseif ($sort === 'most_comments') {
    $orderBy = " ORDER BY comment_count DESC, content_count DESC, like_count DESC, t.name ASC";
} else {
    $orderBy = " ORDER BY content_count DESC, like_count DESC, comment_count DESC, t.name ASC";
}

// Count total
$stmt_count = $conn->prepare("SELECT COUNT(*) FROM `tutors` t $where");
$stmt_count->execute($params);
$total_rows = (int)$stmt_count->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $limit));
if ($page > $total_pages) { $page = $total_pages; $offset = ($page - 1) * $limit; }

// Fetch teachers with pre-aggregated stats
$sql = "SELECT t.*, 
COALESCE(cstats.content_count,0) AS content_count,
COALESCE(lstats.like_count,0) AS like_count,
COALESCE(cmstats.comment_count,0) AS comment_count
FROM `tutors` t
LEFT JOIN (SELECT tutor_id, COUNT(*) AS content_count FROM `content` GROUP BY tutor_id) cstats ON cstats.tutor_id = t.id
LEFT JOIN (SELECT tutor_id, COUNT(*) AS like_count FROM `likes` GROUP BY tutor_id) lstats ON lstats.tutor_id = t.id
LEFT JOIN (SELECT tutor_id, COUNT(*) AS comment_count FROM `comments` GROUP BY tutor_id) cmstats ON cmstats.tutor_id = t.id
$where
$orderBy
LIMIT $limit OFFSET $offset";

$select_teachers = $conn->prepare($sql);
$select_teachers->execute($params);
$teachers = $select_teachers->fetchAll(PDO::FETCH_ASSOC);

try {
    $select_content = $conn->prepare("SELECT c.*, t.name as teacher_name, t.image as teacher_image 
        FROM `content` c 
        INNER JOIN `tutors` t ON c.tutor_id = t.id 
        WHERE c.tutor_id != ? 
        ORDER BY c.date DESC");
    $select_content->execute([$tutor_id]);
    $contents = $select_content->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $contents = [];
    error_log("Error retrieving content: " . $e->getMessage());
}
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
   <meta charset="UTF-8"> 
   <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
   <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
   <title>Connect with Teachers</title> 
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"> 
   <link rel="stylesheet" href="../css/admin_style.css"> 
   <style>
   /* Additional styles that integrate with dashboard theme */
   .search-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1.5rem;
   }
   
   .search-form {
      background: var(--white);
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: var(--box-shadow);
      margin-bottom: 2rem;
      border: var(--border);
   }
   
   .search-row {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: center;
   }
   
   .search-field {
      position: relative;
      flex: 1;
      min-width: 200px;
   }
   
   .search-field i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--main-color);
      z-index: 2;
   }
   
   .search-field input, .search-field select {
      width: 100%;
      padding: 12px 15px 12px 40px;
      border: var(--border);
      border-radius: 8px;
      font-size: 1.4rem;
      color: var(--black);
      background: var(--white);
      transition: all 0.3s;
   }
   
   .search-field input:focus, .search-field select:focus {
      outline: none;
      border-color: var(--main-color);
      box-shadow: 0 0 0 2px rgba(142, 68, 173, 0.2);
   }
   
   .search-btn {
      background: var(--main-color);
      color: var(--white);
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1.4rem;
      transition: all 0.3s;
   }
   
   .search-btn:hover {
      background: var(--black);
   }

   /* Teachers Grid using Dashboard Theme */
   .teachers-section {
      padding: 2rem;
      margin-bottom: 2rem;
   }

   .teachers-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
   }

   .teacher-card {
      background: var(--white);
      border: var(--border);
      border-radius: .5rem;
      padding: 2rem;
      text-align: center;
      box-shadow: var(--box-shadow);
      transition: all 0.3s ease;
      cursor: pointer;
   }

   .teacher-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
   }

   .teacher-image {
      width: 10rem;
      height: 10rem;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 1rem;
      border: 3px solid var(--main-color);
   }

   .teacher-name {
      font-size: 1.8rem;
      color: var(--black);
      margin-bottom: .5rem;
      font-weight: 600;
   }

   .teacher-name i {
      color: var(--main-color);
      margin-right: .5rem;
   }

   .teacher-university {
      font-size: 1.4rem;
      color: var(--light-color);
      margin-bottom: 1rem;
   }

   .teacher-university i {
      color: var(--main-color);
      margin-right: .5rem;
   }

   .teacher-stats-inline {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      font-size: 1.3rem;
      color: var(--light-color);
   }

   .teacher-stats-inline span {
      display: flex;
      align-items: center;
      gap: .5rem;
   }

   .teacher-stats-inline i {
      color: var(--main-color);
   }

   /* Modal styles using dashboard theme */
   .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
   }

   .modal-content {
      background: var(--white);
      color: var(--black);
      margin: 5% auto;
      padding: 3rem;
      width: 80%;
      max-width: 600px;
      text-align: center;
      border-radius: 10px;
      position: relative;
      box-shadow: var(--box-shadow);
   }

   .close {
      position: absolute;
      top: 15px;
      right: 25px;
      font-size: 3rem;
      cursor: pointer;
      color: var(--light-color);
      transition: color 0.3s;
   }

   .close:hover {
      color: var(--main-color);
   }

   .modal-body img {
      width: 12rem;
      height: 12rem;
      border-radius: 50%;
      margin-bottom: 1.5rem;
      border: 3px solid var(--main-color);
   }

   .modal-body h2 {
      font-size: 2.5rem;
      color: var(--black);
      margin-bottom: 1rem;
   }

   .teacher-stats {
      display: flex;
      justify-content: center;
      gap: 3rem;
      margin: 2rem 0;
   }

   .teacher-stats p {
      font-size: 1.6rem;
      color: var(--light-color);
      margin: 0;
   }

   .modal-actions {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      margin-top: 2rem;
   }

   .modal-btn {
      padding: 1rem 2rem;
      border-radius: .5rem;
      text-decoration: none;
      font-weight: 500;
      font-size: 1.4rem;
      color: var(--white);
      transition: all 0.3s;
      border: none;
      cursor: pointer;
   }

   .modal-btn.profile-btn {
      background-color: var(--main-color);
   }

   .modal-btn.chat-btn {
      background-color: green;
   }

   .modal-btn:hover {
      transform: translateY(-2px);
      filter: brightness(1.1);
   }

   /* Pagination using dashboard theme */
   .pagination-container {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 1rem;
      margin: 3rem 0;
      flex-wrap: wrap;
   }

   .pagination-container a, .pagination-container span {
      padding: 1rem 1.5rem;
      border: var(--border);
      border-radius: .5rem;
      text-decoration: none;
      color: var(--black);
      font-size: 1.4rem;
      background: var(--white);
      transition: all 0.3s;
   }

   .pagination-container a:hover {
      background: var(--main-color);
      color: var(--white);
   }

   .pagination-container .current {
      background: var(--main-color);
      color: var(--white);
   }

   .pagination-container .disabled {
      opacity: 0.5;
      pointer-events: none;
   }

   .empty {
      font-size: 2rem;
      color: var(--light-color);
      text-align: center;
      padding: 3rem;
   }

   /* Profile section */
   .profile-section {
      display: none;
      margin-top: 2rem;
      padding: 2rem;
      border-top: var(--border);
      background: var(--light-bg);
      border-radius: .5rem;
   }

   .profile-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
   }

   .profile-info-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      color: var(--black);
      font-size: 1.4rem;
   }

   .profile-info-item i {
      color: var(--main-color);
      font-size: 1.6rem;
   }

   .teacher-content h3 {
      font-size: 2rem;
      color: var(--black);
      margin-bottom: 1.5rem;
   }

   .content-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 2rem;
   }

   .content-item {
      background: var(--white);
      border-radius: .5rem;
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: all 0.3s;
      border: var(--border);
   }

   .content-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
   }

   .content-item img {
      width: 100%;
      height: 150px;
      object-fit: cover;
   }

   .content-item-info {
      padding: 1.5rem;
   }

   .content-item-title {
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: .5rem;
      color: var(--black);
   }

   .content-item-date {
      font-size: 1.2rem;
      color: var(--light-color);
   }

   /* Responsive adjustments */
   @media (max-width: 768px) {
      .search-row {
         flex-direction: column;
      }
      
      .search-field {
         min-width: 100%;
      }
      
      .search-btn {
         width: 100%;
         padding: 1.5rem;
      }

      .teachers-grid {
         grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
         gap: 1.5rem;
      }

      .modal-content {
         width: 90%;
         padding: 2rem;
      }

      .teacher-stats {
         flex-direction: column;
         gap: 1rem;
      }

      .modal-actions {
         flex-direction: column;
      }

      .modal-btn {
         width: 100%;
      }

      .pagination-container {
         gap: .5rem;
      }

      .pagination-container a, .pagination-container span {
         padding: .8rem 1.2rem;
         font-size: 1.2rem;
      }
   }
   </style>
</head> 
<body> 

<?php include '../components/admin_header.php'; ?> 

<section class="teachers-section">
   <h1 class="heading " style="text-align: center;">Connect with <span style="color:var(--main-color);">Other Teachers</span></h1>

   <div class="search-container">
      <form method="get" class="search-form">
         <div class="search-row">
            <div class="search-field">
               <i class="fas fa-user"></i>
               <input type="text" name="name" value="<?= htmlspecialchars($search_name); ?>" placeholder="Search by teacher name" maxlength="100">
            </div>
            
            <div class="search-field">
               <i class="fas fa-university"></i>
               <input type="text" name="university" value="<?= htmlspecialchars($search_university); ?>" placeholder="Search by university" maxlength="100">
            </div>
            
            <div class="search-field">
               <i class="fas fa-sort"></i>
               <select name="sort">
                  <option value="most_content" <?= $sort==='most_content'?'selected':''; ?>>Most Content</option>
                  <option value="most_likes" <?= $sort==='most_likes'?'selected':''; ?>>Most Likes</option>
                  <option value="most_comments" <?= $sort==='most_comments'?'selected':''; ?>>Most Comments</option>
               </select>
            </div>
            <div class="search-field">
               <i class="fas fa-list-ol"></i>
               <select name="limit">
                  <?php foreach ([5,10,20,50] as $opt): ?>
                     <option value="<?= $opt; ?>" <?= $limit===$opt?'selected':''; ?>><?= $opt; ?> per page</option>
                  <?php endforeach; ?>
               </select>
            </div>
            
            <button type="submit" class="search-btn">
               <i class="fas fa-search"></i> Search
            </button>
         </div>
      </form>
   </div>

   <div class="teachers-grid">
      <?php if(empty($teachers)): ?>
         <p class="empty">No other teachers found!</p>
      <?php else: ?>
         <?php foreach($teachers as $teacher): ?>
            <div class="teacher-card" data-teacher-id="<?= $teacher['id']; ?>" 
                 data-teacher-name="<?= $teacher['name']; ?>"
                 data-teacher-email="<?= isset($teacher['email']) ? $teacher['email'] : ''; ?>"
                 data-teacher-university="<?= isset($teacher['university']) ? $teacher['university'] : 'N/A'; ?>"
                 data-teacher-likes="<?= isset($teacher['like_count']) ? (int)$teacher['like_count'] : 0; ?>"
                 data-teacher-comments="<?= isset($teacher['comment_count']) ? (int)$teacher['comment_count'] : 0; ?>"
                 data-teacher-lectures="<?= isset($teacher['content_count']) ? (int)$teacher['content_count'] : 0; ?>">

               <?php if(!empty($teacher['image']) && file_exists('../uploaded_files/' . $teacher['image'])): ?>
                  <img src="../uploaded_files/<?= $teacher['image']; ?>" alt="<?= htmlspecialchars($teacher['name']); ?>" class="teacher-image">
               <?php else: ?>
                  <img src="../images/default-avatar.png" alt="Default Profile" class="teacher-image">
               <?php endif; ?>

               <div class="teacher-name">
                  <i class="fas fa-user"></i> <?= htmlspecialchars($teacher['name']); ?>
               </div>
               <p class="teacher-university">
                  <i class="fas fa-university"></i> <?= strtoupper(htmlspecialchars($teacher['university'])); ?>
               </p>
               <div class="teacher-stats-inline">
                  <span><i class="fas fa-film"></i> <?= (int)$teacher['content_count']; ?></span>
                  <span><i class="fas fa-thumbs-up"></i> <?= (int)$teacher['like_count']; ?></span>
                  <span><i class="fas fa-comment"></i> <?= (int)$teacher['comment_count']; ?></span>
               </div>
            </div>
         <?php endforeach; ?>
      <?php endif; ?>
   </div>
</section>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination-container">
   <?php
   $base_params = $_GET;
   unset($base_params['page']);
   $base_url = '?' . http_build_query($base_params);
   $base_url = $base_url === '?' ? '?page=' : $base_url . '&page=';
   ?>
   
   <?php if ($page > 1): ?>
      <a href="<?= $base_url . ($page - 1); ?>">« Previous</a>
   <?php else: ?>
      <span class="disabled">« Previous</span>
   <?php endif; ?>
   
   <?php
   $start_page = max(1, $page - 2);
   $end_page = min($total_pages, $page + 2);
   
   if ($start_page > 1): ?>
      <a href="<?= $base_url . '1'; ?>">1</a>
      <?php if ($start_page > 2): ?>
         <span>...</span>
      <?php endif;
   endif;
   
   for ($i = $start_page; $i <= $end_page; $i++): ?>
      <?php if ($i == $page): ?>
         <span class="current"><?= $i; ?></span>
      <?php else: ?>
         <a href="<?= $base_url . $i; ?>"><?= $i; ?></a>
      <?php endif;
   endfor;
   
   if ($end_page < $total_pages): ?>
      <?php if ($end_page < $total_pages - 1): ?>
         <span>...</span>
      <?php endif; ?>
      <a href="<?= $base_url . $total_pages; ?>"><?= $total_pages; ?></a>
   <?php endif; ?>
   
   <?php if ($page < $total_pages): ?>
      <a href="<?= $base_url . ($page + 1); ?>">Next »</a>
   <?php else: ?>
      <span class="disabled">Next »</span>
   <?php endif; ?>
</div>
<?php endif; ?>

<div id="teacherModal" class="modal">
   <div class="modal-content">
      <span class="close">&times;</span>
      <div class="modal-body">
         <img id="modalTeacherImage" src="../images/default-avatar.png" alt="Teacher Profile">
         <h2 id="modalTeacherName">Teacher Name</h2>

         <div class="teacher-stats">
            <p id="modalTeacherLikes">Likes: 0</p>
            <p id="modalTeacherComments">Comments: 0</p>
            <p id="modalTeacherLectures">Content: 0</p>
         </div>
         
         <div class="modal-actions">
            <a href="#" id="viewProfileBtn" class="modal-btn profile-btn">Profile</a>
            <a href="#" id="chatBtn" class="modal-btn chat-btn">Message</a>
         </div>

         <div id="profileSection" class="profile-section">
            <div class="profile-info">
               <div class="profile-info-item">
                  <i class="fas fa-envelope"></i>
                  <span id="modalTeacherEmail">Email</span>
               </div>
               <div class="profile-info-item">
                  <i class="fas fa-university"></i>
                  <span id="modalTeacherUniversity">University</span>
               </div>
               <div class="profile-info-item">
                  <i class="fas fa-graduation-cap"></i>
                  <span id="modalTeacherQualification">Qualification</span>
               </div>
               <div class="profile-info-item">
                  <i class="fas fa-briefcase"></i>
                  <span id="modalTeacherExperience">Experience</span>
               </div>
            </div>

            <div class="teacher-content">
               <h3>Recent Content</h3>
               <div id="teacherContentGrid" class="content-grid"></div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const teacherCards = document.querySelectorAll(".teacher-card");
    const modal = document.getElementById("teacherModal");
    const closeBtn = document.querySelector(".close");
    const profileSection = document.getElementById("profileSection");
    const viewProfileBtn = document.getElementById("viewProfileBtn");

    const modalTeacherImage = document.getElementById("modalTeacherImage");
    const modalTeacherName = document.getElementById("modalTeacherName");
    const modalTeacherLikes = document.getElementById("modalTeacherLikes");
    const modalTeacherComments = document.getElementById("modalTeacherComments");
    const modalTeacherLectures = document.getElementById("modalTeacherLectures");
    const modalTeacherEmail = document.getElementById("modalTeacherEmail");
    const modalTeacherUniversity = document.getElementById("modalTeacherUniversity");
    const modalTeacherQualification = document.getElementById("modalTeacherQualification");
    const modalTeacherExperience = document.getElementById("modalTeacherExperience");
    const teacherContentGrid = document.getElementById("teacherContentGrid");
    const chatBtn = document.getElementById("chatBtn");

    teacherCards.forEach(card => {
        card.addEventListener("click", function () {
            const teacherId = card.getAttribute("data-teacher-id");
            const name = card.getAttribute("data-teacher-name");
            const email = card.getAttribute("data-teacher-email");
            const university = card.getAttribute("data-teacher-university");
            const likes = card.getAttribute("data-teacher-likes");
            const comments = card.getAttribute("data-teacher-comments");
            const lectures = card.getAttribute("data-teacher-lectures");

            modalTeacherName.textContent = name;
            modalTeacherEmail.textContent = email;
            modalTeacherUniversity.textContent = university;
            modalTeacherLikes.textContent = "Likes: " + likes;
            modalTeacherComments.textContent = "Comments: " + comments;
            modalTeacherLectures.textContent = "Content: " + lectures;

            const imgElement = card.querySelector("img");
            if (imgElement) {
                modalTeacherImage.src = imgElement.src;
            }

            chatBtn.href = `chat.php?teacher_id=${teacherId}`;

            loadTeacherContent(teacherId);

            modal.style.display = "block";
            profileSection.style.display = "none"; 
        });
    });

    viewProfileBtn.addEventListener("click", function(e) {
        e.preventDefault();
        const teacherId = modalTeacherName.getAttribute('data-teacher-id') || document.querySelector('.teacher-card[data-teacher-name="' + modalTeacherName.textContent + '"]').getAttribute('data-teacher-id');
        if (teacherId) {
            window.location.href = `teacher_profile.php?teacher_id=${teacherId}`;
        }
    });

    function loadTeacherContent(teacherId) {
        fetch(`get_teacher_content.php?teacher_id=${teacherId}`)
            .then(response => response.json())
            .then(data => {
                teacherContentGrid.innerHTML = '';
                data.forEach(content => {
                    const contentItem = `
                        <div class="content-item">
                            <img src="../uploaded_files/${content.thumb}" alt="${content.title}">
                            <div class="content-item-info">
                                <div class="content-item-title">${content.title}</div>
                                <div class="content-item-date">${content.date}</div>
                            </div>
                        </div>
                    `;
                    teacherContentGrid.innerHTML += contentItem;
                });
            })
            .catch(error => console.error('Error loading content:', error));
    }

    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
        profileSection.style.display = "none";
    });

    window.addEventListener("click", function (e) {
        if (e.target === modal) {
            modal.style.display = "none";
            profileSection.style.display = "none";
        }
    });
});
</script>

<script src="../js/admin_script.js"></script>
<?php include '../components/footer.php'; ?>

</body>
</html>