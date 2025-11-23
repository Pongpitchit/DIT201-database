-- แก้ไขข้อผิดพลาด: Column not found: booking_id
-- คัดลอกและรัน SQL นี้ใน phpMyAdmin หรือ MySQL Command Line

USE dit201_dashboard;

-- 1. สร้างตาราง bookings (ถ้ายังไม่มี)
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  car_id INT NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  customer_id_card VARCHAR(20) NOT NULL,
  customer_license VARCHAR(50),
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE RESTRICT
);

-- 2. เพิ่มคอลัมน์ booking_id ในตาราง rentals
-- ถ้าเกิด error "Duplicate column name" แสดงว่ามีคอลัมน์นี้อยู่แล้ว ให้ข้ามไปขั้นตอนที่ 3
ALTER TABLE rentals 
ADD COLUMN booking_id INT NULL AFTER customer_id;

-- 3. เพิ่ม Foreign Key
-- ถ้าเกิด error "Duplicate key name" แสดงว่ามี Foreign Key นี้อยู่แล้ว
ALTER TABLE rentals 
ADD FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL;

