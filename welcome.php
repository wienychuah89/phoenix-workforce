<?php
session_start();

// 检查用户是否已登录，如果没有，则重定向到登录页面
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// 1. 获取当前 URL 中的 page 参数，如果没传，默认是 'home'
$current_page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Nixforce Management</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <style>
        .custom-banner {
		  /* 1. 植入背景图片 */
		  background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/topbanner.jpg');
		  
		  /* 2. 背景图片自适应控制 */
		  background-size: cover;       /* 核心：让图片自动裁剪并撑满整个容器，绝不变形 */
		  background-position: center;   /* 图片居中对齐 */
		  background-repeat: no-repeat;  /* 防止图片重复平铺 */
		  
		  /* 3. 确保容器有足够的高度和定位基准 */
		  position: relative;
		  min-height: 120px;            /* 你可以根据需要调整横幅的高度 */
		}
    </style>
</head>
<body>

<!-- 去掉了 bg-dark，加入了 custom-banner -->
<div class="mt-4 p-5 text-white rounded custom-banner">
  <div class="banner-text">
    <!-- 保持之前的两端对齐 Flex 布局 -->
    <h6 class="d-flex justify-content-between align-items-center w-100 m-0">
      
      <!-- 左侧：欢迎语和名字 -->
      <span>Welcome, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
      
      <!-- 右侧：退出登录链接 -->
      <a href="logout.php" style="color: #ff4d4d; text-decoration: none; font-weight: bold;">Logout</a>
      
    </h6>
  </div> 
</div>


<!-- 1. 按钮导航栏：引入 nav 和 nav-tabs 概念，但保持你的按钮样式 -->
<div class="d-flex flex-row bg-secondary p-2 rounded" id="myTab" role="tablist">
	<a href="?page=home" class="btn btn-info btn-sm me-2 <?php echo $current_page === 'home' ? 'active' : ''; ?>"><b>Home Page</b></a>

	
	<!-- Dropdown Maintenance -->
	<div class="dropdown">
		<?php 
		  $dropdown_pages = ['user', 'department','position','bank','currency','paycate','supcate'];
		  $is_dropdown_active = in_array($current_page, $dropdown_pages);
		?>
		<button type="button" class="btn btn-info btn-sm dropdown-toggle <?php echo $is_dropdown_active ? 'active' : ''; ?>" data-bs-toggle="dropdown">
		  <b>Maintenance</b>
		</button>
		<ul class="dropdown-menu">
		  <li><a class="dropdown-item <?php echo $current_page === 'user' ? 'active' : ''; ?>" href="?page=user"><b>User</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'department' ? 'active' : ''; ?>" href="?page=department"><b>Department</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'position' ? 'active' : ''; ?>" href="?page=position"><b>Position</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'bank' ? 'active' : ''; ?>" href="?page=bank"><b>Bank</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'currency' ? 'active' : ''; ?>" href="?page=currency"><b>Currency</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'paycate' ? 'active' : ''; ?>" href="?page=paycate"><b>Category [Pay]</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'supcate' ? 'active' : ''; ?>" href="?page=supcate"><b>Category [Supplier]</b></a></li>
		</ul>
    </div>&nbsp;

	
	<!-- Dropdown Master Data -->
	<div class="dropdown">
		<?php 
		  $dropdown_pages = ['employee', 'emppay','supplier'];
		  $is_dropdown_active = in_array($current_page, $dropdown_pages);
		?>
		<button type="button" class="btn btn-info btn-sm dropdown-toggle <?php echo $is_dropdown_active ? 'active' : ''; ?>" data-bs-toggle="dropdown">
		  <b>Master Data</b>
		</button>
		<ul class="dropdown-menu">
		  <li><a class="dropdown-item <?php echo $current_page === 'employee' ? 'active' : ''; ?>" href="?page=employee"><b>Basic Information</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'empsalary' ? 'active' : ''; ?>" href="?page=empsalary"><b>Salary Profile</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'supplier' ? 'active' : ''; ?>" href="?page=supplier"><b>Supplier Profile</b></a></li>
		</ul>
    </div>&nbsp;
	

	<!-- Dropdown Transaction -->
	<div class="dropdown">
		<?php 
		  $dropdown_pages = ['payrollcontent', 'purchaseorder','purchaseordercom'];
		  $is_dropdown_active = in_array($current_page, $dropdown_pages);
		?>
		<button type="button" class="btn btn-info btn-sm dropdown-toggle <?php echo $is_dropdown_active ? 'active' : ''; ?>" data-bs-toggle="dropdown">
		  <b>Transaction</b>
		</button>
		<ul class="dropdown-menu">
		  <li><a class="dropdown-item <?php echo $current_page === 'payrollcontent' ? 'active' : ''; ?>" href="?page=payrollcontent"><b>Payroll Record</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'purchaseorder' ? 'active' : ''; ?>" href="?page=purchaseorder"><b>Purchase Order</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'purchaseordercom' ? 'active' : ''; ?>" href="?page=purchaseordercom"><b>PO Confirmation</b></a></li>
		</ul>
    </div>&nbsp;
	
	<!-- Dropdown 菜单 -->
	<div class="dropdown">
		<?php 
		  $dropdown_pages = ['mainconfig', 'optionsetup','option01setup'];
		  $is_dropdown_active = in_array($current_page, $dropdown_pages);
		?>
		<button type="button" class="btn btn-info btn-sm dropdown-toggle <?php echo $is_dropdown_active ? 'active' : ''; ?>" data-bs-toggle="dropdown">
		  <b>Salary Manage</b>
		</button>
		<ul class="dropdown-menu">
		  <li><a class="dropdown-item <?php echo $current_page === 'mainconfig' ? 'active' : ''; ?>" href="?page=mainconfig"><b>Master Setup</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'optionsetup' ? 'active' : ''; ?>" href="?page=optionsetup"><b>Category</b></a></li>
		  <li><a class="dropdown-item <?php echo $current_page === 'option01setup' ? 'active' : ''; ?>" href="?page=option01setup"><b>Category Distribution</b></a></li>
		</ul>
    </div>&nbsp;
	
	

  
  
  
