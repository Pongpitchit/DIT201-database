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
            $license_plate = $_POST['license_plate'] ?? '';
            $brand = $_POST['brand'] ?? '';
            $model = $_POST['model'] ?? '';
            $year = $_POST['year'] ?? '';
            $color = $_POST['color'] ?? '';
            $status = $_POST['status'] ?? 'available';
            $price_per_day = $_POST['price_per_day'] ?? 0;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO cars (license_plate, brand, model, year, color, status, price_per_day) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$license_plate, $brand, $model, $year, $color, $status, $price_per_day]);
                $message = 'เพิ่มข้อมูลรถสำเร็จ';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'] ?? 0;
            $license_plate = $_POST['license_plate'] ?? '';
            $brand = $_POST['brand'] ?? '';
            $model = $_POST['model'] ?? '';
            $year = $_POST['year'] ?? '';
            $color = $_POST['color'] ?? '';
            $status = $_POST['status'] ?? 'available';
            $price_per_day = $_POST['price_per_day'] ?? 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE cars SET license_plate = ?, brand = ?, model = ?, year = ?, 
                    color = ?, status = ?, price_per_day = ? WHERE id = ?");
                $stmt->execute([$license_plate, $brand, $model, $year, $color, $status, $price_per_day, $id]);
                $message = 'อัปเดตข้อมูลรถสำเร็จ';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                // ตรวจสอบว่ามีการเช่าที่กำลังใช้งานอยู่หรือไม่
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE car_id = ? AND status = 'active'");
                $stmt->execute([$id]);
                $has_active_rental = $stmt->fetchColumn() > 0;
                
                if ($has_active_rental) {
                    $message = 'ไม่สามารถลบรถได้ เนื่องจากมีการเช่าที่กำลังดำเนินการอยู่';
                    $message_type = 'error';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'ลบข้อมูลรถสำเร็จ';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get all cars
$stmt = $pdo->query("SELECT * FROM cars ORDER BY created_at DESC");
$cars = $stmt->fetchAll();

// Get car for editing
$edit_car = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_car = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรถ - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>จัดการรถ</h1>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <div class="form-section">
            <h2><?php echo $edit_car ? 'แก้ไขข้อมูลรถ' : 'เพิ่มรถใหม่'; ?></h2>
            <form method="POST" class="form-card">
                <input type="hidden" name="action" value="<?php echo $edit_car ? 'update' : 'create'; ?>">
                <?php if ($edit_car): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_car['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ทะเบียนรถ *</label>
                        <input type="text" name="license_plate" value="<?php echo htmlspecialchars($edit_car['license_plate'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>ยี่ห้อ *</label>
                        <input type="text" name="brand" value="<?php echo htmlspecialchars($edit_car['brand'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>รุ่น *</label>
                        <input type="text" name="model" value="<?php echo htmlspecialchars($edit_car['model'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>ปี *</label>
                        <input type="number" name="year" value="<?php echo htmlspecialchars($edit_car['year'] ?? date('Y')); ?>" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>สี</label>
                        <input type="text" name="color" value="<?php echo htmlspecialchars($edit_car['color'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>สถานะ *</label>
                        <select name="status" required>
                            <option value="available" <?php echo ($edit_car['status'] ?? '') === 'available' ? 'selected' : ''; ?>>ว่าง</option>
                            <option value="rented" <?php echo ($edit_car['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>เช่าแล้ว</option>
                            <option value="maintenance" <?php echo ($edit_car['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>ซ่อมบำรุง</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ราคาเช่าต่อวัน (บาท) *</label>
                    <input type="number" name="price_per_day" step="0.01" min="0" value="<?php echo htmlspecialchars($edit_car['price_per_day'] ?? ''); ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_car ? 'อัปเดต' : 'เพิ่ม'; ?></button>
                    <?php if ($edit_car): ?>
                        <a href="cars.php" class="btn btn-secondary">ยกเลิก</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Table -->
        <div class="table-section">
            <h2>รายการรถทั้งหมด</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ทะเบียน</th>
                            <th>ยี่ห้อ/รุ่น</th>
                            <th>ปี</th>
                            <th>สี</th>
                            <th>สถานะ</th>
                            <th>ราคา/วัน</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cars)): ?>
                            <tr>
                                <td colspan="7" class="text-center">ยังไม่มีข้อมูลรถ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($car['license_plate']); ?></td>
                                    <td><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></td>
                                    <td><?php echo $car['year']; ?></td>
                                    <td><?php echo htmlspecialchars($car['color']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $car['status']; ?>">
                                            <?php 
                                            echo $car['status'] === 'available' ? 'ว่าง' : 
                                                ($car['status'] === 'rented' ? 'เช่าแล้ว' : 'ซ่อมบำรุง');
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($car['price_per_day'], 2); ?> บาท</td>
                                    <td>
                                        <a href="cars.php?edit=<?php echo $car['id']; ?>" class="btn btn-sm btn-edit">แก้ไข</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบรถคันนี้?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $car['id']; ?>">
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

