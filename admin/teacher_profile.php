<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
    $tutor_id = $_COOKIE['tutor_id'];
}else{
    $tutor_id = '';
    header('location:../login1.php');
    exit();
}

// Get teacher ID from URL
if(isset($_GET['teacher_id'])){
    $teacher_id = $_GET['teacher_id'];
} else {
    header('location:t.php');
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

try {
    $teacher['likes'] = 0;
    $teacher['comments'] = 0;
    $teacher['lectures'] = 0;
    
    // Count likes
    $check_likes_table = $conn->prepare("SHOW TABLES LIKE 'likes'");
    $check_likes_table->execute();
    if($check_likes_table->rowCount() > 0) {
        $count_likes = $conn->prepare("SELECT COUNT(*) as total FROM `likes` WHERE tutor_id = ?");
        $count_likes->execute([$teacher_id]);
        $total_likes = $count_likes->fetch(PDO::FETCH_ASSOC)['total'];
        $teacher['likes'] = $total_likes;
    }

    // Count comments
    $check_comments_table = $conn->prepare("SHOW TABLES LIKE 'comments'");
    $check_comments_table->execute();
    if($check_comments_table->rowCount() > 0) {
        $count_comments = $conn->prepare("SELECT COUNT(*) as total FROM `comments` WHERE tutor_id = ?");
        $count_comments->execute([$teacher_id]);
        $total_comments = $count_comments->fetch(PDO::FETCH_ASSOC)['total'];
        $teacher['comments'] = $total_comments;
    }

    // Count content
    $check_content_table = $conn->prepare("SHOW TABLES LIKE 'content'");
    $check_content_table->execute();
    if($check_content_table->rowCount() > 0) {
        $count_content = $conn->prepare("SELECT COUNT(*) as total FROM `content` WHERE tutor_id = ?");
        $count_content->execute([$teacher_id]);
        $total_content = $count_content->fetch(PDO::FETCH_ASSOC)['total'];
        $teacher['lectures'] = $total_content;
    }
} catch (PDOException $e) {
    error_log("Error retrieving stats: " . $e->getMessage());
}

// Get teacher's playlists/folders
try {
    $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ? ORDER BY date DESC");
    $select_playlists->execute([$teacher_id]);
    $teacher_playlists = $select_playlists->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teacher_playlists = [];
    error_log("Error retrieving playlists: " . $e->getMessage());
}

// Get teacher's content from content table
try {
    $select_content = $conn->prepare("SELECT * FROM `content` WHERE tutor_id = ? ORDER BY date DESC LIMIT 12");
    $select_content->execute([$teacher_id]);
    $teacher_content = $select_content->fetchAll(PDO::FETCH_ASSOC);

    // Process content files and create proper paths
    foreach ($teacher_content as &$content) {
        $file = isset($content['video']) ? trim($content['video']) : '';
        $thumb = isset($content['thumb']) ? trim($content['thumb']) : '';

        // Clean up file paths
        if ($file && ($file[0] === '/' || $file[0] === '\\')) {
            $file = ltrim($file, "/\\");
        }
        if ($thumb && ($thumb[0] === '/' || $thumb[0] === '\\')) {
            $thumb = ltrim($thumb, "/\\");
        }

        // Remove uploaded_files/ prefix if present
        $file = preg_replace('#^uploaded_files/+#', '', $file);
        $thumb = preg_replace('#^uploaded_files/+#', '', $thumb);

        // Build absolute filesystem paths for existence checks
        $absUploadDir = realpath(__DIR__ . '/../uploaded_files');
        $absFilePath = $file ? ($absUploadDir ? $absUploadDir . DIRECTORY_SEPARATOR . $file : '') : '';
        $absThumbPath = $thumb ? ($absUploadDir ? $absUploadDir . DIRECTORY_SEPARATOR . $thumb : '') : '';

        // Build web paths relative to this page (admin/*)
        $webFilePath = ($file && $absFilePath && is_file($absFilePath)) ? '../uploaded_files/' . $file : '';
        $webThumbPath = ($thumb && $absThumbPath && is_file($absThumbPath)) ? '../uploaded_files/' . $thumb : '../images/pic-1.jpg';

        $content['__web_file'] = $webFilePath;
        $content['__web_thumb'] = $webThumbPath;
        $content['__is_folder'] = false; // Content items are not folders
        $content['__item_type'] = 'content';
    }
    unset($content);
} catch (PDOException $e) {
    $teacher_content = [];
    error_log("Error retrieving content: " . $e->getMessage());
}

// Process playlists/folders for display
foreach ($teacher_playlists as &$playlist) {
    // For playlists, we use a folder icon instead of thumbnail
    $playlist['__web_thumb'] = '../images/folder-icon.png'; // Create this image or use a default
    $playlist['__web_file'] = ''; // Playlists don't have direct files
    $playlist['__is_folder'] = true;
    $playlist['__item_type'] = 'playlist';
    
    // Use playlist title as the main title
    $playlist['title'] = $playlist['title'] ?? 'Untitled Playlist';
    $playlist['description'] = $playlist['description'] ?? 'No description available';
}
unset($playlist);

// Combine playlists and content for carousel display
$carousel_items = array_merge($teacher_playlists, $teacher_content);

// Sort by date (newest first)
usort($carousel_items, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limit to 12 items for carousel
$carousel_items = array_slice($carousel_items, 0, 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($teacher['name']); ?> - Teacher Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/teachers_profile.css">
    
  
</head>

<body>
    <?php include '../components/admin_header.php'; ?>

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <a href="other_teacher.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Teachers
            </a>
            
            <div class="profile-image-container ">
                <?php if(!empty($teacher['image']) && file_exists('../uploaded_files/' . $teacher['image'])): ?>
                    <img src="../uploaded_files/<?= $teacher['image']; ?>" 
                         alt="<?= htmlspecialchars($teacher['name']); ?>" 
                         class="profile-image">
                <?php else: ?>
                    <img src="../images/default-avatar.png" 
                         alt="Default Profile" 
                         class="profile-image">
                <?php endif; ?>
            </div>

            <h1 class="profile-name"><?= htmlspecialchars($teacher['name']); ?></h1>
            <p class="profile-university">
                <i class="fas fa-university"></i>
                <?= htmlspecialchars($teacher['university'] ?? 'Not specified'); ?>
            </p>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-number"><?= $teacher['likes']; ?></div>
                    <div class="stat-label">Total Likes</div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-number"><?= $teacher['comments']; ?></div>
                    <div class="stat-label">Comments</div>
                </div>

                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-number"><?= $teacher['lectures']; ?></div>
                    <div class="stat-label">Content Created</div>
                </div>
            </div>
        </div>

        <!-- Recent Content Carousel -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-play-circle"></i>
                Recent Content 
            </h2>

            <?php if(empty($carousel_items)): ?>
                <div class="no-items-message">
                    <i class="fas fa-folder-open"></i>
                    <h3>No Content Available</h3>
                    <p>This teacher hasn't uploaded any content yet.</p>
                </div>
            <?php else: ?>
                <div class="carousel-container">
                    <button class="carousel-nav prev" onclick="previousSlide()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <div class="carousel-wrapper" id="carouselWrapper">
                        <!-- Carousel slides will be generated by JavaScript -->
                    </div>
                    
                    <button class="carousel-nav next" onclick="nextSlide()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <div class="carousel-dots" id="carouselDots">
                        <!-- Carousel dots will be generated by JavaScript -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Content Modal -->
    <div id="contentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-close" id="closeContentModal">&times;</span>
                
                <img id="modalThumbnail" src="" alt="Content Thumbnail" class="modal-thumbnail">
                
                <h3 id="modalTitle" class="modal-title"></h3>
                <p id="modalDescription" class="modal-description"></p>
            </div>
            
            <div class="modal-body">
                <div id="modalFileContainer" class="file-viewer"></div>
            </div>
        </div>
    </div>

    <script>
        // Content data from PHP - includes both folders and content
        const carouselData = [
            <?php foreach($carousel_items as $index => $item): ?>
            {
                title: <?= json_encode($item['title']); ?>,
                description: <?= json_encode($item['description'] ?? 'No description'); ?>,
                thumb: <?= json_encode($item['__web_thumb']); ?>,
                file: <?= json_encode($item['__web_file']); ?>,
                date: <?= json_encode($item['date']); ?>,
                isFolder: <?= json_encode($item['__is_folder']); ?>,
                itemType: <?= json_encode($item['__item_type']); ?>,
                id: <?= json_encode($item['id']); ?>,
                // Additional data for folders
                playlistId: <?= json_encode($item['id'] ?? null); ?>,
                tutorId: <?= json_encode($teacher_id); ?>
            }<?= $index < count($carousel_items) - 1 ? ',' : ''; ?>
            <?php endforeach; ?>
        ];
        
        console.log('Carousel Data:', carouselData);

        // Carousel functionality
        let currentSlide = 0;
        const itemsPerSlide = window.innerWidth > 768 ? 3 : 1;
        const totalSlides = Math.ceil(carouselData.length / itemsPerSlide);

        function initializeCarousel() {
            if (carouselData.length === 0) return;
            
            const wrapper = document.getElementById('carouselWrapper');
            const dotsContainer = document.getElementById('carouselDots');
            
            // Clear existing content
            wrapper.innerHTML = '';
            dotsContainer.innerHTML = '';
            
            // Create slides
            for (let i = 0; i < totalSlides; i++) {
                const slide = document.createElement('div');
                slide.className = 'carousel-slide';
                
                const startIndex = i * itemsPerSlide;
                const endIndex = Math.min(startIndex + itemsPerSlide, carouselData.length);
                
                for (let j = startIndex; j < endIndex; j++) {
                    const item = carouselData[j];
                    const contentItem = document.createElement('div');
                    contentItem.className = 'content-item';
                    
                    // Different click handlers for folders vs content
                    if (item.isFolder) {
                        contentItem.onclick = () => openPlaylistModal(item);
                        contentItem.classList.add('folder-item');
                    } else {
                        contentItem.onclick = () => openContentModal(item);
                        contentItem.classList.add('content-file-item');
                    }
                    
                    // Add folder/content indicator
                    const typeIcon = item.isFolder ? 
                        '<i class="fas fa-folder" style="color: #f39c12;"></i>' : 
                        '<i class="fas fa-file-video" style="color: #3498db;"></i>';
                    
                    contentItem.innerHTML = `
                        <div class="item-type-indicator">
                            ${typeIcon}
                            <span>${item.isFolder ? 'Folder' : 'Content'}</span>
                        </div>
                        <img src="${item.thumb}" alt="${item.title}" class="content-image">
                        <div class="content-title">${item.title}</div>
                        <div class="content-date">
                            <i class="fas fa-calendar"></i>
                            ${new Date(item.date).toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            })}
                        </div>
                    `;
                    
                    slide.appendChild(contentItem);
                }
                
                wrapper.appendChild(slide);
                
                // Create dot
                const dot = document.createElement('div');
                dot.className = `carousel-dot ${i === 0 ? 'active' : ''}`;
                dot.onclick = () => goToSlide(i);
                dotsContainer.appendChild(dot);
            }
        }

        function updateCarousel() {
            const wrapper = document.getElementById('carouselWrapper');
            const dots = document.querySelectorAll('.carousel-dot');
            
            wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        function nextSlide() {
            if (totalSlides <= 1) return;
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function previousSlide() {
            if (totalSlides <= 1) return;
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            updateCarousel();
        }
        function openContentModal(content) {
            const modal = document.getElementById('contentModal');
            const modalThumbnail = document.getElementById('modalThumbnail');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');
            const fileContainer = document.getElementById('modalFileContainer');
            
            // Set basic modal content
            modalThumbnail.src = content.thumb;
            modalTitle.textContent = content.title;
            modalDescription.textContent = content.description;
            
            // Clear and populate file container
            fileContainer.innerHTML = '';
            
            console.log('Opening content modal for:', content);
            
            if (content.file && content.file.trim() !== '') {
                const filePath = content.file.trim();
                const ext = filePath.split('.').pop().toLowerCase();
                const fileName = filePath.split('/').pop();
                
                const viewerContainer = document.createElement('div');
                viewerContainer.className = 'file-viewer';
                
                switch(ext) {
                    case 'mp4':
                    case 'webm':
                    case 'ogg':
                    case 'avi':
                    case 'mov':
                        viewerContainer.innerHTML = `
                            <video controls style="width:100%; max-height:50rem; border-radius:0.5rem;">
                                <source src="${filePath}" type="video/${ext === 'mov' ? 'quicktime' : ext}">
                                Your browser does not support the video tag.
                            </video>
                            <div class="file-actions">
                                <a href="${filePath}" target="_blank" class="file-btn primary">
                                    <i class="fas fa-play"></i> Open Video
                                </a>
                                <a href="${filePath}" download="${fileName}" class="file-btn secondary">
                                    <i class="fas fa-download"></i> Download Video
                                </a>
                            </div>
                        `;
                        break;
                        
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'bmp':
                    case 'webp':
                        viewerContainer.innerHTML = `
                            <img src="${filePath}" alt="Content Image" 
                                 style="width:100%; max-height:50rem; object-fit:contain; border-radius:0.5rem;">
                            <div class="file-actions">
                                <a href="${filePath}" target="_blank" class="file-btn primary">
                                    <i class="fas fa-external-link-alt"></i> Open Image
                                </a>
                                <a href="${filePath}" download="${fileName}" class="file-btn secondary">
                                    <i class="fas fa-download"></i> Download Image
                                </a>
                            </div>
                        `;
                        break;
                        
                    case 'pdf':
                        viewerContainer.innerHTML = `
                            <div class="file-preview">
                                <i class="fas fa-file-pdf file-icon" style="color:#d32f2f;"></i>
                                <h4 class="file-name">${fileName}</h4>
                                <iframe src="${filePath}#toolbar=1&navpanes=0&scrollbar=1" 
                                        style="width:100%; height:50rem; border:none; border-radius:0.5rem;"
                                        title="PDF Viewer">
                                </iframe>
                                <div class="file-actions">
                                    <a href="${filePath}" target="_blank" class="file-btn primary">
                                        <i class="fas fa-external-link-alt"></i> Open PDF
                                    </a>
                                    <a href="${filePath}" download="${fileName}" class="file-btn secondary">
                                        <i class="fas fa-download"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        `;
                        break;
                        
                    default:
                        viewerContainer.innerHTML = `
                            <div class="file-preview">
                                <i class="fas fa-file file-icon" style="color:#666;"></i>
                                <h4 class="file-name">${fileName}</h4>
                                <p>File type: .${ext.toUpperCase()}</p>
                                <div class="file-actions">
                                    <a href="${filePath}" target="_blank" class="file-btn primary">
                                        <i class="fas fa-external-link-alt"></i> Open File
                                    </a>
                                    <a href="${filePath}" download="${fileName}" class="file-btn secondary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        `;
                }
                
                fileContainer.appendChild(viewerContainer);
            } else {
                fileContainer.innerHTML = `
                    <div class="file-preview">
                        <i class="fas fa-exclamation-triangle file-icon" style="color:#ff9800;"></i>
                        <h4>No File Available</h4>
                        <p>This content doesn't have an associated file.</p>
                    </div>
                `;
            }
            
            modal.style.display = 'flex';
        }

        // Modal for playlist/folders - redirect to playlist view
        function openPlaylistModal(playlist) {
            // You can either redirect to a playlist page or show playlist contents in modal
            // For now, let's redirect to a playlist page
           
            window.location.href = `view_Tplaylist.php?playlist_id=${playlist.id}&teacher_id=${playlist.tutorId}`;
            
            
            // Alternatively, you could show playlist contents in the modal:
            /*
            const modal = document.getElementById('contentModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');
            const fileContainer = document.getElementById('modalFileContainer');
            
            modalTitle.textContent = playlist.title;
            modalDescription.textContent = playlist.description;
            
            fileContainer.innerHTML = `
                <div class="file-preview">
                    <i class="fas fa-folder-open file-icon" style="color:#f39c12; font-size:5rem;"></i>
                    <h4>Playlist Folder</h4>
                    <p>This is a playlist containing multiple content items.</p>
                    <div class="file-actions">
                        <a href="playlist_view.php?playlist_id=${playlist.id}&teacher_id=${playlist.tutorId}" 
                           class="file-btn primary">
                            <i class="fas fa-folder-open"></i> Open Playlist
                        </a>
                    </div>
                </div>
            `;
            
            modal.style.display = 'flex';
            */
        }

        // Modal close functionality
        document.getElementById('closeContentModal').onclick = function() {
            document.getElementById('contentModal').style.display = 'none';
        };

        window.onclick = function(event) {
            const modal = document.getElementById('contentModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };

        // Initialize carousel when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.carousel-container');
            if (container && carouselData.length > 0) {
                initializeCarousel();
                
                // Setup autoplay
                if (totalSlides > 1) {
                    let autoPlay = setInterval(nextSlide, 5000);
                    container.addEventListener('mouseenter', function() {
                        clearInterval(autoPlay);
                    });
                    container.addEventListener('mouseleave', function() {
                        autoPlay = setInterval(nextSlide, 5000);
                    });
                }

                // Touch support
                let touchStartX = 0;
                let touchEndX = 0;

                container.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                });

                container.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchEndX < touchStartX - 50) nextSlide();
                    if (touchEndX > touchStartX + 50) previousSlide();
                });
            }
        });
    </script>
    <?php include '../components/footer.php'; ?>
    <script src="../js/admin_script.js"></script>
</body>
</html>