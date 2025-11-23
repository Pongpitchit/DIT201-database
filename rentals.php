<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_login();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $car_id = $_POST['car_id'] ?? 0;
            $customer_id = $_POST['customer_id'] ?? 0;
            $start_date = $_POST['start_date'] ?? '';
            $expected_return_date = $_POST['expected_return_date'] ?? '';
            $price_per_day = $_POST['price_per_day'] ?? 0;
            $notes = $_POST['notes'] ?? '';
            
            try {
                // ตรวจสอบว่ารถว่างหรือไม่
                $stmt = $pdo->prepare("SELECT status FROM cars WHERE id = ?");
                $stmt->execute([$car_id]);
                $car = $stmt->fetch();
                
                if (!$car) {
                    throw new Exception('ไม่พบข้อมูลรถ');
                }
                
                if ($car['status'] !== 'available') {
                    throw new Exception('รถคันนี้ไม่ว่าง ไม่สามารถเช่าได้');
                }
                
                // ตรวจสอบว่ามีการเช่าที่กำลังดำเนินการอยู่หรือไม่
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = ? AND status = 'active'");
                $stmt->execute([$car_id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('รถคันนี้มีการเช่าที่กำลังดำเนินการอยู่');
                }
                
                // คำนวณจำนวนวันและยอดรวม
                $start = new DateTime($start_date);
                $end = new DateTime($expected_return_date);
                $total_days = $start->diff($end)->days + 1;
                $total_amount = $total_days * $price_per_day;
                
                // สร้างการเช่า
                $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_id, start_date, expected_return_date, 
                    price_per_day, total_days, total_amount, status, notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)");
                $stmt->execute([$car_id, $customer_id, $start_date, $expected_return_date, 
                    $price_per_day, $total_days, $total_amount, $notes, $_SESSION['user_id']]);
                
                // อัปเดตสถานะรถ
                $stmt = $pdo->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
                $stmt->execute([$car_id]);
                
                $message = 'บันทึกการเช่าสำเร็จ';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'return') {
            $id = $_POST['id'] ?? 0;
            $actual_return_date = $_POST['actual_return_date'] ?? date('Y-m-d');
            
            try {
                // ดึงข้อมูลการเช่า
                $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
                $stmt->execute([$id]);
                $rental = $stmt->fetch();
                
                if (!$rental) {
                    throw new Exception('ไม่พบข้อมูลการเช่า');
                }
                
                if ($rental['status'] !== 'active') {
                    throw new Exception('การเช่านี้ไม่ได้อยู่ในสถานะ active');
                }
                
                // อัปเดตการเช่า
                $stmt = $pdo->prepare("UPDATE rentals SET actual_return_date = ?, status = 'completed' WHERE id = ?");
                $stmt->execute([$actual_return_date, $id]);
                
                // คืนสถานะรถ
                $stmt = $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
                $stmt->execute([$rental['car_id']]);
                
                $message = 'บันทึกการคืนรถสำเร็จ';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                // ดึงข้อมูลการเช่า
                $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ?");
                $stmt->execute([$id]);
                $rental = $stmt->fetch();
                
                if (!$rental) {
                    throw new Exception('ไม่พบข้อมูลการเช่า');
                }
                
                // ลบการเช่า
                $stmt = $pdo->prepare("DELETE FROM rentals WHERE id = ?");
                $stmt->execute([$id]);
                
                // ถ้าการเช่ายัง active อยู่ ให้คืนสถานะรถ
                if ($rental['status'] === 'active') {
                    $stmt = $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
                    $stmt->execute([$rental['car_id']]);
                }
                
                $message = 'ลบข้อมูลการเช่าสำเร็จ';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get all rentals
$stmt = $pdo->query("SELECT r.*, c.license_plate, c.brand, c.model, cu.name as customer_name
    FROM rentals r
    JOIN cars c ON r.car_id = c.id
    JOIN customers cu ON r.customer_id = cu.id
    ORDER BY r.created_at DESC");
$rentals = $stmt->fetchAll();

// Get available cars
$stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' ORDER BY license_plate");
$available_cars = $stmt->fetchAll();

// Get all customers
$stmt = $pdo->query("SELECT * FROM customers ORDER BY name");
$customers = $stmt->fetchAll();

// Get rental for returning
$return_rental = null;
if (isset($_GET['return'])) {
    $stmt = $pdo->prepare("SELECT r.*, c.license_plate, c.brand, c.model, cu.name as customer_name
        FROM rentals r
        JOIN cars c ON r.car_id = c.id
        JOIN customers cu ON r.customer_id = cu.id
        WHERE r.id = ?");
    $stmt->execute([$_GET['return']]);
    $return_rental = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการเช่า - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>จัดการการเช่า</h1>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form for creating rental -->
        <?php if (!$return_rental): ?>
        <div class="form-section">
            <h2>เพิ่มการเช่าใหม่</h2>
            <form method="POST" class="form-card">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>รถ *</label>
                        <select name="car_id" required>
                            <option value="">-- เลือกรถ --</option>
                            <?php foreach ($available_cars as $car): ?>
                                <option value="<?php echo $car['id']; ?>" data-price="<?php echo $car['price_per_day']; ?>">
                                    <?php echo htmlspecialchars($car['license_plate'] . ' - ' . $car['brand'] . ' ' . $car['model']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ลูกค้า *</label>
                        <select name="customer_id" required>
                            <option value="">-- เลือกลูกค้า --</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>">
                                    <?php echo htmlspecialchars($customer['name'] . ' (' . $customer['phone'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>วันที่เริ่มเช่า *</label>
                        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>วันที่คาดว่าจะคืน *</label>
                        <input type="date" name="expected_return_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ราคาเช่าต่อวัน (บาท) *</label>
                    <input type="number" name="price_per_day" step="0.01" min="0" id="price_per_day" required>
                    <small>เลือกรถเพื่อโหลดราคาเริ่มต้น</small>
                </div>
                
                <div class="form-group">
                    <label>หมายเหตุ</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">บันทึกการเช่า</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Form for returning car -->
        <?php if ($return_rental): ?>
        <div class="form-section">
            <h2>คืนรถ</h2>
            <div class="info-box">
                <p><strong>รถ:</strong> <?php echo htmlspecialchars($return_rental['license_plate'] . ' - ' . $return_rental['brand'] . ' ' . $return_rental['model']); ?></p>
                <p><strong>ลูกค้า:</strong> <?php echo htmlspecialchars($return_rental['customer_name']); ?></p>
                <p><strong>วันที่เริ่มเช่า:</strong> <?php echo date('d/m/Y', strtotime($return_rental['start_date'])); ?></p>
                <p><strong>วันที่คาดว่าจะคืน:</strong> <?php echo date('d/m/Y', strtotime($return_rental['expected_return_date'])); ?></p>
            </div>
            <form method="POST" class="form-card">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="id" value="<?php echo $return_rental['id']; ?>">
                
                <div class="form-group">
                    <label>วันที่คืนจริง *</label>
                    <input type="date" name="actual_return_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">บันทึกการคืนรถ</button>
                    <a href="rentals.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Table -->
        <div class="table-section">
            <h2>รายการการเช่าทั้งหมด</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>รถ</th>
                            <th>ลูกค้า</th>
                            <th>วันที่เริ่ม</th>
                            <th>วันที่คืน (คาดการณ์)</th>
                            <th>วันที่คืน (จริง)</th>
                            <th>จำนวนวัน</th>
                            <th>ยอดรวม</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rentals)): ?>
                            <tr>
                                <td colspan="9" class="text-center">ยังไม่มีข้อมูลการเช่า</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rentals as $rental): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rental['license_plate'] . ' - ' . $rental['brand'] . ' ' . $rental['model']); ?></td>
                                    <td><?php echo htmlspecialchars($rental['customer_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rental['start_date'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rental['expected_return_date'])); ?></td>
                                    <td><?php echo $rental['actual_return_date'] ? date('d/m/Y', strtotime($rental['actual_return_date'])) : '-'; ?></td>
                                    <td><?php echo $rental['total_days']; ?> วัน</td>
                                    <td><?php echo number_format($rental['total_amount'], 2); ?> บาท</td>
                                    <td>
                                        <span class="badge badge-<?php echo $rental['status'] === 'active' ? 'active' : ($rental['status'] === 'completed' ? 'completed' : 'cancelled'); ?>">
                                            <?php 
                                            echo $rental['status'] === 'active' ? 'กำลังเช่า' : 
                                                ($rental['status'] === 'completed' ? 'คืนแล้ว' : 'ยกเลิก');
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($rental['status'] === 'active'): ?>
                                            <a href="rentals.php?return=<?php echo $rental['id']; ?>" class="btn btn-sm btn-primary">คืนรถ</a>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบการเช่านี้?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $rental['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-delete">ลบ</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script>
        // Auto-fill price when car is selected
        document.querySelector('select[name="car_id"]')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price) {
                document.getElementById('price_per_day').value = price;
            }
        });
    </script>
</body>
</html>

