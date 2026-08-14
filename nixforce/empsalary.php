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
	
	$stmt = $pdo->prepare('SELECT COUNT(*) FROM pemployee WHERE empstatus = "Y"');
	$stmt->execute(); // Since there are no parameters/placeholders, you can leave this empty
	$totalEmp = (int)$stmt->fetchColumn(); 
	
	$employees = $pdo->query('SELECT emptype, empid, empname, empimg, empdept, deptcode, empstatus, empic, (SELECT COUNT(*) FROM pemppay WHERE ppempid=empid) AS dtl,
    pppost01, ppjoin01, ppcomfirm01, ppresign01
	FROM pemployee LEFT JOIN pdepart ON deptid=empdept LEFT JOIN pemppay ON ppempid=empid LEFT JOIN pposition on pppost01=postid
	order by empid;')->fetchAll(PDO::FETCH_ASSOC);
	

/* SELECT emptype, IFNULL(empic,'') AS empic, empdept, deptcode, empid, empname, empimg, 
ppjoin01, ppcomfirm01, ppresign01, pppost01, postcode,
ppbasic02, pptype02, ppfreq02, ppcur02, ppeffdate02,
ppmarriage03, ppspouse03, ppchild03, 
ppcrby, ppcron, ppupby, ppupon 
FROM pemployee
LEFT JOIN pdepart ON empdept=deptid
LEFT JOIN pemppay ON ppempid=empid
LEFT JOIN pposition on pppost01=postid
ORDER BY empid, empname; */



?>
<div class="container my-1">
    <h4 class="mb-4 text-start">Employee Salary Profile</h4>
	<div class="text-center text-danger fw-bold mb-4"><?php echo $msg; ?></div>
    <div class="container mt-1">
	    <div class="row">
			<div class="col-sm-3">
			   
						
			    <?php if ($totalEmp>0){?>
					<div class="rounded p-1 bg-light" style="height: 700px; overflow-y: auto;">
						<div class="d-flex flex-column gap-2">
						
							<?php foreach ($employees as $emp): ?>
							    <?php 
								    $isInactive = (($emp['empstatus'] ?? '') === 'N'); 
									$dtlValue = (int)($emp['dtl'] ?? 0);
									$circleColorClass = ($dtlValue > 0) ? 'bg-success' : 'bg-danger';
								?>
									
								<div class="card border-0 shadow-sm overflow-hidden">									
									<div class="row g-0 align-items-center p-1 <?php echo $isInactive ? '' : 'bg-white'; ?>" style="<?php echo $isInactive ? 'background-color: pink;' : ''; ?>">
										
										<!-- 左侧：员工图片（调小尺寸） -->
										<div class="col-auto text-center ps-1">
											<img src="<?php echo htmlspecialchars($emp['empimg']); ?>" 
												class="img-fluid rounded emp-thumbnail" 
												alt="<?php echo htmlspecialchars($emp['empname']); ?>"
												style="height: 60px; width: 60px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
												data-id="<?php echo htmlspecialchars($emp['empid']); ?>"
												data-img="<?php echo htmlspecialchars($emp['empimg']); ?>"
												data-name="<?php echo htmlspecialchars($emp['empname']); ?>"
												data-type="<?php echo htmlspecialchars($emp['emptype']); ?>"
												data-dept="<?php echo htmlspecialchars($emp['deptcode']); ?>"
												data-ic="<?php echo htmlspecialchars($emp['empic']); ?>"
												data-post="<?php echo htmlspecialchars($emp['pppost01']); ?>"
												data-join="<?php echo htmlspecialchars($emp['ppjoin01']); ?>"
												data-con="<?php echo htmlspecialchars($emp['ppcomfirm01']); ?>"
												data-res="<?php echo htmlspecialchars($emp['ppresign01']); ?>"
												onclick="fillEmployeeForm(this)"
												onmouseover="this.style.transform='scale(1.05)';"
												onmouseout="this.style.transform='scale(1)';">
										</div>
										
										<!-- 右侧：员工信息 -->
										<div class="col">
											<div class="card-body py-1 ps-2">
												<h6 class="card-title mb-1 text-primary d-flex align-items-center gap-2">
													<span><?php echo htmlspecialchars($emp['empid']); ?></span>
													<div class="<?php echo $circleColorClass; ?> rounded-circle" 
														 style="width: 12px; height: 12px; flex-shrink: 0;" 
														 title="DTL: <?php echo $dtlValue; ?>">
													</div>
												</h6>
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
					<p class="text-danger mb-0">* Pink bg: Inactive Employee</p>
					<p class="text-danger mb-0">* Red dot: Pending Salary Profile</p>
			    <?php }else{ ?>
					<p class="text-danger">No active employee found</p>
			    <?php } ?>
			</div>
			
			
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
				
				<div class="card-body">
                    <form method="POST" action="?page=empsalary">
						<div class="card">
						    <div class="card-body">
							    <h5 class="card-title text-bg-secondary fw-bold">&nbsp;Basic Information </h5>
							    <!-- Changed g-3 to g-2 to match the row below -->
								<div class="row w-100 g-2">
									<div class="col-2">
									  <label class="form-label text-primary fw-bold">Type</label>
									</div>
									<div class="col-2">
									  <input type="text" class="form-control bg-light" id="form-emptype" name="emptype" value="" readonly>
									</div>
									<div class="col-8">
									  &nbsp;
									</div>
								</div>	
									
							    <div class="row w-100 g-2">
									<div class="col-2">
									  <label class="form-label text-primary fw-bold">Employee</label>
									</div>
									<div class="col-2">
									  <input type="text" class="form-control bg-light" id="form-empid" name="empid" value="" readonly>
									</div>
									<div class="col-8">
									  <input type="text" class="form-control bg-light" id="form-empname" name="empname" value="" readonly>
									</div>
							    </div>

							    <div class="row w-100 g-2">
									<div class="col-2">
									  <label class="form-label text-primary fw-bold">Department</label>
									</div>
									<div class="col-4">
									  <input type="text" class="form-control bg-light" id="form-empdept" name="empdept" value="" readonly>
									</div>
									<div class="col-2">
									  <label class="form-label text-primary fw-bold">IC/Passport</label>
									</div>
									<div class="col-4">
									  <input type="text" class="form-control bg-light" id="form-empic" name="empic" value="" readonly>
									</div>
							    </div>

								<?php
								try {
									$postStmt = $pdo->query("SELECT postid,postcode,postname FROM pposition WHERE poststts='Y' ORDER BY postcode;");
									$positions = $postStmt->fetchAll();
								} catch (PDOException $e) {
									$positions = [];
								}
								$current_post = isset($emp['pppost01']) ? $emp['pppost01'] : ''; 
								?>
								<div class="row w-100 g-2">
									<div class="col-2">
									  <label class="form-label fw-bold">Position</label>
									</div>
									<div class="col-4">
									  <select class="form-select" id="form-emppost" name="emppost" required>
										<option value="">-- Select Position --</option>
										<?php foreach ($positions as $post): ?>
											<option value="<?php echo htmlspecialchars($post['postid']); ?>" 
												<?php echo ($current_post == $post['postid']) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($post['postcode'] . ' - ' . $post['postname']); ?>
											</option>
										<?php endforeach; ?>
									  </select>
									</div>					
									<div class="col-2">
									  <label class="form-label fw-bold">Date Joined</label>
									</div>
									<div class="col-4">
									  <input type="date" class="form-control" id="form-empjoin" name="empjoin" value="">
									</div>
									<div class="col-2">
									  <label class="form-label fw-bold">Date Confirm</label>
									</div>
									<div class="col-4">
									  <input type="date" class="form-control" id="form-empcon" name="empcon" value="">
									</div>
									<div class="col-2">
									  <label class="form-label fw-bold">Date Resign</label>
									</div>
									<div class="col-4">
									  <input type="date" class="form-control" id="form-empres" name="empres" value="">
									</div>
							    </div>
							</div>
						</div>
			            
						<div class="card">
						    <div class="card-body">
							    <h5 class="card-title text-bg-secondary fw-bold">&nbsp;Salary Information </h5>
								<div class="row w-100 g-2">
									<div class="col-2">
									  <label class="form-label fw-bold">Salary</label>
									</div>
									<div class="col-4">
									  <input type="text" class="form-control bg-light" id="form-empsal" name="empsal" value="" >
									</div>
									<div class="col-2">
									  <label class="form-label fw-bold">Currency</label>
									</div>
									<div class="col-4">
									  <input type="text" class="form-control bg-light" id="form-empcur" name="empcur" value="" >
									</div>
							    </div>
					        </div>
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
		const empType = element.getAttribute('data-type');
		const empDept = element.getAttribute('data-dept');
		const empIC = element.getAttribute('data-ic');
		const empPost = element.getAttribute('data-post');
		const empJoin = element.getAttribute('data-join');
		const empCon = element.getAttribute('data-con');
		const empRes = element.getAttribute('data-res');

	
		// Populate inputs
		const idInput = document.getElementById('form-empid');
		idInput.value = empId;
		idInput.readOnly = true; // Lock ID field for editing
		idInput.classList.add('bg-light');
		
		document.getElementById('form-empname').value = empName;
		if(document.getElementById('form-emptype')) document.getElementById('form-emptype').value = empType;
        if(document.getElementById('form-empdept')) document.getElementById('form-empdept').value = empDept;
		if(document.getElementById('form-empic')) document.getElementById('form-empic').value = empIC;
		if(document.getElementById('form-emppost')) document.getElementById('form-emppost').value = empPost;
		if(document.getElementById('form-empjoin')) document.getElementById('form-empjoin').value = empJoin;
		if(document.getElementById('form-empcon')) document.getElementById('form-empcon').value = empCon;
		if(document.getElementById('form-empres')) document.getElementById('form-empres').value = empRes;
	}
	function handleSearchKeyPress(event) {
		if (event.key === "Enter") {
			event.preventDefault(); // Stop form submission
			searchEmployeeById();
		}
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

</script>