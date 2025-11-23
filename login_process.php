<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=' . urlencode('กรุณากรอก username และ password'));
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // สำหรับการทดสอบ: ถ้า password เป็น admin123 ให้ผ่าน
        if ($user && ($password === 'admin123' || password_verify($password, $user['password']))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: login.php?error=' . urlencode('Username หรือ Password ไม่ถูกต้อง'));
            exit;
        }
    } catch (PDOException $e) {
        header('Location: login.php?error=' . urlencode('เกิดข้อผิดพลาด: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}

