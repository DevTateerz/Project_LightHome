// Admin Panel JavaScript
class AdminPanel {
    constructor() {
        this.currentTab = 'users';
        this.init();
    }

    init() {
        console.log('Admin Panel initializing...');
        this.loadUsers();
        this.loadRooms();
        this.loadLights();
        this.loadVoiceCommands();
        this.loadAllLogs();
        console.log('Admin Panel initialized!');
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            if (this.currentTab === 'users') this.loadUsers();
            else if (this.currentTab === 'rooms') this.loadRooms();
            else if (this.currentTab === 'lights') this.loadLights();
            else if (this.currentTab === 'logs') this.loadAllLogs();
        }, 30000);
    }

    // === Tab Management ===
    showTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
        
        event.target.closest('.tab').classList.add('active');
        document.getElementById(tab + '-section').classList.add('active');
    }

    // === Users Management ===
    async loadUsers() {
        try {
            const response = await fetch('api.php?action=get_users');
            const data = await response.json();
            
            if (data.success) {
                this.renderUsers(data.users);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    renderUsers(users) {
        const container = document.getElementById('users-list');
        if (users.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #9ca3af; padding: 40px;">ยังไม่มีสมาชิก</p>';
            return;
        }

        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>ชื่อผู้ใช้</th><th>ชื่อ-นามสกุล</th><th>อีเมล</th><th>Role</th><th>สถานะ</th><th>เข้าสู่ระบบล่าสุด</th><th>จัดการ</th>';
        html += '</tr></thead><tbody>';

        users.forEach(user => {
            // Role badge
            let roleBadge = '';
            if (user.role === 'admin') {
                roleBadge = '<span class="badge" style="background: #dbeafe; color: #1e40af;">👑 Admin</span>';
            } else if (user.role === 'user') {
                roleBadge = '<span class="badge" style="background: #d1fae5; color: #065f46;">👤 User</span>';
            } else {
                roleBadge = '<span class="badge" style="background: #f3f4f6; color: #6b7280;">👁️ Guest</span>';
            }
            
            const statusBadge = user.status === 'active' 
                ? '<span class="badge badge-success">ใช้งาน</span>' 
                : '<span class="badge badge-danger">ระงับ</span>';
            
            const lastLogin = user.last_login || '-';
            const isOnlyAdmin = user.role === 'admin'; // จะตรวจสอบในฝั่ง delete

            html += `<tr>
                <td>${user.id}</td>
                <td><strong>${user.username}</strong></td>
                <td>${user.full_name || '-'}</td>
                <td>${user.email}</td>
                <td>${roleBadge}</td>
                <td>${statusBadge}</td>
                <td style="font-size: 12px;">${lastLogin}</td>
                <td>
                    <button class="icon-btn" onclick="editUser(${user.id})" title="แก้ไข">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button class="icon-btn" onclick="deleteUser(${user.id}, '${user.username}')" title="ลบ">
                        <svg style="width: 20px; height: 20px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    showAddUserModal() {
        const modal = this.createModal('เพิ่มสมาชิกใหม่', `
            <form id="add-user-form">
                <div class="form-group">
                    <label>ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>อีเมล</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>รหัสผ่าน</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>สิทธิ์การใช้งาน (Role)</label>
                    <select name="role" class="form-control" required>
                        <option value="user">👤 User - ผู้ใช้งานทั่วไป</option>
                        <option value="admin">👑 Admin - ผู้ดูแลระบบ</option>
                        <option value="guest">👁️ Guest - ผู้เยี่ยมชม</option>
                    </select>
                    <small style="color: #6b7280; margin-top: 5px; display: block;">
                        <strong>User:</strong> ควบคุมไฟได้, ดู Dashboard ได้<br>
                        <strong>Admin:</strong> สิทธิ์เต็ม, เข้า Admin Panel ได้<br>
                        <strong>Guest:</strong> ดูอย่างเดียว, ไม่สามารถควบคุมไฟได้
                    </small>
                </div>
                <div class="flex gap-2" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">เพิ่มสมาชิก</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                </div>
            </form>
        `);

        document.getElementById('add-user-form').onsubmit = (e) => {
            e.preventDefault();
            this.addUser(new FormData(e.target));
        };
    }

    async addUser(formData) {
        formData.append('action', 'add_user');
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.closeModal();
                this.loadUsers();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async deleteUser(userId, username) {
        if (!confirm(`คุณต้องการลบสมาชิก "${username}" ใช่หรือไม่?`)) return;

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_user&user_id=${userId}`
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.loadUsers();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async editUser(userId) {
        console.log('editUser called with userId:', userId, 'type:', typeof userId);
        // โหลดข้อมูลผู้ใช้ก่อน
        try {
            const response = await fetch(`api.php?action=get_users`);
            const data = await response.json();
            
            console.log('API Response:', data);
            
            if (data.success) {
                const user = data.users.find(u => u.id == userId); // เปลี่ยนเป็น ==
                if (!user) {
                    console.error('User not found:', userId);
                    console.log('Available users:', data.users.map(u => ({id: u.id, type: typeof u.id})));
                    return;
                }

                console.log('Found user:', user);

                const modal = this.createModal('แก้ไขข้อมูลสมาชิก', `
                    <form id="edit-user-form">
                        <input type="hidden" name="user_id" value="${user.id}">
                        <div class="form-group">
                            <label>ชื่อผู้ใช้</label>
                            <input type="text" value="${user.username}" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>ชื่อ-นามสกุล</label>
                            <input type="text" name="full_name" class="form-control" value="${user.full_name || ''}" required>
                        </div>
                        <div class="form-group">
                            <label>อีเมล</label>
                            <input type="email" name="email" class="form-control" value="${user.email}" required>
                        </div>
                        <div class="form-group">
                            <label>สิทธิ์การใช้งาน (Role)</label>
                            <select name="role" class="form-control" required>
                                <option value="user" ${user.role === 'user' ? 'selected' : ''}>👤 User - ผู้ใช้งานทั่วไป</option>
                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>👑 Admin - ผู้ดูแลระบบ</option>
                                <option value="guest" ${user.role === 'guest' ? 'selected' : ''}>👁️ Guest - ผู้เยี่ยมชม</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="active" ${user.status === 'active' ? 'selected' : ''}>ใช้งาน</option>
                                <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>ระงับ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>รหัสผ่านใหม่ (ไม่ต้องกรอกหากไม่ต้องการเปลี่ยน)</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        <div class="flex gap-2" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">บันทึกการแก้ไข</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                        </div>
                    </form>
                `);

                document.getElementById('edit-user-form').onsubmit = (e) => {
                    e.preventDefault();
                    this.updateUser(new FormData(e.target));
                };
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async updateUser(formData) {
        formData.append('action', 'update_user');
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.closeModal();
                this.loadUsers();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    // === Rooms Management ===
    async loadRooms() {
        try {
            const response = await fetch('api.php?action=get_rooms');
            const data = await response.json();
            
            if (data.success) {
                this.renderRooms(data.rooms);
            }
        } catch (error) {
            console.error('Error loading rooms:', error);
        }
    }

    renderRooms(rooms) {
        const container = document.getElementById('rooms-list');
        if (rooms.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #9ca3af; padding: 40px;">ยังไม่มีห้อง</p>';
            return;
        }

        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>ชื่อห้อง</th><th>รหัสห้อง</th><th>คำอธิบาย</th><th>จำนวนไฟ</th><th>จัดการ</th>';
        html += '</tr></thead><tbody>';

        rooms.forEach(room => {
            html += `<tr>
                <td>${room.id}</td>
                <td><strong>${room.room_name}</strong></td>
                <td><span class="badge badge-info">${room.room_code}</span></td>
                <td>${room.description || '-'}</td>
                <td>${room.light_count} จุด</td>
                <td>
                    <button class="icon-btn" onclick="editRoom(${room.id})" title="แก้ไข">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button class="icon-btn" onclick="deleteRoom(${room.id}, '${room.room_name}')" title="ลบ">
                        <svg style="width: 20px; height: 20px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    showAddRoomModal() {
        const modal = this.createModal('เพิ่มห้องใหม่', `
            <form id="add-room-form">
                <div class="form-group">
                    <label>ชื่อห้อง</label>
                    <input type="text" name="room_name" class="form-control" placeholder="เช่น ห้องนอนชั้น 2" required>
                </div>
                <div class="form-group">
                    <label>รหัสห้อง (ภาษาอังกฤษ)</label>
                    <input type="text" name="room_code" class="form-control" placeholder="เช่น bedroom2" required pattern="[a-z0-9_]+">
                    <small style="color: #6b7280;">ใช้ตัวพิมพ์เล็ก ตัวเลข และ _ เท่านั้น</small>
                </div>
                <div class="form-group">
                    <label>คำอธิบาย</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="flex gap-2" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">เพิ่มห้อง</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                </div>
            </form>
        `);

        document.getElementById('add-room-form').onsubmit = (e) => {
            e.preventDefault();
            this.addRoom(new FormData(e.target));
        };
    }

    async addRoom(formData) {
        formData.append('action', 'add_room');
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.closeModal();
                this.loadRooms();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async deleteRoom(roomId, roomName) {
        if (!confirm(`คุณต้องการลบห้อง "${roomName}" ใช่หรือไม่?\n(จุดไฟทั้งหมดในห้องนี้จะถูกลบด้วย)`)) return;

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_room&room_id=${roomId}`
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.loadRooms();
                this.loadLights();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async editRoom(roomId) {
        console.log('editRoom called with roomId:', roomId, 'type:', typeof roomId);
        try {
            const response = await fetch(`api.php?action=get_rooms`);
            const data = await response.json();
            
            console.log('Rooms API Response:', data);
            
            if (data.success) {
                const room = data.rooms.find(r => r.id == roomId); // เปลี่ยนเป็น ==
                if (!room) {
                    console.error('Room not found:', roomId);
                    console.log('Available rooms:', data.rooms.map(r => ({id: r.id, type: typeof r.id})));
                    return;
                }
                
                console.log('Found room:', room);

                const modal = this.createModal('แก้ไขห้อง', `
                    <form id="edit-room-form">
                        <input type="hidden" name="room_id" value="${room.id}">
                        <div class="form-group">
                            <label>ชื่อห้อง</label>
                            <input type="text" name="room_name" class="form-control" value="${room.room_name}" required>
                        </div>
                        <div class="form-group">
                            <label>รหัสห้อง (ภาษาอังกฤษ)</label>
                            <input type="text" value="${room.room_code}" class="form-control" disabled>
                            <small style="color: #6b7280;">ไม่สามารถแก้ไขรหัสห้องได้</small>
                        </div>
                        <div class="form-group">
                            <label>คำอธิบาย</label>
                            <textarea name="description" class="form-control" rows="3">${room.description || ''}</textarea>
                        </div>
                        <div class="flex gap-2" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">บันทึกการแก้ไข</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                        </div>
                    </form>
                `);

                document.getElementById('edit-room-form').onsubmit = (e) => {
                    e.preventDefault();
                    this.updateRoom(new FormData(e.target));
                };
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async updateRoom(formData) {
        formData.append('action', 'update_room');
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.closeModal();
                this.loadRooms();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    // === Lights Management ===
    async loadLights() {
        try {
            const response = await fetch('api.php?action=get_all_lights');
            const data = await response.json();
            
            if (data.success) {
                this.renderLights(data.lights);
            }
        } catch (error) {
            console.error('Error loading lights:', error);
        }
    }

    renderLights(lights) {
        const container = document.getElementById('lights-list');
        if (lights.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #9ca3af; padding: 40px;">ยังไม่มีจุดไฟ</p>';
            return;
        }

        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>ชื่อจุดไฟ</th><th>ห้อง</th><th>รหัสอุปกรณ์</th><th>สถานะ</th><th>ความสว่าง</th><th>จัดการ</th>';
        html += '</tr></thead><tbody>';

        lights.forEach(light => {
            const statusBadge = light.status === 'on' 
                ? '<span class="badge badge-success">เปิด</span>' 
                : '<span class="badge badge-danger">ปิด</span>';

            html += `<tr>
                <td>${light.id}</td>
                <td><strong>${light.light_name}</strong></td>
                <td>${light.room_name}</td>
                <td><code style="font-size: 11px;">${light.device_code}</code></td>
                <td>${statusBadge}</td>
                <td>${light.brightness}%</td>
                <td>
                    <button class="icon-btn" onclick="editLight(${light.id})" title="แก้ไข">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button class="icon-btn" onclick="deleteLight(${light.id}, '${light.light_name}')" title="ลบ">
                        <svg style="width: 20px; height: 20px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    async showAddLightModal() {
        // โหลดรายการห้องก่อน
        const response = await fetch('api.php?action=get_rooms');
        const data = await response.json();
        
        let roomOptions = '';
        if (data.success) {
            data.rooms.forEach(room => {
                roomOptions += `<option value="${room.id}">${room.room_name}</option>`;
            });
        }

        const modal = this.createModal('เพิ่มจุดไฟใหม่', `
            <form id="add-light-form">
                <div class="form-group">
                    <label>ชื่อจุดไฟ</label>
                    <input type="text" name="light_name" class="form-control" placeholder="เช่น ไฟเพดาน, ไฟข้างเตียง" required>
                </div>
                <div class="form-group">
                    <label>ห้อง</label>
                    <select name="room_id" class="form-control" required>
                        <option value="">-- เลือกห้อง --</option>
                        ${roomOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label>รหัสอุปกรณ์ (Device Code)</label>
                    <input type="text" name="device_code" class="form-control" placeholder="เช่น bedroom_ceiling" required pattern="[a-z0-9_]+">
                    <small style="color: #6b7280;">ใช้ตัวพิมพ์เล็ก ตัวเลข และ _ เท่านั้น</small>
                </div>
                <div class="flex gap-2" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">เพิ่มจุดไฟ</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                </div>
            </form>
        `);

        document.getElementById('add-light-form').onsubmit = (e) => {
            e.preventDefault();
            this.addLight(new FormData(e.target));
        };
    }

    async addLight(formData) {
        formData.append('action', 'add_light');
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.closeModal();
                this.loadLights();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async deleteLight(lightId, lightName) {
        if (!confirm(`คุณต้องการลบจุดไฟ "${lightName}" ใช่หรือไม่?`)) return;

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_light&light_id=${lightId}`
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.loadLights();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async editLight(lightId) {
        console.log('editLight called with lightId:', lightId, 'type:', typeof lightId);
        try {
            // โหลดรายการห้อง
            const roomsResponse = await fetch('api.php?action=get_rooms');
            const roomsData = await roomsResponse.json();
            
            // โหลดข้อมูลจุดไฟ
            const lightsResponse = await fetch('api.php?action=get_all_lights');
            const lightsData = await lightsResponse.json();
            
            console.log('Lights API Response:', lightsData);
            
            if (roomsData.success && lightsData.success) {
                const light = lightsData.lights.find(l => l.id == lightId); // เปลี่ยนเป็น ==
                if (!light) {
                    console.error('Light not found:', lightId);
                    console.log('Available lights:', lightsData.lights.map(l => ({id: l.id, type: typeof l.id})));
                    return;
                }
                
                console.log('Found light:', light);

                let roomOptions = '';
                roomsData.rooms.forEach(room => {
                    const selected = room.id == light.room_id ? 'selected' : '';
                    roomOptions += `<option value="${room.id}" ${selected}>${room.room_name}</option>`;
                });

                const modal = this.createModal('แก้ไขจุดไฟ', `
                    <form id="edit-light-form">
                        <input type="hidden" name="light_id" value="${light.id}">
                        <div class="form-group">
                            <label>ชื่อจุดไฟ</label>
                            <input type="text" name="light_name" class="form-control" value="${light.light_name}" required>
                        </div>
                        <div class="form-group">
                            <label>ห้อง</label>
                            <select name="room_id" class="form-control" required>
                                ${roomOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>รหัสอุปกรณ์</label>
                            <input type="text" value="${light.device_code}" class="form-control" disabled>
                            <small style="color: #6b7280;">ไม่สามารถแก้ไขรหัสอุปกรณ์ได้</small>
                        </div>
                        <div class="flex gap-2" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">บันทึกการแก้ไข</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
                        </div>
                    </form>
                `);

                document.getElementById('edit-light-form').onsubmit = (e) => {
                    e.preventDefault();
                    this.updateLight(new FormData(e.target));
                };
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    async updateLight(formData) {
        formData.append('action', 'update_light');
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('success', data.message);
                this.closeModal();
                this.loadLights();
            } else {
                this.showNotification('error', data.message);
            }
        } catch (error) {
            this.showNotification('error', 'เกิดข้อผิดพลาด');
        }
    }

    // === Voice Commands ===
    loadVoiceCommands() {
        const container = document.getElementById('voice-commands');
        container.innerHTML = `
            <div style="background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 15px;">คำสั่งเสียงทั้งหมด</h3>
                <p style="color: #6b7280; margin-bottom: 15px;">ระบบรองรับคำสั่งเสียงดังนี้:</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                    <div style="background: white; padding: 15px; border-radius: 8px;">
                        <h4 style="font-weight: 600; margin-bottom: 10px;">🔥 คำสั่งทั่วไป</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 5px 0;">✓ "เปิดไฟทั้งหมด"</li>
                            <li style="padding: 5px 0;">✓ "ปิดไฟทั้งหมด"</li>
                        </ul>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 8px;">
                        <h4 style="font-weight: 600; margin-bottom: 10px;">🏠 คำสั่งรายห้อง</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 5px 0;">✓ "เปิดไฟห้องนอน"</li>
                            <li style="padding: 5px 0;">✓ "ปิดไฟห้องครัว"</li>
                            <li style="padding: 5px 0;">✓ "เปิดไฟนั่งเล่น"</li>
                        </ul>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 8px;">
                        <h4 style="font-weight: 600; margin-bottom: 10px;">💡 คำสั่งรายจุด</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 5px 0;">✓ "เปิดไฟเพดานห้องนอน"</li>
                            <li style="padding: 5px 0;">✓ "ปิดไฟโคมตั้งโต๊ะ"</li>
                            <li style="padding: 5px 0;">✓ "เปิดไฟข้างเตียง"</li>
                        </ul>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #fffbeb; border-left: 4px solid #fbbf24; border-radius: 4px;">
                    <p style="margin: 0; color: #92400e;">
                        <strong>💡 เคล็ดลับ:</strong> คำสั่งเสียงจะทำงานโดยอัตโนมัติตามชื่อห้องและชื่อจุดไฟที่คุณตั้งไว้
                    </p>
                </div>
            </div>
        `;
    }

    // === Activity Logs ===
    async loadAllLogs() {
        try {
            const response = await fetch('../api/activity.php?limit=100');
            const data = await response.json();
            
            if (data.success) {
                this.renderAllLogs(data.activities);
            }
        } catch (error) {
            console.error('Error loading logs:', error);
        }
    }

    renderAllLogs(logs) {
        const container = document.getElementById('all-logs');
        if (logs.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #9ca3af; padding: 40px;">ยังไม่มีประวัติ</p>';
            return;
        }

        let html = '<table class="data-table"><thead><tr>';
        html += '<th>เวลา</th><th>ผู้ใช้</th><th>ประเภท</th><th>รายละเอียด</th>';
        html += '</tr></thead><tbody>';

        logs.forEach(log => {
            html += `<tr>
                <td style="font-size: 12px;">${log.created_at}</td>
                <td><strong>${log.username}</strong></td>
                <td><span class="badge badge-info">${log.action_type}</span></td>
                <td>${log.action_detail}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    // === Modal Management ===
    createModal(title, content) {
        const modalHTML = `
            <div class="modal active" id="admin-modal">
                <div class="modal-content">
                    <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 20px;">${title}</h3>
                    ${content}
                </div>
            </div>
        `;
        document.getElementById('modal-container').innerHTML = modalHTML;
        
        // Close on outside click
        document.getElementById('admin-modal').addEventListener('click', (e) => {
            if (e.target.id === 'admin-modal') {
                this.closeModal();
            }
        });
    }

    closeModal() {
        document.getElementById('modal-container').innerHTML = '';
    }

    // === Notification ===
    showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'error'}`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 99999; animation: slideIn 0.3s;';
        notification.innerHTML = `
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Global functions for onclick handlers
function showTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    
    event.target.closest('.tab').classList.add('active');
    document.getElementById(tab + '-section').classList.add('active');
    
    // Track current tab
    if (window.adminPanel) {
        window.adminPanel.currentTab = tab;
    }
}

// Helper function to ensure adminPanel is loaded
function waitForAdminPanel(callback) {
    if (window.adminPanel) {
        callback();
    } else {
        console.log('Waiting for adminPanel to initialize...');
        setTimeout(() => waitForAdminPanel(callback), 100);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.adminPanel = new AdminPanel();
    console.log('AdminPanel assigned to window.adminPanel');
});

// For backward compatibility
let adminPanel;
window.addEventListener('load', () => {
    adminPanel = window.adminPanel;
    console.log('adminPanel ready:', !!adminPanel);
});

// Helper functions for buttons (work even if adminPanel not ready yet)
window.showAddUserModal = function() {
    waitForAdminPanel(() => window.adminPanel.showAddUserModal());
};

window.showAddRoomModal = function() {
    waitForAdminPanel(() => window.adminPanel.showAddRoomModal());
};

window.showAddLightModal = function() {
    waitForAdminPanel(() => window.adminPanel.showAddLightModal());
};

// User management helpers
window.editUser = function(userId) {
    console.log('window.editUser called with:', userId);
    waitForAdminPanel(() => window.adminPanel.editUser(userId));
};

window.deleteUser = function(userId, username) {
    console.log('window.deleteUser called with:', userId, username);
    waitForAdminPanel(() => window.adminPanel.deleteUser(userId, username));
};

// Room management helpers
window.editRoom = function(roomId) {
    console.log('window.editRoom called with:', roomId);
    waitForAdminPanel(() => window.adminPanel.editRoom(roomId));
};

window.deleteRoom = function(roomId, roomName) {
    console.log('window.deleteRoom called with:', roomId, roomName);
    waitForAdminPanel(() => window.adminPanel.deleteRoom(roomId, roomName));
};

// Light management helpers
window.editLight = function(lightId) {
    console.log('window.editLight called with:', lightId);
    waitForAdminPanel(() => window.adminPanel.editLight(lightId));
};

window.deleteLight = function(lightId, lightName) {
    console.log('window.deleteLight called with:', lightId, lightName);
    waitForAdminPanel(() => window.adminPanel.deleteLight(lightId, lightName));
};

// Modal helpers
window.closeModal = function() {
    waitForAdminPanel(() => window.adminPanel.closeModal());
};