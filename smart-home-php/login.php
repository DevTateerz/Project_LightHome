<?php
require_once __DIR__ . '/includes/auth.php';

// ถ้าล็อกอินอยู่แล้ว ไปหน้า dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// จัดการการล็อกอิน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        try {
            $result = login($username, $password);
            if ($result['success']) {
                header('Location: index.php');
                exit();
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Light Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <svg style="width: 48px; height: 48px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h1 style="font-size: 28px; font-weight: 700; color: #1f2937;">Light Home</h1>
                <p style="color: #6b7280; margin-top: 8px;">ระบบควบคุมไฟ</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="กรอกชื่อผู้ใช้" required autofocus 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="กรอกรหัสผ่าน" required>
                </div>

                <button type="submit" name="login" class="btn btn-primary" style="width: 100%; margin-bottom: 12px;">
                    เข้าสู่ระบบ
                </button>

                <a href="register.php" class="btn btn-secondary" style="width: 100%; display: block; text-align: center;">
                    สมัครสมาชิก
                </a>
            </form>

            <!-- <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center;">
                <p style="font-size: 14px; color: #6b7280;">บัญชีทดสอบ</p>
                <p style="font-size: 12px; color: #9ca3af; margin-top: 4px;">
                    Username: <strong>admin</strong> / Password: <strong>admin123</strong>
                </p>
            </div> --->
        </div>
    </div>
</body>
</html>