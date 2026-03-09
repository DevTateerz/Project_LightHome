<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

// ตรวจสอบว่า Guest สามารถควบคุมได้หรือไม่
$user = getCurrentUser();
if ($user['role'] === 'guest') {
    echo json_encode(['success' => false, 'message' => 'Guest ไม่สามารถใช้คำสั่งเสียงได้ (ดูอย่างเดียว)']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';
$command = $_POST['command'] ?? '';
$room_name = $_POST['room_name'] ?? '';
$light_type = $_POST['light_type'] ?? '';  // 🆕 เพิ่มสำหรับจุดไฟเฉพาะ

$response = ['success' => false, 'message' => 'ไม่สามารถดำเนินการได้'];

switch ($action) {
    case 'specific_light_on':
        // 🆕 เปิดไฟเฉพาะจุด
        if (empty($light_type)) {
            $response = ['success' => false, 'message' => 'ไม่พบข้อมูลจุดไฟ'];
            break;
        }
        
        // ค้นหาจุดไฟที่มีชื่อตรงกับ keyword
        $search_term = '%' . $light_type . '%';
        $stmt = $conn->prepare("
            UPDATE lights 
            SET status = 'on', updated_at = CURRENT_TIMESTAMP
            WHERE light_name LIKE ?
        ");
        $stmt->bind_param("s", $search_term);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            logActivity($user_id, 'voice_command', 'เปิดไฟ' . $light_type . 'ด้วยเสียง', null, $command);
            $response = [
                'success' => true, 
                'message' => 'เปิดไฟ' . $light_type . 'สำเร็จ (' . $stmt->affected_rows . ' จุด)',
                'action' => 'specific_light_on',
                'light_type' => $light_type,
                'affected' => $stmt->affected_rows
            ];
        } else {
            $response = ['success' => false, 'message' => 'ไม่พบจุดไฟที่ตรงกับ "' . $light_type . '"'];
        }
        $stmt->close();
        break;
        
    case 'specific_light_off':
        // 🆕 ปิดไฟเฉพาะจุด
        if (empty($light_type)) {
            $response = ['success' => false, 'message' => 'ไม่พบข้อมูลจุดไฟ'];
            break;
        }
        
        // ค้นหาจุดไฟที่มีชื่อตรงกับ keyword
        $search_term = '%' . $light_type . '%';
        $stmt = $conn->prepare("
            UPDATE lights 
            SET status = 'off', updated_at = CURRENT_TIMESTAMP
            WHERE light_name LIKE ?
        ");
        $stmt->bind_param("s", $search_term);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            logActivity($user_id, 'voice_command', 'ปิดไฟ' . $light_type . 'ด้วยเสียง', null, $command);
            $response = [
                'success' => true, 
                'message' => 'ปิดไฟ' . $light_type . 'สำเร็จ (' . $stmt->affected_rows . ' จุด)',
                'action' => 'specific_light_off',
                'light_type' => $light_type,
                'affected' => $stmt->affected_rows
            ];
        } else {
            $response = ['success' => false, 'message' => 'ไม่พบจุดไฟที่ตรงกับ "' . $light_type . '"'];
        }
        $stmt->close();
        break;
    
    case 'all_on':
        // เปิดไฟทั้งหมด
        $stmt = $conn->prepare("UPDATE lights SET status = 'on', updated_at = CURRENT_TIMESTAMP");
        if ($stmt->execute()) {
            logActivity($user_id, 'voice_command', 'เปิดไฟทั้งหมดด้วยเสียง', null, $command);
            $response = [
                'success' => true, 
                'message' => 'เปิดไฟทั้งหมดสำเร็จ',
                'action' => 'all_on'
            ];
        }
        $stmt->close();
        break;
        
    case 'all_off':
        // ปิดไฟทั้งหมด
        $stmt = $conn->prepare("UPDATE lights SET status = 'off', updated_at = CURRENT_TIMESTAMP");
        if ($stmt->execute()) {
            logActivity($user_id, 'voice_command', 'ปิดไฟทั้งหมดด้วยเสียง', null, $command);
            $response = [
                'success' => true, 
                'message' => 'ปิดไฟทั้งหมดสำเร็จ',
                'action' => 'all_off'
            ];
        }
        $stmt->close();
        break;
        
    case 'room_on':
        // เปิดไฟในห้อง
        if (empty($room_name)) {
            $response = ['success' => false, 'message' => 'ไม่พบข้อมูลห้อง'];
            break;
        }
        
        $stmt = $conn->prepare("
            UPDATE lights l
            INNER JOIN rooms r ON l.room_id = r.id
            SET l.status = 'on', l.updated_at = CURRENT_TIMESTAMP
            WHERE r.room_name = ?
        ");
        $stmt->bind_param("s", $room_name);
        
        if ($stmt->execute()) {
            logActivity($user_id, 'voice_command', 'เปิดไฟ' . $room_name . 'ด้วยเสียง', null, $command);
            $response = [
                'success' => true, 
                'message' => 'เปิดไฟ' . $room_name . 'สำเร็จ',
                'action' => 'room_on',
                'room' => $room_name
            ];
        }
        $stmt->close();
        break;
        
    case 'room_off':
        // ปิดไฟในห้อง
        if (empty($room_name)) {
            $response = ['success' => false, 'message' => 'ไม่พบข้อมูลห้อง'];
            break;
        }
        
        $stmt = $conn->prepare("
            UPDATE lights l
            INNER JOIN rooms r ON l.room_id = r.id
            SET l.status = 'off', l.updated_at = CURRENT_TIMESTAMP
            WHERE r.room_name = ?
        ");
        $stmt->bind_param("s", $room_name);
        
        if ($stmt->execute()) {
            logActivity($user_id, 'voice_command', 'ปิดไฟ' . $room_name . 'ด้วยเสียง', null, $command);
            $response = [
                'success' => true, 
                'message' => 'ปิดไฟ' . $room_name . 'สำเร็จ',
                'action' => 'room_off',
                'room' => $room_name
            ];
        }
        $stmt->close();
        break;
        
    default:
        $response = ['success' => false, 'message' => 'คำสั่งไม่ถูกต้อง'];
}

echo json_encode($response);
closeDBConnection($conn);
?>