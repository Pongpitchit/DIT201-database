# คู่มือการติดตั้งและเริ่มใช้งานระบบจัดการเช่ารถ

## ข้อกำหนดเบื้องต้น
- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Web Server (Apache/Nginx) หรือใช้ XAMPP/WAMP/Laragon

## ขั้นตอนการติดตั้ง

### 1. ตั้งค่า Web Server

#### วิธีที่ 1: ใช้ XAMPP (แนะนำสำหรับ Windows)
1. ดาวน์โหลดและติดตั้ง [XAMPP](https://www.apachefriends.org/)
2. เปิด XAMPP Control Panel
3. Start **Apache** และ **MySQL**

#### วิธีที่ 2: ใช้ Laragon (Windows)
1. ดาวน์โหลดและติดตั้ง [Laragon](https://laragon.org/)
2. เปิด Laragon และ Start All

### 2. ตั้งค่าฐานข้อมูล

#### วิธีที่ 1: ใช้ phpMyAdmin
1. เปิดเบราว์เซอร์ไปที่ `http://localhost/phpMyAdmin`
2. คลิก "New" เพื่อสร้างฐานข้อมูลใหม่
3. ตั้งชื่อฐานข้อมูล: `dit201_dashboard`
4. เลือก Collation: `utf8mb4_unicode_ci`
5. คลิก "Import" และเลือกไฟล์ `database.sql`
6. คลิก "Go" เพื่อรัน SQL

#### วิธีที่ 2: ใช้ Command Line
```bash
mysql -u root -p < database.sql
```

#### วิธีที่ 3: รัน SQL โดยตรงใน phpMyAdmin
1. เปิด phpMyAdmin
2. คลิก "SQL" tab
3. Copy เนื้อหาทั้งหมดจาก `database.sql`
4. Paste และคลิก "Go"

### 3. ตั้งค่า config.php

เปิดไฟล์ `config.php` และตรวจสอบ/แก้ไขการตั้งค่าตามเครื่องของคุณ:

```php
$host = 'localhost';        // เปลี่ยนถ้าจำเป็น
$db   = 'dit201_dashboard'; // ชื่อฐานข้อมูล
$user = 'root';             // MySQL username (เปลี่ยนถ้าจำเป็น)
$pass = '';                 // MySQL password (เปลี่ยนถ้าจำเป็น)
```

### 4. วางไฟล์โปรเจ็กต์

#### สำหรับ XAMPP:
- วางโฟลเดอร์โปรเจ็กต์ใน `C:\xampp\htdocs\`
- ตัวอย่าง: `C:\xampp\htdocs\DIT201-database\`

#### สำหรับ Laragon:
- วางโฟลเดอร์โปรเจ็กต์ใน `C:\laragon\www\`
- ตัวอย่าง: `C:\laragon\www\DIT201-database\`

### 5. เริ่มใช้งาน

1. เปิดเบราว์เซอร์
2. ไปที่: `http://localhost/DIT201-database/home.php`
   หรือ `http://localhost/DIT201-database/` (ถ้า index.php redirect)

3. **เข้าสู่ระบบ:**
   - Username: `admin` หรือ `staff`
   - Password: `admin123`

## โครงสร้างไฟล์

```
DIT201-database/
├── auth.php              # ฟังก์ชัน authentication
├── cars.php              # จัดการรถ (CRUD)
├── config.php            # การตั้งค่าฐานข้อมูล
├── customers.php         # จัดการลูกค้า (CRUD)
├── dashboard.php         # Dashboard
├── database.sql          # SQL schema และข้อมูลตัวอย่าง
├── header.php            # Header/Navigation
├── home.php              # หน้าแรก
├── login.php             # หน้า Login
├── login_process.php     # ประมวลผล Login
├── logout.php            # Logout
├── rentals.php           # จัดการการเช่า (CRUD)
├── reports.php           # รายงาน
└── styles.css            # CSS Styling
```

## ฟีเจอร์หลัก

### Dashboard
- สรุปสถานะรถ (ว่าง, เช่าแล้ว, ซ่อมบำรุง)
- ยอดการเช่ารายเดือน
- รายการเช่าล่าสุด

### จัดการรถ (Cars)
- เพิ่ม/แก้ไข/ลบข้อมูลรถ
- กำหนดสถานะ: available, rented, maintenance
- กำหนดราคาเช่าต่อวัน

### จัดการลูกค้า (Customers)
- เพิ่ม/แก้ไข/ลบข้อมูลลูกค้า
- บันทึก: ชื่อ, เบอร์, บัตรประชาชน, ใบขับขี่

### จัดการการเช่า (Rentals)
- สร้างการเช่าใหม่
- กำหนดวันที่เริ่ม-คืน
- คำนวณราคาอัตโนมัติ
- คืนรถ (เปลี่ยนสถานะอัตโนมัติ)

### รายงาน (Reports)
- ประวัติการเช่าทั้งหมด
- สถิติรายเดือน
- กรองตามเดือนและสถานะ

## Business Rules

1. ✅ รถแต่ละคันมีสถานะ: available, rented, maintenance
2. ✅ ลูกค้าต้องมีข้อมูลพื้นฐานก่อนเช่า
3. ✅ การเช่าต้องกำหนด: วันที่เริ่ม, วันที่คืน, ราคา/วัน
4. ✅ รถ 1 คันเช่าได้ครั้งละ 1 รายการเท่านั้น
5. ✅ เมื่อคืนรถ → สถานะเปลี่ยนเป็น "available"
6. ✅ การลบการเช่า → คืนสถานะรถอัตโนมัติ

## การแก้ไขปัญหา

### ไม่สามารถเชื่อมต่อฐานข้อมูล
- ตรวจสอบว่า MySQL ทำงานอยู่
- ตรวจสอบ username/password ใน config.php
- ตรวจสอบว่าฐานข้อมูลถูกสร้างแล้ว

### หน้าเว็บไม่แสดงผล
- ตรวจสอบว่า Apache ทำงานอยู่
- ตรวจสอบ path ของไฟล์ในเบราว์เซอร์
- ตรวจสอบ PHP error log

### Session ไม่ทำงาน
- ตรวจสอบว่า session_start() ถูกเรียกใช้
- ตรวจสอบสิทธิ์การเขียนไฟล์ในโฟลเดอร์

## ข้อมูลผู้ใช้เริ่มต้น

- **Admin:** username: `admin`, password: `admin123`
- **Staff:** username: `staff`, password: `admin123`

## ข้อมูลตัวอย่าง

ระบบมาพร้อมข้อมูลตัวอย่าง:
- รถ 4 คัน
- ลูกค้า 2 คน

คุณสามารถลบหรือแก้ไขข้อมูลเหล่านี้ได้ตามต้องการ

