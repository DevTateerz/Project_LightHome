# 🔌 คู่มือเชื่อมต่อ Hardware จริง - Smart Home System

## 📋 สารบัญ
1. [ภาพรวมระบบ](#ภาพรวมระบบ)
2. [Hardware ที่ต้องใช้](#hardware-ที่ต้องใช้)
3. [สถาปัตยกรรมระบบ](#สถาปัตยกรรมระบบ)
4. [การเปลี่ยนแปลงที่ต้องทำ](#การเปลี่ยนแปลงที่ต้องทำ)
5. [Communication Protocol](#communication-protocol)
6. [Security](#security)
7. [ขั้นตอนการพัฒนา](#ขั้นตอนการพัฒนา)
8. [ค่าใช้จ่ายโดยประมาณ](#ค่าใช้จ่ายโดยประมาณ)

---

## 🎯 ภาพรวมระบบ

### ระบบปัจจุบัน (Software Only)
```
[Web Browser] → [PHP Server] → [MySQL Database]
     ↓
[ไฟเปิด/ปิดจำลอง]
```

### ระบบที่จะพัฒนา (Hardware Integration)
```
[Web Browser] → [PHP Server] → [MySQL Database]
                     ↓
            [MQTT Broker / API Gateway]
                     ↓
              [IoT Controller]
                     ↓
            [Smart Relay Modules]
                     ↓
        [ไฟบ้านจริง 220V]
```

---

## 🛠️ Hardware ที่ต้องใช้

### 1. **IoT Controller (เลือก 1 อย่าง)**

#### ตัวเลือกที่ 1: ESP32 (แนะนำ)
- **ราคา:** ~150-300 บาท/ตัว
- **ข้อดี:**
  - WiFi + Bluetooth ในตัว
  - ราคาถูก
  - ชุมชนใหญ่ หาคำตอบง่าย
  - รองรับ Arduino IDE
- **ข้อเสี่ย:**
  - พัฒนาเอง ต้องเขียนโค้ด
- **แนะนำสำหรับ:** เริ่มต้นทดสอบ, งบน้อย

#### ตัวเลือกที่ 2: Raspberry Pi
- **ราคา:** ~2,000-3,000 บาท
- **ข้อดี:**
  - ระบบปฏิบัติการเต็มรูปแบบ (Linux)
  - รันโค้ด PHP/Python ได้
  - เสถียร เหมาะกับระบบที่ต้องทำงาน 24/7
- **ข้อเสี่ย:**
  - ราคาแพงกว่า
- **แนะนำสำหรับ:** ระบบจริง, ต้องการความเสถียร

#### ตัวเลือกที่ 3: Arduino + WiFi Shield
- **ราคา:** ~500-800 บาท
- **ข้อดี:**
  - เขียนโค้ดง่าย
  - มีตัวอย่างเยอะ
- **ข้อเสี่ย:**
  - ต้องใช้ WiFi Shield เพิ่ม
  - ประสิทธิภาพน้อยกว่า ESP32

### 2. **Smart Relay Module (สวิตช์อัจฉริยะ)**

#### แบบที่ 1: Relay Module 4/8 Channel
- **ราคา:** ~200-500 บาท/บอร์ด
- **ข้อมูล:**
  - รองรับ 220V / 10A
  - ควบคุมได้ 4-8 ไฟพร้อมกัน
  - เชื่อมต่อกับ ESP32/Arduino
- **การใช้งาน:**
  ```
  ESP32 GPIO → Relay IN1-IN8 → ไฟบ้าน
  ```

#### แบบที่ 2: Sonoff (Smart Switch สำเร็จรูป)
- **ราคา:** ~150-300 บาท/ตัว
- **ข้อดี:**
  - ติดตั้งง่าย แทนสวิตช์เดิม
  - Flash firmware ใหม่ได้ (Tasmota, ESPHome)
  - ปลอดภัย มี CE certified
- **แนะนำสำหรับ:** ไม่อยากบัดกรี, ต้องการติดตั้งจริง

#### แบบที่ 3: Shelly Relay
- **ราคา:** ~400-600 บาท/ตัว
- **ข้อดี:**
  - ขนาดเล็ก ติดในกล่องสวิตช์ได้
  - API เปิดให้ใช้ฟรี
  - ปลอดภัยสูง
- **แนะนำสำหรับ:** ระบบจริง, ต้องการความปลอดภัยสูง

### 3. **อุปกรณ์เสริม**

- **Power Supply 5V/3.3V:** ~100-200 บาท
- **Jumper Wires:** ~50-100 บาท
- **Breadboard (ทดสอบ):** ~50-100 บาท
- **กล่องพลาสติก (เคส):** ~50-200 บาท
- **MCB (เบรกเกอร์):** ~100-300 บาท (ต่อตัว)

### 4. **Optional: Sensor เพิ่มเติม**

- **DHT22 (Temperature/Humidity):** ~80-150 บาท
- **Motion Sensor (PIR):** ~30-80 บาท
- **Light Sensor (LDR):** ~10-30 บาท
- **Door/Window Sensor:** ~50-150 บาท

---

## 🏗️ สถาปัตยกรรมระบบ

### แผนผังการเชื่อมต่อ

```
┌─────────────────────────────────────────────────────────┐
│                    Internet / WiFi                       │
└────────────┬────────────────────────────┬────────────────┘
             │                            │
             ↓                            ↓
    ┌────────────────┐          ┌─────────────────┐
    │  Web Server    │←─────────│  MQTT Broker    │
    │  (PHP/MySQL)   │          │  (Mosquitto)    │
    └────────────────┘          └────────┬────────┘
                                         │
                            ┌────────────┼────────────┐
                            ↓            ↓            ↓
                    ┌──────────┐  ┌──────────┐  ┌──────────┐
                    │  ESP32   │  │  ESP32   │  │  ESP32   │
                    │ (Living) │  │(Bedroom) │  │(Kitchen) │
                    └────┬─────┘  └────┬─────┘  └────┬─────┘
                         │             │             │
                    ┌────┴─────┐  ┌───┴──────┐  ┌───┴──────┐
                    │ Relay 1-4│  │ Relay 1-4│  │ Relay 1-4│
                    └────┬─────┘  └────┬─────┘  └────┬─────┘
                         │             │             │
                    [ไฟบ้านจริง]  [ไฟบ้านจริง]  [ไฟบ้านจริง]
```

### วิธีการสื่อสาร (3 แบบ)

#### 1. **MQTT (Message Queue Telemetry Transport) - แนะนำ**
```
ข้อดี:
✅ Real-time, Low latency
✅ รองรับ QoS (Quality of Service)
✅ Two-way communication
✅ Publish/Subscribe model

การทำงาน:
PHP → Publish → MQTT Broker → Subscribe → ESP32 → Relay → ไฟ
ESP32 → Publish → MQTT Broker → Subscribe → PHP → Database
```

#### 2. **HTTP REST API**
```
ข้อดี:
✅ ง่าย เข้าใจง่าย
✅ ไม่ต้องติดตั้ง Broker

ข้อเสีย:
❌ Latency สูงกว่า MQTT
❌ One-way (ต้อง poll)

การทำงาน:
PHP → HTTP POST → http://esp32-ip/api/light/on
ESP32 → HTTP GET → http://server/api/status
```

#### 3. **WebSocket**
```
ข้อดี:
✅ Real-time
✅ Two-way communication

ข้อเสีย:
❌ ซับซ้อนกว่า

การทำงาน:
PHP ↔ WebSocket Server ↔ ESP32
```

**คำแนะนำ:** ใช้ **MQTT** เพราะเหมาะสมที่สุดสำหรับ IoT

---

## 🔄 การเปลี่ยนแปลงที่ต้องทำ

### 1. **Database Schema (เพิ่มฟิลด์)**

```sql
-- เพิ่มคอลัมน์ในตาราง lights
ALTER TABLE lights 
ADD COLUMN device_type ENUM('virtual', 'esp32', 'sonoff', 'shelly') DEFAULT 'virtual',
ADD COLUMN mqtt_topic VARCHAR(255),
ADD COLUMN device_ip VARCHAR(45),
ADD COLUMN gpio_pin INT,
ADD COLUMN last_seen TIMESTAMP NULL,
ADD COLUMN is_online BOOLEAN DEFAULT FALSE;

-- ตัวอย่างข้อมูล
UPDATE lights 
SET device_type = 'esp32',
    mqtt_topic = 'home/living/light1',
    gpio_pin = 2
WHERE id = 1;
```

### 2. **MQTT Client Library (PHP)**

#### ติดตั้ง Mosquitto และ PHP MQTT Library

```bash
# ติดตั้ง Mosquitto MQTT Broker
sudo apt-get install mosquitto mosquitto-clients

# ติดตั้ง PHP MQTT Library
composer require php-mqtt/client
```

### 3. **สร้างไฟล์ MQTT Handler**

```php
// mqtt_handler.php
<?php
require_once 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MQTTHandler {
    private $mqtt;
    private $broker = 'localhost';  // หรือ IP ของ MQTT Broker
    private $port = 1883;
    
    public function __construct() {
        $this->mqtt = new MqttClient($this->broker, $this->port, 'smart-home-server');
    }
    
    public function connect() {
        $connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(60)
            ->setUseTls(false);
            
        $this->mqtt->connect($connectionSettings, true);
    }
    
    public function publishLightCommand($topic, $command) {
        // $command = 'ON' or 'OFF'
        $this->mqtt->publish($topic, $command, 0);
    }
    
    public function publishBrightness($topic, $brightness) {
        // $brightness = 0-100
        $payload = json_encode(['brightness' => $brightness]);
        $this->mqtt->publish($topic . '/brightness', $payload, 0);
    }
    
    public function subscribe($topic, $callback) {
        $this->mqtt->subscribe($topic, $callback, 0);
    }
    
    public function disconnect() {
        $this->mqtt->disconnect();
    }
}
?>
```

### 4. **แก้ไข API lights.php**

```php
// api/lights.php (เพิ่มส่วนนี้)
<?php
require_once __DIR__ . '/../mqtt_handler.php';

// ... (โค้ดเดิม)

// เมื่อมีการเปิด/ปิดไฟ
if ($action === 'toggle') {
    // ... (อัพเดท database)
    
    // ส่งคำสั่งไปยัง Hardware
    $lightQuery = $conn->query("SELECT mqtt_topic, device_type FROM lights WHERE id = $light_id");
    $lightData = $lightQuery->fetch_assoc();
    
    if ($lightData['device_type'] !== 'virtual') {
        $mqtt = new MQTTHandler();
        $mqtt->connect();
        
        $command = ($status === 'on') ? 'ON' : 'OFF';
        $mqtt->publishLightCommand($lightData['mqtt_topic'], $command);
        
        $mqtt->disconnect();
    }
    
    // ... (ส่งผลลัพธ์กลับ)
}
?>
```

### 5. **โค้ด ESP32 (Arduino IDE)**

```cpp
// ESP32_Smart_Light.ino
#include <WiFi.h>
#include <PubSubClient.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Configuration
const char* mqtt_server = "192.168.1.100";  // IP ของ MQTT Broker
const int mqtt_port = 1883;
const char* mqtt_topic = "home/living/light1";

// Relay Pin
const int RELAY_PIN = 2;  // GPIO2

WiFiClient espClient;
PubSubClient client(espClient);

void setup() {
  Serial.begin(115200);
  
  // Setup Relay Pin
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);
  
  // Connect WiFi
  setup_wifi();
  
  // Connect MQTT
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void setup_wifi() {
  Serial.print("Connecting to ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("");
  Serial.println("WiFi connected");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
}

void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  
  Serial.print("Message received on ");
  Serial.print(topic);
  Serial.print(": ");
  Serial.println(message);
  
  // Control Relay
  if (message == "ON") {
    digitalWrite(RELAY_PIN, HIGH);
    Serial.println("Light turned ON");
    
    // Report status back
    client.publish("home/living/light1/status", "ON");
  } 
  else if (message == "OFF") {
    digitalWrite(RELAY_PIN, LOW);
    Serial.println("Light turned OFF");
    
    // Report status back
    client.publish("home/living/light1/status", "OFF");
  }
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    
    String clientId = "ESP32-";
    clientId += String(random(0xffff), HEX);
    
    if (client.connect(clientId.c_str())) {
      Serial.println("connected");
      client.subscribe(mqtt_topic);
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
}
```

---

## 🔐 Security (ความปลอดภัย)

### 1. **MQTT Authentication**
```php
// ใช้ username/password สำหรับ MQTT
$connectionSettings = (new ConnectionSettings)
    ->setUsername('mqtt_user')
    ->setPassword('secure_password')
    ->setUseTls(true);  // ใช้ TLS/SSL
```

### 2. **API Token**
```php
// เพิ่ม API token สำหรับ ESP32
ALTER TABLE lights ADD COLUMN api_token VARCHAR(255);

// ตรวจสอบ token ก่อนรับคำสั่ง
if ($_POST['token'] !== $device['api_token']) {
    die('Unauthorized');
}
```

### 3. **Firewall Rules**
```bash
# อนุญาตเฉพาะ Local Network เข้า MQTT
sudo ufw allow from 192.168.1.0/24 to any port 1883
```

### 4. **HTTPS/SSL**
```
- ใช้ Let's Encrypt สำหรับ SSL certificate
- บังคับใช้ HTTPS บนเว็บเซิร์ฟเวอร์
```

### 5. **การป้องกันไฟฟ้า**
```
⚠️ สำคัญมาก:
- ใช้ MCB (เบรกเกอร์) แยกวงจร
- Ground ให้ถูกต้อง
- ไม่ควรทำเองถ้าไม่มีความรู้ไฟฟ้า
- ควรให้ช่างมืออาชีพติดตั้ง
```

---

## 📝 ขั้นตอนการพัฒนา (Step by Step)

### Phase 1: PoC (Proof of Concept) - 1-2 สัปดาห์
```
1. ซื้อ ESP32 + Relay Module (1 ชุด)
2. ติดตั้ง Arduino IDE
3. เขียนโค้ดควบคุม Relay ด้วย WiFi
4. ทดสอบเปิด/ปิด LED ก่อน (ไม่ใช้ไฟบ้าน)
5. เชื่อมต่อกับ PHP ผ่าน HTTP API
```

### Phase 2: MQTT Integration - 1 สัปดาห์
```
1. ติดตั้ง Mosquitto MQTT Broker
2. แก้ไข PHP ให้รองรับ MQTT
3. แก้ไข ESP32 ให้ใช้ MQTT
4. ทดสอบ end-to-end
```

### Phase 3: Multiple Devices - 1-2 สัปดาห์
```
1. เพิ่ม ESP32 อีก 2-3 ตัว
2. แยก topic ตามห้อง
3. ทดสอบควบคุมพร้อมกัน
4. ทดสอบ status feedback
```

### Phase 4: Real Installation - 2-4 สัปดาห์
```
1. ออกแบบวงจรไฟฟ้า
2. ซื้อ Sonoff/Shelly แทน Relay โดยตรง
3. ติดตั้งจริงโดยช่าง (ถ้าไม่มีความรู้)
4. ทดสอบความปลอดภัย
5. ใช้งานจริง
```

### Phase 5: Advanced Features - ต่อเนื่อง
```
1. เพิ่ม Sensor (Temperature, Motion)
2. Automation Rules
3. Voice Control (Alexa, Google Home)
4. Mobile App
5. Energy Monitoring
```

---

## 💰 ค่าใช้จ่ายโดยประมาณ

### งบน้อย (~3,000-5,000 บาท)
```
- ESP32 x 3 ตัว:        900 บาท
- Relay Module x 3:     900 บาท
- Power Supply x 3:     300 บาท
- อุปกรณ์เสริม:         500 บาท
- กล่อง/เคส:           300 บาท
--------------------------------
รวม:                  ~3,000 บาท
```

### งบปานกลาง (~8,000-12,000 บาท)
```
- Raspberry Pi 4:     3,000 บาท
- Sonoff Switch x 10: 2,500 บาท
- Power Supply:         500 บาท
- อุปกรณ์เสริม:       1,000 บาท
- ค่าติดตั้ง:         2,000 บาท
--------------------------------
รวม:                ~10,000 บาท
```

### งบสูง - ระบบจริงจัง (~20,000-50,000 บาท)
```
- Raspberry Pi + UPS:        5,000 บาท
- Shelly Relay x 15:        9,000 บาท
- Smart Sensor x 10:        3,000 บาท
- Professional Installation: 10,000 บาท
- Touch Panel/Display:       5,000 บาท
- Network Equipment:         3,000 บาท
-----------------------------------------
รวม:                      ~35,000 บาท
```

---

## 📚 แหล่งเรียนรู้เพิ่มเติม

### ภาษาไทย
- **YouTube:** 
  - "สอนทำ Smart Home ด้วย ESP32"
  - "NodeMCU ESP8266 Tutorial"
  
- **Facebook Groups:**
  - IoT Thailand
  - Arduino Thailand
  - Smart Home DIY Thailand

### ภาษาอังกฤษ
- **Home Assistant:** https://www.home-assistant.io/
- **ESPHome:** https://esphome.io/
- **Tasmota:** https://tasmota.github.io/
- **MQTT.org:** https://mqtt.org/

### คอร์สออนไลน์
- Udemy: "Complete ESP32 Smart Home"
- YouTube: "Andreas Spiess" channel
- Instructables.com

---

## ⚠️ ข้อควรระวัง

### 1. **ความปลอดภัยไฟฟ้า**
```
❌ อย่าทำถ้าไม่มีความรู้ด้านไฟฟ้า
✅ ใช้ Sonoff/Shelly ที่ได้รับการรับรอง
✅ ติดตั้งโดยช่างมืออาชีพ
✅ ใช้ MCB (เบรกเกอร์) ที่เหมาะสม
✅ Test กับ LED ก่อน ไม่ใช้ไฟบ้านทันที
```

### 2. **Network Security**
```
✅ แยก IoT Devices ออกเป็น VLAN
✅ ใช้ Strong Password
✅ อัพเดท Firmware สม่ำเสมอ
✅ ไม่เปิด Port ออก Internet โดยตรง
```

### 3. **Backup & Redundancy**
```
✅ มีสวิตช์ปกติสำรองอยู่เสมอ
✅ Backup configuration ESP32
✅ เตรียม manual control ไว้
```

---

## 🎯 สรุป

### ระยะเวลา
- **PoC:** 1-2 สัปดาห์
- **Full System:** 1-2 เดือน
- **Professional Installation:** 1-3 เดือน

### งบประมาณ
- **เริ่มต้น:** 3,000-5,000 บาท
- **ระบบจริง:** 10,000-50,000 บาท

### ความยาก
- **PoC (LED):** ⭐⭐ (ง่าย - ปานกลาง)
- **Software Integration:** ⭐⭐⭐ (ปานกลาง)
- **Real Installation:** ⭐⭐⭐⭐ (ยาก - ต้องมีความรู้ไฟฟ้า)

### คำแนะนำ
1. เริ่มจาก **ESP32 + LED** ก่อน (ไม่ใช่ไฟบ้าน)
2. ใช้ **MQTT** สำหรับการสื่อสาร
3. ซื้อ **Sonoff/Shelly** สำเร็จรูปแทนการทำเอง
4. **จ้างช่าง** ติดตั้งจริงถ้าไม่มีความรู้ไฟฟ้า
5. ทดสอบ **ความปลอดภัย** ให้มากที่สุด

---

**หมายเหตุ:** ระบบ Software ที่มีอยู่แล้วพร้อมใช้งาน 80-90% เพียงแค่เพิ่ม MQTT integration และ Hardware เท่านั้น!

**เวอร์ชัน:** 1.0  
**อัพเดทล่าสุด:** มกราคม 2026