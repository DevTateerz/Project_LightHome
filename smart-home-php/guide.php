<?php
session_start();
require_once 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คู่มือการใช้งาน - Smart Home</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .guide-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .guide-section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 24px;
        }
        .command-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }
        .command-box .cmd {
            color: #667eea;
            font-weight: 600;
            font-size: 16px;
        }
        .badge-new {
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="navbar-brand">
                    <div class="icon">🏠</div>
                    <span>Smart Home</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="index.php" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">กลับหน้าหลัก</a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin/" class="btn btn-primary" style="font-size: 14px; padding: 8px 16px;">Admin Panel</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 40px 20px;">
        <h1 style="font-size: 36px; font-weight: 700; margin-bottom: 30px; text-align: center;">
            📚 คู่มือการใช้งาน Smart Home
        </h1>

        <!-- คำสั่งเสียง -->
        <div class="guide-section">
            <h2>🎤 คำสั่งเสียง</h2>
            <p>สั่งงานระบบด้วยเสียงได้ง่ายๆ เพียงคลิกปุ่มไมโครโฟน แล้วพูดคำสั่ง</p>
            
            <h3 style="margin-top: 25px; color: #374151;">คำสั่งทั่วไป:</h3>
            <div class="command-box">
                <span class="cmd">🗣️ "เปิดไฟทั้งหมด"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">เปิดไฟทุกจุดในบ้าน</p>
            </div>
            <div class="command-box">
                <span class="cmd">🗣️ "ปิดไฟทั้งหมด"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">ปิดไฟทุกจุดในบ้าน</p>
            </div>

            <h3 style="margin-top: 25px; color: #374151;">คำสั่งรายห้อง:</h3>
            <div class="command-box">
                <span class="cmd">🗣️ "เปิดไฟห้องนอน"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">เปิดไฟทุกจุดในห้องนอน</p>
            </div>
            <div class="command-box">
                <span class="cmd">🗣️ "ปิดไฟห้องครัว"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">ปิดไฟทุกจุดในห้องครัว</p>
            </div>
            <div class="command-box">
                <span class="cmd">🗣️ "เปิดไฟห้องนั่งเล่น"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">เปิดไฟในห้องนั่งเล่น</p>
            </div>

            <h3 style="margin-top: 25px; color: #374151;">
                คำสั่งเฉพาะจุด <span class="badge-new">ใหม่!</span>
            </h3>
            <div class="command-box">
                <span class="cmd">🗣️ "เปิดไฟเพดาน"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">เปิดไฟเพดานทุกจุดที่มีในบ้าน</p>
            </div>
            <div class="command-box">
                <span class="cmd">🗣️ "ปิดไฟโคมตั้งโต๊ะ"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">ปิดไฟโคมตั้งโต๊ะ</p>
            </div>
            <div class="command-box">
                <span class="cmd">🗣️ "เปิดไฟข้างเตียง"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">เปิดไฟข้างเตียง</p>
            </div>
            <div class="command-box">
                <span class="cmd">🗣️ "ปิดไฟสปอร์ตไลท์"</span>
                <p style="margin: 5px 0 0 0; color: #6b7280;">ปิดไฟสปอร์ตไลท์</p>
            </div>
        </div>

        <!-- การควบคุมด้วยมือ -->
        <div class="guide-section">
            <h2>👆 การควบคุมด้วยมือ</h2>
            <p>คลิกที่การ์ดจุดไฟเพื่อเปิด/ปิด และปรับความสว่างได้ตามต้องการ</p>
            
            <div style="margin-top: 20px;">
                <h4 style="color: #374151; margin-bottom: 10px;">ฟังก์ชันต่างๆ:</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>คลิกการ์ด:</strong> เปิด/ปิดไฟ
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>เลื่อน Slider:</strong> ปรับความสว่าง 0-100%
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>ปุ่ม "เปิดทั้งหมด":</strong> เปิดไฟทุกจุดในห้อง
                    </li>
                    <li style="padding: 10px 0;">
                        <strong>ปุ่ม "ปิดทั้งหมด":</strong> ปิดไฟทุกจุดในห้อง
                    </li>
                </ul>
            </div>
        </div>

        <!-- Admin Panel -->
        <?php if ($user['role'] === 'admin'): ?>
        <div class="guide-section">
            <h2>⚙️ Admin Panel</h2>
            <p>สำหรับผู้ดูแลระบบ - จัดการทุกอย่างได้ที่นี่</p>
            
            <div style="margin-top: 20px;">
                <h4 style="color: #374151; margin-bottom: 10px;">ฟีเจอร์ Admin:</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>👥 จัดการสมาชิก:</strong> เพิ่ม/ลบ/แก้ไขผู้ใช้งาน
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>🏠 จัดการห้อง:</strong> เพิ่มห้องใหม่ แก้ไขชื่อห้อง
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>💡 จัดการจุดไฟ:</strong> เพิ่ม/ย้าย/ลบจุดไฟ
                    </li>
                    <li style="padding: 10px 0;">
                        <strong>📝 ดูประวัติ:</strong> ตรวจสอบการใช้งานทั้งหมด
                    </li>
                </ul>
                
                <a href="admin/" class="btn btn-primary" style="margin-top: 20px;">
                    เปิด Admin Panel
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tips & Tricks -->
        <div class="guide-section">
            <h2>💡 เคล็ดลับการใช้งาน</h2>
            
            <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin: 10px 0;">
                <strong style="color: #1e40af;">✨ Tip #1:</strong> พูดชัดๆ และไม่เร็วเกินไป เพื่อให้ระบบจับคำสั่งได้แม่นยำ
            </div>
            
            <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin: 10px 0;">
                <strong style="color: #1e40af;">✨ Tip #2:</strong> ถ้าคำสั่งไม่ทำงาน ลองพูดใหม่ด้วยคำศัพท์ที่แตกต่างกัน
            </div>
            
            <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin: 10px 0;">
                <strong style="color: #1e40af;">✨ Tip #3:</strong> ระบบจะจำคำสั่งที่คุณใช้บ่อย เพื่อเพิ่มความแม่นยำ
            </div>
            
            <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin: 10px 0;">
                <strong style="color: #1e40af;">✨ Tip #4:</strong> ตั้งชื่อจุดไฟให้สั้นและง่ายจำ เพื่อสั่งด้วยเสียงได้สะดวก
            </div>
        </div>

        <!-- Hardware Integration 
        <div class="guide-section">
            <h2>🔌 เชื่อมต่อ Hardware</h2>
            <p>ระบบพร้อมเชื่อมต่อกับอุปกรณ์จริงได้!</p>
            
            <div style="margin-top: 20px;">
                <p>เอกสารที่เกี่ยวข้อง:</p>
                <ul style="margin-top: 10px;">
                    <li><a href="HARDWARE_INTEGRATION.txt" style="color: #667eea;">คู่มือเชื่อมต่อ Hardware</a></li>
                    <li><a href="SHOPPING_LIST.md" style="color: #667eea;">รายการอุปกรณ์ที่ต้องซื้อ</a></li>
                </ul>
            </div>
        </div>
        -->

        <!-- Support -->
        <div class="guide-section" style="text-align: center;">
            <h2>🆘 ต้องการความช่วยเหลือ?</h2>
            <p>หากพบปัญหาในการใช้งาน สามารถติดต่อได้ที่:</p>
            <div style="margin-top: 20px;">
                <a href="https://www.instagram.com/j.khunnnn/" class="btn btn-secondary">instagram</a>
            </div>
        </div>
    </div>
</body>
</html>