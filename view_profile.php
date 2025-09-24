<?php 
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id']; 
}else{
    $user_id = ''; 
}

// Get teacher ID from URL
$teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : '';

if(empty($teacher_id)) {
    header('location: teachers.php');
    exit();
}

// Fetch teacher details
$select_teacher = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
$select_teacher->execute([$teacher_id]);
$teacher = $select_teacher->fetch(PDO::FETCH_ASSOC);

if(!$teacher) {
    header('location: teachers.php');
    exit();
}

// Fetch teacher's content/courses
$select_content = $conn->prepare("SELECT * FROM `content` WHERE tutor_id = ? ORDER BY date DESC");
$select_content->execute([$teacher_id]);
$contents = $select_content->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$total_content = count($contents);

// Count likes (assuming you have a likes table)
$count_likes = $conn->prepare("SELECT COUNT(*) as total_likes FROM `likes` WHERE tutor_id = ?");
$count_likes->execute([$teacher_id]);
$likes_count = $count_likes->fetch(PDO::FETCH_ASSOC)['total_likes'] ?? 0;

// Count comments (assuming you have a comments table)
$count_comments = $conn->prepare("SELECT COUNT(*) as total_comments FROM `comments` WHERE tutor_id = ?");
$count_comments->execute([$teacher_id]);
$comments_count = $count_comments->fetch(PDO::FETCH_ASSOC)['total_comments'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $teacher['name']; ?> - Profile</title>
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- Teacher Profile Section -->
<section class="teacher-profile-section">
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-image">
                <?php 
                    $image_path = 'uploaded_files/'.$teacher['image'];
                    if(file_exists($image_path) && !empty($teacher['image'])) {
                        echo '<img src="'.$image_path.'" alt="'.$teacher['name'].'">';
                    } else {
                        echo '<img src="images/default-avatar.png" alt="Default Avatar">';
                    }
                ?>
            </div>
            <div class="profile-info">
                <h1><?= $teacher['name']; ?></h1>
                <?php if(!empty($teacher['profession'])): ?>
                    <p class="profession"><i class="fas fa-briefcase"></i> <?= $teacher['profession']; ?></p>
                <?php endif; ?>
                <?php if(!empty($teacher['university'])): ?>
                    <p class="university"><i class="fas fa-university"></i> <?= $teacher['university']; ?></p>
                <?php endif; ?>
                <?php if(!empty($teacher['email'])): ?>
                    <p class="email"><i class="fas fa-envelope"></i> <?= $teacher['email']; ?></p>
                <?php endif; ?>
                
                <!-- Stats -->
                <div class="profile-stats">
                    <div class="stat-item">
                        <i class="fas fa-video"></i>
                        <span class="stat-number"><?= $total_content; ?></span>
                        <span class="stat-label">Contents</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-thumbs-up"></i>
                        <span class="stat-number"><?= $likes_count; ?></span>
                        <span class="stat-label">Likes</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-comments"></i>
                        <span class="stat-number"><?= $comments_count; ?></span>
                        <span class="stat-label">Comments</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="profile-actions">
                    <a href="admin/chat.php?teacher_id=<?= $teacher['id']; ?>" class="btn chat-btn">
                        <i class="fas fa-comment"></i> Start Chat
                    </a>
                    <a href="#teacher-content" class="btn content-btn">
                        <i class="fas fa-video"></i> View Content
                    </a>
                </div>
            </div>
        </div>

        <!-- About Section -->
        <?php if(!empty($teacher['bio']) || !empty($teacher['expertise'])): ?>
        <div class="about-section">
            <h2><i class="fas fa-user"></i> About</h2>
            <?php if(!empty($teacher['bio'])): ?>
                <p class="bio"><?= nl2br(htmlspecialchars($teacher['bio'])); ?></p>
            <?php endif; ?>
            
            <?php if(!empty($teacher['expertise'])): ?>
                <div class="expertise">
                    <h3><i class="fas fa-star"></i> Areas of Expertise</h3>
                    <div class="expertise-tags">
                        <?php 
                        $expertise_list = explode(',', $teacher['expertise']);
                        foreach($expertise_list as $skill): 
                            $skill = trim($skill);
                            if(!empty($skill)):
                        ?>
                            <span class="expertise-tag"><?= htmlspecialchars($skill); ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Content Section -->
        <div class="content-section" id="teacher-content">
            <h2><i class="fas fa-video"></i> Content & Courses</h2>
            
            <?php if(empty($contents)): ?>
                <div class="no-content">
                    <i class="fas fa-folder-open"></i>
                    <p>No content available yet.</p>
                </div>
            <?php else: ?>
                <div class="content-grid">
                    <?php foreach($contents as $content): ?>
                        <div class="content-card">
                            <div class="content-thumbnail">
                                <?php if(!empty($content['thumb'])): ?>
                                    <img src="uploaded_files/<?= $content['thumb']; ?>" alt="<?= $content['title']; ?>">
                                <?php else: ?>
                                    <div class="no-thumbnail">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="content-overlay">
                                    <a href="watch_video.php?get_id=<?= $content['id']; ?>" class="play-btn">
                                        <i class="fas fa-play"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="content-info">
                                <h3><?= $content['title']; ?></h3>
                                <?php if(!empty($content['description'])): ?>
                                    <p class="content-desc"><?= substr($content['description'], 0, 100); ?>...</p>
                                <?php endif; ?>
                                <div class="content-meta">
                                    <span class="upload-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('M j, Y', strtotime($content['date'])); ?>
                                    </span>
                                    <?php if(!empty($content['status'])): ?>
                                        <span class="content-status <?= $content['status']; ?>">
                                            <?= ucfirst($content['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.teacher-profile-section {
    padding: 2rem;
    background: var(--light-bg);
    min-height: 100vh;
}

.profile-container {
    max-width: 1200px;
    margin: 0 auto;
}

.profile-header {
    background: var(--white);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--box-shadow);
    display: flex;
    gap: 2rem;
    align-items: center;
    margin-bottom: 2rem;
}

.profile-image img {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--main-color);
}

