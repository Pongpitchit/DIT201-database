<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_login();

// สถิติรถ
$stmt = $pdo->query("SELECT 
    COUNT(*) as total_cars,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_cars,
    SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented_cars,
    SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_cars
    FROM cars");
$car_stats = $stmt->fetch();

// ยอดการเช่า (รายเดือน)
$stmt = $pdo->query("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as rental_count,
    SUM(total_amount) as total_revenue
    FROM rentals
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6");
$monthly_revenue = $stmt->fetchAll();

// การเช่าที่กำลังดำเนินการ
$stmt = $pdo->query("SELECT COUNT(*) as active_rentals FROM rentals WHERE status = 'active'");
$active_rentals = $stmt->fetch();

// รายการเช่าล่าสุด
$stmt = $pdo->query("SELECT r.*, c.license_plate, c.brand, c.model, cu.name as customer_name
    FROM rentals r
    JOIN cars c ON r.car_id = c.id
    JOIN customers cu ON r.customer_id = cu.id
    ORDER BY r.created_at DESC
    LIMIT 5");
$recent_rentals = $stmt->fetchAll();

// ข้อมูลสำหรับกราฟรายได้รายเดือน (12 เดือนล่าสุด)
$stmt = $pdo->query("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    DATE_FORMAT(created_at, '%b %Y') as month_label,
    COUNT(*) as rental_count,
    SUM(total_amount) as total_revenue
    FROM rentals
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12");
$chart_revenue = $stmt->fetchAll();
$chart_revenue = array_reverse($chart_revenue); // เรียงจากเก่าไปใหม่

// ข้อมูลสำหรับกราฟสถานะรถ
$stmt = $pdo->query("SELECT 
    status,
    COUNT(*) as count
    FROM cars
    GROUP BY status");
$car_status_chart = $stmt->fetchAll();

// ข้อมูลสำหรับกราฟจำนวนการเช่ารายเดือน
$stmt = $pdo->query("SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    DATE_FORMAT(created_at, '%b %Y') as month_label,
    COUNT(*) as rental_count
    FROM rentals
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12");
$chart_rentals = $stmt->fetchAll();
$chart_rentals = array_reverse($chart_rentals);

// ข้อมูลการจองรอยืนยัน
$stmt = $pdo->query("SELECT COUNT(*) as pending_bookings FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetch();

// รายได้รวมทั้งหมด
$stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM rentals WHERE status = 'completed'");
$total_revenue = $stmt->fetch();

// จำนวนลูกค้าทั้งหมด
$stmt = $pdo->query("SELECT COUNT(*) as total_customers FROM customers");
$total_customers = $stmt->fetch();

// จำนวนการเช่าทั้งหมด
$stmt = $pdo->query("SELECT COUNT(*) as total_rentals FROM rentals");
$total_rentals = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p class="dashboard-subtitle">ภาพรวมระบบจัดการเช่ารถ</p>
        </div>
        
        <!-- สถิติหลัก -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">🚗</div>
                <div class="stat-content">
                    <h3>รถทั้งหมด</h3>
                    <p class="stat-number"><?php echo $car_stats['total_cars']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-available">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3>รถว่าง</h3>
                    <p class="stat-number"><?php echo $car_stats['available_cars']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-rented">
                <div class="stat-icon">🔑</div>
                <div class="stat-content">
                    <h3>รถไม่ว่าง</h3>
                    <p class="stat-number"><?php echo $car_stats['rented_cars']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-maintenance">
                <div class="stat-icon">🔧</div>
                <div class="stat-content">
                    <h3>รถซ่อมบำรุง</h3>
                    <p class="stat-number"><?php echo $car_stats['maintenance_cars']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-revenue">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <h3>รายได้รวม</h3>
                    <p class="stat-number"><?php echo number_format($total_revenue['total_revenue'] ?? 0, 2); ?></p>
                    <small>บาท</small>
                </div>
            </div>
            <div class="stat-card stat-customers">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>ลูกค้าทั้งหมด</h3>
                    <p class="stat-number"><?php echo $total_customers['total_customers']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3>การจองรอยืนยัน</h3>
                    <p class="stat-number"><?php echo $pending_bookings['pending_bookings']; ?></p>
                </div>
            </div>
            <div class="stat-card stat-active">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3>การเช่าที่กำลังดำเนินการ</h3>
                    <p class="stat-number"><?php echo $active_rentals['active_rentals']; ?></p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="dashboard-section">
            <h2>📈 กราฟสถิติ</h2>
            <div class="charts-grid">
                <!-- กราฟรายได้รายเดือน -->
                <div class="chart-card">
                    <h3>รายได้รายเดือน</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <!-- กราฟสถานะรถ -->
                <div class="chart-card">
                    <h3>สถานะรถ</h3>
                    <div class="chart-container">
                        <canvas id="carStatusChart"></canvas>
                    </div>
                </div>
                
                <!-- กราฟจำนวนการเช่ารายเดือน -->
                <div class="chart-card chart-full-width">
                    <h3>จำนวนการเช่ารายเดือน</h3>
                    <div class="chart-container">
                        <canvas id="rentalsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ยอดการเช่า -->
        <div class="dashboard-section">
            <h2>ยอดการเช่ารายเดือน</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>เดือน</th>
                            <th>จำนวนการเช่า</th>
                            <th>รายได้รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthly_revenue)): ?>
                            <tr>
                                <td colspan="3" class="text-center">ยังไม่มีข้อมูล</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthly_revenue as $month): ?>
                                <tr>
                                    <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                    <td><?php echo $month['rental_count']; ?></td>
                                    <td><?php echo number_format($month['total_revenue'], 2); ?> บาท</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- การเช่าที่กำลังดำเนินการ -->
        <div class="dashboard-section">
            <h2>การเช่าที่กำลังดำเนินการ: <?php echo $active_rentals['active_rentals']; ?> รายการ</h2>
        </div>

        <!-- รายการเช่าล่าสุด -->
        <div class="dashboard-section">
            <h2>รายการเช่าล่าสุด</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>รถ</th>
                            <th>ลูกค้า</th>
                            <th>วันที่เริ่ม</th>
                            <th>วันที่คืน (คาดการณ์)</th>
                            <th>ยอดรวม</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_rentals)): ?>
                            <tr>
                                <td colspan="6" class="text-center">ยังไม่มีข้อมูล</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_rentals as $rental): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rental['license_plate'] . ' - ' . $rental['brand'] . ' ' . $rental['model']); ?></td>
                                    <td><?php echo htmlspecialchars($rental['customer_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rental['start_date'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rental['expected_return_date'])); ?></td>
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

    <script>
        // กราฟรายได้รายเดือน
        <?php
        $revenue_labels = array_map(function($item) { return $item['month_label']; }, $chart_revenue);
        $revenue_data = array_map(function($item) { return floatval($item['total_revenue'] ?? 0); }, $chart_revenue);
        ?>
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($revenue_labels); ?>,
                datasets: [{
                    label: 'รายได้ (บาท)',
                    data: <?php echo json_encode($revenue_data); ?>,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'รายได้: ' + new Intl.NumberFormat('th-TH', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }).format(context.parsed.y) + ' บาท';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('th-TH').format(value) + ' บาท';
                            }
                        }
                    }
                }
            }
        });

        // กราฟสถานะรถ (Pie Chart)
        <?php
        $statusLabels = [
            'available' => 'ว่าง',
            'rented' => 'เช่าแล้ว',
            'maintenance' => 'ซ่อมบำรุง'
        ];
        $statusColors = [
            'available' => 'rgb(16, 185, 129)',
            'rented' => 'rgb(245, 158, 11)',
            'maintenance' => 'rgb(239, 68, 68)'
        ];
        $car_status_labels = array_map(function($item) use ($statusLabels) { return $statusLabels[$item['status']]; }, $car_status_chart);
        $car_status_data = array_map(function($item) { return intval($item['count']); }, $car_status_chart);
        $car_status_colors = array_map(function($item) use ($statusColors) { return $statusColors[$item['status']]; }, $car_status_chart);
        ?>
        const carStatusCtx = document.getElementById('carStatusChart').getContext('2d');
        const carStatusChart = new Chart(carStatusCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($car_status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($car_status_data); ?>,
                    backgroundColor: <?php echo json_encode($car_status_colors); ?>
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // กราฟจำนวนการเช่ารายเดือน
        <?php
        $rentals_labels = array_map(function($item) { return $item['month_label']; }, $chart_rentals);
        $rentals_data = array_map(function($item) { return intval($item['rental_count']); }, $chart_rentals);
        ?>
        const rentalsCtx = document.getElementById('rentalsChart').getContext('2d');
        const rentalsChart = new Chart(rentalsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($rentals_labels); ?>,
                datasets: [{
                    label: 'จำนวนการเช่า',
                    data: <?php echo json_encode($rentals_data); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgb(102, 126, 234)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

