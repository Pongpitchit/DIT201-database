-- Mock Data: ข้อมูลตัวอย่างสำหรับระบบเช่ารถ
-- รถ 10 คัน, ลูกค้า 20 คน, การเช่า 3-4 เดือน, การซ่อมบำรุง 3 ครั้งต่อเดือน

USE dit201_dashboard;

-- ลบข้อมูลเดิม (ถ้าต้องการ)
-- DELETE FROM rentals;
-- DELETE FROM bookings;
-- DELETE FROM customers;
-- DELETE FROM cars;

-- เพิ่มรถ 10 คัน
INSERT INTO cars (license_plate, brand, model, year, color, status, price_per_day) VALUES
('กข-1001', 'Toyota', 'Camry', 2020, 'ขาว', 'available', 1500.00),
('กข-1002', 'Honda', 'Civic', 2021, 'ดำ', 'available', 1200.00),
('กข-1003', 'Mazda', 'CX-5', 2022, 'แดง', 'available', 2000.00),
('กข-1004', 'Nissan', 'Almera', 2019, 'เงิน', 'available', 1000.00),
('กข-1005', 'Toyota', 'Vios', 2020, 'ฟ้า', 'available', 1100.00),
('กข-1006', 'Honda', 'Accord', 2021, 'เทา', 'available', 1800.00),
('กข-1007', 'Mazda', 'Mazda3', 2022, 'ดำ', 'available', 1300.00),
('กข-1008', 'Nissan', 'Terra', 2021, 'ขาว', 'available', 2200.00),
('กข-1009', 'Toyota', 'Fortuner', 2020, 'ดำ', 'available', 2500.00),
('กข-1010', 'Honda', 'CR-V', 2022, 'แดง', 'available', 2100.00)
ON DUPLICATE KEY UPDATE license_plate = license_plate;

-- เพิ่มลูกค้า 20 คน
INSERT INTO customers (name, phone, id_card, license_number, address) VALUES
('สมชาย ใจดี', '0812345678', '1234567890123', 'DL12345', '123 ถนนสุขุมวิท กรุงเทพฯ'),
('สมหญิง รักดี', '0823456789', '2345678901234', 'DL23456', '456 ถนนพหลโยธิน กรุงเทพฯ'),
('วิชัย สุขใจ', '0834567890', '3456789012345', 'DL34567', '789 ถนนรัชดาภิเษก กรุงเทพฯ'),
('มาลี ดีงาม', '0845678901', '4567890123456', 'DL45678', '321 ถนนสีลม กรุงเทพฯ'),
('ประเสริฐ มั่นคง', '0856789012', '5678901234567', 'DL56789', '654 ถนนเพชรบุรี กรุงเทพฯ'),
('วิไล ใสสะอาด', '0867890123', '6789012345678', 'DL67890', '987 ถนนลาดพร้าว กรุงเทพฯ'),
('สมศักดิ์ เก่งกาจ', '0878901234', '7890123456789', 'DL78901', '147 ถนนบางนา กรุงเทพฯ'),
('นภัสวรรณ สวยงาม', '0889012345', '8901234567890', 'DL89012', '258 ถนนอโศก กรุงเทพฯ'),
('ธีรพงษ์ กล้าหาญ', '0890123456', '9012345678901', 'DL90123', '369 ถนนสาทร กรุงเทพฯ'),
('กัญญา รักดี', '0901234567', '0123456789012', 'DL01234', '741 ถนนราชดำริ กรุงเทพฯ'),
('อรรถพล มั่นใจ', '0912345678', '1122334455667', 'DL11223', '852 ถนนสุขุมวิท กรุงเทพฯ'),
('ปิยะดา ใจดี', '0923456789', '2233445566778', 'DL22334', '963 ถนนพหลโยธิน กรุงเทพฯ'),
('ณัฐพล เก่งมาก', '0934567890', '3344556677889', 'DL33445', '159 ถนนรัชดาภิเษก กรุงเทพฯ'),
('สุชาดา สวยใส', '0945678901', '4455667788990', 'DL44556', '357 ถนนสีลม กรุงเทพฯ'),
('วรพล ดีมาก', '0956789012', '5566778899001', 'DL55667', '468 ถนนเพชรบุรี กรุงเทพฯ'),
('อรทัย ใจกว้าง', '0967890123', '6677889900112', 'DL66778', '579 ถนนลาดพร้าว กรุงเทพฯ'),
('ชาญชัย เก่งจริง', '0978901234', '7788990011223', 'DL77889', '680 ถนนบางนา กรุงเทพฯ'),
('ศิริพร สวยมาก', '0989012345', '8899001122334', 'DL88990', '791 ถนนอโศก กรุงเทพฯ'),
('ธนพล มั่นคง', '0990123456', '9900112233445', 'DL99001', '802 ถนนสาทร กรุงเทพฯ'),
('ปาริฉัตร ดีใจ', '0801234567', '0011223344556', 'DL00112', '913 ถนนราชดำริ กรุงเทพฯ')
ON DUPLICATE KEY UPDATE id_card = id_card;

