<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_login();

// Get filter parameters
$filter_month = $_GET['month'] ?? date('Y-m');
$filter_status = $_GET['status'] ?? 'all';

// Build query
$where_conditions = [];
$params = [];

if ($filter_month) {
    $where_conditions[] = "DATE_FORMAT(r.created_at, '%Y-%m') = ?";
    $params[] = $filter_month;
}

if ($filter_status !== 'all') {
    $where_conditions[] = "r.status = ?";
    $params[] = $filter_status;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get rental history
$sql = "SELECT r.*, c.license_plate, c.brand, c.model, cu.name as customer_name, cu.phone as customer_phone
    FROM rentals r
    JOIN cars c ON r.car_id = c.id
    JOIN customers cu ON r.customer_id = cu.id
    $where_clause
    ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rentals = $stmt->fetchAll();

// Get monthly statistics
$stmt = $pdo->query("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as total_rentals,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_rentals,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_rentals,
    SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue
    FROM rentals
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12");
$monthly_stats = $stmt->fetchAll();

// Calculate totals for current filter
$total_revenue = 0;
$total_rentals = count($rentals);
foreach ($rentals as $rental) {
    if ($rental['status'] === 'completed') {
        $total_revenue += $rental['total_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงาน - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>รายงาน</h1>
        
        <!-- Monthly Statistics -->
        <div class="dashboard-section">
            <h2>สถิติรายเดือน</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>เดือน</th>
                            <th>จำนวนการเช่าทั้งหมด</th>
                            <th>กำลังเช่า</th>
                            <th>คืนแล้ว</th>
                            <th>รายได้รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthly_stats)): ?>
                            <tr>
                                <td colspan="5" class="text-center">ยังไม่มีข้อมูล</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthly_stats as $stat): ?>
                                <tr>
                                    <td><?php echo date('F Y', strtotime($stat['month'] . '-01')); ?></td>
                                    <td><?php echo $stat['total_rentals']; ?></td>
                                    <td><?php echo $stat['active_rentals']; ?></td>
                                    <td><?php echo $stat['completed_rentals']; ?></td>
                                    <td><?php echo number_format($stat['total_revenue'], 2); ?> บาท</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="form-section">
            <h2>กรองข้อมูล</h2>
            <form method="GET" class="form-card" style="display: flex; gap: 1rem; align-items: end;">
                <div class="form-group">
                    <label>เดือน</label>
                    <input type="month" name="month" value="<?php echo htmlspecialchars($filter_month); ?>">
                </div>
                <div class="form-group">
                    <label>สถานะ</label>
                    <select name="status">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                        <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>กำลังเช่า</option>
                        <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>คืนแล้ว</option>
                        <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">กรอง</button>
                    <a href="reports.php" class="btn btn-secondary">รีเซ็ต</a>
                </div>
            </form>
        </div>
        
        <!-- Summary -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <h3>จำนวนการเช่า</h3>
                <p class="stat-number"><?php echo $total_rentals; ?></p>
            </div>
            <div class="stat-card stat-available">
                <h3>รายได้รวม</h3>
                <p class="stat-number"><?php echo number_format($total_revenue, 2); ?> บาท</p>
            </div>
        </div>
        
        <!-- Rental History -->
        <div class="table-section">
            <h2>ประวัติการเช่า</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>วันที่เช่า</th>
                            <th>รถ</th>
                            <th>ลูกค้า</th>
                            <th>เบอร์โทร</th>
                            <th>วันที่เริ่ม</th>
                            <th>วันที่คืน (คาดการณ์)</th>
                            <th>วันที่คืน (จริง)</th>
                            <th>จำนวนวัน</th>
                            <th>ราคา/วัน</th>
                            <th>ยอดรวม</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rentals)): ?>
                            <tr>
                                <td colspan="11" class="text-center">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rentals as $rental): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($rental['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($rental['license_plate'] . ' - ' . $rental['brand'] . ' ' . $rental['model']); ?></td>
                                    <td><?php echo htmlspecialchars($rental['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($rental['customer_phone']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rental['start_date'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rental['expected_return_date'])); ?></td>
                                    <td><?php echo $rental['actual_return_date'] ? date('d/m/Y', strtotime($rental['actual_return_date'])) : '-'; ?></td>
                                    <td><?php echo $rental['total_days']; ?> วัน</td>
                                    <td><?php echo number_format($rental['price_per_day'], 2); ?> บาท</td>
                                    <td><?php echo number_format($rental['total_amount'], 2); ?> บาท</td>
                                    <td>
                                        <span class="badge badge-<?php echo $rental['status'] === 'active' ? 'active' : ($rental['status'] === 'completed' ? 'completed' : 'cancelled'); ?>">
                                            <?php 
                                            echo $rental['status'] === 'active' ? 'กำลังเช่า' : 
                                                ($rental['status'] === 'completed' ? 'คืนแล้ว' : 'ยกเลิก');
                                            ?>
                                        </span>
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

