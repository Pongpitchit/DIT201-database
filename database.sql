CREATE DATABASE IF NOT EXISTS dit201_dashboard
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dit201_dashboard;

-- ตารางผู้ใช้ (พนักงาน/แอดมิน)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'staff') DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- เพิ่มผู้ใช้เริ่มต้น (password: admin123)
-- หมายเหตุ: login_process.php รองรับทั้ง password แบบ plain text (admin123) และ bcrypt hash
INSERT INTO users (username, password, role) VALUES
('admin', 'admin123', 'admin'),
('staff', 'admin123', 'staff')
ON DUPLICATE KEY UPDATE username = username;

-- ตารางรถ
CREATE TABLE IF NOT EXISTS cars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  license_plate VARCHAR(20) NOT NULL UNIQUE,
  brand VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  year INT NOT NULL,
  color VARCHAR(30),
  status ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
  price_per_day DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ตารางลูกค้า
CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  id_card VARCHAR(20) NOT NULL UNIQUE,
  license_number VARCHAR(50),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ตารางการจอง (ลูกค้าจองเอง)
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

-- ตารางการเช่า
CREATE TABLE IF NOT EXISTS rentals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  car_id INT NOT NULL,
  customer_id INT NOT NULL,
  booking_id INT NULL,
  start_date DATE NOT NULL,
  expected_return_date DATE NOT NULL,
  actual_return_date DATE NULL,
  price_per_day DECIMAL(10, 2) NOT NULL,
  total_days INT NOT NULL,
  total_amount DECIMAL(10, 2) NOT NULL,
  status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
  notes TEXT,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE RESTRICT,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- เพิ่มข้อมูลตัวอย่าง
INSERT INTO cars (license_plate, brand, model, year, color, status, price_per_day) VALUES
('กข-1234', 'Toyota', 'Camry', 2020, 'ขาว', 'available', 1500.00),
('กข-5678', 'Honda', 'Civic', 2021, 'ดำ', 'available', 1200.00),
('กข-9012', 'Mazda', 'CX-5', 2022, 'แดง', 'available', 2000.00),
('กข-3456', 'Nissan', 'Almera', 2019, 'เงิน', 'maintenance', 1000.00);

INSERT INTO customers (name, phone, id_card, license_number, address) VALUES
('สมชาย ใจดี', '0812345678', '1234567890123', 'DL12345', '123 ถนนสุขุมวิท กรุงเทพฯ'),
('สมหญิง รักดี', '0823456789', '2345678901234', 'DL23456', '456 ถนนพหลโยธิน กรุงเทพฯ');
