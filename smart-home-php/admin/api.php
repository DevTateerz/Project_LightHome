<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

// ตรวจสอบว่าเป็น admin หรือไม่
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึง']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// ==================== USER MANAGEMENT ====================

if ($action === 'get_users') {
    $result = $conn->query("SELECT id, username, email, full_name, role, status, last_login, created_at FROM users ORDER BY role DESC, id");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(['success' => true, 'users' => $users]);
}

elseif ($action === 'add_user') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'] ?? 'user'; // Default เป็น user
    
    // Validate role
    if (!in_array($role, ['admin', 'user', 'guest'])) {
        echo json_encode(['success' => false, 'message' => 'Role ไม่ถูกต้อง']);
        exit();
    }
    
    // ตรวจสอบว่าชื่อผู้ใช้ซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว']);
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $role);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "เพิ่มสมาชิกใหม่: $username (role: $role)");
        echo json_encode(['success' => true, 'message' => 'เพิ่มสมาชิกสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

elseif ($action === 'delete_user') {
    $delete_user_id = intval($_POST['user_id']);
    
    // ตรวจสอบว่าเป็น admin หรือไม่
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['role'] === 'admin') {
        // นับจำนวน admin
        $count_stmt = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
        $count = $count_stmt->fetch_assoc()['admin_count'];
        
        if ($count <= 1) {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายได้']);
            exit();
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_user_id);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "ลบสมาชิก ID: $delete_user_id");
        echo json_encode(['success' => true, 'message' => 'ลบสมาชิกสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

elseif ($action === 'update_user') {
    $update_user_id = intval($_POST['user_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];
    $role = $_POST['role'] ?? 'user';
    $new_password = $_POST['new_password'] ?? '';
    
    // Validate role
    if (!in_array($role, ['admin', 'user', 'guest'])) {
        echo json_encode(['success' => false, 'message' => 'Role ไม่ถูกต้อง']);
        exit();
    }
    
    // อัพเดทข้อมูลพื้นฐาน
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, status = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $full_name, $email, $hashed_password, $status, $role, $update_user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, status = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $full_name, $email, $status, $role, $update_user_id);
    }
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "แก้ไขข้อมูลสมาชิก ID: $update_user_id (role: $role)");
        echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

// ==================== ROOM MANAGEMENT ====================

elseif ($action === 'get_rooms') {
    $result = $conn->query("
        SELECT r.*, COUNT(l.id) as light_count 
        FROM rooms r 
        LEFT JOIN lights l ON r.id = l.room_id 
        GROUP BY r.id 
        ORDER BY r.id
    ");
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    echo json_encode(['success' => true, 'rooms' => $rooms]);
}

elseif ($action === 'add_room') {
    $room_name = trim($_POST['room_name']);
    $room_code = trim($_POST['room_code']);
    $description = trim($_POST['description'] ?? '');
    
    // ตรวจสอบว่ารหัสห้องซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT id FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'รหัสห้องนี้มีอยู่แล้ว']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO rooms (room_name, room_code, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $room_name, $room_code, $description);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "เพิ่มห้องใหม่: $room_name");
        echo json_encode(['success' => true, 'message' => 'เพิ่มห้องสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

elseif ($action === 'delete_room') {
    $room_id = intval($_POST['room_id']);
    
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "ลบห้อง ID: $room_id");
        echo json_encode(['success' => true, 'message' => 'ลบห้องสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

elseif ($action === 'update_room') {
    $room_id = intval($_POST['room_id']);
    $room_name = trim($_POST['room_name']);
    $description = trim($_POST['description'] ?? '');
    
    $stmt = $conn->prepare("UPDATE rooms SET room_name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $room_name, $description, $room_id);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "แก้ไขห้อง ID: $room_id");
        echo json_encode(['success' => true, 'message' => 'แก้ไขห้องสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

// ==================== LIGHT MANAGEMENT ====================

elseif ($action === 'get_all_lights') {
    $result = $conn->query("
        SELECT l.*, r.room_name 
        FROM lights l 
        INNER JOIN rooms r ON l.room_id = r.id 
        ORDER BY r.id, l.id
    ");
    $lights = [];
    while ($row = $result->fetch_assoc()) {
        $lights[] = $row;
    }
    echo json_encode(['success' => true, 'lights' => $lights]);
}

elseif ($action === 'add_light') {
    $room_id = intval($_POST['room_id']);
    $light_name = trim($_POST['light_name']);
    $device_code = trim($_POST['device_code']);
    
    // ตรวจสอบว่ารหัสอุปกรณ์ซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT id FROM lights WHERE device_code = ?");
    $stmt->bind_param("s", $device_code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'รหัสอุปกรณ์นี้มีอยู่แล้ว']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO lights (room_id, light_name, device_code) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $room_id, $light_name, $device_code);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "เพิ่มจุดไฟใหม่: $light_name");
        echo json_encode(['success' => true, 'message' => 'เพิ่มจุดไฟสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

elseif ($action === 'update_light') {
    $light_id = intval($_POST['light_id']);
    $light_name = trim($_POST['light_name']);
    $room_id = intval($_POST['room_id']);
    
    $stmt = $conn->prepare("UPDATE lights SET light_name = ?, room_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $light_name, $room_id, $light_id);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "แก้ไขจุดไฟ ID: $light_id");
        echo json_encode(['success' => true, 'message' => 'แก้ไขจุดไฟสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

elseif ($action === 'delete_light') {
    $light_id = intval($_POST['light_id']);
    
    $stmt = $conn->prepare("DELETE FROM lights WHERE id = ?");
    $stmt->bind_param("i", $light_id);
    
    if ($stmt->execute()) {
        logActivity($user_id, 'admin_action', "ลบจุดไฟ ID: $light_id");
        echo json_encode(['success' => true, 'message' => 'ลบจุดไฟสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }
}

// ==================== STATISTICS ====================

elseif ($action === 'get_statistics') {
    $stats = [];
    
    // จำนวนสมาชิกทั้งหมด
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // จำนวนห้องทั้งหมด
    $result = $conn->query("SELECT COUNT(*) as count FROM rooms");
    $stats['total_rooms'] = $result->fetch_assoc()['count'];
    
    // จำนวนจุดไฟทั้งหมด
    $result = $conn->query("SELECT COUNT(*) as count FROM lights");
    $stats['total_lights'] = $result->fetch_assoc()['count'];
    
    // จำนวนกิจกรรมวันนี้
    $result = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()");
    $stats['today_activities'] = $result->fetch_assoc()['count'];
    
    // ผู้ใช้งานล่าสุด
    $result = $conn->query("SELECT username, full_name, last_login FROM users WHERE last_login IS NOT NULL ORDER BY last_login DESC LIMIT 5");
    $stats['recent_users'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['recent_users'][] = $row;
    }
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

closeDBConnection($conn);
?>