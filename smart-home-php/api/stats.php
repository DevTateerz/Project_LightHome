<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

$conn = getDBConnection();

$stats = [];

// นับจำนวนไฟที่เปิดอยู่
$result = $conn->query("SELECT COUNT(*) as count FROM lights WHERE status = 'on'");
$row = $result->fetch_assoc();
$stats['lights_on'] = $row['count'];

// คำนวณพลังงานที่ใช้วันนี้ (จำลอง)
$result = $conn->query("
    SELECT COALESCE(SUM(total_kwh), 0) as total_kwh 
    FROM energy_usage 
    WHERE usage_date = CURDATE()
");
$row = $result->fetch_assoc();
$stats['today_energy'] = number_format($row['total_kwh'], 1);

// นับการใช้งานวันนี้
$result = $conn->query("
    SELECT COUNT(*) as count 
    FROM activity_logs 
    WHERE DATE(created_at) = CURDATE()
");
$row = $result->fetch_assoc();
$stats['today_actions'] = $row['count'];

// นับผู้ใช้ออนไลน์จริงๆ จากตาราง user_sessions
// ลบ sessions ที่หมดอายุก่อน
cleanExpiredSessions();

// นับ user ที่มี session active (มีการใช้งานใน 30 นาทีที่ผ่านมา)
$result = $conn->query("
    SELECT COUNT(DISTINCT user_id) as count 
    FROM user_sessions 
    WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
");
$row = $result->fetch_assoc();
$stats['online_users'] = max(1, $row['count']); // อย่างน้อย 1 คน (ตัวเอง)

// อัพเดท session activity ของตัวเอง
updateSessionActivity();

// สถิติการใช้พลังงานรายห้อง
$result = $conn->query("
    SELECT 
        r.room_name,
        COALESCE(SUM(eu.total_kwh), 0) as total_kwh
    FROM rooms r
    LEFT JOIN lights l ON r.id = l.room_id
    LEFT JOIN energy_usage eu ON l.id = eu.light_id AND eu.usage_date = CURDATE()
    GROUP BY r.id, r.room_name
    ORDER BY total_kwh DESC
");

$room_energy = [];
while ($row = $result->fetch_assoc()) {
    $room_energy[] = $row;
}
$stats['room_energy'] = $room_energy;

echo json_encode([
    'success' => true, 
    'stats' => $stats
]);

closeDBConnection($conn);
?>