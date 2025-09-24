<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/connect.php';

if (!isset($_COOKIE['tutor_id'])) {
    header('Location: ../login1.php');
    exit();
}

$tutor_id = $_COOKIE['tutor_id'];

// Validate teacher_id
if (!isset($_GET['teacher_id']) || empty($_GET['teacher_id'])) {
    header('Location: teachers_content.php');
    exit();
}

$teacher_id = $_GET['teacher_id'];

// Mark messages as read when opening this chat
$mark_read_query = $conn->prepare("UPDATE `messages` SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$mark_read_query->execute([$teacher_id, $tutor_id]);

// Fetch selected teacher with last_active status
$select_teacher = $conn->prepare("SELECT id, name, image, last_active FROM tutors WHERE id = ? LIMIT 1");
$select_teacher->execute([$teacher_id]);
$teacher = $select_teacher->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    echo "<script>alert('Teacher not found!'); window.location.href='teachers_content.php';</script>";
    exit();
}

$select_current = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
$select_current->execute([$tutor_id]);
$current_teacher = $select_current->fetch(PDO::FETCH_ASSOC);

// Handle message sending
if (isset($_POST['send_message'])) {
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
    if (!empty($message)) {
        $insert_message = $conn->prepare("INSERT INTO `messages` (sender_id, receiver_id, message, date) VALUES (?, ?, ?, NOW())");
        $insert_message->execute([$tutor_id, $teacher_id, $message]);
    }
    header('Location: chat.php?teacher_id=' . $teacher_id);
    exit();
}

// Fetch chat messages
$select_messages = $conn->prepare("
    SELECT m.*, t.name AS sender_name, t.image AS sender_image
    FROM `messages` m
    LEFT JOIN `tutors` t ON m.sender_id = t.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.date ASC
");
$select_messages->execute([$tutor_id, $teacher_id, $teacher_id, $tutor_id]);
$messages = $select_messages->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with <?= htmlspecialchars($teacher['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="chat.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="chat-container">
<div class="chat-header">
    <?php if(!empty($teacher['image'])): ?>
        <img src="../uploaded_files/<?= htmlspecialchars($teacher['image']); ?>" 
             alt="<?= htmlspecialchars($teacher['name']); ?>">
    <?php else: ?>
        <div class="default-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
    <?php endif; ?>
    
    <div class="teacher-info">
        <h3><?= htmlspecialchars($teacher['name']); ?></h3>
        <small>
            <?php 
            if (!empty($teacher['last_active'])) {
                $last_active_time = strtotime($teacher['last_active']);
                $current_time = time();
                $time_diff = $current_time - $last_active_time;
                
                if ($time_diff < 300) { // Less than 5 minutes
                    echo '<span style="color: #4CAF50;">● Online</span>';
                } elseif ($time_diff < 3600) { // Less than 1 hour
                    echo 'Last seen ' . floor($time_diff / 60) . ' minutes ago';
                } elseif ($time_diff < 86400) { // Less than 24 hours
                    echo 'Last seen ' . floor($time_diff / 3600) . ' hours ago';
                } elseif ($time_diff < 604800) { // Less than 7 days
                    echo 'Last seen ' . floor($time_diff / 86400) . ' days ago';
                } else {
                    echo 'Last seen ' . date('M j, Y', $last_active_time);
                }
            } else {
                echo 'Last seen: Unknown';
            }
            ?>
        </small>
    </div>
</div>
    <div class="chat-box" id="chat-box">
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <?php
            $is_current_user = ($message['sender_id'] == $tutor_id);
        
            $sender_name = $message['sender_name'];
            $sender_image = $message['sender_image'];
            ?>
            <div class="message <?= $is_current_user ? 'right' : 'left'; ?>">
                <?php if (!$is_current_user): ?>
                    <img src="../uploaded_files/<?= htmlspecialchars($sender_image); ?>" 
                         alt="<?= htmlspecialchars($sender_name); ?>">
                <?php endif; ?>
                <div class="message-content">
                    <?= htmlspecialchars($message['message']); ?>
                    <div class="message-time">
                        <?= date('h:i A', strtotime($message['date'])); ?>
                        <?php if ($is_current_user): ?>
                            <span style="font-size:0.8em; color:#ddd;">(You)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($is_current_user): ?>
                    <img src="../uploaded_files/<?= htmlspecialchars($sender_image); ?>" 
                         alt="<?= htmlspecialchars($sender_name); ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; color:#777;">No messages yet. Start the conversation!</p>
    <?php endif; ?>
</div>

    <form action="" method="post" class="chat-form">
        <input type="text" name="message" placeholder="Type a message..." required>
        <button type="submit" name="send_message">Send</button>
    </form>
</section>

<script src="../js/admin_script.js"></script>
<script>
    // Auto-scroll to bottom of chat
    document.addEventListener('DOMContentLoaded', function() {
        const chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;
        
        // Update header notification count after page loads
        setTimeout(function() {
            // Force refresh of notification count by triggering a small request
            fetch('', {method: 'HEAD'}).then(() => {
                // This will cause the header to refresh its notification count
                window.parent.postMessage('refresh_notifications', '*');
            });
        }, 500);
    });
</script>

<?php include '../components/footer.php'; ?>

</body>
</html>