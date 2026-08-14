<?php
// 1. 开启 Session 并在成功登录后生成新 ID 
session_start();
include 'cnopen.php';

// 如果用户已经登录，直接跳转到欢迎页
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: welcome.php');
    exit;
}

// 初始化变量和错误信息
$username = $password = "";
$error = "";

// 检查是否是 POST 请求提交的数据
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please key in username and password!";
    } else {		
        // 优化方案：只根据用户名查找用户，不要在 SQL 中直接比对密码
        $stmt = $pdo->prepare('SELECT * FROM plogin WHERE usrname = :u');
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch();
        
        // 使用安全的方式校验密码（支持数据库中存储的密文哈希）
        // 注意：如果您的数据库目前全是明文密码，测试时需临时用 $password === $row['usrpass']
        //if ($row && password_verify($password, $row['usrpass'])) {
        if ($row && $password === $row['usrpass']) {     
            // 修复防劫持漏洞：生成新的会话 ID
            session_regenerate_id(true);
            
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            
            header('Location: welcome.php');
            exit;
        } else {
            // 修复输出顺序漏洞：通过变量传递错误，避免在此处直接 echo 破坏 HTTP 头部
            $error = "Invalid user login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Nixforce Management</title>
    <style>
        body { font: 14px sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f9; }
        .wrapper { width: 360px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; border-radius: 4px; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .error { color: red; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<div class="wrapper">
    <h2>Login</h2>
    
    <!-- 显示错误信息（包含密码错误和空值错误） -->
    <?php if(!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>    
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <input type="submit" class="btn" value="Login">
        </div>
    </form>
</div>

</body>
</html>
