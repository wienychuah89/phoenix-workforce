<?php
// C:\xampp\htdocs\payrollsys\run_simulation.php
$host = "127.0.0.1:3307"; 
$user = "root";
$pass = "";
$db   = "payrollphp";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 查出 pemployee 表中所有已绑定的卡号
$cards = [];
$sql = "SELECT empid, empcardid FROM pemployee WHERE empcardid IS NOT NULL AND empcardid != ''";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }
}

$output = "";
// 当点击“生成打卡数据”按钮时
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_card = $_POST['card_id'];
    $selected_year = $_POST['year'];
    $selected_month = $_POST['month'];

    if (!empty($selected_card) && !empty($selected_year) && !empty($selected_month)) {
        // 构建 Python 执行命令
        // 请确保系统环境变量中已添加 python 命令，或者写绝对路径如 C:\Python39\python.exe
        $python_path = "python"; 
        $script_path = __DIR__ . "\\sim_worker.py";
        
        // 参数拼装并转义防注入
        //$cmd = escapeshellcmd("$python_path \"$script_path\" \"$selected_card\" \"$selected_year\" \"$selected_month\"");
		$cmd = "set PYTHONIOENCODING=utf-8 && " . escapeshellcmd("$python_path \"$script_path\" \"$selected_card\" \"$selected_year\" \"$selected_month\"");
		// Windows cmd 环境下加上 set PYTHONIOENCODING=utf-8
         
        
        // 执行 Python 并捕捉输出内容 (2>&1 表示将标准错误一起输出)
        $output = shell_exec($cmd . " 2>&1");
    } else {
        $output = "请填写完整参数！";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>考勤模拟数据生成器</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; line-height: 1.6; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 8px; width: 450px; background: #f9f9f9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        select, input, button { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #0056b3; }
        pre { background: #222; color: #00ff00; padding: 15px; border-radius: 5px; overflow-x: auto; }

    </style>
</head>

<body>
<div align="center">
<div class="card">
    <h2>🚀 模拟员工月份打卡数据</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label>选择员工 / 卡号：</label>
            <select name="card_id" required>
                <option value="">-- 请选择卡号 --</option>
                <?php foreach ($cards as $c): ?>
                    <option value="<?php echo htmlspecialchars($c['empcardid']); ?>">
                        员工ID: <?php echo htmlspecialchars($c['empid']); ?> (卡号: <?php echo htmlspecialchars($c['empcardid']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>年份：</label>
            <input type="number" name="year" value="2026" min="2020" max="2030" required>
        </div>

        <div class="form-group">
            <label>月份：</label>
            <select name="month" required>
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == date('n') ? 'selected' : ''; ?>>
                        <?php echo $m; ?> 月
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit">开始运行 Python 模拟程序</button>
    </form>
</div>
<?php if (!empty($output)): ?>
    <h3>控制台执行输出结果：</h3>
    <pre><?php echo htmlspecialchars($output); ?></pre>
<?php endif; ?>

</div>

</body>
</html>