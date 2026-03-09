<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// อัพเดท session activity
updateSessionActivity();

$user = getCurrentUser();
$isGuest = ($user['role'] === 'guest');
$canControl = !$isGuest; // Guest ไม่สามารถควบคุมได้
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Home</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // ส่งข้อมูล role ไปให้ JavaScript
        window.userRole = '<?php echo $user['role']; ?>';
        window.canControl = <?php echo $canControl ? 'true' : 'false'; ?>;
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="navbar-brand">
                    <div class="icon">
                        <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <span>Smart Home Dashboard</span>
                </a>
                <div class="flex items-center gap-2">
                    <span style="color: white; font-size: 14px; display: none;" class="user-info">
                        สวัสดี, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>
                    </span>
                    <a href="guide.php" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">
                        📚 คู่มือ
                    </a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin/" class="btn btn-primary" style="font-size: 14px; padding: 8px 16px;">
                        ⚙️ Admin
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-danger" style="font-size: 14px; padding: 8px 16px;">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top: 24px; padding-bottom: 24px;">
        <!-- Guest Notice -->
        <?php if ($isGuest): ?>
        <div style="background: #fef3c7; border: 2px solid #fbbf24; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
            <svg style="width: 24px; height: 24px; color: #f59e0b; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <strong style="color: #92400e; font-size: 16px;">โหมด Gay (ผู้เงี่ยนจัง)</strong>
                <p style="color: #78350f; font-size: 14px; margin: 4px 0 0 0;">คุณสามารถดูข้อมูลได้อย่างเดียว ไม่สามารถควบคุมไฟหรือใช้คำสั่งเสียงได้</p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="grid grid-4" style="margin-bottom: 24px;">
            <div class="stat-card">
                <div>
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">ไฟเปิดทั้งหมด</p>
                    <p id="totalLightsOn" style="font-size: 32px; font-weight: 700; color: #fbbf24;">0</p>
                </div>
                <div style="background: #fef3c7; padding: 16px; border-radius: 12px;">
                    <svg style="width: 32px; height: 32px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">พลังงานใช้วันนี้</p>
                    <p id="todayEnergy" style="font-size: 32px; font-weight: 700; color: #10b981;">0.0</p>
                    <p style="font-size: 12px; color: #9ca3af;">kWh</p>
                </div>
                <div style="background: #d1fae5; padding: 16px; border-radius: 12px;">
                    <svg style="width: 32px; height: 32px; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">การใช้งานวันนี้</p>
                    <p id="todayActions" style="font-size: 32px; font-weight: 700; color: #3b82f6;">0</p>
                    <p style="font-size: 12px; color: #9ca3af;">ครั้ง</p>
                </div>
                <div style="background: #dbeafe; padding: 16px; border-radius: 12px;">
                    <svg style="width: 32px; height: 32px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <p style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">สมาชิกออนไลน์</p>
                    <p id="onlineUsers" style="font-size: 32px; font-weight: 700; color: #8b5cf6;">1</p>
                    <p style="font-size: 12px; color: #9ca3af;">คน</p>
                </div>
                <div style="background: #ede9fe; padding: 16px; border-radius: 12px;">
                    <svg style="width: 32px; height: 32px; color: #8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-3" style="gap: 24px;">
            <!-- Light Controls -->
            <div style="grid-column: span 2;">
                <div class="card">
                    <div class="flex items-center justify-between" style="margin-bottom: 24px;">
                        <h2 style="font-size: 20px; font-weight: 700; color: #1f2937;">ควบคุมไฟ</h2>
                        <?php if (!$isGuest): ?>
                        <button onclick="smartHome.startVoiceRecognition()" id="voiceBtn" class="btn btn-voice">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                            <span>สั่งงานด้วยเสียง</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isGuest): ?>
                    <div id="voiceStatus" class="voice-status hidden">
                        <div class="flex items-center gap-2">
                            <div class="voice-recording-dot"></div>
                            <span style="font-weight: 600; color: #7c3aed;">กำลังฟัง... พูดคำสั่งได้เลย</span>
                        </div>
                        <p style="font-size: 14px; color: #9333ea; margin-top: 8px;">
                            ตัวอย่าง: "เปิดไฟห้องนอน", "ปิดไฟห้องครัว", "เปิดไฟทั้งหมด", "ปิดไฟทั้งหมด"
                        </p>
                    </div>

                    <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                        <button onclick="smartHome.toggleAllLights(true)" class="btn btn-success">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            เปิดไฟทั้งหมด
                        </button>
                        <button onclick="smartHome.toggleAllLights(false)" class="btn btn-danger">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            ปิดไฟทั้งหมด
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-2" id="lightsContainer">
                        <!-- Lights will be loaded here by JavaScript -->
                        <div style="grid-column: span 2; text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                            <p style="margin-top: 16px; color: #6b7280;">กำลังโหลดข้อมูล...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div>
                <div class="card">
                    <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin-bottom: 24px;">กิจกรรมล่าสุด</h2>
                    <div id="activityLog" style="max-height: 600px; overflow-y: auto;">
                        <div style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                            <p style="margin-top: 16px; color: #6b7280;">กำลังโหลดข้อมูล...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Energy Usage & Schedules -->
        <div class="grid grid-2" style="margin-top: 24px; gap: 24px;">
            <div class="card">
                <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin-bottom: 24px;">การใช้พลังงานรายห้อง</h2>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="flex items-center justify-between">
                        <span style="color: #374151; font-weight: 500;">ห้องนั่งเล่น</span>
                        <div class="flex items-center gap-2" style="flex: 1; max-width: 300px;">
                            <div style="flex: 1; background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="width: 75%; height: 100%; background: linear-gradient(90deg, #fbbf24, #f59e0b);"></div>
                            </div>
                            <span style="font-size: 14px; font-weight: 600; color: #374151; min-width: 60px; text-align: right;">2.5 kWh</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color: #374151; font-weight: 500;">ห้องนอน</span>
                        <div class="flex items-center gap-2" style="flex: 1; max-width: 300px;">
                            <div style="flex: 1; background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="width: 45%; height: 100%; background: linear-gradient(90deg, #10b981, #059669);"></div>
                            </div>
                            <span style="font-size: 14px; font-weight: 600; color: #374151; min-width: 60px; text-align: right;">1.2 kWh</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color: #374151; font-weight: 500;">ห้องครัว</span>
                        <div class="flex items-center gap-2" style="flex: 1; max-width: 300px;">
                            <div style="flex: 1; background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="width: 60%; height: 100%; background: linear-gradient(90deg, #3b82f6, #2563eb);"></div>
                            </div>
                            <span style="font-size: 14px; font-weight: 600; color: #374151; min-width: 60px; text-align: right;">1.8 kWh</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color: #374151; font-weight: 500;">ห้องน้ำ</span>
                        <div class="flex items-center gap-2" style="flex: 1; max-width: 300px;">
                            <div style="flex: 1; background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="width: 30%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #7c3aed);"></div>
                            </div>
                            <span style="font-size: 14px; font-weight: 600; color: #374151; min-width: 60px; text-align: right;">0.8 kWh</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="font-size: 20px; font-weight: 700; color: #1f2937; margin-bottom: 24px;">ตั้งเวลาอัตโนมัติ</h2>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div class="flex items-center justify-between" style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                        <div>
                            <p style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">เปิดไฟห้องนั่งเล่น</p>
                            <p style="font-size: 14px; color: #6b7280;">ทุกวัน 18:00 น.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="flex items-center justify-between" style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                        <div>
                            <p style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">ปิดไฟทั้งหมด</p>
                            <p style="font-size: 14px; color: #6b7280;">ทุกวัน 23:00 น.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="flex items-center justify-between" style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                        <div>
                            <p style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">เปิดไฟสวน</p>
                            <p style="font-size: 14px; color: #6b7280;">ทุกวัน 19:00 น.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>

<style>
@media (max-width: 768px) {
    .user-info {
        display: none !important;
    }
}
@media (min-width: 769px) {
    .user-info {
        display: inline !important;
    }
}
</style>