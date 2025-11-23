-- Migration: เพิ่มตาราง bookings และคอลัมน์ booking_id ในตาราง rentals
-- รันไฟล์นี้ถ้าฐานข้อมูลมีอยู่แล้วและต้องการเพิ่มฟีเจอร์การจอง

USE dit201_dashboard;

-- สร้างตารางการจอง (ถ้ายังไม่มี)
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

-- เพิ่มคอลัมน์ booking_id ในตาราง rentals (ถ้ายังไม่มี)
-- ตรวจสอบว่าคอลัมน์มีอยู่แล้วหรือไม่ก่อนเพิ่ม
SET @dbname = DATABASE();
SET @tablename = 'rentals';
SET @columnname = 'booking_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1', -- ถ้ามีแล้ว ไม่ต้องทำอะไร
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL AFTER customer_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- เพิ่ม Foreign Key สำหรับ booking_id (ถ้ายังไม่มี)
SET @fk_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
    AND REFERENCED_TABLE_NAME = 'bookings'
);

SET @preparedStatement = (SELECT IF(
  @fk_exists > 0,
  'SELECT 1', -- ถ้ามีแล้ว ไม่ต้องทำอะไร
  CONCAT('ALTER TABLE ', @tablename, ' ADD FOREIGN KEY (', @columnname, ') REFERENCES bookings(id) ON DELETE SET NULL')
));
PREPARE alterFKIfNotExists FROM @preparedStatement;
EXECUTE alterFKIfNotExists;
DEALLOCATE PREPARE alterFKIfNotExists;
