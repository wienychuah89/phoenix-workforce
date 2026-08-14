<?php
// 1. 配置数据库参数
$host    = "127.0.0.1:3307"; // 包含端口号
$user    = "root";
$pass    = "";
$db      = "payrollphp";
$charset = "utf8mb4";        // 补充缺少的字符集定义

// 修正：将原先错误的 $dbName 和 $charset 替换为上方定义的变量
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // 2. 建立连接
    // 修正：将原先不一致的 $username 和 $password 替换为上方定义的 $user 和 $pass
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (PDOException $e) {
    exit("数据库连接失败: " . $e->getMessage());
}
?>
