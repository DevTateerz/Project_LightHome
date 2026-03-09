<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

$conn = getDBConnection();

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$limit = max(1, min(100, $limit)); // จำกัด 1-100

$query = "
    SELECT 
        al.id,
        al.action_type,
        al.action_detail,
        al.voice_command,
        al.created_at,
        u.username,
        u.full_name
    FROM activity_logs al
    INNER JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    // แปลงวันที่เป็นภาษาไทย
    $timestamp = strtotime($row['created_at']);
    $thai_date = date('d/m/Y H:i', $timestamp);
    
    $activities[] = [
        'id' => $row['id'],
        'action_type' => $row['action_type'],
        'action_detail' => $row['action_detail'],
        'voice_command' => $row['voice_command'],
        'username' => $row['full_name'] ?: $row['username'],
        'created_at' => $thai_date,
        'created_at_raw' => $row['created_at']
    ];
}

echo json_encode([
    'success' => true, 
    'activities' => $activities,
    'count' => count($activities)
]);

$stmt->close();
closeDBConnection($conn);
?>