.profile-info h1 {
    font-size: 2.5rem;
    color: var(--black);
    margin-bottom: 1rem;
}

.profile-info p {
    font-size: 1.4rem;
    color: var(--light-color);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.profile-info p i {
    color: var(--main-color);
    width: 20px;
}

.profile-stats {
    display: flex;
    gap: 2rem;
    margin: 2rem 0;
    padding: 1.5rem;
    background: var(--light-bg);
    border-radius: 0.5rem;
}

.stat-item {
    text-align: center;
    flex: 1;
}

.stat-item i {
    font-size: 2rem;
    color: var(--main-color);
    margin-bottom: 0.5rem;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    color: var(--black);
}

.stat-label {
    color: var(--light-color);
    font-size: 1.2rem;
}

.profile-actions {
    display: flex;
    gap: 1rem;
}

.profile-actions .btn {
    padding: 1rem 2rem;
    font-size: 1.4rem;
    border-radius: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.chat-btn {
    background: var(--green);
    color: var(--white);
}

.content-btn {
    background: var(--main-color);
    color: var(--white);
}

.about-section {
    background: var(--white);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--box-shadow);
    margin-bottom: 2rem;
}

.about-section h2 {
    font-size: 2rem;
    color: var(--black);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.about-section h2 i {
    color: var(--main-color);
}

.bio {
    font-size: 1.4rem;
    line-height: 1.8;
    color: var(--light-color);
    margin-bottom: 2rem;
}

.expertise h3 {
    font-size: 1.6rem;
    color: var(--black);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.expertise h3 i {
    color: var(--orange);
}

.expertise-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.expertise-tag {
    background: var(--main-color);
    color: var(--white);
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 1.2rem;
}

.content-section {
    background: var(--white);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--box-shadow);
}

.content-section h2 {
    font-size: 2rem;
    color: var(--black);
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.content-section h2 i {
    color: var(--main-color);
}

.no-content {
    text-align: center;
    padding: 3rem;
    color: var(--light-color);
}

.no-content i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--main-color);
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.content-card {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: transform 0.3s ease;
}

.content-card:hover {
    transform: translateY(-5px);
}

.content-thumbnail {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.content-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-thumbnail {
    width: 100%;
    height: 100%;
    background: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-thumbnail i {
    font-size: 4rem;
    color: var(--main-color);
}

.content-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.content-card:hover .content-overlay {
    opacity: 1;
}

.play-btn {
    background: var(--white);
    color: var(--main-color);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.play-btn:hover {
    background: var(--main-color);
    color: var(--white);
}

.content-info {
    padding: 1.5rem;
}

.content-info h3 {
    font-size: 1.6rem;
    color: var(--black);
    margin-bottom: 0.5rem;
}

.content-desc {
    color: var(--light-color);
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.content-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.1rem;
}

.upload-date {
    color: var(--light-color);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.content-status {
    padding: 0.3rem 0.8rem;
    border-radius: 1rem;
    font-size: 1rem;
    text-transform: uppercase;
    font-weight: bold;
}

.content-status.active {
    background: var(--green);
    color: var(--white);
}

.content-status.deactive {
    background: var(--red);
    color: var(--white);
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-image img {
        width: 150px;
        height: 150px;
    }
    
    .profile-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .profile-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .profile-actions .btn {
        justify-content: center;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .teacher-profile-section {
        padding: 1rem;
    }
    
    .profile-header,
    .about-section,
    .content-section {
        padding: 1.5rem;
    }
}
</style>

<?php include 'components/footer.php'; ?>

</body>
</html>