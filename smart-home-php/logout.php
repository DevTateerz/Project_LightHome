<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'logout', 'ออกจากระบบ');
    
    // ลบ session จากฐานข้อมูล
    try {
        $conn = getDBConnection();
        $session_id = session_id();
        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $stmt->close();
        closeDBConnection($conn);
    } catch (Exception $e) {
        error_log("Failed to remove session: " . $e->getMessage());
    }
}

session_unset();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

header('Location: login.php');
exit();
?>