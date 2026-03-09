<?php
require_once __DIR__ . '/includes/auth.php';

// ถ้าล็อกอินอยู่แล้ว ไปหน้า dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// จัดการการสมัครสมาชิก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    } elseif ($password !== $confirm_password) {
        $error = 'รหัสผ่านไม่ตรงกัน';
    } else {
        try {
            $result = register($username, $email, $password, $full_name);
            if ($result['success']) {
                $success = $result['message'] . ' กรุณาเข้าสู่ระบบ';
                // Redirect to login after 2 seconds
                header("refresh:2;url=login.php");
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
    <title>สมัครสมาชิก - Smart Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <svg style="width: 48px; height: 48px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h1 style="font-size: 28px; font-weight: 700; color: #1f2937;">สมัครสมาชิก</h1>
                <p style="color: #6b7280; margin-top: 8px;">สร้างบัญชีใหม่</p>
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
                    <label for="full_name">ชื่อ-นามสกุล</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           placeholder="กรอกชื่อ-นามสกุล" required autofocus
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="กรอกชื่อผู้ใช้" required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="กรอกอีเมล" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="กรอกรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                </div>

                <button type="submit" name="register" class="btn btn-primary" style="width: 100%; margin-bottom: 12px;">
                    สมัครสมาชิก
                </button>

                <a href="login.php" class="btn btn-secondary" style="width: 100%; display: block; text-align: center;">
                    กลับไปเข้าสู่ระบบ
                </a>
            </form>
        </div>
    </div>
</body>
</html>