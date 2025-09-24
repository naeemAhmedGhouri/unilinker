<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/connect.php';

if (!isset($_COOKIE['tutor_id'])) {
    header('Location: ../login1.php');
    exit();
}

$tutor_id = $_COOKIE['tutor_id'];

// Fetch all previous conversations
$previous_chats_query = $conn->prepare("
    SELECT DISTINCT
        CASE 
            WHEN m.sender_id = ? THEN m.receiver_id 
            ELSE m.sender_id 
        END as chat_partner_id,
        t.name as partner_name,
        t.image as partner_image,
        t.profession as partner_profession,
        MAX(m.date) as last_message_date,
        (SELECT message FROM messages m2 
         WHERE ((m2.sender_id = ? AND m2.receiver_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) 
                OR (m2.receiver_id = ? AND m2.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END))
         ORDER BY m2.date DESC LIMIT 1) as last_message,
        (SELECT COUNT(*) FROM messages m3 
         WHERE m3.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END 
         AND m3.receiver_id = ? AND m3.is_read = 0) as unread_count
    FROM messages m
    JOIN tutors t ON t.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND t.status = 'Approved'
    GROUP BY chat_partner_id, t.name, t.image, t.profession
    ORDER BY last_message_date DESC
");
$previous_chats_query->execute([
    $tutor_id, $tutor_id, $tutor_id, $tutor_id, $tutor_id, 
    $tutor_id, $tutor_id, $tutor_id, $tutor_id, $tutor_id
]);
$previous_chats = $previous_chats_query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - Education Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="chat.css">
</head>
<style>
    
</style>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="chat-list-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-comments"></i> My Conversations</h1>
        <p>Stay connected with your colleagues</p>
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number"><?= count($previous_chats); ?></span>
                <span class="stat-label">Active Chats</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= array_sum(array_column($previous_chats, 'unread_count')); ?></span>
                <span class="stat-label">Unread Messages</span>
            </div>
        </div>
    </div>

    <!-- Search and New Chat Section -->
    <div class="search-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="chatSearch" placeholder="Search conversations...">
        </div>
        <button class="start-new-chat-btn" id="startNewChatBtn">
            <i class="fas fa-plus"></i>
            <span>Start New Chat</span>
        </button>
    </div>

    <!-- Chat List -->
    <div class="chat-list" id="chatList">
        <?php if (!empty($previous_chats)): ?>
            <?php foreach ($previous_chats as $chat): ?>
                <a href="chat.php?teacher_id=<?= $chat['chat_partner_id']; ?>" class="chat-item">
                    <div class="chat-avatar">
                        <?php if (!empty($chat['partner_image'])): ?>
                            <img src="../uploaded_files/<?= htmlspecialchars($chat['partner_image']); ?>" 
                                 alt="<?= htmlspecialchars($chat['partner_name']); ?>">
                        <?php else: ?>
                            <div class="default-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                       
                    </div>
                    
                    <div class="chat-info">
                        <div class="chat-header-info">
                            <h3 class="chat-name"><?= htmlspecialchars($chat['partner_name']); ?></h3>
                            <span class="chat-time">
                                <?php 
                                $time_diff = time() - strtotime($chat['last_message_date']);
                                if ($time_diff < 86400) {
                                    echo date('h:i A', strtotime($chat['last_message_date']));
                                } elseif ($time_diff < 604800) {
                                    echo date('D', strtotime($chat['last_message_date']));
                                } else {
                                    echo date('M j', strtotime($chat['last_message_date']));
                                }
                                ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($chat['partner_profession'])): ?>
                            <p class="chat-profession"><?= htmlspecialchars($chat['partner_profession']); ?></p>
                        <?php endif; ?>
                        
                        <p class="chat-preview">
                            <?= htmlspecialchars(substr($chat['last_message'], 0, 60)) . (strlen($chat['last_message']) > 60 ? '...' : ''); ?>
                        </p>
                        
                        <div class="chat-meta">
                            <span></span>
                            <?php if ($chat['unread_count'] > 0): ?>
                                <div class="unread-badge"><?= $chat['unread_count']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-chats">
                <div class="no-chats-icon">
                    <i class="fas fa-comment-slash"></i>
                </div>
                <h3>No conversations yet</h3>
                <p>Start connecting with your colleagues and students.<br>Click the "Start New Chat" button to begin!</p>
                <button class="start-new-chat-btn" onclick="document.getElementById('startNewChatBtn').click()">
                    <i class="fas fa-plus"></i>
                    <span>Start Your First Chat</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- New Chat Modal -->