</div>


<!-- 内容输出区域 -->
<div class="tab-content mt-3 p-3 border rounded bg-light">
  
  <?php if ($current_page === 'home'): ?>
    <!-- Home Page 的内容 -->
    <div class="tab-pane fade show active">
      <h3>Home Page</h3>
      <p>这是主页的内容。</p>
    </div>
  <?php endif; ?>
  
  <!--Dropdown : Maintenance -->
  <?php if ($current_page === 'user'): ?>
    <!-- User Setup 的内容 -->
    <div class="tab-pane fade show active">
      <h3>User Setup</h3>
      <p>这是用户设置内容。</p>
	  <?php include "userpage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'department'): ?>
    <div class="tab-pane fade show active">
	  <?php include "deptpage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'position'): ?>
    <div class="tab-pane fade show active">
	  <?php include "positionpage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'bank'): ?>
    <div class="tab-pane fade show active">
	  <?php include "bankpage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'currency'): ?>
    <div class="tab-pane fade show active">
	  <?php include "curpage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'paycate'): ?>
    <div class="tab-pane fade show active">
	  <?php include "paycatepage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'supcate'): ?>
    <div class="tab-pane fade show active">
	  <?php include "supcatepage.php" ?>
    </div>
  <?php endif; ?>
   
  
  <!--Dropdown : Master Data -->
  <?php if ($current_page === 'employee'): ?>
    <div class="tab-pane fade show active">
	  <?php include "emppage.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'empsalary'): ?>
    <div class="tab-pane fade show active">
	  <?php include "empsalary.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'supplier'): ?>
    <div class="tab-pane fade show active">
	  <?php include "supppage.php" ?>
    </div>
  <?php endif; ?>
  
  <!--Dropdown : Transactin -->
  <?php if ($current_page === 'payrollcontent'): ?>
    <div class="tab-pane fade show active">
	  <?php include "payrollcontent.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'purchaseorder'): ?>
    <div class="tab-pane fade show active">
	  <?php include "purchaseorder.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'purchaseordercom'): ?>
    <div class="tab-pane fade show active">
	  <?php include "purchaseordercom.php" ?>
    </div>
  <?php endif; ?>
  
  
  
  
  
  <!--Dropdown : Salary Manage -->
  <?php if ($current_page === 'mainconfig'): ?>
    <div class="tab-pane fade show active">
	  <?php include "masterconfig.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'optionsetup'): ?>
    <div class="tab-pane fade show active">
	  <?php include "optionsetup.php" ?>
    </div>
  <?php endif; ?>
  <?php if ($current_page === 'option01setup'): ?>
    <div class="tab-pane fade show active">
	  <?php include "option01setup.php" ?>
    </div>
  <?php endif; ?>
  

  
  
  <!-- 新增：Dropdown 对应页面的内容输出 -->

  
  <?php if ($current_page === 'link2_page'): ?>
    <div class="tab-pane fade show active">
      <h3>Link 2 页面</h3>
      <p>这里放 Link 2 的内容。</p>
    </div>
  <?php endif; ?>
  

  <?php if ($current_page === 'link3_page'): ?>
    <div class="tab-pane fade show active">
      <h3>Link 3 页面</h3>
      <p>这里放 Link 3 的内容。</p>
    </div>
  <?php endif; ?>


</div>

</body>
</html>