-- สร้างข้อมูลการเช่า 3-4 เดือน (ประมาณ 60-90 วัน)ฟ
-- ใช้ข้อมูลจาก 3 เดือนที่แล้วถึงวันนี้
SET @start_date = DATE_SUB(CURDATE(), INTERVAL 3 MONTH);
SET @end_date = CURDATE();

-- ฟังก์ชันสุ่มวันที่
-- สร้างการเช่าประมาณ 40-50 รายการในช่วง 3 เดือน

-- เดือนที่ 1 (3 เดือนที่แล้ว) - ใช้วิธี INSERT แบบระบุค่า (ไม่ทับซ้อน)
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, created_by) VALUES
(1, 1, DATE_ADD(@start_date, INTERVAL 2 DAY), DATE_ADD(@start_date, INTERVAL 5 DAY), DATE_ADD(@start_date, INTERVAL 5 DAY), 1500, 3, 4500, 'completed', 1),
(2, 2, DATE_ADD(@start_date, INTERVAL 3 DAY), DATE_ADD(@start_date, INTERVAL 6 DAY), DATE_ADD(@start_date, INTERVAL 6 DAY), 1200, 3, 3600, 'completed', 1),
(3, 3, DATE_ADD(@start_date, INTERVAL 7 DAY), DATE_ADD(@start_date, INTERVAL 11 DAY), DATE_ADD(@start_date, INTERVAL 11 DAY), 2000, 4, 8000, 'completed', 1),
(4, 4, DATE_ADD(@start_date, INTERVAL 8 DAY), DATE_ADD(@start_date, INTERVAL 13 DAY), DATE_ADD(@start_date, INTERVAL 13 DAY), 1000, 5, 5000, 'completed', 1),
(5, 5, DATE_ADD(@start_date, INTERVAL 14 DAY), DATE_ADD(@start_date, INTERVAL 20 DAY), DATE_ADD(@start_date, INTERVAL 20 DAY), 1100, 6, 6600, 'completed', 1),
(6, 6, DATE_ADD(@start_date, INTERVAL 15 DAY), DATE_ADD(@start_date, INTERVAL 20 DAY), DATE_ADD(@start_date, INTERVAL 20 DAY), 1800, 5, 9000, 'completed', 1),
(7, 7, DATE_ADD(@start_date, INTERVAL 21 DAY), DATE_ADD(@start_date, INTERVAL 25 DAY), DATE_ADD(@start_date, INTERVAL 25 DAY), 1300, 4, 5200, 'completed', 1),
(8, 8, DATE_ADD(@start_date, INTERVAL 22 DAY), DATE_ADD(@start_date, INTERVAL 27 DAY), DATE_ADD(@start_date, INTERVAL 27 DAY), 2200, 5, 11000, 'completed', 1),
(9, 9, DATE_ADD(@start_date, INTERVAL 26 DAY), DATE_ADD(@start_date, INTERVAL 30 DAY), NULL, 2500, 4, 10000, 'active', 1),
(10, 10, DATE_ADD(@start_date, INTERVAL 28 DAY), DATE_ADD(@start_date, INTERVAL 30 DAY), DATE_ADD(@start_date, INTERVAL 30 DAY), 2100, 2, 4200, 'completed', 1);

