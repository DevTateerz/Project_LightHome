// Smart Home JavaScript
class SmartHome {
    constructor() {
        this.lights = [];
        this.recognition = null;
        this.isRecording = false;
        this.init();
    }

    init() {
        this.loadLights();
        this.loadStats();
        this.loadActivityLog();
        this.setupVoiceRecognition();
        
        // อัพเดทข้อมูลทุก 5 วินาที
        setInterval(() => {
            this.loadStats();
            this.loadActivityLog();
        }, 5000);
    }

    // โหลดรายการไฟทั้งหมด
    async loadLights() {
        try {
            const response = await fetch('api/lights.php?action=get_all');
            const data = await response.json();
            
            if (data.success) {
                this.lights = data.lights;
                this.renderLights();
            }
        } catch (error) {
            console.error('Error loading lights:', error);
        }
    }

    // แสดงรายการไฟ
    renderLights() {
        const container = document.getElementById('lightsContainer');
        if (!container) return;

        // เช็คว่าเป็น guest หรือไม่
        const isGuest = (window.userRole === 'guest');
        const disabledAttr = isGuest ? 'disabled' : '';
        const disabledStyle = isGuest ? 'opacity: 0.6; cursor: not-allowed;' : '';

        container.innerHTML = this.lights.map(light => `
            <div class="light-card ${light.status === 'on' ? 'light-on' : ''}" data-light-id="${light.id}">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="light-icon ${light.status === 'on' ? 'on' : ''}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: ${light.status === 'on' ? 'white' : '#6b7280'}">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; font-size: 16px; color: #1f2937;">${light.light_name}</h4>
                            <p style="font-size: 14px; color: #6b7280;">${light.room_name}</p>
                            <p style="font-size: 12px; color: ${light.status === 'on' ? '#10b981' : '#ef4444'};">
                                ${light.status === 'on' ? 'เปิด' : 'ปิด'}
                                ${isGuest ? ' <span style="color: #f59e0b;">(ดูอย่างเดียว)</span>' : ''}
                            </p>
                        </div>
                    </div>
                    <label class="toggle-switch" style="${disabledStyle}">
                        <input type="checkbox" ${light.status === 'on' ? 'checked' : ''} 
                               ${disabledAttr}
                               onchange="smartHome.toggleLight(${light.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span style="font-size: 12px; color: #6b7280;">ความสว่าง</span>
                        <span style="font-size: 12px; font-weight: 600; color: #374151;">${light.brightness}%</span>
                    </div>
                    <input type="range" 
                           class="brightness-slider" 
                           min="0" 
                           max="100" 
                           value="${light.brightness}" 
                           onchange="smartHome.changeBrightness(${light.id}, this.value)"
                           ${light.status === 'off' || isGuest ? 'disabled' : ''}
                           style="${isGuest ? disabledStyle : ''}">
                </div>
            </div>
        `).join('');
    }

    // เปิด/ปิดไฟ
    async toggleLight(lightId, isOn) {
        try {
            const response = await fetch('api/lights.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle&light_id=${lightId}&status=${isOn ? 'on' : 'off'}`
            });

            const data = await response.json();
            
            if (data.success) {
                await this.loadLights();
                await this.loadStats();
                await this.loadActivityLog();
                this.showNotification('success', data.message);
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            console.error('Error toggling light:', error);
            this.showNotification('error', 'เกิดข้อผิดพลาดในการควบคุมไฟ');
        }
    }