<div class="modal" id="newChatModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Start New Chat</h3>
            <button class="close-modal" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-search">
                <input type="text" id="teacherSearch" placeholder="Search for teachers...">
            </div>
            <div class="teachers-list" id="teachersList">
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                    <p>Loading available teachers...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/admin_script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startNewChatBtn = document.getElementById('startNewChatBtn');
    const newChatModal = document.getElementById('newChatModal');
    const closeModal = document.getElementById('closeModal');
    const teacherSearch = document.getElementById('teacherSearch');
    const teachersList = document.getElementById('teachersList');
    const chatSearch = document.getElementById('chatSearch');

    // Start new chat modal
    startNewChatBtn.addEventListener('click', function() {
        newChatModal.classList.add('active');
        loadAvailableTeachers();
    });

    // Close modal
    closeModal.addEventListener('click', function() {
        newChatModal.classList.remove('active');
    });

    // Close modal on outside click
    newChatModal.addEventListener('click', function(e) {
        if (e.target === newChatModal) {
            newChatModal.classList.remove('active');
        }
    });

    // Search in existing chats
    chatSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const chatItems = document.querySelectorAll('.chat-item');
        
        chatItems.forEach(item => {
            const teacherName = item.querySelector('.chat-name').textContent.toLowerCase();
            const chatPreview = item.querySelector('.chat-preview').textContent.toLowerCase();
            
            if (teacherName.includes(searchTerm) || chatPreview.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Search in new teachers modal
    teacherSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const teacherItems = teachersList.querySelectorAll('.teacher-item');
        
        teacherItems.forEach(item => {
            const teacherName = item.querySelector('h4').textContent.toLowerCase();
            const profession = item.querySelector('p') ? item.querySelector('p').textContent.toLowerCase() : '';
            
            if (teacherName.includes(searchTerm) || profession.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Global functions (outside DOMContentLoaded)
function loadAvailableTeachers() {
    fetch('get_available_teachers.php')
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                const teachersList = document.getElementById('teachersList');
                let html = '';
                
                if (data.teachers && data.teachers.length > 0) {
                    data.teachers.forEach(teacher => {
                        html += `
                            <div class="teacher-item" data-teacher-id="${teacher.id}" style="cursor: pointer;">
                                <div class="teacher-avatar">
                                    ${teacher.image ? 
                                        `<img src="../uploaded_files/${teacher.image}" alt="${teacher.name}">` :
                                        `<div class="default-avatar"><i class="fas fa-user"></i></div>`
                                    }
                                </div>
                                <div class="teacher-info">
                                    <h4>${teacher.name}</h4>
                                    <p>${teacher.profession || 'Teacher'}</p>
                                </div>
                            </div>
                        `;
                    });
                    
                    teachersList.innerHTML = html;
                    
                    // Add click event listener to all teacher items
                    const teacherItems = teachersList.querySelectorAll('.teacher-item');
                    teacherItems.forEach(item => {
                        item.addEventListener('click', function() {
                            const teacherId = this.dataset.teacherId;
                            console.log('Starting chat with teacher ID:', teacherId);
                            startChatWith(teacherId);
                        });
                    });
                    
                } else {
                    html = `
                        <div style="text-align: center; color: #666; padding: 3rem;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; color: #4CAF50; margin-bottom: 1rem;"></i>
                            <h4>All Set!</h4>
                            <p>You've already connected with all available teachers.</p>
                        </div>
                    `;
                    teachersList.innerHTML = html;
                }
                
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            const teachersList = document.getElementById('teachersList');
            teachersList.innerHTML = `
                <div style="text-align: center; color: #f44336; padding: 3rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h4>Error Loading Teachers</h4>
                    <p>Error: ${error.message}</p>
                    <button onclick="loadAvailableTeachers()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Try Again</button>
                </div>
            `;
        });
}

function startChatWith(teacherId) {
    console.log('Redirecting to chat with teacher ID:', teacherId);
    
    // Close the modal first
    const modal = document.getElementById('newChatModal');
    modal.classList.remove('active');
    
    // Add a small delay to ensure modal closes properly
    setTimeout(() => {
        window.location.href = `chat.php?teacher_id=${teacherId}`;
    }, 100);
}

function viewProfile(teacherId) {
    window.location.href = `teacher_profile.php?teacher_id=${teacherId}`;
}
</script>

</body>
</html>