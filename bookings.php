<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_login();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'confirm') {
            $id = $_POST['id'] ?? 0;
            
            try {
                // Get booking info
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
                $stmt->execute([$id]);
                $booking = $stmt->fetch();
                
                if (!$booking) {
                    throw new Exception('ไม่พบข้อมูลการจอง');
                }
                
                if ($booking['status'] !== 'pending') {
                    throw new Exception('การจองนี้ไม่สามารถยืนยันได้');
                }
                
                // Check if customer exists, if not create one
                $stmt = $pdo->prepare("SELECT id FROM customers WHERE id_card = ?");
                $stmt->execute([$booking['customer_id_card']]);
                $customer = $stmt->fetch();
                
                if (!$customer) {
                    // Create customer
                    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, id_card, license_number) 
                        VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $booking['customer_name'],
                        $booking['customer_phone'],
                        $booking['customer_id_card'],
                        $booking['customer_license']
                    ]);
                    $customer_id = $pdo->lastInsertId();
                } else {
                    $customer_id = $customer['id'];
                }
                
                // Get car price
                $stmt = $pdo->prepare("SELECT price_per_day FROM cars WHERE id = ?");
                $stmt->execute([$booking['car_id']]);
                $car = $stmt->fetch();
                $price_per_day = $car['price_per_day'];
                
                // Calculate days and total
                $start = new DateTime($booking['start_date']);
                $end = new DateTime($booking['end_date']);
                $total_days = $start->diff($end)->days + 1;
                $total_amount = $total_days * $price_per_day;
                
                // Create rental
                // ตรวจสอบว่ามีคอลัมน์ booking_id หรือไม่
                $stmt = $pdo->query("SHOW COLUMNS FROM rentals LIKE 'booking_id'");
                $has_booking_id = $stmt->rowCount() > 0;
                
                if ($has_booking_id) {
                    $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_id, booking_id, 
                        start_date, expected_return_date, price_per_day, total_days, total_amount, 
                        notes, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $booking['car_id'],
                        $customer_id,
                        $id,
                        $booking['start_date'],
                        $booking['end_date'],
                        $price_per_day,
                        $total_days,
                        $total_amount,
                        $booking['notes'],
                        $_SESSION['user_id']
                    ]);
                } else {
                    // ถ้ายังไม่มีคอลัมน์ booking_id ให้ INSERT โดยไม่ใส่ booking_id
                    $stmt = $pdo->prepare("INSERT INTO rentals (car_id, customer_id, 
                        start_date, expected_return_date, price_per_day, total_days, total_amount, 
                        notes, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $booking['car_id'],
                        $customer_id,
                        $booking['start_date'],
                        $booking['end_date'],
                        $price_per_day,
                        $total_days,
                        $total_amount,
                        $booking['notes'],
                        $_SESSION['user_id']
                    ]);
                }
                
                // Update booking status
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$id]);
                
                // Update car status
                $stmt = $pdo->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
                $stmt->execute([$booking['car_id']]);
                
                $message = 'ยืนยันการจองและสร้างการเช่าสำเร็จ';
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'cancel') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ยกเลิกการจองสำเร็จ';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'ลบข้อมูลการจองสำเร็จ';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get all bookings
$filter_status = $_GET['status'] ?? 'all';
$where_clause = $filter_status !== 'all' ? "WHERE b.status = '$filter_status'" : '';

$stmt = $pdo->query("SELECT b.*, c.license_plate, c.brand, c.model, c.price_per_day
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    $where_clause
    ORDER BY b.created_at DESC");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการการจอง - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>จัดการการจอง</h1>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter -->
        <div class="form-section">
            <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                <div class="form-group">
                    <label>สถานะ</label>
                    <select name="status">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>รอยืนยัน</option>
                        <option value="confirmed" <?php echo $filter_status === 'confirmed' ? 'selected' : ''; ?>>ยืนยันแล้ว</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">กรอง</button>
                    <a href="bookings.php" class="btn btn-secondary">รีเซ็ต</a>
                </div>
            </form>
        </div>
        
        <!-- Table -->
        <div class="table-section">
            <h2>รายการการจองทั้งหมด</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>วันที่จอง</th>
                            <th>รถ</th>
                            <th>ชื่อลูกค้า</th>
                            <th>เบอร์โทร</th>
                            <th>บัตรประชาชน</th>
                            <th>วันที่เริ่ม</th>
                            <th>วันที่คืน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="9" class="text-center">ยังไม่มีข้อมูลการจอง</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($booking['license_plate'] . ' - ' . $booking['brand'] . ' ' . $booking['model']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['customer_phone']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['customer_id_card']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($booking['start_date'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($booking['end_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $booking['status'] === 'pending' ? 'active' : 
                                                ($booking['status'] === 'confirmed' ? 'completed' : 'cancelled'); 
                                        ?>">
                                            <?php 
                                            echo $booking['status'] === 'pending' ? 'รอยืนยัน' : 
                                                ($booking['status'] === 'confirmed' ? 'ยืนยันแล้ว' : 'ยกเลิก');
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="confirm">
                                                <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary" 
                                                    onclick="return confirm('ยืนยันการจองนี้และสร้างการเช่า?');">
                                                    ยืนยัน
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-delete" 
                                                    onclick="return confirm('ยกเลิกการจองนี้?');">
                                                    ยกเลิก
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline;" 
                                            onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบการจองนี้?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
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
</body>
</html>

