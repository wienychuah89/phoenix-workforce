<?php
    // 检查用户是否已登录，如果没有，则重定向到登录页面
	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
		header('Location: login.php');
		exit;
	}
    include 'cnopen.php';
	include 'function.php';
	$msg   = "";
	
	
    // 4b. FETCH ALL EMPLOYEES TO DISPLAY
	$employee = $pdo->query("SELECT empid, empname, emptype, IFNULL(ppbasic,0) AS ppbasic, empdept, deptcode, deptname FROM pemployee LEFT JOIN pemppay ON ppempid=empid
	LEFT JOIN pdepart ON deptid=empdept ORDER BY empid;")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-1">
	<h4 class="mb-4 fw-bold">Payroll Content</h4>
	<div class="text-center text-danger fw-bold mb-4"><?php echo $msg; ?></div>

    <div class="container mt-1">
	  <div class="row">
	    <?php 
		    $current_emp = '';
		?>
	    <div class="col-md-5">
			<label class="form-label fw-bold" for="form-empemp">Employee  *</label>
			<select class="form-select" id="form-empemp" name="empemp" >
				<option value="">-- Select Employee --</option>
				<?php foreach ($employee as $emp): ?>
					<option value="<?php echo htmlspecialchars($emp['empid']); ?>" 
						<?php echo ($current_emp == $emp['empid']) ? 'selected' : ''; ?>>
						<?php echo htmlspecialchars($emp['empid'] . ' - ' . $emp['empname']); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		  <!-- Year Selection Dropdown -->
		  <div class="col-md-2">
			<label for="salaryYear" class="form-label"><b>Select Year</b></label>
			<select class="form-select form-select-sm" id="salaryYear" name="salary_year">
			  <?php
			  $current_year = date('Y'); // Gets current year dynamically
			  for ($year = 2026; $year <= 2050; $year++) {
				  // Automatically 'select' the year if it matches current year
				  $selected = ($year == $current_year) ? 'selected' : '';
				  echo "<option value='$year' $selected>$year</option>";
			  }
			  ?>
			</select>
		  </div>

		  <!-- Month Selection Dropdown -->
		  <div class="col-md-2">
			<label for="salaryMonth" class="form-label"><b>Select Month</b></label>
			<select class="form-select form-select-sm" id="salaryMonth" name="salary_month">
			  <?php
			  $current_month = date('n'); // Gets current month number (1-12)
			  for ($month = 1; $month <= 12; $month++) {
				  $month_name = date("M", mktime(0, 0, 0, $month, 1)); // 'Jan', 'Feb', etc.
				  $month_value = sprintf("%02d", $month); // '01', '02', etc.
				  
				  // Automatically 'select' the current month
				  $selected = ($month == $current_month) ? 'selected' : '';
				  echo "<option value='$month_value' $selected>$month_name</option>";
			  }
			  ?>
			</select>
		  </div>
		  
		  <!-- NEW: Get Button Element -->
		  <div class="col-md-1 d-flex align-items-end mt-1">
			<button type="button" id="btn-get-details" class="btn btn-primary w-100">Get</button>
		  </div>
		  
		  <div id="employee-details-container" class="mt-1 d-none">
		    <div class="card card-body bg-light">
			  <div class="row">
			    <div class="col-md-2 mb-1"><strong>Type:</strong></div>
				<div class="col-md-2 mb-1"><span id="detail-emptype"></span></div>
				<div class="col-md-2 mb-1"><strong>Month/Year:</strong></div>
				<div class="col-md-4 mb-1"><span id="detail-empdate"></span></div>
			  </div>
			  <div class="row">
				<div class="col-md-2 mb-1"><strong>Basic Salary (MYR):</strong></div>
				<div class="col-md-6 mb-1"><span id="detail-ppbasic"></div>
			  </div>
		    </div>
          </div>


	  
	  
	  
	  
	  </div>
	</div>


</div>
<script>
	// Convert the PHP database array safely into a JavaScript array
	const employeeData = <?php echo json_encode($employee); ?>;
	document.getElementById('btn-get-details').addEventListener('click', function() {
		const selectedId = document.getElementById('form-empemp').value;
		const selectedYRS = document.getElementById('salaryYear').value;
		const selectedMTH = document.getElementById('salaryMonth').value;


		//const selectedMTHValue = document.getElementById('salaryMonth').value;

		// Convert the input value to a formatted short month name
		//const selectedMTH = selectedMTHValue ? new Date(selectedMTHValue + '-02').toLocaleString('en-US', { month: 'short' }) : '';

		
		
		const container = document.getElementById('employee-details-container');
		
		// Alert user if no employee is picked
		if (!selectedId) {
			alert('Please select an employee.');
			container.classList.add('d-none');
			return;
		}
		
		// Find the matching employee row in the array
		const matchedEmp = employeeData.find(emp => emp.empid == selectedId);

		
		if (matchedEmp) {
			// Inject data into the display spans
			document.getElementById('detail-emptype').textContent = matchedEmp.emptype || 'N/A';
			document.getElementById('detail-ppbasic').textContent = parseFloat(matchedEmp.ppbasic).toFixed(2);
			document.getElementById('detail-empdate').textContent = selectedYRS+ ' '+selectedMTH;
			// Reveal the details card
			container.classList.remove('d-none');
		} else {
			alert('Employee data not found.');
			container.classList.add('d-none');
		}
	});

	
</script>

</script>