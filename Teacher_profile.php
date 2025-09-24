<?php
include 'components/connect.php';

// Check if student is logged in
if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];
}else{
    $user_id = '';
    header('location:login.php');
    exit();
}

// Get teacher ID from URL
if(isset($_GET['teacher_id'])){
    $teacher_id = $_GET['teacher_id'];
} else {
    header('location:teachers.php');
    exit();
}

// Get teacher details
try {
    $select_teacher = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
    $select_teacher->execute([$teacher_id]);
    $teacher = $select_teacher->fetch(PDO::FETCH_ASSOC);
    
    if(!$teacher) {
        header('location:teachers.php');
        exit();
    }
} catch (PDOException $e) {
    header('location:teachers.php');
    exit();
}

// Initialize counters
$teacher['likes'] = 0;
$teacher['comments'] = 0;
$teacher['playlists'] = 0;
$teacher['total_content'] = 0;

try {
    // Count likes for this teacher
    $count_likes = $conn->prepare("SELECT COUNT(*) as total FROM `likes` WHERE tutor_id = ?");
    $count_likes->execute([$teacher_id]);
    $likes_result = $count_likes->fetch(PDO::FETCH_ASSOC);
    $teacher['likes'] = $likes_result ? $likes_result['total'] : 0;

    // Count comments for this teacher's content
    $count_comments = $conn->prepare("SELECT COUNT(*) as total FROM `comments` WHERE tutor_id = ?");
    $count_comments->execute([$teacher_id]);
    $comments_result = $count_comments->fetch(PDO::FETCH_ASSOC);
    $teacher['comments'] = $comments_result ? $comments_result['total'] : 0;

    // Count playlists
    $count_playlists = $conn->prepare("SELECT COUNT(*) as total FROM `playlist` WHERE tutor_id = ? AND status = 'active'");
    $count_playlists->execute([$teacher_id]);
    $playlist_result = $count_playlists->fetch(PDO::FETCH_ASSOC);
    $teacher['playlists'] = $playlist_result ? $playlist_result['total'] : 0;

    // Count total content
    $count_content = $conn->prepare("SELECT COUNT(*) as total FROM `content` WHERE tutor_id = ? AND status = 'active'");
    $count_content->execute([$teacher_id]);
    $content_result = $count_content->fetch(PDO::FETCH_ASSOC);
    $teacher['total_content'] = $content_result ? $content_result['total'] : 0;
    
} catch (PDOException $e) {
    error_log("Error retrieving stats: " . $e->getMessage());
}

// Get teacher's recent content
try {
    $select_content = $conn->prepare("SELECT * FROM `content` WHERE tutor_id = ? AND status = 'active' ORDER BY date DESC LIMIT 8");
    $select_content->execute([$teacher_id]);
    $teacher_content = $select_content->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teacher_content = [];
    error_log("Error retrieving content: " . $e->getMessage());
}

// Get teacher's playlists
try {
    $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ? AND status = 'active' ORDER BY date DESC LIMIT 6");
    $select_playlists->execute([$teacher_id]);
    $teacher_playlists = $select_playlists->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teacher_playlists = [];
    error_log("Error retrieving Folder: " . $e->getMessage());
}

// Check if current user has liked this teacher
$user_liked = false;
if($user_id) {
    try {
        $check_like = $conn->prepare("SELECT id FROM `likes` WHERE user_id = ? AND tutor_id = ?");
        $check_like->execute([$user_id, $teacher_id]);
        $user_liked = $check_like->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error checking like status: " . $e->getMessage());
    }
}