    // ปรับความสว่าง
    async changeBrightness(lightId, brightness) {
        try {
            const response = await fetch('api/lights.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=brightness&light_id=${lightId}&brightness=${brightness}`
            });

            const data = await response.json();
            
            if (data.success) {
                // อัพเดทค่าความสว่างในอาร์เรย์
                const light = this.lights.find(l => l.id == lightId);
                if (light) {
                    light.brightness = brightness;
                }
            }
        } catch (error) {
            console.error('Error changing brightness:', error);
        }
    }

    // เปิด/ปิดไฟทั้งหมด
    async toggleAllLights(isOn) {
        try {
            const response = await fetch('api/lights.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_all&status=${isOn ? 'on' : 'off'}`
            });

            const data = await response.json();
            
            if (data.success) {
                await this.loadLights();
                await this.loadStats();
                await this.loadActivityLog();
                this.showNotification('success', data.message);
            }
        } catch (error) {
            console.error('Error toggling all lights:', error);
        }
    }

    // โหลดสถิติ
    async loadStats() {
        try {
            const response = await fetch('api/stats.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('totalLightsOn').textContent = data.stats.lights_on || 0;
                document.getElementById('todayEnergy').textContent = data.stats.today_energy || '0.0';
                document.getElementById('todayActions').textContent = data.stats.today_actions || 0;
                document.getElementById('onlineUsers').textContent = data.stats.online_users || 1;
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    // โหลดประวัติการใช้งาน
    async loadActivityLog() {
        try {
            const response = await fetch('api/activity.php?limit=10');
            const data = await response.json();
            
            if (data.success) {
                this.renderActivityLog(data.activities);
            }
        } catch (error) {
            console.error('Error loading activity log:', error);
        }
    }

    // แสดงประวัติการใช้งาน
    renderActivityLog(activities) {
        const container = document.getElementById('activityLog');
        if (!container) return;

        if (activities.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #9ca3af; padding: 20px;">ยังไม่มีกิจกรรม</p>';
            return;
        }

        container.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div style="flex: 1;">
                    <p style="font-weight: 600; font-size: 14px; color: #1f2937; margin-bottom: 4px;">
                        ${activity.action_detail}
                    </p>
                    <p style="font-size: 12px; color: #6b7280;">
                        ${activity.username} • ${activity.created_at}
                    </p>
                </div>
            </div>
        `).join('');
    }

    // ตั้งค่าระบบรับรู้เสียง
    setupVoiceRecognition() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.warn('Browser does not support speech recognition');
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        this.recognition.lang = 'th-TH';
        this.recognition.continuous = false;
        this.recognition.interimResults = false;

        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript.toLowerCase();
            console.log('Voice command:', transcript);
            this.processVoiceCommand(transcript);
        };

        this.recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            this.stopVoiceRecognition();
            this.showNotification('error', 'ไม่สามารถรับรู้เสียงได้ กรุณาลองใหม่');
        };

        this.recognition.onend = () => {
            this.stopVoiceRecognition();
        };
    }

    // เริ่มรับรู้เสียง
    startVoiceRecognition() {
        if (!this.recognition) {
            this.showNotification('error', 'เบราว์เซอร์ของคุณไม่รองรับการรับรู้เสียง');
            return;
        }

        if (this.isRecording) {
            this.stopVoiceRecognition();
            return;
        }

        this.isRecording = true;
        document.getElementById('voiceStatus').classList.remove('hidden');
        document.getElementById('voiceBtn').classList.add('recording');
        document.getElementById('voiceBtn').innerHTML = `
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
            </svg>
            <span>หยุดฟัง</span>
        `;

        try {
            this.recognition.start();
        } catch (error) {
            console.error('Error starting recognition:', error);
            this.stopVoiceRecognition();
        }
    }

    // หยุดรับรู้เสียง
    stopVoiceRecognition() {
        this.isRecording = false;
        document.getElementById('voiceStatus').classList.add('hidden');
        document.getElementById('voiceBtn').classList.remove('recording');
        document.getElementById('voiceBtn').innerHTML = `
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
            </svg>
            <span>สั่งงานด้วยเสียง</span>
        `;

        if (this.recognition) {
            this.recognition.stop();
        }
    }

    // ประมวลผลคำสั่งเสียง
    async processVoiceCommand(command) {
        console.log('Processing command:', command);
        
        // ทำให้คำสั่งเป็นตัวพิมพ์เล็กทั้งหมด
        command = command.toLowerCase().trim();
        
        // แสดงคำสั่งที่รับมาให้ผู้ใช้เห็น
        this.showNotification('info', `คำสั่งที่ได้ยิน: "${command}"`);

        // คำสั่งเปิดไฟทั้งหมด - รองรับหลายรูปแบบ
        if (command.includes('เปิดไฟทั้งหมด') || 
            command.includes('เปิดทั้งหมด') || 
            command.includes('ห้องมืดจังว้ะ') || 
            command.includes('ห้องมืดจังวะ') || 
            command.includes('ห้องมืดจังอะ') || 
            command.includes('ควยใหญ่') || 
            command.includes('ผมรักในหลวง') || 
            command.includes('พี่จ๋าไม่ไหวแล้ว') || 
            command.includes('เปิดไฟทุก') ||
            command.includes('เปิดหมด') ||
            (command.includes('เปิด') && command.includes('ทั้งหมด'))) {
            await this.executeVoiceCommand('all_on', 'เปิดไฟทั้งหมด');
            return;
        }

        // คำสั่งปิดไฟทั้งหมด - รองรับหลายรูปแบบ
        if (command.includes('ปิดไฟทั้งหมด') || 
            command.includes('ปิดทั้งหมด') || 
            command.includes('ควยเล็ก') || 
            command.includes('พวกล้มเจ้า') || 
            command.includes('ปิดไฟทุก') ||
            command.includes('ปิดหมด') ||
            (command.includes('ปิด') && command.includes('ทั้งหมด'))) {
            await this.executeVoiceCommand('all_off', 'ปิดไฟทั้งหมด');
            return;
        }

        // 🆕 คำสั่งเปิด/ปิดไฟเฉพาะจุด (Specific Light Control)
        const specificLightKeywords = {
            'เพดาน': ['เพดาน', 'ceiling', 'ซีลิ่ง'],
            'โคม': ['โคม', 'lamp', 'แลมป์', 'โต๊ะ'],
            'ข้างเตียง': ['ข้างเตียง', 'ข้าง', 'เตียง', 'beside'],
            'ใต้ตู้': ['ใต้ตู้', 'ใต้', 'ตู้', 'under'],
            'หลัง': ['หลัง', 'behind', 'back'],
            'สปอร์ตไลท์': ['สปอร์ต', 'สปอร์ตไลท์', 'spot', 'spotlight'],
            'ดาวน์ไลท์': ['ดาวน์', 'ดาวน์ไลท์', 'down', 'downlight'],
            'เชิง': ['เชิง', 'หัว', 'เชิงเทียน'],
            'แขวน': ['แขวน', 'ห้อย', 'pendant'],
            'ผนัง': ['ผนัง', 'wall', 'ฝา']
        };

        // ตรวจสอบว่าคำสั่งมี keyword ของจุดไฟเฉพาะหรือไม่
        let foundSpecificLight = false;
        for (const [lightType, keywords] of Object.entries(specificLightKeywords)) {
            const hasLightKeyword = keywords.some(keyword => command.includes(keyword));
            
            if (hasLightKeyword) {
                foundSpecificLight = true;
                
                // ตรวจสอบว่าเป็นคำสั่งเปิดหรือปิด
                if (command.includes('เปิด')) {
                    await this.executeVoiceCommand('specific_light_on', `เปิดไฟ${lightType}`, lightType);
                    return;
                } else if (command.includes('ปิด')) {
                    await this.executeVoiceCommand('specific_light_off', `ปิดไฟ${lightType}`, lightType);
                    return;
                }
            }
        }

        // คำสั่งเปิดไฟแต่ละห้อง - เพิ่ม keywords ให้ครอบคลุมมากขึ้น
        const roomCommands = {
            'ห้องนั่งเล่น': ['นั่งเล่น', 'ห้องนั่ง', 'รับแขก', 'นั่ง', 'ห้องรับแขก', 'ลิฟวิ่ง'],
            'ห้องนอน': ['นอน', 'ห้องนอน', 'เบด', 'นอนหลับ', 'นอนพัก'],
            'ห้องครัว': ['ครัว', 'ห้องครัว', 'ทำกิน', 'ทำอาหาร', 'กิน', 'คิด', 'คิทเช่น'],
            'ห้องน้ำ': ['น้ำ', 'ห้องน้ำ', 'ส้วม', 'บาธรูม', 'นำ้', 'นั่ม'],
            'ห้องทำงาน': ['ทำงาน', 'ห้องทำงาน', 'ออฟฟิศ', 'งาน', 'ทำงาน', 'ออฟฟิต'],
            'สวน': ['สวน', 'ระเบียง', 'สวนหน้า', 'สวนหลัง', 'นอกบ้าน', 'ซอน']
        };

        // ตรวจสอบคำสั่งเปิด/ปิดไฟแต่ละห้อง
        let foundRoom = false;
        
        for (const [roomName, keywords] of Object.entries(roomCommands)) {
            // ตรวจสอบว่ามี keyword ของห้องนี้ในคำสั่งหรือไม่
            const hasRoomKeyword = keywords.some(keyword => {
                // รองรับทั้งคำเต็มและคำย่อ
                return command.includes(keyword) || 
                       command.replace(/\s+/g, '').includes(keyword.replace(/\s+/g, ''));
            });
            
            if (hasRoomKeyword) {
                foundRoom = true;
                
                // ตรวจสอบว่าเป็นคำสั่งเปิดหรือปิด
                if (command.includes('เปิด')) {
                    await this.executeVoiceCommand('room_on', `เปิดไฟ${roomName}`, roomName);
                    return;
                } else if (command.includes('ปิด')) {
                    await this.executeVoiceCommand('room_off', `ปิดไฟ${roomName}`, roomName);
                    return;
                } else {
                    // ถ้าพูดแค่ชื่อห้อง ไม่ระบุเปิดหรือปิด
                    this.showNotification('error', `ได้ยินชื่อห้อง "${roomName}" แต่ไม่ชัดเจนว่าต้องการเปิดหรือปิด กรุณาพูดใหม่`);
                    return;
                }
            }
        }

        // ถ้าไม่เจอคำสั่งที่รู้จัก
        if (!foundRoom) {
            console.log('Unknown command:', command);
            this.showNotification('error', `ไม่เข้าใจคำสั่ง "${command}" กรุณาลองใหม่\n\nตัวอย่าง: "เปิดไฟห้องนอน" หรือ "ปิดไฟครัว"`);
        }
    }

    // ดำเนินการตามคำสั่งเสียง
    async executeVoiceCommand(action, commandText, param = null) {
        try {
            const formData = new URLSearchParams();
            formData.append('action', action);
            formData.append('command', commandText);
            
            // ส่ง parameter (อาจเป็น roomName หรือ lightType)
            if (param) {
                if (action.startsWith('specific_light')) {
                    formData.append('light_type', param);
                } else {
                    formData.append('room_name', param);
                }
            }

            const response = await fetch('api/voice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                await this.loadLights();
                await this.loadStats();
                await this.loadActivityLog();
                this.showNotification('success', `คำสั่ง: "${commandText}" - ${data.message}`);
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            console.error('Error executing voice command:', error);
            this.showNotification('error', 'เกิดข้อผิดพลาดในการดำเนินการ');
        }
    }

    // แสดงการแจ้งเตือน
    showNotification(type, message) {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'alert-success' : 
                       type === 'info' ? 'alert-info' : 'alert-error';
        notification.className = `alert ${bgColor}`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.style.maxWidth = '500px';
        notification.style.animation = 'slideIn 0.3s ease';
        
        let icon = '';
        if (type === 'success') {
            icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
        } else if (type === 'info') {
            icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        } else {
            icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
        }
        
        notification.innerHTML = `
            <svg style="width: 20px; height: 20px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icon}
            </svg>
            <span style="white-space: pre-line;">${message}</span>
        `;

        document.body.appendChild(notification);

        const duration = type === 'info' ? 2000 : 3000;
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
    }
}

// สร้างอินสแตนซ์
let smartHome;

// เริ่มต้นเมื่อโหลดหน้าเสร็จ
document.addEventListener('DOMContentLoaded', () => {
    smartHome = new SmartHome();
});

// CSS Animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);