<?php
include 'components/connect.php';

header('Content-Type: application/json');

if(!isset($_GET['teacher_id']) || empty($_GET['teacher_id'])) {
    echo json_encode(['error' => 'Teacher ID required']);
    exit();
}

$teacher_id = $_GET['teacher_id'];

try {
    // Count content
    $count_content = $conn->prepare("SELECT COUNT(*) as total FROM `content` WHERE tutor_id = ?");
    $count_content->execute([$teacher_id]);
    $content_count = $count_content->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Count likes (adjust table name as per your database)
    $count_likes = $conn->prepare("SELECT COUNT(*) as total FROM `likes` WHERE tutor_id = ?");
    $count_likes->execute([$teacher_id]);
    $likes_count = $count_likes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Count comments (adjust table name as per your database)
    $count_comments = $conn->prepare("SELECT COUNT(*) as total FROM `comments` WHERE tutor_id = ?");
    $count_comments->execute([$teacher_id]);
    $comments_count = $count_comments->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'content' => $content_count,
        'likes' => $likes_count,
        'comments' => $comments_count
    ]);

} catch(Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>