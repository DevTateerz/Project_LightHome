<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

try {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];

// GET: ดึงข้อมูลไฟทั้งหมด
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_all') {
    $query = "SELECT l.*, r.room_name, r.room_code 
              FROM lights l 
              INNER JOIN rooms r ON l.room_id = r.id 
              ORDER BY r.id, l.id";
    
    $result = $conn->query($query);
    $lights = [];
    
    while ($row = $result->fetch_assoc()) {
        $lights[] = $row;
    }
    
    echo json_encode(['success' => true, 'lights' => $lights]);
    closeDBConnection($conn);
    exit();
}

// POST: จัดการต่างๆ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่า Guest สามารถควบคุมได้หรือไม่
    $user = getCurrentUser();
    if ($user['role'] === 'guest') {
        echo json_encode(['success' => false, 'message' => 'Guest ไม่สามารถควบคุมไฟได้ (ดูอย่างเดียว)']);
        closeDBConnection($conn);
        exit();
    }
    
    $action = $_POST['action'] ?? '';
    
    // เปิด/ปิดไฟแต่ละอัน
    if ($action === 'toggle') {
        $light_id = intval($_POST['light_id']);
        $status = $_POST['status'] === 'on' ? 'on' : 'off';
        
        $stmt = $conn->prepare("UPDATE lights SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $status, $light_id);
        
        if ($stmt->execute()) {
            // ดึงข้อมูลไฟ
            $lightStmt = $conn->prepare("SELECT l.light_name, r.room_name FROM lights l INNER JOIN rooms r ON l.room_id = r.id WHERE l.id = ?");
            $lightStmt->bind_param("i", $light_id);
            $lightStmt->execute();
            $lightResult = $lightStmt->get_result();
            $light = $lightResult->fetch_assoc();
            
            // บันทึก log
            $action_type = $status === 'on' ? 'light_on' : 'light_off';
            $action_detail = ($status === 'on' ? 'เปิด' : 'ปิด') . 'ไฟ' . $light['light_name'] . ' (' . $light['room_name'] . ')';
            logActivity($user_id, $action_type, $action_detail, $light_id);
            
            echo json_encode([
                'success' => true, 
                'message' => $action_detail . 'สำเร็จ'
            ]);
            $lightStmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
        }
        
        $stmt->close();
    }
    
    // ปรับความสว่าง
    elseif ($action === 'brightness') {
        $light_id = intval($_POST['light_id']);
        $brightness = intval($_POST['brightness']);
        
        // จำกัดค่า 0-100
        $brightness = max(0, min(100, $brightness));
        
        $stmt = $conn->prepare("UPDATE lights SET brightness = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ii", $brightness, $light_id);
        
        if ($stmt->execute()) {
            // ดึงข้อมูลไฟ
            $lightStmt = $conn->prepare("SELECT l.light_name, r.room_name FROM lights l INNER JOIN rooms r ON l.room_id = r.id WHERE l.id = ?");
            $lightStmt->bind_param("i", $light_id);
            $lightStmt->execute();
            $lightResult = $lightStmt->get_result();
            $light = $lightResult->fetch_assoc();
            
            // บันทึก log
            $action_detail = 'ปรับความสว่างไฟ' . $light['light_name'] . ' (' . $light['room_name'] . ') เป็น ' . $brightness . '%';
            logActivity($user_id, 'brightness_change', $action_detail, $light_id);
            
            echo json_encode([
                'success' => true, 
                'message' => 'ปรับความสว่างสำเร็จ'
            ]);
            $lightStmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
        }
        
        $stmt->close();
    }
    
    // เปิด/ปิดไฟทั้งหมด
    elseif ($action === 'toggle_all') {
        $status = $_POST['status'] === 'on' ? 'on' : 'off';
        
        $stmt = $conn->prepare("UPDATE lights SET status = ?, updated_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("s", $status);
        
        if ($stmt->execute()) {
            // บันทึก log
            $action_type = $status === 'on' ? 'all_on' : 'all_off';
            $action_detail = ($status === 'on' ? 'เปิด' : 'ปิด') . 'ไฟทั้งหมด';
            logActivity($user_id, $action_type, $action_detail);
            
            echo json_encode([
                'success' => true, 
                'message' => $action_detail . 'สำเร็จ'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
        }
        
        $stmt->close();
    }
    
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    closeDBConnection($conn);
    exit();
}

} catch (Exception $e) {
    error_log("Lights API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>