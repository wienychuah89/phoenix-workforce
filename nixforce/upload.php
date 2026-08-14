<?php
// C:\xampp\htdocs\payrollsys\upload.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

$host = "127.0.0.1:3307"; 
$user = "root";
$pass = "";
$db   = "payrollphp";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

$card_uid = isset($_POST['card_uid']) ? $_POST['card_uid'] : null;

if ($card_uid) {
    $card_id = $conn->real_escape_string($card_uid); 
    
    // ✨ 步骤 1：去员工表(employees)里查询有没有这张卡
    // 假设你的员工表里卡号字段叫 card_id
    $check_sql = "SELECT empname FROM pemployee WHERE empcardid = '$card_id' LIMIT 1";
    $result = $conn->query($check_sql);
    
    if ($result && $result->num_rows > 0) {
        // 找到了员工，说明是合法授权卡
        $employee = $result->fetch_assoc();
        $emp_name = $employee['empname'];
        
     /*    // 静态记录考勤历史
        $insert_sql = "INSERT INTO attendance_logs (card_id) VALUES ('$card_id')"; 
        $conn->query($insert_sql);
		
		// 接收 Python 传递过来的历史打卡时间，若无则取默认时间
		$created_at = isset($_POST['created_at']) ? $_POST['created_at'] : date('Y-m-d H:i:s');
		$created_at_escaped = $conn->real_escape_string($created_at);

		// 将时间存入 attendance_logs 表（假设你的时间列名为 created_at 或 log_time）
		$insert_sql = "INSERT INTO attendance_logs (card_id, created_at) VALUES ('$card_id', '$created_at_escaped')";
         $conn->query($insert_sql); */
		 
		 
		 
		// ✨ 步骤 2：判断请求来源（实体 RFID 还是 Python 模拟器）
        if (isset($_POST['created_at']) && !empty($_POST['created_at'])) {
            // ------------------ 【分支 A：模拟器打卡】 ------------------
            $created_at = $conn->real_escape_string($_POST['created_at']);
            
            // 插入历史自定义时间，并标记 source 为 'simulator' (或 is_simulated = 1)
            $insert_sql = "INSERT INTO attendance_logs (card_id, created_at, source) 
                           VALUES ('$card_id', '$created_at', 'simulator')";
        } else {
            // ------------------ 【分支 B：实体 RFID 刷卡】 ------------------
            // 硬件刷卡：不传 created_at，直接以数据库当前时间 CURRENT_TIMESTAMP 插入
            $insert_sql = "INSERT INTO attendance_logs (card_id, created_at, source) 
                           VALUES ('$card_id', NOW(), 'rfid')";
        }
		$conn->query($insert_sql);
		
		
        
        // 🔑 返回给 ESP32：验证通过，并把员工姓名捎带过去
        echo json_encode([
            "status" => "success", 
            "auth" => true, 
            "message" => "Welcome " . $emp_name
        ]);
    } else {
        // 没找到员工，说明是非法卡或未注册卡
        // 你也可以选择是否把失败记录也存入 attendance_logs，这里演示为不合法不记录
        echo json_encode([
            "status" => "success", 
            "auth" => false, 
            "message" => "Unknown Card"
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data received"]);
}

$conn->close();
?>
