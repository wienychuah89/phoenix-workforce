<script>
	function add_po(){
	    //header
		var errorItm = "";
		var getdeadline = document.getElementById("form-dateline").value;
		var getpodate   = document.getElementById("form-podate").value;

		var deadlineDate = new Date(getdeadline);
		var poDate = new Date(getpodate);
		var currentDate = new Date(); // Gets the current date, month, and year
		
		var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth(); // Note: January is 0, August is 7
		
        if (deadlineDate < poDate) {
			if (errorItm =="" ){
				errorItm = "-Deadline date must be equal/after the PO date.";
			}else{
				errorItm = errorItm+"\n-Deadline date must be equal/after the PO date.";
			}
		}
		var isPoValid = (poDate.getFullYear() === currentYear && poDate.getMonth() === currentMonth);
		var isDeadlineValid = (deadlineDate.getFullYear() === currentYear && deadlineDate.getMonth() === currentMonth);

		if (!isPoValid || !isDeadlineValid) {
			if (errorItm =="" ){
				errorItm = "-Both dates must be within the current month and year.";
			}else{
				errorItm = errorItm+"\n-Both dates must be within the current month and year.";
			}
		}
		
		var getrow = parseFloat(document.getElementById("form-drow").value); 

		var cntactual =0;
		for (let i = 1; i <= getrow; i++) {
			let element = document.getElementById("qty_" + i);
	
			if (!element || !element.value) {
				console.log(i +"Element not found, or value is empty");
				continue; 
			}else{
                cntactual = cntactual + 1;
				let getqty = parseFloat(element.value); 
				var getcode  = document.getElementById("code_"+i).value; 
				var getprice = parseFloat(document.getElementById("price_"+i).value); 
				console.log(i +"item : " + cntactual + " = qty = " + getqty + " price : " + getprice);
				if (getcode==""){
					if (errorItm =="" ){
						errorItm = "-Item #"+cntactual;
					}else{
						errorItm = errorItm+"\n-Item #"+cntactual;
					}
				}
				if(getqty<=0 ){
					if (errorItm =="" ){
						errorItm = "-Qty #"+cntactual;
					}else{
						errorItm = errorItm+"\n-Qty #"+cntactual;
					}
				}
		
				if(getprice<=0){
					if (errorItm =="" ){
						errorItm = "-Price #"+cntactual;
					}else{
						errorItm = errorItm+"\n-Price #"+cntactual;
					}
				}
			}
		}
		if (cntactual==0){
			if (errorItm =="" ){
				errorItm = "-No detail found.";
			}else{
				errorItm = errorItm+"\n-No detail found.";
			}
		}
		var getpono = document.getElementById("form-pono").value;
		if(errorItm==""){
			if (getpono==""){
				var getsupp = document.getElementById("form-suppid").value;
				var ex= confirm("Proceed save new Purchase Order for supplier ID: " + getsupp +"? ");
				if(ex){
					document.getElementById("po-main-form").submit();
				}
			}else{
				var ex= confirm("Proceed edit Purchase Order : " + getpono +"? ");
				if(ex){
					document.getElementById("po-main-form").submit();
				}
			}
		}else{
			alert("ERROR : \n"+errorItm+"\nPlease check above error to proceed.");
		}
	}

