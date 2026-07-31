<?php
// 开启 Session 记录登录状态
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
    
    // 过滤用户输入，防止基本的安全隐患
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 检查用户名和密码是否为空
    if (empty($username) || empty($password)) {
        $error = "Please key in username and password！";
    } else {		
		// 3. 使用 prepare 防止 SQL 注入（注意：usrpass 后面要加 = 号）
		$stmt = $pdo->prepare('SELECT * FROM plogin WHERE usrname = :u AND usrpass = :p');
		$stmt->execute([
			':u' => $username,
			':p' => $password  // 假设 $getpass 是你获取的密码变量
		]);
      
		$row = $stmt->fetch();
		// 4. 直接判断 $row 是否有数据即可
		if ($row) {
			// 登录成功！
			// 此时数据库已经帮你验证了账号密码是正确的
			$_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
		} else {
			// 登录失败
			// 可能是用户名不存在，或者密码错误
			echo "<script>alert('Invalid user login.');</script>";
		}
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Smart Payroll System</title>
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
    
    <!-- 显示错误信息 -->
    <?php if(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
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