-- เดือนที่ 2 (2 เดือนที่แล้ว) - ไม่ทับซ้อนกับเดือนที่ 1
SET @month2_start = DATE_ADD(@start_date, INTERVAL 1 MONTH);
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, created_by) VALUES
(1, 11, DATE_ADD(@month2_start, INTERVAL 1 DAY), DATE_ADD(@month2_start, INTERVAL 4 DAY), DATE_ADD(@month2_start, INTERVAL 4 DAY), 1500, 3, 4500, 'completed', 1),
(2, 12, DATE_ADD(@month2_start, INTERVAL 2 DAY), DATE_ADD(@month2_start, INTERVAL 6 DAY), DATE_ADD(@month2_start, INTERVAL 6 DAY), 1200, 4, 4800, 'completed', 1),
(3, 13, DATE_ADD(@month2_start, INTERVAL 5 DAY), DATE_ADD(@month2_start, INTERVAL 10 DAY), DATE_ADD(@month2_start, INTERVAL 10 DAY), 2000, 5, 10000, 'completed', 1),
(4, 14, DATE_ADD(@month2_start, INTERVAL 6 DAY), DATE_ADD(@month2_start, INTERVAL 11 DAY), DATE_ADD(@month2_start, INTERVAL 11 DAY), 1000, 5, 5000, 'completed', 1),
(5, 15, DATE_ADD(@month2_start, INTERVAL 12 DAY), DATE_ADD(@month2_start, INTERVAL 17 DAY), DATE_ADD(@month2_start, INTERVAL 17 DAY), 1100, 5, 5500, 'completed', 1),
(6, 16, DATE_ADD(@month2_start, INTERVAL 13 DAY), DATE_ADD(@month2_start, INTERVAL 18 DAY), DATE_ADD(@month2_start, INTERVAL 18 DAY), 1800, 5, 9000, 'completed', 1),
(7, 17, DATE_ADD(@month2_start, INTERVAL 19 DAY), DATE_ADD(@month2_start, INTERVAL 24 DAY), DATE_ADD(@month2_start, INTERVAL 24 DAY), 1300, 5, 6500, 'completed', 1),
(8, 18, DATE_ADD(@month2_start, INTERVAL 20 DAY), DATE_ADD(@month2_start, INTERVAL 25 DAY), DATE_ADD(@month2_start, INTERVAL 25 DAY), 2200, 5, 11000, 'completed', 1),
(9, 19, DATE_ADD(@month2_start, INTERVAL 26 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), NULL, 2500, 4, 10000, 'active', 1),
(10, 20, DATE_ADD(@month2_start, INTERVAL 27 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), 2100, 3, 6300, 'completed', 1),
(1, 1, DATE_ADD(@month2_start, INTERVAL 28 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), 1500, 2, 3000, 'completed', 1),
(2, 2, DATE_ADD(@month2_start, INTERVAL 29 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), DATE_ADD(@month2_start, INTERVAL 30 DAY), 1200, 1, 1200, 'completed', 1);

-- เดือนที่ 3 (1 เดือนที่แล้ว) - ไม่ทับซ้อน
SET @month3_start = DATE_ADD(@start_date, INTERVAL 2 MONTH);
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, created_by) VALUES
(3, 4, DATE_ADD(@month3_start, INTERVAL 1 DAY), DATE_ADD(@month3_start, INTERVAL 5 DAY), DATE_ADD(@month3_start, INTERVAL 5 DAY), 2000, 4, 8000, 'completed', 1),
(4, 5, DATE_ADD(@month3_start, INTERVAL 2 DAY), DATE_ADD(@month3_start, INTERVAL 7 DAY), DATE_ADD(@month3_start, INTERVAL 7 DAY), 1000, 5, 5000, 'completed', 1),
(5, 6, DATE_ADD(@month3_start, INTERVAL 6 DAY), DATE_ADD(@month3_start, INTERVAL 11 DAY), DATE_ADD(@month3_start, INTERVAL 11 DAY), 1100, 5, 5500, 'completed', 1),
(6, 7, DATE_ADD(@month3_start, INTERVAL 8 DAY), DATE_ADD(@month3_start, INTERVAL 13 DAY), DATE_ADD(@month3_start, INTERVAL 13 DAY), 1800, 5, 9000, 'completed', 1),
(7, 8, DATE_ADD(@month3_start, INTERVAL 9 DAY), DATE_ADD(@month3_start, INTERVAL 14 DAY), DATE_ADD(@month3_start, INTERVAL 14 DAY), 1300, 5, 6500, 'completed', 1),
(8, 9, DATE_ADD(@month3_start, INTERVAL 15 DAY), DATE_ADD(@month3_start, INTERVAL 20 DAY), NULL, 2200, 5, 11000, 'active', 1),
(9, 10, DATE_ADD(@month3_start, INTERVAL 16 DAY), DATE_ADD(@month3_start, INTERVAL 21 DAY), DATE_ADD(@month3_start, INTERVAL 21 DAY), 2500, 5, 12500, 'completed', 1),
(10, 11, DATE_ADD(@month3_start, INTERVAL 17 DAY), DATE_ADD(@month3_start, INTERVAL 22 DAY), DATE_ADD(@month3_start, INTERVAL 22 DAY), 2100, 5, 10500, 'completed', 1),
(1, 12, DATE_ADD(@month3_start, INTERVAL 18 DAY), DATE_ADD(@month3_start, INTERVAL 23 DAY), DATE_ADD(@month3_start, INTERVAL 23 DAY), 1500, 5, 7500, 'completed', 1),
(2, 13, DATE_ADD(@month3_start, INTERVAL 19 DAY), DATE_ADD(@month3_start, INTERVAL 24 DAY), DATE_ADD(@month3_start, INTERVAL 24 DAY), 1200, 5, 6000, 'completed', 1),
(3, 14, DATE_ADD(@month3_start, INTERVAL 25 DAY), DATE_ADD(@month3_start, INTERVAL 30 DAY), DATE_ADD(@month3_start, INTERVAL 30 DAY), 2000, 5, 10000, 'completed', 1),
(4, 15, DATE_ADD(@month3_start, INTERVAL 26 DAY), DATE_ADD(@month3_start, INTERVAL 30 DAY), NULL, 1000, 4, 4000, 'active', 1);

