<?php
// หน้า Home แบบ public
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>ยินดีต้อนรับสู่ระบบจัดการเช่ารถ</h1>
        <p>ระบบจัดการเช่ารถที่ครบวงจร สำหรับการจัดการรถยนต์, ลูกค้า, และการเช่า</p>
        
        <div style="margin-top: 2rem;">
            <h2>ฟีเจอร์หลัก</h2>
            <div class="stats-grid" style="margin-top: 1rem;">
                <div class="stat-card">
                    <h3>จัดการรถ</h3>
                    <p>เพิ่ม, แก้ไข, ลบข้อมูลรถ พร้อมสถานะ (ว่าง, เช่าแล้ว, ซ่อมบำรุง)</p>
                </div>
                <div class="stat-card">
                    <h3>จัดการลูกค้า</h3>
                    <p>บันทึกข้อมูลลูกค้า พร้อมบัตรประชาชนและใบขับขี่</p>
                </div>
                <div class="stat-card">
                    <h3>จัดการการเช่า</h3>
                    <p>บันทึกการเช่า, กำหนดวันที่รับ-คืน, คำนวณราคาอัตโนมัติ</p>
                </div>
                <div class="stat-card">
                    <h3>รายงาน</h3>
                    <p>ดูประวัติการเช่า, สถิติรายเดือน, และรายได้</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="login.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">เข้าสู่ระบบ</a>
        </div>
    </main>
</body>
</html>
