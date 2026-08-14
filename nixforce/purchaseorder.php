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
	
	$mypodata =  $pdo->query("SELECT pono, posuppid, suppname, podate, podateline, postts, pormk,
	IFNULL((SELECT ROUND(SUM(podprice * podqty), 2) FROM podet WHERE podno = pono AND podstts <> 'X'), 0) AS ttlpo  
	FROM pohdr LEFT JOIN supplier on suppid=posuppid ORDER by pono DESC")->fetchAll(PDO::FETCH_ASSOC);
	
?>
<div class="container my-1">
    <h4 class="mb-4 text-start">Purchase Order</h4>
	<div class="text-center text-danger fw-bold mb-4"><?php echo $msg; ?></div>
    <div class="container mt-1">
	    <div class="row">
			<div class="col-sm-3 border border-secondary rounded p-3">
			    <!-- 1. NEW: Quick Search Box -->
				<input type="text" id="search-pono" class="form-control mb-2" placeholder="Type PO#/Name and press ENTER..." onkeypress="handleSearchKeyPress(event)">
				<div id="search-error" class="text-danger small mb-2 d-none">PO# not found!</div>		
			    
				<div class="rounded p-3 bg-light" style="height: 700px; overflow-y: auto;">
					<?php foreach ($mypodata as $po): ?>
						<!-- 核心修改：加上 supplier-list-item 类，并绑定 data 属性用于过滤 -->
						<div class="card border-0 shadow-sm overflow-hidden mb-2 supplier-list-item" 
							 data-id="<?php echo htmlspecialchars($po['pono']); ?>"
							 data-suppname="<?php echo htmlspecialchars($po['suppname'] ?? ''); ?>"
							 data-podate="<?php echo htmlspecialchars($po['podate'] ?? ''); ?>">  
				
							<!-- 保持点击事件不变，直接传入 ID 即可 -->
							<div class="card-body py-1 ps-3" style="cursor: pointer;" onclick="selectPO('<?php echo htmlspecialchars($supp['pono']); ?>')">
								<h6 class="card-title mb-1 text-primary"><?php echo htmlspecialchars($po['pono']); ?></h6>
								<p class="card-text text-muted small mb-0 fw-bold"><?php echo htmlspecialchars($po['suppname']); ?></p>
								<p class="card-text text-muted small mb-0"><?php echo htmlspecialchars($po['podate'] ?? ''); ?></p>
							</div>		
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		
			<div id="po-data-container" style="display: none;">
			    <?php foreach ($mypodata as $po): ?>

					<div class="po-item" 
						data-id="<?php echo htmlspecialchars($po['pono']); ?>"
						data-suppid="<?php echo htmlspecialchars($po['suppid']); ?>"
						data-suppname="<?php echo htmlspecialchars(trim($po['suppname'])); ?>"
						data-podate="<?php echo htmlspecialchars($po['podate']); ?>"
						data-poline="<?php echo htmlspecialchars($po['podateline']); ?>"
						data-stts="<?php echo htmlspecialchars($po['postts']); ?>"
						data-total="<?php echo number_format($po['ttlpo'], 2, '.', ''); ?>"
						data-rmk="<?php echo htmlspecialchars($po['pormk']); ?>">
					</div>
				<?php endforeach; ?>
			
			</div>
		</div>
    </div>
</div>

<script>
    // 如果你坚持要按回车键（ENTER）才触发过滤，就把上面的监听换成这个函数：
	function handleSearchKeyPress(event) {
		if (event.key === 'Enter') {
			event.preventDefault(); // 阻止表单回车提交
			// 触发上面绑定的 input 逻辑
			document.getElementById('search-pono').dispatchEvent(new Event('input'));
		}
	}
	// 2. 点击左侧过滤后的某一项，才把数据提取并填充到右侧 Form
	function selectPO(pono) {
		// 从隐藏的 #supp-data-container 容器里捞出完整数据
		const targetItem = document.querySelector(`#po-data-container .po-item[data-id="${pono}"]`);
		
		if (targetItem) {
			const data = {
				id: targetItem.getAttribute('data-id'),
				suppid: targetItem.getAttribute('data-suppid'),
				suppname: targetItem.getAttribute('data-suppname'),
				podate: targetItem.getAttribute('data-podate'),
				poline: targetItem.getAttribute('data-poline'),
				stts: targetItem.getAttribute('data-stts'),
				total: targetItem.getAttribute('data-total'),
				rmk: targetItem.getAttribute('data-rmk') || targetItem.getAttribute('data-rmk') 
			};
			
									
			// 填充到表单
			document.getElementById('form-id').value = data.id;
			document.getElementById('form-suppid').value = data.suppid;
			document.getElementById('form-podate').value = data.podate;
			document.getElementById('form-poline').value = data.poline;
			document.getElementById('form-stts').value = data.stts;
			document.getElementById('form-total').value = data.total;
			document.getElementById('form-rmk').value = data.rmk;

			// 切换为更新模式
			document.getElementById('form-action').value = 'update_po';
			document.getElementById('submit-btn').innerText = 'Save Changes';
			document.getElementById('submit-btn').className = 'btn btn-success px-4';
			document.getElementById('form-supid').readOnly = true; 
		}
	}

	// 3. 清空表单（新增模式）
	function clearPOForm() {
		document.getElementById('form-id').value = '';
		document.getElementById('form-id').readOnly = false; 
		document.getElementById('form-suppid').value = '';
		document.getElementById('form-podate').value = '';
		document.getElementById('form-poline').value = '';
		document.getElementById('form-stts').value = '';
		document.getElementById('form-total').value = '';
		document.getElementById('form-rmk').value = '';

		document.getElementById('form-action').value = 'add_po';
		document.getElementById('submit-btn').innerText = '➕ Add PO';
		document.getElementById('submit-btn').className = 'btn btn-primary px-4';
		
		// 重置搜索框并恢复左侧全列表显示
		document.getElementById('search-pono').value = '';
		document.querySelectorAll('.po-list-item').forEach(item => item.style.display = 'block');
		document.getElementById('search-error').classList.add('d-none');
	}
</script>