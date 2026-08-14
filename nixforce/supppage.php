<?php
    // 检查用户是否已登录，如果没有，则重定向到登录页面
	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
		header('Location: login.php');
		exit;
	}
	include 'cnopen.php';
	include 'function.php';
	$action   = isset($_POST['action']) ? $_POST['action'] : '';
	$msg = "";
	if($action=="add_supplier"){
		$getsupid       = isset($_POST['supid']) ? $_POST['supid'] : '';
		$getsupname     = isset($_POST['supname']) ? $_POST['supname'] : '';
		$getsupcate     = isset($_POST['supcate']) ? $_POST['supcate'] : '';
		$getsuppic      = isset($_POST['suppic']) ? $_POST['suppic'] : '';
		$getsupadd      = isset($_POST['supadd']) ? $_POST['supadd'] : '';
		$getsuprmk      = isset($_POST['suprmk']) ? $_POST['suprmk'] : '';
		$getsuptel      = isset($_POST['suptel']) ? $_POST['suptel'] : '';
		$getsupemail    = isset($_POST['supemail']) ? $_POST['supemail'] : '';
		if($getsupid  == "" || $getsupname =="" ||$getsupcate=="" ){
			$msg = "Please make sure [ID],[NAME],[CATEOGRY] are not blank";
			return;
		}
		//check ID existing 		
		try {
			$stmt = $pdo->prepare('SELECT COUNT(*) FROM supplier WHERE suppid = :supid');
			$stmt->execute(['supid' => $getsupid]);
			$count = $stmt->fetchColumn();
			
			if ($count > 0) {
				$msg = "Supplier ID " . $getsupid . " Duplicated.";
				$getsupid = '';
			} 

		} catch (PDOException $e) {
			exit("Database query failed: " . $e->getMessage());
		}if ($getsupid==""){
		}else{
			$insertData = [
				'supid'   => $getsupid,
				'supname' => $getsupname,
				'supcate' => $getsupcate,
				'suppic'  => $getsuppic,
				'supadd'  => $getsupadd,
				'suprmk'   => $getsuprmk,
				'suptel'   => $getsuptel ,
				'supemail' =>$getsupemail,
				'supstts'=>"Y",
				'supcrby' => htmlspecialchars($_SESSION['username'])
			];
			try {
			$sql = "INSERT INTO supplier (suppid, suppname, suppcate, supppic, suppadd,supptel,suppemail,suppstts,supprmk,suppcrby) 
					VALUES (:supid, :supname, :supcate, :suppic, :supadd, :suptel, :supemail, :supstts , :suprmk, :supcrby)";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the array directly
			$stmt->execute($insertData);

			// 3. Get the ID of the newly inserted row
			$msg = "Successfully add new supplier detail : " . $getsupid. " -" .$getsupname; 
			
			saveLog(
			    $pdo,
				$insertData['supcrby'], 
				'INSERT', 
				'supplier', 
				$insertData['supid'], 
				"Add new supplier profile for " . $insertData['supname']
			);
			} catch (PDOException $e) {
				$msg = "Insert failed: " . $e->getMessage();
			}
		}
		$action="";	
	}elseif($action=="update_supplier"){
		date_default_timezone_set('Asia/Kuala_Lumpur');
		// 直接用 date() 函数，传入格式字符串
		$currentDate = date('Y-m-d H:i:s');
		$getsupid       = isset($_POST['supid']) ? $_POST['supid'] : '';
		$getsupname     = isset($_POST['supname']) ? $_POST['supname'] : '';
		$getsupcate     = isset($_POST['supcate']) ? $_POST['supcate'] : '';
		$getsuppic      = isset($_POST['suppic']) ? $_POST['suppic'] : '';
		$getsupadd      = isset($_POST['supadd']) ? $_POST['supadd'] : '';
		$getsuprmk      = isset($_POST['suprmk']) ? $_POST['suprmk'] : '';
		$getsuptel      = isset($_POST['suptel']) ? $_POST['suptel'] : '';
		$getsupemail    = isset($_POST['supemail']) ? $_POST['supemail'] : '';
		if($getsupid  == "" || $getsupname =="" ||$getsupcate=="" ){
			$msg = "Please make sure [ID],[NAME],[CATEOGRY] are not blank";
			return;
		}
		$updateData = [
			'supid'   => $getsupid,
			'supname' => $getsupname,
			'supcate' => $getsupcate,
			'suppic'  => $getsuppic,
			'supadd'  => $getsupadd,
			'suprmk'   => $getsuprmk,
			'suptel' => $getsuptel ,
			'supemail' => $getsupemail ,
			'supupby' => htmlspecialchars($_SESSION['username']),
			'supupon' => $currentDate
		];
		try {
			$sql = "UPDATE supplier 
					SET suppname = :supname, suppcate = :supcate, supppic = :suppic, suppadd = :supadd, supptel = :suptel, suppemail = :supemail, supprmk = :suprmk, 
					suppupon = :supupon, suppupby = :supupby 
					WHERE suppid = :supid";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the data array
			$stmt->execute($updateData);

			// 3. Check how many rows were actually changed
			$rowCount = $stmt->rowCount();
			$msg = "Successfully update supplier detail " .$rowCount. " row(s): " . $getsupid. " -" .$getsupname; 
			
			saveLog(
			    $pdo,
				$updateData['supupby'], 
				'UPDATE', 
				'supplier', 
				$updateData['supid'], 
				"Updated supplier profile for " . $updateData['supname']
			);
		
		} catch (PDOException $e) {
			$msg = "Update failed: " . $e->getMessage();
		}
		$action="";	
	}

	$suppliers = $pdo->query("SELECT suppid, suppname, suppcate, scatename, supppic, suppadd, supptel,suppemail, suppstts, supprmk
	FROM supplier LEFT JOIN suppcate ON scateid=suppcate ORDER BY suppid")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container my-1">
    <h4 class="mb-4 text-start">Supplier Information Portal</h4>
	<div class="text-center text-danger fw-bold mb-4"><?php echo $msg; ?></div>
    <div class="container mt-1">
	    <div class="row">
			<div class="col-sm-3 border border-secondary rounded p-3">
			    <!-- 1. NEW: Quick Search Box -->
				<input type="text" id="search-suppid" class="form-control mb-2" placeholder="Type ID/Code and press ENTER..." onkeypress="handleSearchKeyPress(event)">
				<div id="search-error" class="text-danger small mb-2 d-none">Supplier not found!</div>		
				
				<div class="rounded p-3 bg-light" style="height: 700px; overflow-y: auto;">
					<?php foreach ($suppliers as $supp): ?>
						<!-- 核心修改：加上 supplier-list-item 类，并绑定 data 属性用于过滤 -->
						<div class="card border-0 shadow-sm overflow-hidden mb-2 supplier-list-item" 
							 data-id="<?php echo htmlspecialchars($supp['suppid']); ?>"
							 data-catename="<?php echo htmlspecialchars($supp['scatename'] ?? ''); ?>"
							 data-name="<?php echo htmlspecialchars($supp['suppname'] ?? ''); ?>">  
							 
					
							<!-- 保持点击事件不变，直接传入 ID 即可 -->
							<div class="card-body py-1 ps-3" style="cursor: pointer;" onclick="selectSupplier('<?php echo htmlspecialchars($supp['suppid']); ?>')">
								<h6 class="card-title mb-1 text-primary"><?php echo htmlspecialchars($supp['scatename']); ?></h6>
								<p class="card-text text-muted small mb-0 fw-bold"><?php echo htmlspecialchars($supp['suppid']); ?></p>
								<p class="card-text text-muted small mb-0"><?php echo htmlspecialchars($supp['suppname'] ?? ''); ?></p>
							</div>
							
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<!-- 核心：用 PHP 循环把所有部门数据渲染成隐藏的 HTML 元素，供 JS 搜索 -->
			<div id="supp-data-container" style="display: none;">
				<?php foreach ($suppliers as $supp01): ?>
					<div class="dept-item" 
						 data-id="<?php echo htmlspecialchars($supp01['suppid']); ?>"
						 data-name="<?php echo htmlspecialchars(trim($supp01['suppname'])); ?>"
						 data-cate="<?php echo htmlspecialchars(trim($supp01['suppcate'])); ?>"
						 data-catename="<?php echo htmlspecialchars(trim($supp01['scatename'])); ?>"
						 data-tel="<?php echo htmlspecialchars($supp01['supptel']); ?>"
						 data-email="<?php echo htmlspecialchars($supp01['suppemail']); ?>"
						 data-add="<?php echo htmlspecialchars($supp01['suppadd']); ?>"
						 data-stts="<?php echo htmlspecialchars($supp01['suppstts']); ?>"
						 data-pic="<?php echo htmlspecialchars($supp01['supppic']); ?>"
						 data-rmk="<?php echo htmlspecialchars($supp01['supprmk']); ?>">
					</div>
				<?php endforeach; ?>
			</div>
			
			<div class="col-sm-9">
			    <div class="card shadow-sm border-0">
			        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Supplier Detail Form (Add / Edit)</h5>
						<!-- 在 Header 加上一个醒目的 Clear 按钮 -->
						<button type="button" class="btn btn-light btn-sm fw-bold shadow-sm" onclick="clearSupplierForm()">
							➕ Add New / Clear
						</button>
					</div>
					<div class="card-body">
						<form method="POST" action="?page=supplier">
					        <div class="row g-3">
							    <div class="col-md-2">
									<label class="form-label fw-bold">Supplier ID *</label>
									<input type="text" class="form-control bg-light" id="form-supid" name="supid" value="" readonly required>
								</div>
								<div class="col-md-10">
									<label class="form-label fw-bold">Supplier Name *</label>
									<input type="text" class="form-control" id="form-supname" name="supname" value="" required>
								</div>
								
								<?php
									try {
										$scateStmt = $pdo->query("SELECT scateid, scatecode, scatename FROM suppcate ORDER BY scatecode ASC");
										$suppliercate = $scateStmt->fetchAll(); 
									} catch (PDOException $e) {
										$suppliercate = []; 
									}
									// Ensure $current_scate is defined so it doesn't throw an "Undefined variable" notice
									$current_scate = isset($current_scate) ? $current_scate : ''; 
									?>

									<div class="col-md-6">
										<label class="form-label fw-bold" for="form-supcate">Category  *</label>
										<select class="form-select" id="form-supcate" name="supcate" required>
											<option value="">-- Select Category --</option>
											<?php foreach ($suppliercate as $scate): ?>
												<option value="<?php echo htmlspecialchars($scate['scateid']); ?>" 
													<?php echo ($current_scate == $scate['scateid']) ? 'selected' : ''; ?>>
													<?php echo htmlspecialchars($scate['scatecode'] . ' - ' . $scate['scatename']); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									
									<div class="col-md-6">
										<label class="form-label fw-bold">P.I.C</label>
										<input type="text" class="form-control" id="form-suppic" name="suppic" value="" >
									</div>
									
									<div class="col-md-6"> 
										<label class="form-label fw-bold">Address</label> 
										<textarea class="form-control" id="form-supadd" name="supadd" rows="3" ></textarea> 
									</div>
									<div class="col-md-6"> 
										<label class="form-label fw-bold">Remark</label> 
										<textarea class="form-control" id="form-suprmk" name="suprmk" rows="3" ></textarea> 
									</div>
									
									<div class="col-md-6">
										<label class="form-label fw-bold">Phone No.</label>
										<input type="text" class="form-control" id="form-suptel" name="suptel" value="" >
									</div>
									
									<div class="col-md-6">
										<label class="form-label fw-bold">Email</label>
										<input type="email" class="form-control" id="form-supemail" name="supemail" value="" >
									</div>
										
								
								
								
								
							</div>
							<div class="mt-4 text-end">
								<input type="hidden" name="action"  id="form-action" value="update_supplier">
								<button type="submit" class="btn btn-success px-4" id="submit-btn">Save Changes</button>
							</div>
					    </form>
					</div>
				</div>
			</div>
		</div>
    </div>
</div>
<script>
	// 1. 实时过滤左侧列表 (实现 LIKE %ID% 或 %Name% 的效果)
	document.getElementById('search-suppid').addEventListener('input', function() {
		const keyword = this.value.trim().toLowerCase();
		const listItems = document.querySelectorAll('.supplier-list-item');
		let hasResult = false;

		listItems.forEach(item => {
			const id = item.getAttribute('data-id').toLowerCase();
			const name = item.getAttribute('data-name').toLowerCase();
			const catename = (item.getAttribute('data-catename') || '').toLowerCase();


			// 模糊匹配：只要 ID 或 Name 包含关键字就显示
			if (id.includes(keyword) || name.includes(keyword)|| catename.includes(keyword)) {
				item.style.setProperty('display', 'block', 'important'); // 显示
				hasResult = true;
			} else {
				item.style.setProperty('display', 'none', 'important');  // 隐藏
			}
		});

		// 控制“未找到”错误提示的显示
		const errorDiv = document.getElementById('search-error');
		if (keyword !== '' && !hasResult) {
			errorDiv.classList.remove('d-none');
		} else {
			errorDiv.classList.add('d-none');
		}
	});

	// 如果你坚持要按回车键（ENTER）才触发过滤，就把上面的监听换成这个函数：
	function handleSearchKeyPress(event) {
		if (event.key === 'Enter') {
			event.preventDefault(); // 阻止表单回车提交
			// 触发上面绑定的 input 逻辑
			document.getElementById('search-suppid').dispatchEvent(new Event('input'));
		}
	}

	// 2. 点击左侧过滤后的某一项，才把数据提取并填充到右侧 Form
	function selectSupplier(suppId) {
		// 从隐藏的 #supp-data-container 容器里捞出完整数据
		const targetItem = document.querySelector(`#supp-data-container .dept-item[data-id="${suppId}"]`);
		
		if (targetItem) {
			const data = {
				id: targetItem.getAttribute('data-id'),
				name: targetItem.getAttribute('data-name'),
				cate: targetItem.getAttribute('data-cate'),
				tel: targetItem.getAttribute('data-tel'),
				mail: targetItem.getAttribute('data-email'),
				add: targetItem.getAttribute('data-add'),
				stts: targetItem.getAttribute('data-stts'),
				rmk: targetItem.getAttribute('data-rmk'),
				pic: targetItem.getAttribute('data-pic') || targetItem.getAttribute('data-pic') 
			};
			
			// 填充到表单
			document.getElementById('form-supid').value = data.id;
			document.getElementById('form-supname').value = data.name;
			document.getElementById('form-supcate').value = data.cate;
			document.getElementById('form-suppic').value = data.pic || '';
			document.getElementById('form-supadd').value = data.add;
			document.getElementById('form-suprmk').value = data.rmk;
			document.getElementById('form-suptel').value = data.tel;
			document.getElementById('form-supemail').value = data.mail;

			// 切换为更新模式
			document.getElementById('form-action').value = 'update_supplier';
			document.getElementById('submit-btn').innerText = 'Save Changes';
			document.getElementById('submit-btn').className = 'btn btn-success px-4';
			document.getElementById('form-supid').readOnly = true; 
		}
	}

	// 3. 清空表单（新增模式）
	function clearSupplierForm() {
		document.getElementById('form-supid').value = '';
		document.getElementById('form-supid').readOnly = false; 
		document.getElementById('form-supname').value = '';
		document.getElementById('form-supcate').value = '';
		document.getElementById('form-suppic').value = '';
		document.getElementById('form-supadd').value = '';
		document.getElementById('form-suprmk').value = '';
		document.getElementById('form-suptel').value = '';
		document.getElementById('form-supemail').value = '';

		document.getElementById('form-action').value = 'add_supplier';
		document.getElementById('submit-btn').innerText = '➕ Add Supplier';
		document.getElementById('submit-btn').className = 'btn btn-primary px-4';
		
		// 重置搜索框并恢复左侧全列表显示
		document.getElementById('search-suppid').value = '';
		document.querySelectorAll('.supplier-list-item').forEach(item => item.style.display = 'block');
		document.getElementById('search-error').classList.add('d-none');
	}

	
</script>