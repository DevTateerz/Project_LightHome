<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';

// ตรวจสอบการล็อกอินและ role
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// ตรวจสอบว่าเป็น admin หรือไม่
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Home</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .admin-nav .container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .admin-nav .tab {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .admin-nav .tab:hover {
            background: rgba(255,255,255,0.3);
        }
        .admin-nav .tab.active {
            background: white;
            color: #667eea;
            border-color: #667eea;
        }
        .admin-section {
            display: none;
        }
        .admin-section.active {
            display: block;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table tr:hover {
            background: #f9fafb;
        }
        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 3px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .icon-btn:hover {
            background: #e5e7eb;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-control:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }
        select.form-control {
            cursor: pointer;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="../index.php" class="navbar-brand">
                    <div class="icon">
                        <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <span>Admin Panel</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="../index.php" class="btn btn-secondary" style="font-size: 14px; padding: 8px 16px;">
                        กลับหน้าหลัก
                    </a>
                    <a href="../logout.php" class="btn btn-danger" style="font-size: 14px; padding: 8px 16px;">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="admin-nav">
        <div class="container">
            <div class="tab active" onclick="showTab('users')">
                <svg style="width: 20px; height: 20px; display: inline; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                จัดการสมาชิก
            </div>
            <div class="tab" onclick="showTab('rooms')">
                <svg style="width: 20px; height: 20px; display: inline; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                จัดการห้อง
            </div>
            <div class="tab" onclick="showTab('lights')">
                <svg style="width: 20px; height: 20px; display: inline; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                จัดการจุดไฟ
            </div>
            <div class="tab" onclick="showTab('voice')">
                <svg style="width: 20px; height: 20px; display: inline; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                </svg>
                คำสั่งเสียง
            </div>
            <div class="tab" onclick="showTab('logs')">
                <svg style="width: 20px; height: 20px; display: inline; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                ประวัติทั้งหมด
            </div>
        </div>
    </div>

    <div class="container" style="padding-bottom: 50px;">
        <!-- Section: จัดการสมาชิก -->
        <div id="users-section" class="admin-section active">
            <div class="card">
                <div class="flex justify-between items-center mb-3">
                    <h2 style="font-size: 24px; font-weight: 700;">จัดการสมาชิก</h2>
                    <button class="btn btn-primary" onclick="showAddUserModal()">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        เพิ่มสมาชิก
                    </button>
                </div>
                <div id="users-list">กำลังโหลด...</div>
            </div>
        </div>

        <!-- Section: จัดการห้อง -->
        <div id="rooms-section" class="admin-section">
            <div class="card">
                <div class="flex justify-between items-center mb-3">
                    <h2 style="font-size: 24px; font-weight: 700;">จัดการห้อง</h2>
                    <button class="btn btn-primary" onclick="showAddRoomModal()">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        เพิ่มห้อง
                    </button>
                </div>
                <div id="rooms-list">กำลังโหลด...</div>
            </div>
        </div>

        <!-- Section: จัดการจุดไฟ -->
        <div id="lights-section" class="admin-section">
            <div class="card">
                <div class="flex justify-between items-center mb-3">
                    <h2 style="font-size: 24px; font-weight: 700;">จัดการจุดไฟ</h2>
                    <button class="btn btn-primary" onclick="showAddLightModal()">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        เพิ่มจุดไฟ
                    </button>
                </div>
                <div id="lights-list">กำลังโหลด...</div>
            </div>
        </div>

        <!-- Section: คำสั่งเสียง -->
        <div id="voice-section" class="admin-section">
            <div class="card">
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 20px;">ตั้งค่าคำสั่งเสียง</h2>
                <div id="voice-commands">กำลังโหลด...</div>
            </div>
        </div>

        <!-- Section: ประวัติทั้งหมด -->
        <div id="logs-section" class="admin-section">
            <div class="card">
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 20px;">ประวัติการใช้งานทั้งหมด</h2>
                <div id="all-logs">กำลังโหลด...</div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="modal-container"></div>

    <script src="admin.js"></script>
</body>
</html>