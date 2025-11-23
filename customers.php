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
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $id_card = $_POST['id_card'] ?? '';
            $license_number = $_POST['license_number'] ?? '';
            $address = $_POST['address'] ?? '';
            
            try {
                $stmt = $pdo->prepare("INSERT INTO customers (name, phone, id_card, license_number, address) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $phone, $id_card, $license_number, $address]);
                $message = 'เพิ่มข้อมูลลูกค้าสำเร็จ';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $id_card = $_POST['id_card'] ?? '';
            $license_number = $_POST['license_number'] ?? '';
            $address = $_POST['address'] ?? '';
            
            try {
                $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, id_card = ?, 
                    license_number = ?, address = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $id_card, $license_number, $address, $id]);
                $message = 'อัปเดตข้อมูลลูกค้าสำเร็จ';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            try {
                // ตรวจสอบว่ามีการเช่าที่เกี่ยวข้องหรือไม่
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE customer_id = ?");
                $stmt->execute([$id]);
                $has_rentals = $stmt->fetchColumn() > 0;
                
                if ($has_rentals) {
                    $message = 'ไม่สามารถลบลูกค้าได้ เนื่องจากมีประวัติการเช่า';
                    $message_type = 'error';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'ลบข้อมูลลูกค้าสำเร็จ';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get all customers
$stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
$customers = $stmt->fetchAll();

// Get customer for editing
$edit_customer = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_customer = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการลูกค้า - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>จัดการลูกค้า</h1>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <div class="form-section">
            <h2><?php echo $edit_customer ? 'แก้ไขข้อมูลลูกค้า' : 'เพิ่มลูกค้าใหม่'; ?></h2>
            <form method="POST" class="form-card">
                <input type="hidden" name="action" value="<?php echo $edit_customer ? 'update' : 'create'; ?>">
                <?php if ($edit_customer): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_customer['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>ชื่อ-นามสกุล *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($edit_customer['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>เบอร์โทรศัพท์ *</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_customer['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>บัตรประชาชน *</label>
                        <input type="text" name="id_card" value="<?php echo htmlspecialchars($edit_customer['id_card'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ใบขับขี่</label>
                    <input type="text" name="license_number" value="<?php echo htmlspecialchars($edit_customer['license_number'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>ที่อยู่</label>
                    <textarea name="address" rows="3"><?php echo htmlspecialchars($edit_customer['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_customer ? 'อัปเดต' : 'เพิ่ม'; ?></button>
                    <?php if ($edit_customer): ?>
                        <a href="customers.php" class="btn btn-secondary">ยกเลิก</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Table -->
        <div class="table-section">
            <h2>รายการลูกค้าทั้งหมด</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ชื่อ-นามสกุล</th>
                            <th>เบอร์โทร</th>
                            <th>บัตรประชาชน</th>
                            <th>ใบขับขี่</th>
                            <th>ที่อยู่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6" class="text-center">ยังไม่มีข้อมูลลูกค้า</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['id_card']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['license_number'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['address'] ?: '-'); ?></td>
                                    <td>
                                        <a href="customers.php?edit=<?php echo $customer['id']; ?>" class="btn btn-sm btn-edit">แก้ไข</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบลูกค้านี้?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
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

