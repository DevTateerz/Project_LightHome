<?php
// ไฟล์ทดสอบระบบ - ลบออกหลังแก้ไขเสร็จ
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Smart Home - System Check</h1>";
echo "<hr>";

// 1. ตรวจสอบ PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 7.4 or higher<br>";
echo phpversion() >= 7.4 ? "✅ OK<br>" : "❌ PHP version too old<br>";

// 2. ตรวจสอบการเชื่อมต่อฐานข้อมูล
echo "<hr><h2>2. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $conn = getDBConnection();
    echo "✅ Database connected successfully<br>";
    echo "Database: " . DB_NAME . "<br>";
    
    // ตรวจสอบตาราง
    $tables = ['users', 'rooms', 'lights', 'activity_logs', 'schedules', 'energy_usage'];
    echo "<h3>Tables:</h3>";
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
            echo "✅ $table ($count rows)<br>";
        } else {
            echo "❌ $table (not found)<br>";
        }
    }
    
    closeDBConnection($conn);
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// 3. ตรวจสอบไฟล์
echo "<hr><h2>3. File Check</h2>";
$files = [
    'config/database.php',
    'includes/auth.php',
    'api/lights.php',
    'api/voice.php',
    'api/stats.php',
    'api/activity.php',
    'css/style.css',
    'js/app.js',
    'index.php',
    'login.php',
    'register.php',
    'logout.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file (not found)<br>";
    }
}

// 4. ตรวจสอบ Session
echo "<hr><h2>4. Session Check</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Active" : "❌ Inactive") . "<br>";
echo "Session ID: " . session_id() . "<br>";
if (isset($_SESSION['user_id'])) {
    echo "Logged in as User ID: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "Not logged in<br>";
}

// 5. ตรวจสอบ Error Log
echo "<hr><h2>5. Error Logging</h2>";
echo "Error Log: " . ini_get('error_log') . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";

// 6. ข้อมูลเซิร์ฟเวอร์
echo "<hr><h2>6. Server Info</h2>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

echo "<hr>";
echo "<h3>✅ ถ้าทุกอย่างเป็น ✅ แสดงว่าระบบพร้อมใช้งาน</h3>";
echo "<p><a href='login.php'>ไปหน้า Login</a> | <a href='index.php'>ไปหน้า Dashboard</a></p>";
?>