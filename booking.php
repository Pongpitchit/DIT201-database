<?php
require_once __DIR__ . '/config.php';

$message = '';
$message_type = '';

// Check for messages
if (isset($_GET['success'])) {
    $message = $_GET['success'];
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $message_type = 'error';
}

// Get available cars
$stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' ORDER BY license_plate");
$available_cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองรถ - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>จองรถออนไลน์</h1>
        <p>กรุณากรอกข้อมูลเพื่อจองรถ</p>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <form method="POST" action="booking_process.php" class="form-card" id="bookingForm">
                <h2>ข้อมูลรถที่ต้องการจอง</h2>
                <div class="form-group">
                    <label>เลือกรถ *</label>
                    <select name="car_id" id="car_select" required>
                        <option value="">-- เลือกรถ --</option>
                        <?php foreach ($available_cars as $car): ?>
                            <option value="<?php echo $car['id']; ?>" 
                                data-price="<?php echo $car['price_per_day']; ?>"
                                data-info="<?php echo htmlspecialchars($car['license_plate'] . ' - ' . $car['brand'] . ' ' . $car['model'] . ' (' . $car['year'] . ')'); ?>">
                                <?php echo htmlspecialchars($car['license_plate'] . ' - ' . $car['brand'] . ' ' . $car['model'] . ' (' . $car['year'] . ') - ' . number_format($car['price_per_day'], 2) . ' บาท/วัน'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="car_info" style="margin-top: 0.5rem; padding: 0.5rem; background: #f0f0f0; border-radius: 4px; display: none;">
                        <small id="car_details"></small>
                    </div>
                </div>
                
                <h2 style="margin-top: 2rem;">ข้อมูลส่วนตัว</h2>
                <div class="form-group">
                    <label>ชื่อ-นามสกุล *</label>
                    <input type="text" name="customer_name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>เบอร์โทรศัพท์ *</label>
                        <input type="text" name="customer_phone" required>
                    </div>
                    <div class="form-group">
                        <label>บัตรประชาชน *</label>
                        <input type="text" name="customer_id_card" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ใบขับขี่</label>
                    <input type="text" name="customer_license">
                </div>
                
                <h2 style="margin-top: 2rem;">วันที่ต้องการเช่า</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>วันที่เริ่มเช่า *</label>
                        <input type="date" name="start_date" id="start_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>วันที่คืนรถ *</label>
                        <input type="date" name="end_date" id="end_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ราคาเช่าต่อวัน</label>
                    <input type="text" id="price_display" readonly style="background: #f0f0f0;">
                    <small>ราคาจะแสดงเมื่อเลือกรถ</small>
                </div>
                
                <div class="form-group">
                    <label>จำนวนวัน</label>
                    <input type="text" id="days_display" readonly style="background: #f0f0f0;">
                </div>
                
                <div class="form-group">
                    <label>ยอดรวม (ประมาณการ)</label>
                    <input type="text" id="total_display" readonly style="background: #f0f0f0; font-weight: bold; font-size: 1.1rem;">
                </div>
                
                <div class="form-group">
                    <label>หมายเหตุ</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">ยืนยันการจอง</button>
                    <a href="home.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        const carSelect = document.getElementById('car_select');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const priceDisplay = document.getElementById('price_display');
        const daysDisplay = document.getElementById('days_display');
        const totalDisplay = document.getElementById('total_display');
        const carInfo = document.getElementById('car_info');
        const carDetails = document.getElementById('car_details');
        
        function calculateTotal() {
            const selectedOption = carSelect.options[carSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            
            if (startDate.value && endDate.value && start <= end && price > 0) {
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                const total = days * price;
                
                priceDisplay.value = number_format(price) + ' บาท';
                daysDisplay.value = days + ' วัน';
                totalDisplay.value = number_format(total) + ' บาท';
            } else {
                priceDisplay.value = '';
                daysDisplay.value = '';
                totalDisplay.value = '';
            }
        }
        
        function number_format(num) {
            return num.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        
        carSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const info = selectedOption.getAttribute('data-info');
            
            if (info) {
                carDetails.textContent = 'รถที่เลือก: ' + info;
                carInfo.style.display = 'block';
            } else {
                carInfo.style.display = 'none';
            }
            
            calculateTotal();
        });
        
        startDate.addEventListener('change', function() {
            if (this.value) {
                endDate.min = this.value;
            }
            calculateTotal();
        });
        
        endDate.addEventListener('change', function() {
            if (this.value && startDate.value) {
                if (this.value < startDate.value) {
                    alert('วันที่คืนรถต้องมากกว่าหรือเท่ากับวันที่เริ่มเช่า');
                    this.value = startDate.value;
                }
            }
            calculateTotal();
        });
    </script>
</body>
</html>

