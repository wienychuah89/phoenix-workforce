<?php
session_start();
 
// 清空所有 Session 变量
$_SESSION = array();
 
// 销毁 Session
session_destroy();
 
// 重定向到登录页面
header("location: login.php");
exit;
?>
