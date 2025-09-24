<?php include 'components/connect.php';  
if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id']; 
}else{
    $user_id = ''; 
}  

// Default selected university (if any)
$selected_university = isset($_GET['selected_university']) ? $_GET['selected_university'] : ''; 
$search_tutor = isset($_GET['search_tutor']) ? $_GET['search_tutor'] : '';

// Pagination parameters
$limit = 16;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// AJAX request to get teacher stats
if(isset($_POST['get_teacher_stats'])) {
    $teacher_id = $_POST['teacher_id'];
    
    // Get likes count (assuming you have a likes table)
    $likes_query = $conn->prepare("SELECT COUNT(*) FROM `likes` WHERE tutor_id = ?");
    $likes_query->execute([$teacher_id]);
    $likes_count = $likes_query->fetchColumn();
    
    // Get comments count (assuming you have a comments table)
    $comments_query = $conn->prepare("SELECT COUNT(*) FROM `comments` WHERE tutor_id = ?");
    $comments_query->execute([$teacher_id]);
    $comments_count = $comments_query->fetchColumn();
    
    // Get content count (assuming you have a content/playlist table)
    $content_query = $conn->prepare("SELECT COUNT(*) FROM `playlist` WHERE tutor_id = ?");
    $content_query->execute([$teacher_id]);
    $content_count = $content_query->fetchColumn();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'likes' => $likes_count,
        'comments' => $comments_count,
        'content' => $content_count
    ]);
    exit;
}
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers</title>
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/s_teachers.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>   
<!-- Teachers Section -->
<section class="teachers">
    <h1 class="heading">Select University</h1>

    <!-- University and Name Search in One Line -->
    <form action="" method="get" class="search-tutor">
        <select name="selected_university" id="university" onchange="this.form.submit()">
            <option value="" disabled selected>Select a university</option>
            <option value="quest" <?= ($selected_university == 'quest') ? 'selected' : ''; ?>>QUEST</option>
            <option value="mehran" <?= ($selected_university == 'mehran') ? 'selected' : ''; ?>>Mehran</option>
            <option value="sbbu" <?= ($selected_university == 'sbbu') ? 'selected' : ''; ?>>SBBU</option>
        </select>
        <input type="text" name="search_tutor" maxlength="100" placeholder="Search by Name" value="<?= htmlspecialchars($search_tutor); ?>">
        <input type="hidden" name="page" value="1">
        <button type="submit" name="search_tutor_btn"><i class="fas fa-search"></i> Search</button>
    </form>

    <h1 class="heading">Explore Teachers</h1>
    <div class="teachers-grid">
        <?php
        // Build the query based on filters
        $conditions = ["status = 'Approved'"];
        $params = [];

        // Handle university filter
        if(!empty($selected_university)) {
            $conditions[] = "university = ?";
            $params[] = $selected_university;
        }

        // Handle name search
        if(!empty($search_tutor)) {
            $conditions[] = "name LIKE ?";
            $params[] = '%' . $search_tutor . '%';
        }

        // Build the final query
        $query = "SELECT * FROM `tutors`";
        $count_query = "SELECT COUNT(*) FROM `tutors`";
        
        if(!empty($conditions)) {
            $where_clause = " WHERE " . implode(" AND ", $conditions);
            $query .= $where_clause;
            $count_query .= $where_clause;
        }
        
        $query .= " ORDER BY name ASC LIMIT $limit OFFSET $offset";

        // Get total count for pagination
        $count_tutors = $conn->prepare($count_query);
        $count_tutors->execute($params);
        $total_tutors = $count_tutors->fetchColumn();
        $total_pages = ceil($total_tutors / $limit);

        // Fetch tutors
        $select_teachers = $conn->prepare($query);
        $select_teachers->execute($params);
        
        $teachers = $select_teachers->fetchAll(PDO::FETCH_ASSOC);
        if(empty($teachers)){
            echo '<p class="empty" style="grid-column: 1 / -1; text-align: center;">No teachers found!</p>';
        }else{
            foreach($teachers as $teacher){
        ?>
            <div class="teacher-card" data-teacher-id="<?= htmlspecialchars($teacher['id']); ?>">
                <?php 
                    $image_path = 'uploaded_files/'.$teacher['image'];
                    if(file_exists($image_path) && !empty($teacher['image'])) {
                        echo '<img src="'.htmlspecialchars($image_path).'" alt="">';
                    } else {
                        echo '<img src="images/default-avatar.png" alt="">';
                    }
                ?>
                <div class="teacher-info">
                    <h3><?= htmlspecialchars($teacher['name']); ?></h3>
                    <?php if(!empty($teacher['profession'])): ?>
                        <p class="profession" style="display: none;"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($teacher['profession']); ?></p>
                    <?php endif; ?>
                    <?php if(!empty($teacher['university'])): ?>
                        <p class="university"><i class="fas fa-university"></i> <?= htmlspecialchars($teacher['university']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php
            }
        }
        ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                <i class="fas fa-arrow-left"></i> Prev
            </a>
        <?php else: ?>
            <a class="disabled"><i class="fas fa-arrow-left"></i> Prev</a>
        <?php endif; ?>
        
        <span>Page <?= $page ?> of <?= $total_pages ?></span>
        
        <?php if($page < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                Next <i class="fas fa-arrow-right"></i>
            </a>
        <?php else: ?>
            <a class="disabled">Next <i class="fas fa-arrow-right"></i></a>
        <?php endif; ?>
    </div>
</section>

<!-- Teacher Profile Modal -->
<div id="teacherModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-body">
            <div class="teacher-profile">
                <img id="modalImage" src="" alt="Teacher Image">
                <h3 id="modalName"></h3>
                <div class="profile-details">
                    <p id="modalProfession"></p>
                    <p id="modalUniversity"></p>
                    <div class="counts">
                        <p><i class="fas fa-thumbs-up"></i> <span id="modalLikes">Loading...</span> Likes</p>
                        <p><i class="fas fa-comment"></i> <span id="modalComments">Loading...</span> Comments</p>
                        <p><i class="fas fa-folder-open"></i> <span id="modalContent">Loading...</span> Contents</p>
                    </div>
                </div>

                <div class="modal-actions">
                    <a href="#" id="chatBtn" class="btn chat-btn">
                        <i class="fas fa-comment"></i> Chat
                    </a>
                    <a href="#" id="profileBtn" class="btn profile-btn">
                        <i class="fas fa-user"></i> View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('teacherModal');
    const modalImage = document.getElementById('modalImage');
    const modalName = document.getElementById('modalName');
    const modalProfession = document.getElementById('modalProfession');
    const modalUniversity = document.getElementById('modalUniversity');
    const modalLikes = document.getElementById('modalLikes');
    const modalComments = document.getElementById('modalComments');
    const modalContent = document.getElementById('modalContent');
    const chatBtn = document.getElementById('chatBtn');
    const profileBtn = document.getElementById('profileBtn');
    const closeBtn = document.querySelector('.close');

    // Function to fetch real teacher stats
    function fetchTeacherStats(teacherId) {
        // Reset to loading state
        modalLikes.textContent = 'Loading...';
        modalComments.textContent = 'Loading...';
        modalContent.textContent = 'Loading...';

        // Create FormData for POST request
        const formData = new FormData();
        formData.append('get_teacher_stats', '1');
        formData.append('teacher_id', teacherId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            modalLikes.textContent = data.likes || 0;
            modalComments.textContent = data.comments || 0;
            modalContent.textContent = data.content || 0;
        })
        .catch(error => {
            console.error('Error fetching teacher stats:', error);
            modalLikes.textContent = '0';
            modalComments.textContent = '0';
            modalContent.textContent = '0';
        });
    }

    // Add click event to all teacher cards
    document.querySelectorAll('.teacher-card').forEach(card => {
        card.addEventListener('click', function() {
            const teacherId = this.dataset.teacherId; 
            
            const img = this.querySelector('img').src;
            const name = this.querySelector('h3').textContent;
            const professionElement = this.querySelector('.profession');
            const universityElement = this.querySelector('.university');
            
            const profession = professionElement ? professionElement.textContent : '';
            const university = universityElement ? universityElement.textContent : '';

            // Update modal content
            modalImage.src = img;
            modalName.textContent = name;
            modalProfession.innerHTML = profession;
            modalUniversity.innerHTML = university;
            
            // Fetch real stats from database
            fetchTeacherStats(teacherId);

            // Update action buttons with correct paths
            profileBtn.href = `Teacher_profile.php?teacher_id=${teacherId}`;

            // Show modal
            modal.style.display = 'block';
        });
    });

    // Close modal when clicking close button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
    
    // Add hover effects to teacher cards
    const cards = document.querySelectorAll('.teacher-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 8px 15px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'var(--box-shadow)';
        });
    });
});
</script>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>