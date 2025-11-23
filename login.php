<?php
require_once __DIR__ . '/config.php';

// ถ้า login อยู่แล้ว redirect ไป Dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ระบบจัดการเช่ารถ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>เข้าสู่ระบบ</h1>
        
        <?php if ($error): ?>
            <div class="message message-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <form method="post" action="login_process.php" class="form-card">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
                <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                    หมายเหตุ: ใช้ username: <strong>admin</strong> หรือ <strong>staff</strong> และ password: <strong>admin123</strong>
                </p>
            </form>
        </div>
    </main>
</body>
</html>
