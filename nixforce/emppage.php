<?php
    // 检查用户是否已登录，如果没有，则重定向到登录页面
	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
		header('Location: login.php');
		exit;
	}

    include 'cnopen.php';
	include 'function.php';
	$action   = isset($_POST['action']) ? $_POST['action'] : '';
	$action2   = isset($_POST['action2']) ? $_POST['action2'] : '';
	//update_employee / add_employee
	// Initialize messages and variables
	$msg = "";
	if($action2 == "update_image"){
		$target_dir = "./images/emp/";
		// Create directory if it does not exist
		if (!file_exists($target_dir)) {
			mkdir($target_dir, 0755, true);
		}

		$file_name = basename($_FILES["imageInput"]["name"]);
		$target_file = $target_dir . $file_name;
		$upload_ok = 1;
		$image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        //echo "target_file: " . $target_file."<br/>";
		

		// 1. Verify if file is an actual image
		$check = getimagesize($_FILES["imageInput"]["tmp_name"]);
		if ($check === false) {
			$msg = "File is not an image.";
			$alert_class = "alert-danger";
			$upload_ok = 0;
		}
		// FIX: Use print_r inside pre tags to view array contents cleanly
		//echo "<pre>check: ";
		//print_r($check);
		//echo "</pre>";
    
		// 2. Limit file size (5MB max)
		if ($_FILES["imageInput"]["size"] > 5000000) {
			$msg = "Your file is too large. Max size is 5MB.";
			$alert_class = "alert-danger";
			$upload_ok = 0;
		}
        //echo "FIle Size : " .$_FILES["imageInput"]["size"]."<br/>";
		 
		// 3. Restrict file formats
		$allowed_types = ["jpg"];
		if (!in_array($image_file_type, $allowed_types)) {
			$msg = "Only JPG files are allowed.";
			$alert_class = "alert-danger";
			$upload_ok = 0;
		}
        //echo "<pre>allowed_types: ";
		//print_r($allowed_types);
		//echo "</pre>";
		//echo "upload_ok : " .$upload_ok."<br/>";
        
		$createdby = htmlspecialchars($_SESSION['username']);
		
		// 4. Move file if all checks pass
		if ($upload_ok == 1) {
			if (move_uploaded_file($_FILES["imageInput"]["tmp_name"], $target_file)) {
				$msg = "The file <strong>" . htmlspecialchars($file_name) . "</strong> has been uploaded.";
				$alert_class = "alert-success";
				saveLog(
					$pdo,
					$createdby, 
					'UPDATE', 
					'./images/emp/', 
					$file_name, 
					"Update images file for employee - " . $file_name
				);
			} else {
				$msg = "There was an error uploading your file.";
				$alert_class = "alert-danger";
			}
		}
		$action="";	

	}
	if($action=="add_employee"){
		$getempid       = isset($_POST['empid']) ? $_POST['empid'] : '';
		$getempdept     = isset($_POST['empdept']) ? $_POST['empdept'] : '';
		$getempname     = isset($_POST['empname']) ? $_POST['empname'] : '';
		$getemptype     = isset($_POST['emptype']) ? $_POST['emptype'] : '';
		$getempdob      = isset($_POST['empdob']) ? $_POST['empdob'] : '';
		if($getempid  == "" || $getempname =="" ||$getemptype=="" || $getempdob==""){
			$msg = "Please make sure [ID],[DEPARTMENT],[FULL NAME],[DOB],[TYPE] are not blank";
			return;
		}
		//check ID existing 		
		try {
			// 1. Use a placeholder (:empid) instead of direct variable insertion
			$stmt = $pdo->prepare('SELECT COUNT(*) FROM pemployee WHERE empid = :empid');
			
			// 2. Pass the variable safely inside the execute array
			$stmt->execute(['empid' => $getempid]);
			
			// 3. Fetch the actual total count number
			$count = $stmt->fetchColumn();
			
			if ($count > 0) {
				$msg = "Employee ID " . $getempid . " Duplicated.";
				$getempid = '';
			} 

		} catch (PDOException $e) {
			exit("Database query failed: " . $e->getMessage());
		}
        //echo '$getempid : '.$getempid;
	    if ($getempid==""){
		}else{
			$getempadd      = isset($_POST['empadd']) ? $_POST['empadd'] : '';
			$getemptel      = isset($_POST['emptel']) ? $_POST['emptel'] : '';
			$getempemail    = isset($_POST['empemail']) ? $_POST['empemail'] : '';
			$getempremark   = isset($_POST['empremark']) ? $_POST['empremark'] : '';
			$getempimg      = './images/emp/'.$getempid.'.jpg';
			$getempic       = isset($_POST['empic']) ? $_POST['empic'] : '';
			$getempcardid   = isset($_POST['empcardid']) ? $_POST['empcardid'] : '';
			$getempnation   = isset($_POST['empnation']) ? $_POST['empnation'] : '';
			$getempetel     = isset($_POST['empetel']) ? $_POST['empetel'] : '';
			$getempreligion = isset($_POST['empreligion']) ? $_POST['empreligion'] : '';
			$getempmstts    = isset($_POST['empmstts']) ? $_POST['empmstts'] : '';
			if($getempcardid==""){
				$getempcardid = "000000";
			}

			// Sample data to update
			$insertData = [
				'empid'   => $getempid,
				'empname' => $getempname,
				'empic' => $getempic,
				'empdob'  => $getempdob,
				'empdept'  => $getempdept,
				'empadd'   => $getempadd,
				'emptel' => $getemptel ,
				'empemail' => $getempemail,
				'empremark' => $getempremark,
				'emptype' => $getemptype,
				'empnation' => $getempnation,
				'empetel' => $getempetel,
				'empreligion' => $getempreligion,
				'empmstts' => $getempmstts,
				'empstatus' => 'Y',
				'empcardid' => $getempcardid,
				'empimg' =>$getempimg,
				'empcrby' => htmlspecialchars($_SESSION['username'])
			];
			try {
		
			// 1. Prepare the SQL statement with named placeholders
			$sql = "INSERT INTO pemployee (empid, empname, empic, empdob, empdept,empadd,empemail,emptel,empremark,emptype,empcardid,empstatus,empimg,empcrby,empcardid,empnation) 
					VALUES (:empid, :empname, :empic, :empdob, :empdept, :empadd, :empemail , :emptel, :empremark, :emptype, :empcardid, :empstatus, :empimg, :empcrby, :empcardid, :empnation,
					:empetel, :empreligion, :empmstts)";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the array directly
			$stmt->execute($insertData);

			// 3. Get the ID of the newly inserted row
			$msg = "Successfully add new employee detail : " . $getempid. " -" .$getempname; 
			
			saveLog(
			    $pdo,
				$insertData['empcrby'], 
				'INSERT', 
				'pemployee', 
				$insertData['empid'], 
				"Add new employee profile for " . $insertData['empname']
			);
			echo "<script>
				window.addEventListener('load', function() {
					// Wait 150ms to guarantee carousel nodes are completely loaded
					setTimeout(function() {
						if (typeof window.searchEmployeeById === 'function') {
							// Pass the ID straight into the function parameters
							window.searchEmployeeById(" . json_encode($getempid) . ");
						} else {
							console.error('Search function is missing or out of scope.');
						}
					}, 150);
				});
			</script>";
			} catch (PDOException $e) {
				$msg = "Insert failed: " . $e->getMessage();
			}
		}
		$action="";	
	}elseif($action=="update_employee"){
		$getempid     = isset($_POST['empid']) ? $_POST['empid'] : '';
		$getempname   = isset($_POST['empname']) ? $_POST['empname'] : '';
		$getempic     = isset($_POST['empic']) ? $_POST['empic'] : '';
		$getempdob    = isset($_POST['empdob']) ? $_POST['empdob'] : '';
		$getempdept   = isset($_POST['empdept']) ? $_POST['empdept'] : '';
		$getempadd    = isset($_POST['empadd']) ? $_POST['empadd'] : '';
		$getemptel    = isset($_POST['emptel']) ? $_POST['emptel'] : '';
		$getempemail  = isset($_POST['empemail']) ? $_POST['empemail'] : '';
		$getempremark = isset($_POST['empremark']) ? $_POST['empremark'] : '';
		$getemptype   = isset($_POST['emptype']) ? $_POST['emptype'] : '';
		$getempnation   = isset($_POST['empnation']) ? $_POST['empnation'] : '';
		$getempetel     = isset($_POST['empetel']) ? $_POST['empetel'] : '';
		$getempreligion = isset($_POST['empreligion']) ? $_POST['empreligion'] : '';
		$getempmstts    = isset($_POST['empmstts']) ? $_POST['empmstts'] : '';
		$getempcardid   = isset($_POST['empcardid']) ? $_POST['empcardid'] : '';
		$getempstatus ='Y';
		if($getempid  == "" || $getempname =="" ||$getemptype=="" || $getempdob==""){
			$msg = "Please make sure [ID],[DEPARTMENT],[FULL NAME],[DOB],[TYPE] are not blank";
			return;
		}
		// Sample data to update
		$updateData = [
		    'empid'   => $getempid,
			'empname' => $getempname,
			'empic' => $getempic,
			'empdob'  => $getempdob,
			'empadd'   => $getempadd,
			'empdept'  => $getempdept,
			'emptel' => $getemptel ,
			'empemail' => $getempemail,
			'empremark' => $getempremark,
			'emptype' => $getemptype,
			'empnation' => $getempnation,
			'empetel' => $getempetel,
			'empreligion' => $getempreligion,
			'empmstts' => $getempmstts,
			'empcardid' => $getempcardid,
			'empupby' => htmlspecialchars($_SESSION['username'])
		];

		try {
			// 1. Prepare the SQL statement with SET clause and WHERE clause
			$sql = "UPDATE pemployee 
					SET empname = :empname, empic = :empic, empdob = :empdob, empadd = :empadd, empdept = :empdept, emptel = :emptel, 
					empemail = :empemail, empremark = :empremark, emptype = :emptype, empupby = :empupby, empcardid= :empcardid, empnation= :empnation,
					empetel= :empetel, empreligion= :empreligion, empmstts= :empmstts
					WHERE empid = :empid";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the data array
			$stmt->execute($updateData);

			// 3. Check how many rows were actually changed
			$rowCount = $stmt->rowCount();
			$msg = "Successfully update employee detail " .$rowCount. " row(s): " . $getempid. " -" .$getempname; 
			
			saveLog(
			    $pdo,
				$updateData['empupby'], 
				'UPDATE', 
				'pemployee', 
				$updateData['empid'], 
				"Updated employee profile for " . $updateData['empname']
			);
			echo "<script>
				window.addEventListener('load', function() {
					// Wait 150ms to guarantee carousel nodes are completely loaded
					setTimeout(function() {
						if (typeof window.searchEmployeeById === 'function') {
							// Pass the ID straight into the function parameters
							window.searchEmployeeById(" . json_encode($getempid) . ");
						} else {
							console.error('Search function is missing or out of scope.');
						}
					}, 150);
				});
			</script>";
		} catch (PDOException $e) {
			$msg = "Update failed: " . $e->getMessage();
		}
		$action="";
	}
	// 4a. Count current total of Employee
	$stmt = $pdo->prepare('SELECT COUNT(*) FROM pemployee WHERE empstatus = "Y"');
	$stmt->execute(); // Since there are no parameters/placeholders, you can leave this empty
	$totalEmp = (int)$stmt->fetchColumn(); 
	// 4b. FETCH ALL EMPLOYEES TO DISPLAY
	$employees = $pdo->query("SELECT empid, empname, empdob, empdept, deptcode, empimg, empadd, emptel, empemail, empremark, emptype, empic, 
	empcardid, empnation, empetel, empreligion, empmstts
	FROM pemployee LEFT JOIN pdepart ON empdept=deptid ORDER BY empid")->fetchAll(PDO::FETCH_ASSOC);
	
?>
<div class="container my-1">
    <h4 class="mb-4 text-start">Employee Basic Information Portal</h4>
	<div class="text-center text-danger fw-bold mb-4"><?php echo $msg; ?></div>
	
	
	<div class="container mt-1">
	  <div class="row">
		<div class="col-sm-3">
		  <!-- Ensure enctype and method are exactly as written below -->
			<div class="card-body">
				<form action="?page=employee" method="POST" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="imageInput" class="form-label">Choose Image File</label>
						<!-- The name attribute MUST exactly match your PHP key "imageInput" -->
						<input class="form-control" type="file" id="imageInput" name="imageInput" required>
						<div class="form-text">Accepted formats: JPG(Max 5MB)</div>
						<div class="card-title mb-1 text-danger">[Make sure image name with correct Employee ID]</div>
					</div>
					<div class="text-center">
						<button type="submit" name="submit" id="form-action-img" class="btn btn-primary btn-sm" value="update_image">Upload</button>
					</div>
					<input type="hidden" name="action2"  id="form-action-img" value="update_image">
				</form>
			</div>
					
		  <?php if ($totalEmp>0){?>
		    <div class="rounded p-3 bg-light" style="height: 700px; overflow-y: auto;">
				<div class="d-flex flex-column gap-2">
					<?php foreach ($employees as $emp): ?>
						<div class="card border-0 shadow-sm overflow-hidden">
							<div class="row g-0 align-items-center bg-white p-2">
							    
								<!-- 左侧：员工图片（调小尺寸） -->
								<div class="col-auto text-center ps-2">
									<img src="<?php echo htmlspecialchars($emp['empimg']); ?>" 
										 class="img-fluid rounded emp-thumbnail" 
										 alt="<?php echo htmlspecialchars($emp['empname']); ?>"
										 style="height: 100px; width: 100px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
										 data-id="<?php echo htmlspecialchars($emp['empid']); ?>"
										 data-img="<?php echo htmlspecialchars($emp['empimg']); ?>"
										 data-name="<?php echo htmlspecialchars($emp['empname']); ?>"
										 data-ic="<?php echo htmlspecialchars($emp['empic']); ?>"
										 data-cardid="<?php echo htmlspecialchars($emp['empcardid']); ?>"
										 data-dob="<?php echo htmlspecialchars($emp['empdob'] ?? ''); ?>"
										 data-dept="<?php echo htmlspecialchars($emp['empdept'] ?? ''); ?>"
										 data-add="<?php echo htmlspecialchars($emp['empadd'] ?? ''); ?>"
										 data-tel="<?php echo htmlspecialchars($emp['emptel'] ?? ''); ?>"
										 data-email="<?php echo htmlspecialchars($emp['empemail'] ?? ''); ?>"
										 data-remark="<?php echo htmlspecialchars($emp['empremark'] ?? ''); ?>"
										 data-type="<?php echo htmlspecialchars($emp['emptype'] ?? ''); ?>"
										 data-nation="<?php echo htmlspecialchars($emp['empnation'] ?? ''); ?>"
										 data-religion="<?php echo htmlspecialchars($emp['empreligion'] ?? ''); ?>"
										 data-etel="<?php echo htmlspecialchars($emp['empetel'] ?? ''); ?>"
										 data-mstts="<?php echo htmlspecialchars($emp['empmstts'] ?? ''); ?>"
										 onclick="fillEmployeeForm(this)"
										 onmouseover="this.style.transform='scale(1.05)';"
										 onmouseout="this.style.transform='scale(1)';">
								</div>
								
								<!-- 右侧：员工信息 -->
								<div class="col">
									<div class="card-body py-1 ps-3">
										<h6 class="card-title mb-1 text-primary"><?php echo htmlspecialchars($emp['empid']); ?></h6>
										<p class="card-text text-muted small mb-0 fw-bold"><?php echo htmlspecialchars($emp['empname']); ?></p>
										<p class="card-text text-muted small mb-0"><?php echo htmlspecialchars($emp['deptcode'] ?? ''); ?></p>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<p class="fw-bold mb-3">Total Active Employees: <?php echo $totalEmp; ?></p>
		  <?php }else{ ?>
			<p class="text-danger">No active employee found</p>
		  <?php } ?>
		 
		</div>
		<!-- ================= 右边：接收并显示资料的表单 ================= -->
        <div class="col-sm-9">
		    <!-- 1. NEW: Quick Search Box -->
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <span class="fw-bold text-secondary">Find Employee:</span>
                        </div>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="text" id="search-empid" class="form-control" placeholder="Type Employee ID and press Enter..." onkeypress="handleSearchKeyPress(event)">
                                <button type="button" class="btn btn-outline-primary" onclick="searchEmployeeById()">Search</button>
                            </div>
                            <div id="search-error" class="text-danger small mt-1 d-none">Employee ID not found!</div>
                        </div>
                    </div>
                </div>
            </div>
			
			
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Employee Detail Form (Add / Edit)</h5>
                    <!-- 在 Header 加上一个醒目的 Clear 按钮 -->
                    <button type="button" class="btn btn-light btn-sm fw-bold shadow-sm" onclick="clearEmployeeForm()">
                        ➕ Add New / Clear
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="?page=employee">
                        <div class="row g-3">
							<div class="row g-3">
								<!-- 左边：员工编号 -->
								<div class="col-md-2">
									<label class="form-label fw-bold">Employee ID *</label>
									<input type="text" class="form-control bg-light" id="form-empid" name="empid" value="" readonly required>
								</div>

								<!-- 中间：如果你有其他表单项（例如之前的 Full Name），可以放在这里占位 -->
								<?php
								// Fetch all departments from the database for the combobox
								try {
									$deptStmt = $pdo->query("SELECT deptid, deptcode, deptname FROM pdepart ORDER BY deptcode ASC");
									$departments = $deptStmt->fetchAll();
								} catch (PDOException $e) {
									// Fallback if the query fails
									$departments = [];
								}

								// Assume $current_dept contains the value passed to the form (e.g., 1)
								// If you are using the previous template, this would be $emp['emp_dept']
								$current_dept = isset($emp['emp_dept']) ? $emp['emp_dept'] : ''; 
								?>

								<div class="col-md-8">
									<label class="form-label fw-bold" for="form-empdept">Department  *</label>
									<select class="form-select" id="form-empdept" name="empdept" required>
										<option value="">-- Select Department --</option>
										<?php foreach ($departments as $dept): ?>
											<option value="<?php echo htmlspecialchars($dept['deptid']); ?>" 
												<?php echo ($current_dept == $dept['deptid']) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($dept['deptcode'] . ' - ' . $dept['deptname']); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							
								<!-- 右边：图片移动到最后 -->
								<div class="col-md-2 d-flex justify-content-end align-items-end"> 
									<img id="form-empimg" name="empimg" src="" alt="Photo" class="img-thumbnail d-block" style="width: 100px; height: 120px; object-fit: cover;">
									
								</div>
							</div>

                            <!-- 员工名字 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name *</label>
                                <input type="text" class="form-control" id="form-empname" name="empname" value="" required placeholder="Click an employee image on the left to load data..." >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Birth(MM/DD/YYYY)  *</label>
                                <input type="date" class="form-control" id="form-empdob" name="empdob" value="">
                            </div>
							
							<div class="col-md-4">
								<label class="form-label fw-bold" for="form-emptype">Type * </label>
								<select class="form-select" id="form-emptype" name="emptype" required>
									<?php
									$current_type = isset($emp['emptype']) ? $emp['emptype'] : ''; 
									$current_nation = isset($emp['empnation']) ? $emp['empnation'] : ''; 
									
									if ($current_type == 'LOCAL') {
										$current_nation = 'MALAYSIA';
									}
									$foreign_options = ['MYANMAR', 'VIETNAM', 'BANGLADESH', 'THAILAND', 'INDONESIA'];
									?>
									<!-- 增加了 PHP selected 回显，确保刷新后 Type 状态不丢失 -->
									<option value="" <?php echo ($current_type == '') ? 'selected' : ''; ?>>-- Select Type --</option>
									<option value="LOCAL" <?php echo ($current_type == 'LOCAL') ? 'selected' : ''; ?>>LOCAL</option>
									<option value="FOREIGN" <?php echo ($current_type == 'FOREIGN') ? 'selected' : ''; ?>>FOREIGN</option>
								</select>
							</div>

							<div class="col-md-4">
								<label class="form-label fw-bold" for="form-empnation">Nationality</label>
								<!-- 修正点：将这里的 id 改为 form-empnation，与 JavaScript 保持绝对一致 -->
								<select class="form-select" id="form-empnation" name="empnation" <?php echo ($current_type == '') ? 'disabled' : ''; ?>>
									<?php
									if ($current_type == 'LOCAL') {
										echo '<option value="MALAYSIA" selected>MALAYSIA</option>';
									} elseif ($current_type == 'FOREIGN') {
										foreach ($foreign_options as $option) {
											$selected = ($current_nation == $option) ? 'selected' : '';
											echo "<option value='{$option}' {$selected}>{$option}</option>";
										}
									} else {
										echo '<option value="" selected>Choose nationality...</option>';
									}
									?>
								</select>
							</div>
							
							<div class="col-md-4">
								<label class="form-label fw-bold" for="form-empdept">Religion</label>
								<select class="form-select" id="form-empreligion" name="empreligion" >
									<option value="">-- Select Religion --</option>
									<option value="Islam">Islam</option>
									<option value="Buddhism">Buddhism</option>
									<option value="Christianity">Christianity</option>
									<option value="Hinduism">Hinduism</option>
									<option value="Other Faiths">Other Faiths</option>
								</select>
							</div>
							
							<div class="col-md-6">
                                <label class="form-label fw-bold">IC/Passport</label>
                                <input type="text" class="form-control" id="form-empic" name="empic" value="" >
                            </div>
							
							<div class="col-md-6">
                                <label class="form-label fw-bold">RFID ID</label>
                                <input type="text" class="form-control" id="form-empcardid" name="empcardid" value="" >
                            </div>

							
							<div class="col-md-6"> 
							    <label class="form-label fw-bold">Address</label> 
								<textarea class="form-control" id="form-empadd" name="empadd" rows="3" ></textarea> 
							</div>
							
							<div class="col-md-6"> 
							    <label class="form-label fw-bold">Remark</label> 
								<textarea class="form-control" id="form-empremark" name="empremark" rows="3" ></textarea> 
							</div>
							
							<div class="col-md-6">
                                <label class="form-label fw-bold">Phone No.</label>
                                <input type="text" class="form-control" id="form-emptel" name="emptel" value="">
                            </div>
							
							<div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="text" class="form-control" id="form-empemail" name="empemail" value="">
                            </div>
							
							<div class="col-md-4">
								<label class="form-label fw-bold" for="form-empdept">Marital Status</label>
								<select class="form-select" id="form-empmstts" name="empmstts" >
									<option value="">-- Select Status --</option>
									<option value="Single">Single</option>
									<option value="Married">Married</option>
									<option value="Divorced">Divorced</option>
									<option value="Widowed">Widowed</option>
								</select>
							</div>
							
							<div class="col-md-8">
                                <label class="form-label fw-bold">Emergency Contact</label>
                                <input type="text" class="form-control" id="form-empetel" name="empetel" value="">
                            </div>
					
                        </div>
                        
                        <div class="mt-4 text-end">
                            <input type="hidden" name="action"  id="form-action" value="update_employee">
                            <button type="submit" class="btn btn-success px-4" id="submit-btn">Save Changes</button>
                        </div>
                    </form> 
                </div>
            </div>
        </div>
	  </div>
	</div>

    <script>
	// 1. Core function to populate form and lock it in EDIT mode
	function fillEmployeeForm(element) {
		// Hide error message if it was showing
		document.getElementById('search-error').classList.add('d-none');

		// Extract data from attributes
		const empId = element.getAttribute('data-id');
		const empImg = element.getAttribute('data-img');
		const empName = element.getAttribute('data-name');
		const empIC = element.getAttribute('data-ic');
		const empCardID = element.getAttribute('data-cardid');
		const empDob = element.getAttribute('data-dob');
		const empDept = element.getAttribute('data-dept');
		const empAdd = element.getAttribute('data-add');
		const empTel = element.getAttribute('data-tel');
		const empEmail = element.getAttribute('data-email');
		const empRemark = element.getAttribute('data-remark');
		const empType = element.getAttribute('data-type');
		const empNation = element.getAttribute('data-nation');
		const empReligion = element.getAttribute('data-religion');
		const empEtel = element.getAttribute('data-etel');
		const empMstts = element.getAttribute('data-mstts');

		// Populate inputs
		const idInput = document.getElementById('form-empid');
		idInput.value = empId;
		idInput.readOnly = true; // Lock ID field for editing
		idInput.classList.add('bg-light');
		
		document.getElementById('form-empname').value = empName;
		const targetImg = document.getElementById('form-empimg');
		if (empImg) {
            targetImg.src = empImg;
        }
		if(document.getElementById('form-empic')) document.getElementById('form-empic').value = empIC;
		if(document.getElementById('form-empcardid')) document.getElementById('form-empcardid').value = empCardID;
		if(document.getElementById('form-empdept')) document.getElementById('form-empdept').value = empDept;
		if(document.getElementById('form-empdob')) document.getElementById('form-empdob').value = empDob;
		if(document.getElementById('form-empadd')) document.getElementById('form-empadd').value = empAdd;
		if(document.getElementById('form-emptel')) document.getElementById('form-emptel').value = empTel;
		if(document.getElementById('form-empemail')) document.getElementById('form-empemail').value = empEmail;
		if(document.getElementById('form-empremark')) document.getElementById('form-empremark').value = empRemark;
		if(document.getElementById('form-emptype')) document.getElementById('form-emptype').value = empType;
		if(document.getElementById('form-empnation')) document.getElementById('form-empnation').value = empNation;
		if(document.getElementById('form-empreligion')) document.getElementById('form-empreligion').value = empReligion;
		if(document.getElementById('form-empetel')) document.getElementById('form-empetel').value = empEtel;
		if(document.getElementById('form-empmstts')) document.getElementById('form-empmstts').value = empMstts;


		// Set form state to EDIT/UPDATE mode
		document.getElementById('form-action').value = 'update_employee';
		document.getElementById('submit-btn').textContent = 'Save Changes';
		document.getElementById('submit-btn').className = 'btn btn-success px-4';
	}

	// 2. Clear function for adding a new employee
	function clearEmployeeForm() {
		document.getElementById('search-error').classList.add('d-none');
		document.getElementById('search-empid').value = '';

		const idInput = document.getElementById('form-empid');
		idInput.value = '';
		idInput.readOnly = false; // Unlock ID for fresh entry
		idInput.classList.remove('bg-light');
		
		document.getElementById('form-empname').value = '';
		document.getElementById('form-empimg').src = '';
		if(document.getElementById('form-empic')) document.getElementById('form-empic').value = '';
		if(document.getElementById('form-empcardid')) document.getElementById('form-empcardid').value = '';
		if(document.getElementById('form-empdept')) document.getElementById('form-empdept').value = '';
		if(document.getElementById('form-empdob')) document.getElementById('form-empdob').value = '';
		if(document.getElementById('form-empadd')) document.getElementById('form-empadd').value = '';
		if(document.getElementById('form-emptel')) document.getElementById('form-emptel').value = '';
		if(document.getElementById('form-empemail')) document.getElementById('form-empemail').value = '';
		if(document.getElementById('form-empremark')) document.getElementById('form-empremark').value = '';
		if(document.getElementById('form-emptype')) document.getElementById('form-emptype').value = '';
		if(document.getElementById('form-empnation')) document.getElementById('form-empnation').value = '';
		if(document.getElementById('form-empreligion')) document.getElementById('form-empreligion').value = '';
		if(document.getElementById('form-empetel')) document.getElementById('form-empetel').value = '';
		if(document.getElementById('form-empmstts')) document.getElementById('form-empmstts').value = '';
		if(document.getElementById('form-empnation')) document.getElementById('form-empnation').value = '';
		if(document.getElementById('form-empreligion')) document.getElementById('form-empreligion').value = '';
		if(document.getElementById('form-empetel')) document.getElementById('form-empetel').value = '';
		if(document.getElementById('form-empmstts')) document.getElementById('form-empmstts').value = '';

		idInput.focus();

		// Set form state to ADD NEW mode
		document.getElementById('form-action').value = 'add_employee';
		document.getElementById('submit-btn').textContent = '➕ Add Employee';
		document.getElementById('submit-btn').className = 'btn btn-primary px-4';
	}
	
	// Change "function searchEmployeeById()" to this explicit global assignment:
	window.searchEmployeeById = function(forcedId = null) {
		// If an ID is passed directly, use it. Otherwise, pull from the input box.
		const searchVal = (forcedId ? forcedId : document.getElementById('search-empid').value).trim().toLowerCase();
		const errorDiv = document.getElementById('search-error');
		
		if (searchVal === '') return;

		// Set the input display value so it matches what we are searching
		const searchInput = document.getElementById('search-empid');
		if (searchInput) {
			searchInput.value = searchVal.toUpperCase();
		}

		const thumbnails = document.querySelectorAll('.emp-thumbnail');
		let found = false;

		for (let img of thumbnails) {
			const empId = img.getAttribute('data-id').trim().toLowerCase();
			
			if (empId === searchVal) {
				fillEmployeeForm(img); // This pushes the details down to your form fields
				found = true;
				break;
			}
		}

		if (!found) {
			if (errorDiv) errorDiv.classList.remove('d-none');
		} else {
			if (errorDiv) errorDiv.classList.add('d-none');
		}
	};


	// 4. NEW: Allow searching by hitting the "Enter" key inside the search box
	function handleSearchKeyPress(event) {
		if (event.key === "Enter") {
			event.preventDefault(); // Stop form submission
			searchEmployeeById();
		}
	}

	// Default state: load the first active employee data on page startup
	document.addEventListener("DOMContentLoaded", function() {
		const firstEmp = document.querySelector('.emp-thumbnail');
		if (firstEmp) {
			fillEmployeeForm(firstEmp);
		}
	});
	
	document.getElementById('form-emptype').addEventListener('change', function() {
		const typeValue = this.value;
		// 这里成功对应 HTML 中的 id="form-empnation"
		const nationSelect = document.getElementById('form-empnation');
		
		nationSelect.innerHTML = '';
		nationSelect.disabled = false;
		
		if (typeValue === 'LOCAL') {
			const options = [
				{ text: 'MALAYSIA', value: 'MALAYSIA' }
			];
			
			options.forEach(opt => {
				let el = document.createElement('option');
				el.textContent = opt.text;
				el.value = opt.value;
				el.selected = true; // 确保动态切换时直接默认选中
				nationSelect.appendChild(el);
			});
			
		} else if (typeValue === 'FOREIGN') {
			const options = ['MYANMAR', 'VIETNAM', 'BANGLADESH', 'THAILAND', 'INDONESIA'];
			let defaultOpt = document.createElement('option');
			defaultOpt.textContent = 'Choose foreign country...';
			defaultOpt.value = '';
			defaultOpt.disabled = true;
			defaultOpt.selected = true;
			nationSelect.appendChild(defaultOpt);
			
			options.forEach(country => {
				let el = document.createElement('option');
				el.textContent = country;
				el.value = country;
				nationSelect.appendChild(el);
			});
		} else {
			// 如果用户选回了 "-- Select Type --"
			let defaultOpt = document.createElement('option');
			defaultOpt.textContent = 'Choose nationality...';
			defaultOpt.value = '';
			defaultOpt.selected = true;
			nationSelect.appendChild(defaultOpt);
			nationSelect.disabled = true;
		}
	});
	</script>
</div>










