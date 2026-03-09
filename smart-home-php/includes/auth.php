<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// ฟังก์ชันตรวจสอบการล็อกอิน
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ฟังก์ชันเช็คและเปลี่ยนเส้นทางถ้ายังไม่ล็อกอิน
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// ฟังก์ชันล็อกอิน
function login($username, $password) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // เซ็ต session ก่อน
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // บันทึก session ในฐานข้อมูล
            $session_id = session_id();
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $sessionStmt = $conn->prepare("
                INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    last_activity = CURRENT_TIMESTAMP,
                    ip_address = ?,
                    user_agent = ?
            ");
            $sessionStmt->bind_param("isssss", $user['id'], $session_id, $ip_address, $user_agent, $ip_address, $user_agent);
            $sessionStmt->execute();
            $sessionStmt->close();
            
            // อัพเดทเวลาล็อกอินล่าสุด
            $updateStmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            // บันทึก activity log (หลังจากเซ็ต session แล้ว)
            try {
                logActivity($user['id'], 'login', 'เข้าสู่ระบบสำเร็จ');
            } catch (Exception $e) {
                // ถ้า log ไม่ได้ก็ไม่เป็นไร
                error_log("Failed to log activity: " . $e->getMessage());
            }
            
            $stmt->close();
            closeDBConnection($conn);
            
            return ['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ'];
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    
    return ['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'];
}

// ฟังก์ชันสมัครสมาชิก
function register($username, $email, $password, $full_name) {
    $conn = getDBConnection();
    
    // ตรวจสอบว่ามี username หรือ email ซ้ำหรือไม่
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        closeDBConnection($conn);
        return ['success' => false, 'message' => 'ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว'];
    }
    
    // เข้ารหัสรหัสผ่าน
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // เพิ่มผู้ใช้ใหม่
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $full_name);
    
    if ($stmt->execute()) {
        $checkStmt->close();
        $stmt->close();
        closeDBConnection($conn);
        return ['success' => true, 'message' => 'สมัครสมาชิกสำเร็จ'];
    }
    
    $checkStmt->close();
    $stmt->close();
    closeDBConnection($conn);
    
    return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการสมัครสมาชิก'];
}

// ฟังก์ชันล็อกเอาท์
function logout() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'ออกจากระบบ');
    }
    
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// ฟังก์ชันบันทึก Activity Log
function logActivity($user_id, $action_type, $action_detail, $light_id = null, $voice_command = null) {
    try {
        $conn = getDBConnection();
        
        // ตรวจสอบว่า user_id มีอยู่จริง
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->bind_param("i", $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            $checkStmt->close();
            closeDBConnection($conn);
            error_log("Invalid user_id: $user_id");
            return false;
        }
        $checkStmt->close();
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, light_id, action_type, action_detail, voice_command, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $user_id, $light_id, $action_type, $action_detail, $voice_command, $ip_address, $user_agent);
        $result = $stmt->execute();
        $stmt->close();
        closeDBConnection($conn);
        
        return $result;
    } catch (Exception $e) {
        error_log("logActivity Error: " . $e->getMessage());
        return false;
    }
}

// ฟังก์ชันดึงข้อมูลผู้ใช้
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    // ดึงข้อมูล role จาก database แทนการใช้ session
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, role, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        closeDBConnection($conn);
        return $user;
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return null;
}

// ตรวจสอบว่าเป็น Admin หรือไม่
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

// ตรวจสอบว่ามีสิทธิ์หรือไม่
function hasRole($requiredRole) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Admin มีสิทธิ์ทุกอย่าง
    if ($user['role'] === 'admin') return true;
    
    // ตรวจสอบ role ที่ต้องการ
    if (is_array($requiredRole)) {
        return in_array($user['role'], $requiredRole);
    }
    
    return $user['role'] === $requiredRole;
}

// Require Admin (ใช้แทน username check)
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

// ฟังก์ชันอัพเดท session activity
function updateSessionActivity() {
    if (!isLoggedIn()) {
        return false;
    }
    
    try {
        $conn = getDBConnection();
        $session_id = session_id();
        
        $stmt = $conn->prepare("UPDATE user_sessions SET last_activity = CURRENT_TIMESTAMP WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $stmt->close();
        closeDBConnection($conn);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to update session activity: " . $e->getMessage());
        return false;
    }
}

// ฟังก์ชันลบ session ที่หมดอายุ
function cleanExpiredSessions() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $stmt->execute();
        $stmt->close();
        closeDBConnection($conn);
    } catch (Exception $e) {
        error_log("Failed to clean expired sessions: " . $e->getMessage());
    }
}
?>