// Check if user is bookmarked by this teacher
$is_bookmarked = false;
if($user_id) {
    try {
        $check_bookmark = $conn->prepare("SELECT id FROM `bookmark` WHERE user_id = ? AND tutor_id = ?");
        $check_bookmark->execute([$user_id, $teacher_id]);
        $is_bookmarked = $check_bookmark->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error checking bookmark status: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($teacher['name']); ?> - Teacher Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .teacher-profile {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--light-bg);
            min-height: 100vh;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--main-color) 0%, var(--red) 100%);
            border-radius: 2rem;
            padding: 3rem 2rem;
            text-align: center;
            color: var(--white);
            position: relative;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .back-btn {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            background: rgba(255,255,255,0.2);
            color: var(--white);
            padding: 1rem 1.5rem;
            border-radius: 5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            z-index: 2;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .profile-image {
            width: 15rem;
            height: 15rem;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
            object-fit: cover;
            margin-bottom: 2rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.3);
        }

        .profile-name {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 800;
            text-shadow: 0 0.5rem 1rem rgba(0,0,0,0.3);
        }

        .profile-profession {
            font-size: 1.4rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 1.5rem;
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--light-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 2rem 4rem rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--main-color);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--black);
            margin-bottom: 1rem;
        }

        .stat-label {
            color: var(--light-color);
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .teacher-info {
            background: var(--white);
            border-radius: 1.5rem;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: var(--box-shadow);
        }

        .section-title {
            font-size: 2.2rem;
            margin-bottom: 2rem;
            color: var(--black);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 700;
        }

        .section-title i {
            color: var(--main-color);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 2rem;
            background: var(--light-bg);
            border-radius: 1rem;
            border-left: 4px solid var(--main-color);
        }

        .info-icon {
            font-size: 2rem;
            color: var(--main-color);
            width: 5rem;
            text-align: center;
        }

        .info-label {
            font-size: 1rem;
            color: var(--light-color);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .info-value {
            font-weight: 700;
            color: var(--black);
            font-size: 1.4rem;
        }

        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1.5rem 3rem;
            border-radius: 5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--box-shadow);
            border: none;
            cursor: pointer;
            font-size: 1.4rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--main-color) 0%, var(--red) 100%);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--main-color);
            border: 2px solid var(--main-color);
        }

        .btn-like {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: var(--white);
        }

        .btn-like.liked {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.2);
        }

        .content-section, .playlist-section {
            background: var(--white);
            border-radius: 1.5rem;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: var(--box-shadow);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .content-card {
            background: var(--light-bg);
            border-radius: 1rem;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1.5rem 4rem rgba(0,0,0,0.15);
        }

        .content-thumbnail {
            width: 100%;
            height: 18rem;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .content-card:hover .content-thumbnail {
            transform: scale(1.05);
        }

        .content-info {
            padding: 2rem;
        }

        .content-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--black);
            font-size: 1.4rem;
            line-height: 1.4;
        }

        .content-date {
            color: var(--light-color);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .playlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .playlist-card {
            background: linear-gradient(135deg, var(--main-color) 0%, var(--red) 100%);
            color: var(--white);
            border-radius: 1rem;
            padding: 2.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .playlist-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 10rem;
            height: 10rem;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(3rem, -3rem);
        }

        .playlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1.5rem 4rem rgba(0,0,0,0.2);
        }

        .playlist-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .playlist-desc {
            opacity: 0.9;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
            font-size: 1.2rem;
        }

        .playlist-count {
            font-size: 1.1rem;
            opacity: 0.8;
            position: relative;
            z-index: 2;
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: var(--light-color);
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 1rem;
            color: var(--black);
            font-size: 2rem;
        }

        .empty-state p {
            font-size: 1.4rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--white);
            margin: 2% auto;
            border-radius: 1.5rem;
            max-width: 90rem;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 3rem;
            border-bottom: 1px solid var(--light-color);
            position: relative;
        }

        .close {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: var(--light-bg);
            color: var(--light-color);
            width: 4rem;
            height: 4rem;
            border: none;
            border-radius: 50%;
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close:hover {
            background: var(--red);
            color: var(--white);
        }

        .modal-body {
            padding: 3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .teacher-profile {
                padding: 1rem;
            }

            .profile-header {
                padding: 2rem 1rem;
            }

            .profile-name {
                font-size: 2.5rem;
            }

            .profile-image {
                width: 12rem;
                height: 12rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 1rem;
            }

            .stat-card {
                padding: 2rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .content-grid, .playlist-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'components/user_header.php'; ?>

    <section class="teacher-profile">
        <!-- Profile Header -->
        <div class="profile-header">
            <a href="teachers.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Teachers
            </a>
            
            <?php if(!empty($teacher['image']) && file_exists('uploaded_files/' . $teacher['image'])): ?>
                <img src="uploaded_files/<?= htmlspecialchars($teacher['image']); ?>" 
                     alt="<?= htmlspecialchars($teacher['name']); ?>" 
                     class="profile-image">
            <?php else: ?>
                <img src="images/pic-1.jpg" 
                     alt="Default Profile" 
                     class="profile-image">
            <?php endif; ?>

            <h1 class="profile-name"><?= htmlspecialchars($teacher['name']); ?></h1>
            <p class="profile-profession">
                <i class="fas fa-graduation-cap"></i>
                <?= htmlspecialchars($teacher['profession'] ?? 'Professional Teacher'); ?>
            </p>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-number" id="likesCount"><?= $teacher['likes']; ?></div>
                <div class="stat-label">Total Likes</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-number"><?= $teacher['comments']; ?></div>
                <div class="stat-label">Comments</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stat-number"><?= $teacher['playlists']; ?></div>
                <div class="stat-label">folder</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-number"><?= $teacher['total_content']; ?></div>
                <div class="stat-label">Total Content</div>
            </div>
        </div>

        <!-- Teacher Information -->
        <div class="teacher-info">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                Teacher Information
            </h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?= htmlspecialchars($teacher['email'] ?? 'Not provided'); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <div class="info-label">Profession</div>
                        <div class="info-value"><?= htmlspecialchars($teacher['profession'] ?? 'Teacher'); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?= date('F Y', strtotime($teacher['date'] ?? 'now')); ?></div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div class="info-label">Status</div>
                        <div class="info-value">Active Teacher</div>
                    </div>
                </div>
            </div>
        </div>

  
        <!-- Teacher's Playlists -->
        <?php if(!empty($teacher_playlists)): ?>
        <div class="playlist-section">
            <h2 class="section-title">
                 <i class="fas fa-folder-open"></i> 
                Recent Folder (<?= count($teacher_playlists); ?>)
            </h2>
            
            <div class="playlist-grid">
                <?php foreach($teacher_playlists as $playlist): ?>
                    <div class="playlist-card" onclick="location.href='playlist.php?get_id=<?= $playlist['id']; ?>'">
                        <div class="playlist-title"><?= htmlspecialchars($playlist['title']); ?></div>
                        <div class="playlist-desc"><?= htmlspecialchars($playlist['description'] ?? 'No description available'); ?></div>
                        <div class="playlist-count">
                            <i class="fas fa-file-alt"></i>
                            Created on <?= date('M d, Y', strtotime($playlist['date'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="playlist.php?tutor_id=<?= $teacher_id; ?>" class="btn btn-secondary">
                     <i class="fas fa-folder-open"></i> 
                    View All folder
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Teacher's Recent Content -->
        <div class="content-section">
            <h2 class="section-title">
               <i class="fas fa-file-alt"></i>
                Recent Content (<?= count($teacher_content); ?>)
            </h2>

            <?php if(empty($teacher_content)): ?>
                <div class="empty-state">
                    <i class="fas fa-video-slash"></i>
                    <h3>No Content Available</h3>
                    <p>This teacher hasn't uploaded any ontent yet.</p>
                </div>
            <?php else: ?>
                <div class="content-grid">
                    <?php foreach($teacher_content as $content): ?>
                        <div class="content-card" onclick="openVideoModal('<?= $content['id']; ?>')">
                            <?php if(!empty($content['thumb']) && file_exists('uploaded_files/' . $content['thumb'])): ?>
                                <img src="uploaded_files/<?= htmlspecialchars($content['thumb']); ?>"
                                     alt="<?= htmlspecialchars($content['title']); ?>"
                                     class="content-thumbnail">
                            <?php else: ?>
                                <img src="images/post-1-1.png"
                                     alt="Default Thumbnail"
                                     class="content-thumbnail">
                            <?php endif; ?>
                            
                            <div class="content-info">
                                <div class="content-title"><?= htmlspecialchars($content['title']); ?></div>
                                <div class="content-date">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('M d, Y', strtotime($content['date'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="text-align: center; margin-top: 3rem;">
                    <a href="courses.php?tutor_id=<?= $teacher_id; ?>" class="btn btn-primary">
                        <i class="fas fa-file-alt"></i>
                        View All Content
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Video Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" onclick="closeVideoModal()">&times;</button>
                <h3 id="modalTitle"></h3>
            </div>
            <div class="modal-body">
                <div id="modalVideoContainer"></div>
            </div>
        </div>
    </div>

    </body>
<script src="js/script.js"></script>
    <script>
        // Like functionality
        function toggleLike(teacherId) {
            <?php if(!$user_id): ?>
                alert('Please login to like teachers');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            fetch('components/like_teacher.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'teacher_id=' + encodeURIComponent(teacherId)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const likeBtn = document.getElementById('likeBtn');
                    const likeText = document.getElementById('likeText');
                    const likesCount = document.getElementById('likesCount');
                    
                    if(data.action === 'liked') {
                        likeBtn.classList.add('liked');
                        likeText.textContent = 'Liked';
                        likesCount.textContent = parseInt(
                        

<?php include 'components/footer.php'; ?>

</body>
</html>