-- เดือนที่ 4 (เดือนนี้)
SET @month4_start = DATE_ADD(@start_date, INTERVAL 3 MONTH);
SET @today = CURDATE();
SET @days_passed = DAY(@today);
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, created_by) VALUES
(6, 16, DATE_ADD(@month4_start, INTERVAL 1 DAY), DATE_ADD(@month4_start, INTERVAL 5 DAY), 
    CASE WHEN @days_passed >= 5 THEN DATE_ADD(@month4_start, INTERVAL 5 DAY) ELSE NULL END, 1800, 4, 7200, 
    CASE WHEN @days_passed >= 5 THEN 'completed' ELSE 'active' END, 1),
(7, 17, DATE_ADD(@month4_start, INTERVAL 3 DAY), DATE_ADD(@month4_start, INTERVAL 7 DAY), 
    CASE WHEN @days_passed >= 7 THEN DATE_ADD(@month4_start, INTERVAL 7 DAY) ELSE NULL END, 1300, 4, 5200, 
    CASE WHEN @days_passed >= 7 THEN 'completed' ELSE 'active' END, 1),
(8, 18, DATE_ADD(@month4_start, INTERVAL 5 DAY), DATE_ADD(@month4_start, INTERVAL 10 DAY), 
    CASE WHEN @days_passed >= 10 THEN DATE_ADD(@month4_start, INTERVAL 10 DAY) ELSE NULL END, 2200, 5, 11000, 
    CASE WHEN @days_passed >= 10 THEN 'completed' ELSE 'active' END, 1),
(9, 19, DATE_ADD(@month4_start, INTERVAL 7 DAY), DATE_ADD(@month4_start, INTERVAL 12 DAY), NULL, 2500, 5, 12500, 'active', 1),
(10, 20, DATE_ADD(@month4_start, INTERVAL 9 DAY), DATE_ADD(@month4_start, INTERVAL 14 DAY), 
    CASE WHEN @days_passed >= 14 THEN DATE_ADD(@month4_start, INTERVAL 14 DAY) ELSE NULL END, 2100, 5, 10500, 
    CASE WHEN @days_passed >= 14 THEN 'completed' ELSE 'active' END, 1);

-- อัปเดตสถานะรถตามการเช่า
UPDATE cars c
SET status = 'rented'
WHERE c.id IN (
    SELECT DISTINCT car_id 
    FROM rentals 
    WHERE status = 'active'
);

-- สร้างข้อมูลการซ่อมบำรุง 3 ครั้งต่อเดือน (รวม 12 ครั้งใน 3-4 เดือน)
-- หลังซ่อมแล้วให้สถานะกลับเป็น available

