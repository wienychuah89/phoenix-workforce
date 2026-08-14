<?php
include 'cnopen.php';
function saveLog($pdo, $userId, $action, $table, $recordId, $details) {
    try {
        $sql = "INSERT INTO activity_logs (user_id, action_type, table_name, record_id, details) 
                VALUES (:user_id, :action_type, :table_name, :record_id, :details)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id'     => $userId,
            'action_type' => $action,
            'table_name'  => $table,
            'record_id'   => $recordId,
            'details'     => $details
        ]);
        return true;
    } catch (PDOException $e) {
        // Log the error to a file instead of crashing the main script
        error_log("Logging failed: " . $e->getMessage());
        return false;
    }
}
?>