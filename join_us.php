<?php
// 1. 引入数据库连接
require_once __DIR__ . '/lib/db.php';

$successMsg = '';
$errorMsg = '';

// 辅助函数：智能获取正确的列名 (自动解决 password 报错问题)
function getCorrectColumnName($pdo, $table, $keyword) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table`");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($columns as $col) {
            if (strpos($col, $keyword) !== false) return $col; // 找到类似 password 的列
        }
    } catch (Exception $e) { }
    return null; // 没找到
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $category = $_POST['category'];
    $rate = floatval($_POST['rate']);
    $bio = trim($_POST['bio']);

    if (empty($name) || empty($email) || empty($password) || empty($category)) {
        $errorMsg = "Please fill in all required fields.";
    } else {
        try {
            $pdo = db(); 
            $pdo->beginTransaction();

            // --- 第一步：静默创建一个关联账号 (为了获取 user_id) ---
            
            // 1. 智能查找 'password' 列名
            $passCol = getCorrectColumnName($pdo, 'users', 'pass'); // 找 password, password_hash
            if (!$passCol) $passCol = getCorrectColumnName($pdo, 'users', 'pwd'); // 找 pwd
            if (!$passCol) $passCol = 'password'; // 默认保底

            // 2. 检查邮箱
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmtCheck->execute([$email]);
            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Email already registered.");
            }

            // 3. 插入 users 表 (仅为了拿到 ID)
            $pwdHash = password_hash($password, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO users (name, email, `$passCol`, role, created_at) VALUES (?, ?, ?, 'provider', NOW())";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute([$name, $email, $pwdHash]);
            $newUserId = $pdo->lastInsertId();

            // --- 第二步：存入 Providers 表 (这才是重点) ---
            
            // 根据你的截图 [image_91232f.jpg] 修正了所有字段名：
            // rate -> hourly_rate
            // is_approved -> verification_status
            // 增加了 display_name
            
            $sqlProv = "INSERT INTO providers 
                        (user_id, display_name, category, hourly_rate, bio, verification_status, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
            
            $stmtProv = $pdo->prepare($sqlProv);
            $stmtProv->execute([$newUserId, $name, $category, $rate, $bio]);

            $pdo->commit();
            $successMsg = "Registration successful! Data has been saved to the system.";
            
            // 清空表单
            $name = $email = $category = $rate = $bio = ''; 

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Registration</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background-color: #e9ecef; display: flex; justify-content: center; padding-top: 40px; }
        .register-box { background: white; width: 100%; max-width: 480px; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 25px; }
        label { font-weight: 600; color: #495057; display: block; margin-top: 15px; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        input:focus, select:focus { border-color: #80bdff; outline: none; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
        button { width: 100%; margin-top: 25px; padding: 12px; background-color: #0d6efd; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        button:hover { background-color: #0b5ed7; }
        .msg { padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .error { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .note { font-size: 12px; color: #6c757d; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Become a Provider</h2>

    <?php if ($successMsg): ?>
        <div class="msg success"><?php echo $successMsg; ?></div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="msg error"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="name" required placeholder="Your Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">

        <label>Email Address</label>
        <input type="email" name="email" required placeholder="name@example.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">

        <label>Password</label>
        <input type="password" name="password" required placeholder="Set a login password">

        <label>Service Category</label>
        <select name="category">
            <option value="Plumbing">Plumbing</option>
            <option value="Massage">Massage</option>
            <option value="Cleaning">Cleaning</option>
            <option value="Repair">Repair</option>
        </select>

        <label>Hourly Rate ($)</label>
        <input type="number" name="rate" step="0.01" required placeholder="e.g. 50.00" value="<?php echo isset($rate) ? htmlspecialchars($rate) : ''; ?>">

        <label>Bio / Experience</label>
        <textarea name="bio" rows="3" placeholder="Briefly describe your skills..."><?php echo isset($bio) ? htmlspecialchars($bio) : ''; ?></textarea>

        <button type="submit">Register Now</button>
    </form>
    
    <div class="note">
        This information will be submitted to the Admin System.
    </div>
</div>

</body>
</html>