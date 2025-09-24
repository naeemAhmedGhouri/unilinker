<?php
// Prevent any HTML output before JSON
ob_start();
error_reporting(0); // Turn off error display for JSON response

header('Content-Type: application/json');

try {
    include '../components/connect.php';
    
    if (!isset($_COOKIE['tutor_id'])) {
        ob_clean();
        echo json_encode(['teachers' => [], 'error' => 'Not logged in']);
        exit();
    }

    $tutor_id = $_COOKIE['tutor_id'];

    // Test database connection
    if (!$conn) {
        ob_clean();
        echo json_encode(['teachers' => [], 'error' => 'Database connection failed']);
        exit();
    }

    // Get only approved teachers who have never chatted with current user, excluding self
    $query = $conn->prepare("
        SELECT id, name, image, profession 
        FROM tutors 
        WHERE status = 'Approved' 
        AND id != ?
        AND id NOT IN (
            SELECT DISTINCT 
                CASE 
                    WHEN sender_id = ? THEN receiver_id 
                    ELSE sender_id 
                END as chat_partner_id
            FROM messages 
            WHERE sender_id = ? OR receiver_id = ?
        )
        ORDER BY name
    ");
    
    $query->execute([$tutor_id, $tutor_id, $tutor_id, $tutor_id]);
    $teachers = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Clean any output buffer and send JSON
    ob_clean();
    echo json_encode([
        'teachers' => $teachers, 
        'success' => true,
        'count' => count($teachers)
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'teachers' => [], 
        'error' => $e->getMessage(),
        'success' => false
    ]);
}
?>