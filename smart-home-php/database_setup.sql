-- Smart Home Database Setup
-- สร้างฐานข้อมูลนี้ใน phpMyAdmin ก่อนใช้งาน

CREATE DATABASE IF NOT EXISTS smart_home_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE smart_home_db;

-- ตารางผู้ใช้งาน
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางห้อง/อุปกรณ์
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL,
    room_code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_room_code (room_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางไฟ/อุปกรณ์
CREATE TABLE IF NOT EXISTS lights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    light_name VARCHAR(100) NOT NULL,
    device_code VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('on', 'off') DEFAULT 'off',
    brightness INT DEFAULT 100 CHECK (brightness >= 0 AND brightness <= 100),
    power_consumption DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_room_id (room_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางประวัติการใช้งาน
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    light_id INT,
    action_type ENUM('light_on', 'light_off', 'brightness_change', 'voice_command', 'login', 'logout', 'all_on', 'all_off') NOT NULL,
    action_detail TEXT,
    voice_command VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (light_id) REFERENCES lights(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action_type (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางการตั้งเวลาอัตโนมัติ
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    light_id INT,
    schedule_name VARCHAR(100) NOT NULL,
    action_type ENUM('on', 'off') NOT NULL,
    schedule_time TIME NOT NULL,
    days_of_week VARCHAR(20) DEFAULT '1,2,3,4,5,6,7',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (light_id) REFERENCES lights(id) ON DELETE CASCADE,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตารางสถิติการใช้พลังงาน
CREATE TABLE IF NOT EXISTS energy_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    light_id INT NOT NULL,
    usage_date DATE NOT NULL,
    total_kwh DECIMAL(10,2) DEFAULT 0.00,
    total_hours DECIMAL(10,2) DEFAULT 0.00,
    cost DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (light_id) REFERENCES lights(id) ON DELETE CASCADE,
    INDEX idx_light_date (light_id, usage_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลเริ่มต้น: สร้างห้อง
INSERT INTO rooms (room_name, room_code, description, icon) VALUES
('ห้องนั่งเล่น', 'living', 'ห้องนั่งเล่นและพื้นที่รับแขก', 'sofa'),
('ห้องนอน', 'bedroom', 'ห้องนอนหลัก', 'bed'),
('ห้องครัว', 'kitchen', 'ห้องครัวและพื้นที่ทานอาหาร', 'utensils'),
('ห้องน้ำ', 'bathroom', 'ห้องน้ำ', 'bath'),
('ห้องทำงาน', 'office', 'ห้องทำงานและศึกษา', 'laptop'),
('สวน', 'garden', 'พื้นที่สวนและระเบียง', 'tree');

-- ข้อมูลเริ่มต้น: สร้างอุปกรณ์ไฟ
INSERT INTO lights (room_id, light_name, device_code, status, brightness, power_consumption) VALUES
(1, 'ไฟเพดานห้องนั่งเล่น', 'living_ceiling', 'off', 100, 0.06),
(1, 'ไฟโคมตั้งโต๊ะ', 'living_table', 'off', 80, 0.04),
(2, 'ไฟเพดานห้องนอน', 'bedroom_ceiling', 'off', 100, 0.06),
(2, 'ไฟข้างเตียง', 'bedroom_side', 'off', 60, 0.03),
(3, 'ไฟเพดานห้องครัว', 'kitchen_ceiling', 'off', 100, 0.08),
(3, 'ไฟใต้ตู้', 'kitchen_under', 'off', 80, 0.04),
(4, 'ไฟห้องน้ำ', 'bathroom_main', 'off', 100, 0.05),
(5, 'ไฟห้องทำงาน', 'office_main', 'off', 100, 0.07),
(5, 'ไฟโต๊ะทำงาน', 'office_desk', 'off', 90, 0.05),
(6, 'ไฟสวน', 'garden_main', 'off', 70, 0.10);

-- ข้อมูลเริ่มต้น: สร้างผู้ใช้ทดสอบ (password: admin123)
INSERT INTO users (username, email, password, full_name, status) VALUES
('admin', 'admin@smarthome.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'active');

-- สร้าง View สำหรับสถิติ
CREATE OR REPLACE VIEW vw_daily_stats AS
SELECT 
    DATE(al.created_at) as log_date,
    COUNT(DISTINCT al.user_id) as active_users,
    COUNT(*) as total_actions,
    SUM(CASE WHEN al.action_type IN ('light_on', 'all_on') THEN 1 ELSE 0 END) as lights_turned_on,
    SUM(CASE WHEN al.action_type IN ('light_off', 'all_off') THEN 1 ELSE 0 END) as lights_turned_off,
    SUM(CASE WHEN al.action_type = 'voice_command' THEN 1 ELSE 0 END) as voice_commands
FROM activity_logs al
GROUP BY DATE(al.created_at)
ORDER BY log_date DESC;

-- สร้าง View สำหรับสถานะไฟปัจจุบัน
CREATE OR REPLACE VIEW vw_current_lights_status AS
SELECT 
    l.id,
    l.light_name,
    l.device_code,
    l.status,
    l.brightness,
    l.power_consumption,
    r.room_name,
    r.room_code,
    CASE 
        WHEN l.status = 'on' THEN l.power_consumption * (l.brightness / 100)
        ELSE 0 
    END as current_consumption
FROM lights l
INNER JOIN rooms r ON l.room_id = r.id
ORDER BY r.id, l.id;

-- สร้าง Stored Procedure สำหรับการเปิดไฟทั้งหมด
DELIMITER //
CREATE PROCEDURE sp_turn_all_lights(IN p_action VARCHAR(10), IN p_user_id INT)
BEGIN
    IF p_action = 'on' THEN
        UPDATE lights SET status = 'on', updated_at = CURRENT_TIMESTAMP;
    ELSE
        UPDATE lights SET status = 'off', updated_at = CURRENT_TIMESTAMP;
    END IF;
    
    INSERT INTO activity_logs (user_id, action_type, action_detail, created_at)
    VALUES (p_user_id, CONCAT('all_', p_action), CONCAT('เปิดไฟทั้งหมด'), CURRENT_TIMESTAMP);
END //
DELIMITER ;

-- สร้าง Trigger สำหรับบันทึกการเปลี่ยนแปลงสถานะไฟ
DELIMITER //
CREATE TRIGGER trg_lights_status_change
AFTER UPDATE ON lights
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO energy_usage (light_id, usage_date, total_hours, total_kwh)
        VALUES (NEW.id, CURDATE(), 0, 0)
        ON DUPLICATE KEY UPDATE 
            total_hours = total_hours + 0.1,
            total_kwh = total_kwh + (NEW.power_consumption * 0.1);
    END IF;
END //
DELIMITER ;