</script>
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
	
	date_default_timezone_set('Asia/Kuala_Lumpur');
    $currentDate = date('Y-m-d'); 

	// 1. 正常计算 7 天后的日期
	$sevenDaysLater = date('Y-m-d', strtotime('+7 days'));

	// 2. 检查 7 天后的月份是否和今天相同
	if (date('m', strtotime($sevenDaysLater)) !== date('m') OR date('Y', strtotime($sevenDaysLater)) !== date('Y')) {
		// 如果月份不同（说明跨月了），就取本月的最后一天
		$defaultDeadline = date('Y-m-t'); // 't' 在 PHP 中代表当前月份的总天数（即最后一天）
	} else {
		// 如果还在同一个月，就正常使用 7 天后的日期
		$defaultDeadline = $sevenDaysLater;
	}
    if ($action=="update_po"){ 
	    $getpono      = isset($_POST['pono']) ? $_POST['pono'] : '';
		$getsuppid    = isset($_POST['suppid']) ? $_POST['suppid'] : '';
		$getdeadline  = isset($_POST['dateline']) ? $_POST['dateline'] : '';
		$getdate      = isset($_POST['podate']) ? $_POST['podate'] : '';
		$getcurr      = isset($_POST['curr']) ? $_POST['curr'] : '';
		$getrmk       = isset($_POST['pormk']) ? $_POST['pormk'] : '';
		$getrow       = isset($_POST['drow']) ? $_POST['drow'] : '';
	    $today = date('Y-m-d H:i:s');
		$pass  = "Y";
		$updateData = [
			'pono'   => $getpono,
			'posuppid' => $getsuppid,
			'podate' => $getdate,
			'podateline'  => $getdeadline,
			'pocurr'  => $getcurr,
			'pormk'   => $getrmk,
			'poupby' =>htmlspecialchars($_SESSION['username']),
			'poupon'=>$today
		];
		try {
			$sql = "UPDATE pohdr 
					SET posuppid = :posuppid, podate = :podate , podateline = :podateline, pocurr = :pocurr, pormk = :pormk, poupby = :poupby, poupon = :poupon
					WHERE pono = :pono";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($updateData);

		} catch (PDOException $e) {
			error_log("Database Update Header Error: " . $e->getMessage());
			$pass = "N";
		}	
		if($pass == "Y"){
			//delete from podet
			$deleteData = [
				'podno'   => $getpono,
			];
			try {
				$sqlDEL = "DELETE FROM podet WHERE podno = :podno";
				$stmt = $pdo->prepare($sqlDEL);
				$stmt->execute($deleteData);
			} catch(PDOException $e) {
				error_log("Database Delete Old podet Error: " . $e->getMessage());
				$pass = "N";
			}

			if($pass == "Y"){
			   //insert into podet
				$cntActual = 0;
				for ($x = 1; $x <= $getrow; $x++) {
					$getqty = isset($_POST['qty_'.$x]) ? $_POST['qty_'.$x] : '';
					if (empty($getqty)) {
						continue; 
					}else{
						$cntActual = $cntActual +1;
						$getcode = isset($_POST['code_'.$x]) ? $_POST['code_'.$x] : '';
						$getqty = isset($_POST['qty_'.$x]) ? $_POST['qty_'.$x] : '';
						$getprice = isset($_POST['price_'.$x]) ? $_POST['price_'.$x] : '';
						
						$stmt = $pdo->prepare('SELECT produom1 FROM product WHERE prodid = :itemcode');
						$stmt->execute(['itemcode' => $getcode]);
						$getuom = $stmt->fetchColumn();
						
						$insertDet = [
							'podno'   => $getpono,
							'poditm' => $getcode,
							'podqty' => $getqty,
							'poduom'  => $getuom,
							'podrcv'  => 0,
							'podprice'   => $getprice,
							'podcurr'   => $getcurr,
							'podstts'   => 'O' ,
							'podrmk'   => '',
							'podcrby' =>htmlspecialchars($_SESSION['username']),
							'podupby'=>htmlspecialchars($_SESSION['username'])

						];
						$pass = "N";
						try {
							$sql2 = "INSERT INTO podet (podno, poditm, podqty, poduom, podrcv,podprice,podcurr,podstts,podrmk,podcrby,podupby) 
									VALUES (:podno, :poditm, :podqty, :poduom, :podrcv, :podprice, :podcurr, :podstts , :podrmk, :podcrby, :podupby)";
							$stmt = $pdo->prepare($sql2);
							$stmt->execute($insertDet);
							$pass = "Y";
						} catch (PDOException $e) {
							error_log("Database Insert(Update) Detail ".$cntActual." Error: " . $e->getMessage());
							$pass = "N";
						}
					}
				}	
			}
		}
		if($pass=="Y"){
			saveLog(
				$pdo,
				htmlspecialchars($_SESSION['username']), 
				'UPDATE', 
				'pohdr/podet', 
				$getpono, 
				"Update purchase order " . $getpono
			);
		}
		$msg = "Successfully Udate Purchase Order : " . $getpono. " Under Supplier ID [" .$getsuppid."]"; 			
		$action = "";
	}
	
	if ($action=="add_po"){
        $getpono      = isset($_POST['pono']) ? $_POST['pono'] : '';
		$getsuppid    = isset($_POST['suppid']) ? $_POST['suppid'] : '';
		$getdeadline  = isset($_POST['dateline']) ? $_POST['dateline'] : '';
		$getdate      = isset($_POST['podate']) ? $_POST['podate'] : '';
		$getcurr      = isset($_POST['curr']) ? $_POST['curr'] : '';
		$getrmk       = isset($_POST['pormk']) ? $_POST['pormk'] : '';
		$getrow       = isset($_POST['drow']) ? $_POST['drow'] : '';
		
		if ($getpono ==""){
			$prefix     = "PO";
			$getnonext  = "";
			$sql = $pdo->prepare('SELECT gen_nonext FROM pn_gen WHERE gen_prefix = :prefix');
			$sql->execute([':prefix' => $prefix]);		
			$getnonext = $sql->fetchColumn();
			if($getnonext=="" OR empty($getnonext)){
				$insertGen = [
					'pprefix'   => $prefix,
					'psuffix' => '',
					'pnonext' => "1",
					'puby' => htmlspecialchars($_SESSION['username'])
				];
				try {
					$sql = "INSERT INTO pn_gen (gen_prefix,gen_suffix, gen_nonext,gen_uby) 
						VALUES (:pprefix, :psuffix, :pnonext, :puby)";
					$stmt = $pdo->prepare($sql);
					$stmt->execute($insertGen);

					// 3. Get the ID of the newly inserted row
					$msg = "Add new prefix : " . $prefix;
					saveLog(
						$pdo,
						htmlspecialchars($_SESSION['username']), 
						'INSERT', 
						'pn_gen', 
						$prefix, 
						"Add new prefix ".$prefix
					);
				} catch (PDOException $e) {
					$msg = "Insert failed: " . $e->getMessage();
				}
				$getpono  = "PO-".sprintf("%06d", 1);
			}else{
				$getpono = "PO-".sprintf("%06d", $getnonext);
			}
		}
		$pass = "Y";
		$insertHdr = [
			'pono'   => $getpono,
			'posuppid' => $getsuppid,
			'podate' => $getdate,
			'podateline'  => $getdeadline,
			'pocurr'  => $getcurr,
			'pormk'   => $getrmk,
			'postts'   => 'O' ,
			'pocrby' =>htmlspecialchars($_SESSION['username']),
			'poupby'=>htmlspecialchars($_SESSION['username'])
		];
		try {
			$sql = "INSERT INTO pohdr (pono, posuppid, podate, podateline, pocurr, pormk, postts, pocrby, poupby) 
					VALUES (:pono, :posuppid, :podate, :podateline, :pocurr, :pormk, :postts, :pocrby, :poupby)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($insertHdr);
		} catch (PDOException $e) {
			error_log("Database Insert Header Error: " . $e->getMessage());
			$pass = "N";
		}
		if($pass == "Y"){
			//insert into podet
			$cntActual = 0;
			for ($x = 1; $x <= $getrow; $x++) {
				$getqty = isset($_POST['qty_'.$x]) ? $_POST['qty_'.$x] : '';
				if (empty($getqty)) {
					continue; 
				}else{
					$cntActual = $cntActual +1;
					$getcode = isset($_POST['code_'.$x]) ? $_POST['code_'.$x] : '';
					$getqty = isset($_POST['qty_'.$x]) ? $_POST['qty_'.$x] : '';
					$getprice = isset($_POST['price_'.$x]) ? $_POST['price_'.$x] : '';
					
					$stmt = $pdo->prepare('SELECT produom1 FROM product WHERE prodid = :itemcode');
					$stmt->execute(['itemcode' => $getcode]);
					$getuom = $stmt->fetchColumn();
					
					//echo "cntActual : " .$cntActual." | item : " .$getcode ." | qty : " .$getqty." | price : " .$getprice." | uom : " .$getuom."<br/>";

					$insertDet = [
						'podno'   => $getpono,
						'poditm' => $getcode,
						'podqty' => $getqty,
						'poduom'  => $getuom,
						'podrcv'  => 0,
						'podprice'   => $getprice,
						'podcurr'   => $getcurr,
						'podstts'   => 'O' ,
						'podrmk'   => '',
						'podcrby' =>htmlspecialchars($_SESSION['username']),
						'podupby'=>htmlspecialchars($_SESSION['username'])
					];
					$pass = "N";
					try {
						$sql2 = "INSERT INTO podet (podno, poditm, podqty, poduom, podrcv,podprice,podcurr,podstts,podrmk,podcrby,podupby) 
								VALUES (:podno, :poditm, :podqty, :poduom, :podrcv, :podprice, :podcurr, :podstts , :podrmk, :podcrby, :podupby)";
						$stmt = $pdo->prepare($sql2);
						$stmt->execute($insertDet);
						$pass = "Y";
					} catch (PDOException $e) {
						error_log("Database Insert Detail ".$cntActual." Error: " . $e->getMessage());
						$pass = "N";
					}
				}
			}	
		}
		if($pass=="Y"){
			saveLog(
				$pdo,
				htmlspecialchars($_SESSION['username']), 
				'INSERT', 
				'pohdr/podet', 
				$getpono, 
				"Add new purchase order " . $getpono
			);
			//**********update pn_gen*
			$today = date('Y-m-d H:i:s');
			$updateDataGEN = [
				'gen_nonext'   => $getnonext+1,
				'gen_prefix' => $prefix,
				'gen_uby' => htmlspecialchars($_SESSION['username']),
				'gen_udate'  => $today
			];
			try {
				$sql = "UPDATE pn_gen 
						SET gen_nonext = :gen_nonext, gen_uby = :gen_uby, gen_udate = :gen_udate
						WHERE gen_prefix = :gen_prefix";
				$stmt = $pdo->prepare($sql);
				$stmt->execute($updateDataGEN);
				//$rowCount = $stmt->rowCount();
				saveLog(
					$pdo,
					htmlspecialchars($_SESSION['username']),
					'UPDATE', 
					'pn_gen', 
					$prefix,
					"Updated pn_gen:gen_nonext to " . $getnonext+1
				);
			
			} catch (PDOException $e) {
				error_log("Update pn_gen Error: " . $e->getMessage());
			}
		}
		$msg = "New Purchase Order Generated : " . $getpono. " Under Supplier ID [" .$getsuppid."]"; 			
		$action = "";
	

	}
		
	$mypodata =  $pdo->query("SELECT pono, posuppid, suppname, podate, podateline, postts, pormk, pocurr,
	IFNULL((SELECT ROUND(SUM(podprice * podqty), 2) FROM podet WHERE podno = pono AND podstts <> 'X'), 0) AS ttlpo,
    IFNULL((SELECT COUNT(*) FROM podet WHERE podno=pono AND podstts<>'X'),0) AS ttldtl	
	FROM pohdr LEFT JOIN supplier on suppid=posuppid ORDER by pono DESC")->fetchAll(PDO::FETCH_ASSOC);
	
	try {
		// 确保你的数据库连接里查出了产品库（对齐你之前的真实字段：produom1, prodstts）
		$prodStmt = $pdo->query("SELECT prodid, prodname, produom1 FROM product WHERE prodstts = 'Y' ORDER BY prodid ASC");
		$allProductsForJs = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		$allProductsForJs = [];
	}

	
	
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
						<!-- 核心修改：加上 po-list-item 类，并绑定 data 属性用于过滤 -->
						<div class="card border-0 shadow-sm overflow-hidden mb-2 po-list-item" 
							 data-id="<?php echo htmlspecialchars($po['pono']); ?>"
							 data-suppname="<?php echo htmlspecialchars($po['suppname'] ?? ''); ?>"
							 data-podate="<?php echo htmlspecialchars($po['podate'] ?? ''); ?>">  
				
							<!-- 保持点击事件不变，直接传入 ID 即可 -->
							<div class="card-body py-1 ps-3" style="cursor: pointer;" onclick="selectPO('<?php echo htmlspecialchars($po['pono']); ?>')">
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
						data-suppid="<?php echo htmlspecialchars($po['posuppid']); ?>"
						data-suppname="<?php echo htmlspecialchars(trim($po['suppname'])); ?>"
						data-podate="<?php echo htmlspecialchars($po['podate']); ?>"
						data-poline="<?php echo htmlspecialchars($po['podateline']); ?>"
						data-curr="<?php echo htmlspecialchars($po['pocurr']); ?>"
						data-stts="<?php echo htmlspecialchars($po['postts']); ?>"
						data-total="<?php echo number_format($po['ttlpo'], 2, '.', ''); ?>"
						data-rmk="<?php echo htmlspecialchars($po['pormk']); ?>"
						data-drow="<?php echo number_format($po['ttldtl'], 0, '.', ''); ?>">

					</div>
				<?php endforeach; ?>
			</div>
			<div class="col-sm-9">
			    <div class="card shadow-sm border-0">
				    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">Purchase Order Form (Add / Edit)</h5>
						<!-- 在 Header 加上一个醒目的 Clear 按钮 -->
						<button type="button" class="btn btn-light btn-sm fw-bold shadow-sm" onclick="clearPOForm()">
							➕ Add New / Clear
						</button>
					</div>
				</div>
				<div>&nbsp;</div>
				<div class="card-body">
                    <form method="POST" action="?page=purchaseorder" id="po-main-form">
					    <div class="row g-5">
							<div class="col-md-2">
								<label class="form-label fw-bold">PO Number</label>
								<input type="text" class="form-control bg-light" id="form-pono" name="pono" value="" readonly>
							</div>
							
							<div class="col-md-1">&nbsp;</div>
						
							<div class="col-md-4">
								<label class="form-label fw-bold">PO Deadline <span class="text-danger">[Default 7 days]</span>*</label>
								<input type="date" class="form-control" id="form-dateline" name="dateline" value="<?php echo $defaultDeadline; ?>">
							</div>
							
							<div class="col-md-1">&nbsp;</div>
							
							<div class="col-md-4">
								<label class="form-label fw-bold">PO Date (MM/DD/YYYY) </label>
								<input type="date" class="form-control" id="form-podate" name="podate" value="<?php echo $currentDate; ?>">
							</div>
							
			
							
						</div>
						
						<div class="row g-3">
						    <?php
								try {
									$suppStmt = $pdo->query("SELECT suppid, suppname, suppcurr FROM supplier WHERE suppstts='Y' ORDER BY suppid;");
									$suppliers = $suppStmt->fetchAll();
								} catch (PDOException $e) {
									// Fallback if the query fails
									$suppliers = [];
								}
								$current_supp = isset($po['supp']) ? $po['supp'] : ''; 
							?>
							<div class="col-md-12">
								<label class="form-label fw-bold" for="form-posupp">Supplier  *</label>
								<select class="form-select" id="form-suppid" name="suppid" required>
									<option value="">-- Select Supplier --</option>
									<?php foreach ($suppliers as $supp): ?>			
										<option value="<?php echo htmlspecialchars($supp['suppid']); ?>" 
										    data-currency="<?php echo htmlspecialchars($supp['suppcurr']); ?>"
											<?php echo ($current_supp == $supp['suppid']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($supp['suppid'] . ' - ' . $supp['suppname']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							
							<?php
								try {
									$currStmt = $pdo->query("SELECT curcode, curname FROM pcurrency WHERE curstts='Y' ORDER BY curcode;");
									$currency = $currStmt->fetchAll();
								} catch (PDOException $e) {
									$currency = [];
								}
								$current_curr = isset($po['curr']) ? $po['curr'] : ''; 
						
							?>
							
							<div class="col-md-4">
								<label class="form-label fw-bold" for="form-curr">Currency  *</label>
								<select class="form-select" id="form-curr" name="curr" required>
									<option value="">-- Select Currency --</option>
									<?php foreach ($currency as $curr): ?>
										<option value="<?php echo htmlspecialchars($curr['curcode']); ?>" 
											<?php echo ($current_curr == $curr['curcode']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($curr['curcode'] . ' - ' . $curr['curname']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							
							<div class="col-md-8"> 
							    <label class="form-label fw-bold">Remark</label> 
								<input type="text" class="form-control" id="form-pormk" name="pormk">
							</div>
							
							<hr class="border-primary">
						    <input type="hidden" class="form-control" name="drow" id="form-drow" >
							
							<!-- 在表单中增加明细展示区域 -->
							<div class="row mt-4">
								<div class="col-md-12">
									<h5 class="border-bottom pb-2 fw-bold">PO Items Detail</h5>
									<div class="table-responsive">
										<table class="table table-bordered table-striped table-hover align-middle">
											<thead class="table-light">
												<tr>
													<th style="width: 50px;">#</th>
													<th>Item Code</th>
													<th>Description</th>
													<th class="text-end" style="width: 100px;">Qty</th>
													<th style="width: 80px;">UOM</th>
													<th class="text-end" style="width: 120px;">Unit Price</th>
													<th class="text-end" style="width: 120px;">Amount</th>
													<th class="text-center" style="width: 80px;">Action</th> <!-- ✨ 新增操作列 -->
												</tr>
											</thead>
											<tbody id="po-details-tbody">
												<tr>
													<td colspan="8" class="text-center text-muted">Click ⬅️ PO# To Preview</td>
												</tr>
											</tbody>
										</table>
									</div>
									
									<!-- ✨ 新增：在表格下方提供一个添加新行的按钮（默认隐藏，只有状态是 'O' 时才显示） -->
									<div class="mt-2 mb-3">
										<button type="button" class="btn btn-outline-primary btn-sm d-none" id="btn-add-po-item" onclick="addNewRow()">
											<i class="bi bi-plus-circle"></i> ➕ Add Item 
										</button>
									</div>
									<hr class="border-primary">
									<div class="d-flex justify-content-end align-items-center my-2">
										<label for="gtotal" class="fw-bold me-2">Grand Total:</label>
										<input type="number" step="0.01" min="0" class="form-control form-control-sm text-end w-auto bg-secondary  text-white fw-bold" id="form-gtotal" name="gtotal" value="0.00" readonly>
									</div>
								</div>
							</div>
						</div>
						<div class="mt-4 text-end">
                            <input type="hidden" name="action"  id="form-action" value="update_po">
							<input type="hidden" id="form-podet-json" name="podet_json" value="">
                            <button type="button" class="btn btn-success px-4 d-none"" id="submit-btn" onclick="javascript:add_po();">Save Changes</button>
                        </div>
					</form>
				</div>
				
				
				
			</div>
			
			
		</div>
    </div>
</div>

<script>
    //let globalProductList = [];
	let globalProductList = <?php echo json_encode($allProductsForJs); ?>;
    // 如果你坚持要按回车键（ENTER）才触发过滤，就把上面的监听换成这个函数：
	function handleSearchKeyPress(event) {
		if (event.key === 'Enter') {
			event.preventDefault(); // 阻止表单回车提交
			// 触发上面绑定的 input 逻辑
			document.getElementById('search-pono').dispatchEvent(new Event('input'));
		}
	}
	// 1. 实时过滤左侧列表 (实现 LIKE %ID% 或 %Name% 的效果)
	document.getElementById('search-pono').addEventListener('input', function() {
		const keyword = this.value.trim().toLowerCase();
		const listItems = document.querySelectorAll('.po-list-item');
		let hasResult = false;
		
	
		listItems.forEach(item => {
			const id = item.getAttribute('data-id').toLowerCase();
			const suppname = item.getAttribute('data-suppname').toLowerCase();

			// 模糊匹配：只要 ID 或 Name 包含关键字就显示
			if (id.includes(keyword) || suppname.includes(keyword)) {
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
				curr: targetItem.getAttribute('data-curr'),
				stts: targetItem.getAttribute('data-stts'),
				total: targetItem.getAttribute('data-total'),
				rmk: targetItem.getAttribute('data-rmk') || targetItem.getAttribute('data-rmk') ,
				drow: targetItem.getAttribute('data-drow'),
			};										
			// 填充到表单
			document.getElementById('form-pono').value = data.id;
			document.getElementById('form-suppid').value = data.suppid;
			document.getElementById('form-podate').value = data.podate;
			document.getElementById('form-dateline').value = data.poline;
			//document.getElementById('form-stts').value = data.stts;
			document.getElementById('form-gtotal').value = data.total;
			document.getElementById('form-curr').value = data.curr;
			document.getElementById('form-pormk').value = data.rmk;
			document.getElementById('form-drow').value = data.drow;

			// 切换为更新模式
			document.getElementById('form-action').value = 'update_po';
			document.getElementById('submit-btn').innerText = 'Save Changes';
			document.getElementById('submit-btn').className = 'btn btn-success px-4';
			document.getElementById('form-pono').readOnly = true; 
			
			const submitBtn = document.getElementById('submit-btn');
			if (data.stts === "O") {
			    submitBtn.disabled = false;
			}else{
				submitBtn.disabled = true;
			}
			loadPODetails(data.id, data.stts);
		}
	}

    // 1. 主渲染函数
	function loadPODetails(poNumber, poStatus) {
		fetch(`get_podet.php?pono=${poNumber}`)
        .then(response => response.json())
        .then(res => {
            const tbody = document.getElementById('po-details-tbody');
            const addBtn = document.getElementById('btn-add-po-item');
            if (!tbody) return;
            
            tbody.innerHTML = ''; // 清空旧数据

            // 💡 将后端返回的产品库缓存到全局变量中，供后续添加新行使用
            globalProductList = res.products || [];

            const isEditable = (poStatus === "O"); // 状态是否为 Open

            // 控制 "Add Item" 按钮的显示与隐藏
            if (addBtn) {
                if (isEditable) addBtn.classList.remove('d-none');
                else addBtn.classList.add('d-none');
            }

            if (res.success && res.data.length > 0) {
                res.data.forEach((item, index) => {
                    let itemCodeHtml = '';
                    let actionHtml = '';

                    // 如果是可编辑状态 'O'
                    if (isEditable) {
                        // 生成 Item Code 下拉选择框
                        itemCodeHtml = `<select class="form-select form-select-sm item-code-select" name="code_${index+1}" id="code_${index+1}" onchange="handleItemChange(this)">`;
                        itemCodeHtml += `<option value="">-- Select Item --</option>`;
      
						globalProductList.forEach(prod => {
							const selected = (prod.prodid === item.poditm) ? 'selected' : '';
							// 💡 埋入产品表的主单位：data-uom="${prod.produom1}"
							itemCodeHtml += `<option value="${prod.prodid}" data-name="${prod.prodname}" data-uom="${prod.produom1}" ${selected}>${prod.prodid}</option>`;
						});

                        itemCodeHtml += `</select>`;

                        // 生成红色的 Remove 按钮
                        actionHtml = `<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button>`;
                    } else {
                        // 结案状态显示纯文本和禁用按钮
                        itemCodeHtml = item.poditm || '';
                        actionHtml = `<button type="button" class="btn btn-outline-secondary btn-sm" disabled>Locked</button>`;
                    }

                    const row = `
                        <tr>
                            <td class="row-number">${index + 1}</td>
                            <td>${itemCodeHtml}</td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-desc" name="desc_${index+1}" id="desc_${index+1}" value="${item.prodname || ''}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-end item-qty" name="qty_${index+1}" id="qty_${index+1}" value="${item.podqty || 0}" ${isEditable ? '' : 'disabled'} oninput="calculateRowAmount(this)">
                            </td>
                            <td>
                                <span class="badge bg-secondary item-uom" name="uom_${index+1}" id="uom_${index+1}">${item.poduom || ''}</span>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm text-end item-price" name="price_${index+1}" id="price_${index+1}"  value="${parseFloat(item.podprice || 0).toFixed(4)}" ${isEditable ? '' : 'disabled'} oninput="calculateRowAmount(this)">
                            </td>
                            <td class="text-end fw-bold item-amount" name="amt_${index+1}">${parseFloat(item.amt || 0).toFixed(2)}</td>
                            <td class="text-center">${actionHtml}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">暂无明细数据</td></tr>';
            }
        })
        .catch(error => console.error('Error loading PO details:', error));
	}

	// 2. 下拉框选择改变时的联动函数（自动填入 Description 和 UOM）
	function handleItemChange(selectElem) {
		const row = selectElem.closest('tr');
		const selectedOption = selectElem.options[selectElem.selectedIndex];
		
		const prodName = selectedOption.getAttribute('data-name') || '';
		const prodUom = selectedOption.getAttribute('data-uom') || '';
		
		row.querySelector('.item-desc').value = prodName;
		row.querySelector('.item-uom').innerText = prodUom;
	}

	// 3. 数量和单价变动时的算账函数
	function calculateRowAmount(inputElem) {
		// 1. 确保能准确找到当前行 <tr>
		const currentRow = inputElem.closest('tr');
		if (!currentRow) return; // 安全守护：如果找不到行，直接退出
		
		// 2. 在当前行里精准抓取数量和单价的输入框
		const currentQtyInput = currentRow.querySelector('.item-qty');
		const currentPriceInput = currentRow.querySelector('.item-price');
		const currentAmountCell = currentRow.querySelector('.item-amount');
		
		// 3. 安全检查：必须确保两个输入框在页面上真实存在，才执行计算
		if (currentQtyInput && currentPriceInput && currentAmountCell) {
			// 4. 使用全新的局部变量名读取数值， parseFloat 自动吃掉前导 0
			const parsedQty = parseFloat(currentQtyInput.value) || 0;
			const parsedPrice = parseFloat(currentPriceInput.value) || 0;
			
			// 5. 计算金额
			const finalAmount = parsedQty * parsedPrice;
			
			// 6. 渲染最右侧的小计（保留两位小数）
			currentAmountCell.innerText = finalAmount.toFixed(2);
			// 计算完当前行后，立即更新总计
			updateGrandTotal();
		}
	}


	// 4. ✨ 新增：从表格中移除当前行的函数
	function removeRow(buttonElem) {
		if (confirm("Confirm to remove selected row？")) {
			//var currentcnt =document.getElementById('form-drow').value
		    //document.getElementById('form-drow').value = currentcnt-1;
			const row = buttonElem.closest('tr');
			const tbody = row.parentNode;
			row.remove(); // 移除当前行
			
			// 重新编排左侧的行号数字 (#)
			const rows = tbody.querySelectorAll('tr');
			if (rows.length === 0) {
				tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No record found. Please click add item to proceed.</td></tr>';
			} else {
				rows.forEach((r, idx) => {
					const numCell = r.querySelector('.row-number');
					if (numCell) numCell.innerText = idx + 1;
				});
			}
		}
		updateGrandTotal();
	}
	// 2. 遍历所有行并计算 Grand Total
	function updateGrandTotal() {
		let grandTotal = 0;
		
		// 抓取页面上所有行的小计单元格
		const allAmountCells = document.querySelectorAll('.item-amount');
		
		allAmountCells.forEach(cell => {
			// 如果 .item-amount 是 input，用 cell.value；如果是 td/span，用 cell.innerText
			const amount = parseFloat(cell.innerText || cell.value) || 0;
			grandTotal += amount;
		});
		
		// 写入 gtotal 输入框并强制保留 2 位小数
		const gtotalElem = document.getElementById('form-gtotal'); // 如果你的ID是 form-gtotal 请对应修改
		if (gtotalElem) {
			gtotalElem.value = grandTotal.toFixed(2);
		}
	}

	// 5. ✨ 新增：在表格最下方动态追加新空行的函数
	function addNewRow() {
		const tbody = document.getElementById('po-details-tbody');
		if (!tbody) return;

		// 如果当前显示的是“暂无明细数据”的提示文本，先把它清空
		if (tbody.rows.length === 1 && tbody.rows[0].cells.length === 1) {
			tbody.innerHTML = '';
		}

		//const nextIndex = tbody.rows.length + 1;
		const nextIndex = (tbody.rows ? tbody.rows.length : 0) + 1;
        document.getElementById('form-drow').value = nextIndex;
		// 动态组装属于全局产品库的下拉列表
		let itemCodeHtml = `<select class="form-select form-select-sm item-code-select" name="code_${nextIndex}"  id="code_${nextIndex}" onchange="handleItemChange(this)">`;
		itemCodeHtml += `<option value="">-- Select Item --</option>`;
		globalProductList.forEach(prod => {
			// 💡 新增行同样埋入：data-uom="${prod.produom1}"
			itemCodeHtml += `<option value="${prod.prodid}" data-name="${prod.prodname}" data-uom="${prod.produom1}">${prod.prodid}</option>`;
		});
		itemCodeHtml += `</select>`;
		
		// 拼装全新的一行
		const newRowHtml = `
			<tr>
				<td class="row-number">${nextIndex}</td>
				<td>${itemCodeHtml}</td>
				<td>
					<input type="text" class="form-control form-control-sm item-desc" name="desc_${nextIndex}" id="desc_${nextIndex}" value="" readonly>
				</td>
				<td>
					<input type="number" class="form-control form-control-sm text-end item-qty" name="qty_${nextIndex}" id="qty_${nextIndex}" value="0" oninput="calculateRowAmount(this)">
				</td>
				<td>
					<span class="badge bg-secondary item-uom" name="uom_${nextIndex}" id="uom_${nextIndex}"></span>
				</td>
				<td>
					<input type="number" step="0.01" class="form-control form-control-sm text-end item-price" name="price_${nextIndex}" id="price_${nextIndex}" value="0.0000" oninput="calculateRowAmount(this)">
				</td>
				<td class="text-end fw-bold item-amount" name="amt_${nextIndex}" id="amt_${nextIndex}">0.00</td>
				<td class="text-center">
					<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button>
				</td>
			</tr>
		`;
		tbody.insertAdjacentHTML('beforeend', newRowHtml);
	}
    // 6. 专门用来拦截明细表格内回车键的函数
	// 1. 抽取出来的公共格式化函数（精简、复用）
	function formatInputRow(inputTarget) {
		if (!inputTarget) return;
		
		// 如果是数量输入框，去掉前导 0
		if (inputTarget.classList.contains('item-qty')) {
			const val = parseFloat(inputTarget.value);
			inputTarget.value = !isNaN(val) ? val : 0;
		}
		
		// 如果是单价输入框，去掉前导 0 并规范为 4 位小数
		if (inputTarget.classList.contains('item-price')) {
			const val = parseFloat(inputTarget.value);
			inputTarget.value = !isNaN(val) ? val.toFixed(4) : "0.0000";
		}
	}

	// 2. 核心大监听器：同时处理键盘回车、光标离开、以及现场算账
	const poTbody = document.getElementById('po-details-tbody');
	if (poTbody) {
		
		// 动作 A：监听键盘按键
		poTbody.addEventListener('keydown', function(event) {
			if (event.key === 'Enter') {
				event.preventDefault(); // 阻止网页意外刷新
				
				const target = event.target;
				if (target.classList.contains('item-qty') || target.classList.contains('item-price')) {
					
					// ✨ 关键点：按回车跳走前，立刻先执行格式化
					formatInputRow(target); 
					
					// 模拟 Tab 键自动聚焦下一个输入框
					const nextInput = target.closest('td').nextElementSibling?.querySelector('input:not([readonly])');
					if (nextInput) {
						nextInput.focus();
						nextInput.select();
					} else {
						target.blur(); // 如果是最后一个单价框，直接失焦
					}
				}
			}
		});

		// 动作 B：监听鼠标点走（不论是点空白处、还是换行点击，只要离开就格式化）
		poTbody.addEventListener('focusout', function(event) {
			formatInputRow(event.target);
		});
	}



	// 7. 清空表单（新增模式）
	function clearPOForm() {
		document.getElementById('form-pono').value = '';
		document.getElementById('form-pono').readOnly = true; 
		document.getElementById('form-suppid').value = '';
		document.getElementById('form-podate').value = '';
		document.getElementById('form-dateline').value = '';
		document.getElementById('form-pormk').value = '';
		document.getElementById('form-curr').value = '';
		document.getElementById('form-gtotal').value =(0).toFixed(2); 
		
		// 1. Get the DOM elements of the two input fields
		const poDateInput = document.getElementById('form-podate');
		const poDeadlineInput = document.getElementById('form-dateline');

		// 2. Safely populate the values using PHP echo
		if (poDateInput) {
			poDateInput.value = "<?php echo $currentDate; ?>";
		}
		
		if (poDeadlineInput) {
			poDeadlineInput.value = "<?php echo $defaultDeadline; ?>";
		}
		
		// === 3. ✨ 新增：清空明细表格数据 ===
		const tbody = document.getElementById('po-details-tbody');
		if (tbody) {
			// 恢复初始的提示文字，确保表格不空秃，并占满 8 列列宽
			tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Click ⬅️ PO# To Preview</td></tr>';
		}
	

		document.getElementById('form-action').value = 'add_po';
		document.getElementById('submit-btn').innerText = '➕ Add PO';
		document.getElementById('submit-btn').className = 'btn btn-primary px-4';
		
		// 重置搜索框并恢复左侧全列表显示
		document.getElementById('search-pono').value = '';
		document.querySelectorAll('.po-list-item').forEach(item => item.style.display = 'block');
		document.getElementById('search-error').classList.add('d-none');
		
		const submitBtn = document.getElementById('submit-btn');
		submitBtn.disabled = false;
		
		
		const addBtn = document.getElementById('btn-add-po-item');
        // 控制 "Add Item" 按钮的显示与隐藏
        addBtn.classList.remove('d-none');
        document.getElementById('form-drow').value = 0;
     
			
	}
	
    document.addEventListener('DOMContentLoaded', function() {
		// 获取两个下拉框的 DOM 对象
		const suppSelect = document.getElementById('form-suppid');
		const currSelect = document.getElementById('form-curr');

		// 监听 供应商 下拉框的改变事件
		suppSelect.addEventListener('change', function() {
			// 获取当前被选中的 option 元素
			const selectedOption = this.options[this.selectedIndex];
			
			// 读取该 option 身上埋藏的 data-currency 值
			const defaultCurrency = selectedOption.getAttribute('data-currency');
			
			// 如果有对应的值，且币种下拉框里存在这个选项，就自动选中它
			if (defaultCurrency) {
				currSelect.value = defaultCurrency;
			} else {
				// 如果选了 "-- Select Supplier --"，可以考虑清空币种或保持默认
				currSelect.value = ""; 
			}
		});
	});
	

</script>