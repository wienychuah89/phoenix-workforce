<?php
    // 检查用户是否已登录，如果没有，则重定向到登录页面
	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
		header('Location: login.php');
		exit;
	}
    include 'cnopen.php';
	include 'function.php';
	$msg   = "";
	
	

?>

<div class="container my-1">
	<h4 class="mb-4 text-start">Master Setup</h4>
	<div class="text-center text-danger fw-bold mb-4"><?php echo $msg; ?></div>




</div>
