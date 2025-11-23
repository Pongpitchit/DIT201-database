<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'] ?? 0;
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $customer_id_card = $_POST['customer_id_card'] ?? '';
    $customer_license = $_POST['customer_license'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validation
    if (empty($car_id) || empty($customer_name) || empty($customer_phone) || 
        empty($customer_id_card) || empty($start_date) || empty($end_date)) {
        header('Location: booking.php?error=' . urlencode('กรุณากรอกข้อมูลให้ครบถ้วน'));
        exit;
    }
    
    // Validate dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    if ($start > $end) {
        header('Location: booking.php?error=' . urlencode('วันที่คืนรถต้องมากกว่าหรือเท่ากับวันที่เริ่มเช่า'));
        exit;
    }
    
    try {
        // Check if car is available
        $stmt = $pdo->prepare("SELECT status FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch();
        
        if (!$car) {
            header('Location: booking.php?error=' . urlencode('ไม่พบข้อมูลรถ'));
            exit;
        }
        
        if ($car['status'] !== 'available') {
            header('Location: booking.php?error=' . urlencode('รถคันนี้ไม่ว่าง ไม่สามารถจองได้'));
            exit;
        }
        
        // Check if car has active rental or booking in the same period
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals 
            WHERE car_id = ? AND status = 'active' 
            AND ((start_date <= ? AND expected_return_date >= ?) 
            OR (start_date <= ? AND expected_return_date >= ?))");
        $stmt->execute([$car_id, $start_date, $start_date, $end_date, $end_date]);
        $has_active_rental = $stmt->fetchColumn() > 0;
        
        if ($has_active_rental) {
            header('Location: booking.php?error=' . urlencode('รถคันนี้มีการเช่าในช่วงเวลานี้แล้ว'));
            exit;
        }
        
        // Check for pending/confirmed bookings in the same period
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings 
            WHERE car_id = ? AND status IN ('pending', 'confirmed')
            AND ((start_date <= ? AND end_date >= ?) 
            OR (start_date <= ? AND end_date >= ?))");
        $stmt->execute([$car_id, $start_date, $start_date, $end_date, $end_date]);
        $has_booking = $stmt->fetchColumn() > 0;
        
        if ($has_booking) {
            header('Location: booking.php?error=' . urlencode('รถคันนี้มีการจองในช่วงเวลานี้แล้ว'));
            exit;
        }
        
        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (car_id, customer_name, customer_phone, 
            customer_id_card, customer_license, start_date, end_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$car_id, $customer_name, $customer_phone, $customer_id_card, 
            $customer_license, $start_date, $end_date, $notes]);
        
        header('Location: booking.php?success=' . urlencode('จองรถสำเร็จ! รอการยืนยันจากเจ้าหน้าที่'));
        exit;
    } catch (PDOException $e) {
        header('Location: booking.php?error=' . urlencode('เกิดข้อผิดพลาด: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: booking.php');
    exit;
}

