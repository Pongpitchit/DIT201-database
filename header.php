<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="header-content">
        <h1>ระบบจัดการเช่ารถ</h1>
        <nav>
            <a href="home.php">Home</a>
            <a href="booking.php">จองรถ</a>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="cars.php">รถ</a>
                <a href="customers.php">ลูกค้า</a>
                <a href="rentals.php">การเช่า</a>
                <a href="bookings.php">การจอง</a>
                <a href="reports.php">รายงาน</a>
                <a href="logout.php">ออกจากระบบ (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