-- เดือนที่ 1
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, notes, created_by) VALUES
(1, 1, DATE_ADD(@start_date, INTERVAL 5 DAY), DATE_ADD(@start_date, INTERVAL 8 DAY), DATE_ADD(@start_date, INTERVAL 8 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1),
(4, 1, DATE_ADD(@start_date, INTERVAL 12 DAY), DATE_ADD(@start_date, INTERVAL 15 DAY), DATE_ADD(@start_date, INTERVAL 15 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1),
(7, 1, DATE_ADD(@start_date, INTERVAL 20 DAY), DATE_ADD(@start_date, INTERVAL 23 DAY), DATE_ADD(@start_date, INTERVAL 23 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1);

-- เดือนที่ 2
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, notes, created_by) VALUES
(2, 1, DATE_ADD(@month2_start, INTERVAL 10 DAY), DATE_ADD(@month2_start, INTERVAL 13 DAY), DATE_ADD(@month2_start, INTERVAL 13 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1),
(5, 1, DATE_ADD(@month2_start, INTERVAL 18 DAY), DATE_ADD(@month2_start, INTERVAL 21 DAY), DATE_ADD(@month2_start, INTERVAL 21 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1),
(8, 1, DATE_ADD(@month2_start, INTERVAL 25 DAY), DATE_ADD(@month2_start, INTERVAL 28 DAY), DATE_ADD(@month2_start, INTERVAL 28 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1);

-- เดือนที่ 3
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, notes, created_by) VALUES
(3, 1, DATE_ADD(@month3_start, INTERVAL 7 DAY), DATE_ADD(@month3_start, INTERVAL 10 DAY), DATE_ADD(@month3_start, INTERVAL 10 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1),
(6, 1, DATE_ADD(@month3_start, INTERVAL 15 DAY), DATE_ADD(@month3_start, INTERVAL 18 DAY), DATE_ADD(@month3_start, INTERVAL 18 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1),
(9, 1, DATE_ADD(@month3_start, INTERVAL 22 DAY), DATE_ADD(@month3_start, INTERVAL 25 DAY), DATE_ADD(@month3_start, INTERVAL 25 DAY), 0, 3, 0, 'completed', 'ซ่อมบำรุง', 1);

-- เดือนที่ 4
INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, actual_return_date, 
    price_per_day, total_days, total_amount, status, notes, created_by) VALUES
(1, 1, DATE_ADD(@month4_start, INTERVAL 5 DAY), DATE_ADD(@month4_start, INTERVAL 8 DAY), 
    CASE WHEN @days_passed >= 8 THEN DATE_ADD(@month4_start, INTERVAL 8 DAY) ELSE NULL END, 0, 3, 0, 
    CASE WHEN @days_passed >= 8 THEN 'completed' ELSE 'active' END, 'ซ่อมบำรุง', 1),
(4, 1, DATE_ADD(@month4_start, INTERVAL 12 DAY), DATE_ADD(@month4_start, INTERVAL 15 DAY), 
    CASE WHEN @days_passed >= 15 THEN DATE_ADD(@month4_start, INTERVAL 15 DAY) ELSE NULL END, 0, 3, 0, 
    CASE WHEN @days_passed >= 15 THEN 'completed' ELSE 'active' END, 'ซ่อมบำรุง', 1),
(10, 1, DATE_ADD(@month4_start, INTERVAL 18 DAY), DATE_ADD(@month4_start, INTERVAL 21 DAY), 
    CASE WHEN @days_passed >= 21 THEN DATE_ADD(@month4_start, INTERVAL 21 DAY) ELSE NULL END, 0, 3, 0, 
    CASE WHEN @days_passed >= 21 THEN 'completed' ELSE 'active' END, 'ซ่อมบำรุง', 1);

-- อัปเดตสถานะรถที่ซ่อมบำรุงแล้วให้กลับเป็น available
UPDATE cars c
SET status = 'available'
WHERE c.id IN (
    SELECT DISTINCT car_id 
    FROM rentals 
    WHERE notes = 'ซ่อมบำรุง' 
    AND status = 'completed'
    AND actual_return_date IS NOT NULL
    AND actual_return_date < CURDATE()
)
AND c.status = 'maintenance';

-- สร้างข้อมูลการจอง (bookings) บางส่วน
INSERT INTO bookings (car_id, customer_name, customer_phone, customer_id_card, customer_license, 
    start_date, end_date, status, notes) VALUES
(1, 'ลูกค้าจอง 1', '0811111111', '1111111111111', 'DL11111', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 'pending', 'จองออนไลน์'),
(2, 'ลูกค้าจอง 2', '0822222222', '2222222222222', 'DL22222', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pending', 'จองออนไลน์'),
(3, 'ลูกค้าจอง 3', '0833333333', '3333333333333', 'DL33333', DATE_ADD(CURDATE(), INTERVAL 12 DAY), DATE_ADD(CURDATE(), INTERVAL 17 DAY), 'confirmed', 'จองออนไลน์'),
(4, 'ลูกค้าจอง 4', '0844444444', '4444444444444', 'DL44444', DATE_ADD(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'pending', 'จองออนไลน์'),
(5, 'ลูกค้าจอง 5', '0855555555', '5555555555555', 'DL55555', DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'cancelled', 'จองออนไลน์');

