<?php
	// 检查用户是否已登录，如果没有，则重定向到登录页面
	if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
		header('Location: login.php');
		exit;
	}
    $msg = "";
	include 'cnopen.php';
    include 'function.php';
    $msg   = "";
	$hiderow = "";
    $action   = isset($_POST['action']) ? $_POST['action'] : '';
	//echo 'action : ' .$action.'<br/>';
	
	if ($action==="SAVE_DEPT"){
		$currentUser = htmlspecialchars($_SESSION['username'] ?? 'SYSTEM'); 
        $currentDate = date('Y-m-d H:i:s');
		$editRow  = trim($_POST['hiderow'] ?? '');
		$editCode = trim($_POST['hiddeptcode' . $editRow] ?? '');
		$editName  = trim($_POST['deptname' . $editRow] ?? '');
        $editDesc  = trim($_POST['deptdesc' . $editRow] ?? '');
        $updateData = [
			':pdeptcode'  => $editCode,
			':pdeptname'   => $editName,
			':pdeptdesc'  => $editDesc,
			':pdeptupdate'=> $currentDate
		];
		try {
			// 1. Prepare the SQL statement with SET clause and WHERE clause
			$sql = "UPDATE pdepart 
					SET deptname = :pdeptname, deptdesc = :pdeptdesc, deptupdate = :pdeptupdate
					WHERE deptcode = :pdeptcode";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the data array
			$stmt->execute($updateData);

			// 3. Check how many rows were actually changed
			$rowCount = $stmt->rowCount();
			$msg = "Successfully update department detail " .$editRow. " row(s): " . $editCode. " -" .$editName; 
			
			saveLog(
				$pdo,
				$currentUser, 
				'UPDATE', 
				'pdepart', 
				$editCode, 
				"Updated department code " . $editCode. " - " .$editName
			);
		} catch (PDOException $e) {
			$msg = "Update failed: " . $e->getMessage();
		}
		$action="";
		$hiderow = "";
	}
    if ($action === 'ADD_DEPT') {
        $newCode  = trim($_POST['txtNewCode'] ?? '');
		$newName   = trim($_POST['txtNewName'] ?? '');
        $newDesc  = trim($_POST['txtNewDesc'] ?? '');
        // 安全获取用户名
        $currentUser = htmlspecialchars($_SESSION['username'] ?? 'SYSTEM'); 
        $currentDate = date('Y-m-d H:i:s');

        if (!empty($newCode) && !empty($newName)) {
            try {
                // 检查 ID 是否已存在
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pdepart WHERE deptcode = :id");
                $checkStmt->execute([':id' => $newCode]);
                
                if ($checkStmt->fetchColumn() > 0) {
                    //no action if id already existing!
                } else {
                    // 插入 psalarycate 表
                    $sql = "INSERT INTO pdepart 
                            (deptcode, deptname, deptdesc, deptupdate) 
                            VALUES 
                            (:pdeptcode, :pdeptname, :pdeptdesc, :pdeptupdate)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':pdeptcode'     => $newCode,
                        ':pdeptname'   => $newName,
						':pdeptdesc'   => $newDesc,
                        ':pdeptupdate'   => $currentDate
                    ]);
                    saveLog(
						$pdo,
						$currentUser, 
						'INSERT', 
						'pdepart', 
						$newCode, 
						"Add new deparment[pdepart] " . $newCode . $newName
					);
					$msg = "Successfully add new department ". $newCode. " - " .$newName; 
                }
            } catch (PDOException $e) {
                $msg = "Save New Department Failed: " . $e->getMessage();
            }
        } else {
            $msg = "Please Column Code, Name To Proceed.";
        }
    }
	$department = $pdo->query("SELECT deptid, deptcode, deptname, deptdesc, deptupdate FROM pdepart order by deptcode;")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container my-1">
    <div class="text-center text-danger fw-bold mb-2"><?php echo $msg; ?></div>
	<div class="container bg-white p-3 rounded shadow-sm">
	    <h6 class="mb-2 fw-bold">Department Setup</h6>
		<form id="mainForm" method="POST" action="">
		    <input type="hidden" name="action" id="form-action" value="">
			<table class="table table-bordered table-hover align-middle" id="departmentTable">
			    <thead class="table-light">
                    <tr>
                        <th style="width: 4%;">NO.</th>
                        <th style="width: 7%;">Code</th>
						<th style="width: 20%;">Name</th>
                        <th>Description</th>
                        <th style="width: 15%;">Action</th>
                    </tr>
                </thead>
				<tbody>
				    <?php $cntDept = 1; ?>
					<?php foreach ($department as $index => $dept) { ?>
				    <tr>
						<td class="row-no"><?php echo $cntDept; ?></td>  
						<td>
							<input type="text" 
							   id="deptcode<?php echo $cntDept; ?>" 
							   name="deptcode<?php echo $cntDept; ?>" 
							   class="form-control edit-mode col-code" 
							   value="<?php echo htmlspecialchars($dept['deptcode']); ?>" 
							   disabled>
						</td>
						
						<input type="hidden" id="hiddeptcode<?php echo $cntDept; ?>" name="hiddeptcode<?php echo $cntDept; ?>" value="<?php echo htmlspecialchars($dept['deptcode']); ?>" >
						
						<td>
							<input type="text" 
							   id="deptname<?php echo $cntDept; ?>" 
							   name="deptname<?php echo $cntDept; ?>" 
							   class="form-control edit-mode col-desc" 
							   value="<?php echo htmlspecialchars($dept['deptname']); ?>" 
							   disabled>
						</td>
							   
						<td>
							<input type="text" 
							   id="deptdesc<?php echo $cntDept; ?>" 
							   name="deptdesc<?php echo $cntDept; ?>" 
							   class="form-control edit-mode col-desc" 
							   value="<?php echo htmlspecialchars($dept['deptdesc']); ?>" 
							   disabled>
						</td>
						<td>
							<button type="button" class="btn btn-sm btn-primary btn-edit view-mode" id="deptedit_<?php echo $cntDept; ?>" name="deptedit_<?php echo $cntDept; ?>">EDIT</button>
							<button type="button" class="btn btn-sm btn-success btn-save edit-mode d-none" id="deptsave_<?php echo $cntDept; ?>" name="deptsave_<?php echo $cntDept; ?>">SAVE</button>
							<button type="button" class="btn btn-sm btn-danger btn-abort edit-mode d-none" id="deptabort_<?php echo $cntDept; ?>" name="deptabort_<?php echo $cntDept; ?>">ABORT</button>
						</td>
					
					</tr>
					<?php
                        $cntDept++; 
                    } ?>
				</tbody>
			</table>
			<input type="hidden" name="hidrow" id="hidrow" value="<?php echo $cntDept - 1; ?>"> 
			<input type="hidden" name="hiderow" id="hiderow" value="<?php echo $hiderow;?>"> 
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary" id="btnAdd" name="btnAdd">ADD NEW</button>
            </div>
	    </form>     
	</div>
</div>
<script>
const tableBody = document.querySelector('#departmentTable tbody');
const btnAdd = document.getElementById('btnAdd');
const formAction = document.getElementById('form-action');
// 2. 表格内部事件代理（只处理 EDIT, SAVE, ABORT）
tableBody.addEventListener('click', function(e) {
	const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
        const row = editBtn.closest('tr');
        const rowIndex = editBtn.id.split('_')[1];
        console.log('当前编辑的是第 ' + rowIndex + ' 行');
		document.getElementById('hiderow').value=rowIndex;
		
		//row.querySelectorAll('input, select').forEach(input => {
            //input.disabled = false;
        //});
		document.getElementById('deptname'+rowIndex).disabled = false;
		document.getElementById('deptdesc'+rowIndex).disabled = false;
        toggleAllEditButtons(true);         // 隐藏所有的 EDIT 按钮
        toggleRowSaveButton(row, false);     // 显示当前行的 SAVE 按钮
        toggleRowAbortButton(row, false);   // 显示当前行的 ABORT 按钮

        if (btnAdd) btnAdd.disabled = true;
        if (formAction) formAction.value = 'update_department';
        return; 
    }	
	// --- 处理 SAVE 按钮 ---
    const saveBtn = e.target.closest('.btn-save');
    if (saveBtn) {
        const row = saveBtn.closest('tr');
        const rowIndex = saveBtn.id.split('_')[1];

        toggleAllEditButtons(false);        // 恢复显示所有 EDIT 按钮
        toggleRowSaveButton(row, true);     // 隐藏当前行的 SAVE 按钮
        toggleRowAbortButton(row, true);    // 隐藏当前行的 ABORT 按钮

        //row.querySelectorAll('input, select').forEach(input => {
            //input.disabled = true;
        //});

        if (btnAdd) btnAdd.disabled = false;
        if (formAction) formAction.value = 'SAVE_DEPT';
		const mainForm = document.getElementById('mainForm');
		if (mainForm) {
			// 1. Submit the form first while elements are still ENABLED
			mainForm.submit();
			
			// 2. Delay disabling by 10-50ms so the browser has time to read the values
			setTimeout(() => {
				row.querySelectorAll('input, select').forEach(input => {
					input.disabled = true;
				});
			}, 50);
		
		
		} else {
			console.error("未找到 id='mainForm' 的 <form> 标签！");
		}

    }
	// --- 处理 ABORT 按钮 ---
    const abortBtn = e.target.closest('.btn-abort');
    if (abortBtn) {
        const row = abortBtn.closest('tr');

        toggleAllEditButtons(false);        // 恢复显示所有 EDIT 按钮
        toggleRowSaveButton(row, true);     // 隐藏当前行的 SAVE 按钮
        toggleRowAbortButton(row, true);    // 隐藏当前行的 ABORT 按钮

        row.querySelectorAll('input, select').forEach(input => {
            input.disabled = true;
        });

        if (btnAdd) btnAdd.disabled = false;
        if (formAction) formAction.value = '';
        return; 
    }	
});
// 3. 底部 ADD NEW 按钮独立的点击事件
if (btnAdd) {
    btnAdd.addEventListener('click', function(e) {
        e.preventDefault();
        
        // 避免重复创建新增行
        if (document.querySelector('.new-row-placeholder')) return;

        const newRow = document.createElement('tr');
        newRow.classList.add('editing-row', 'new-row-placeholder'); 
        newRow.innerHTML = `
            <td class="row-no"></td>
            <td><input type="text" class="form-control edit-mode col-code" placeholder="Enter Code" id="txtNewCode" name="txtNewCode" required></td>
			<td><input type="text" class="form-control edit-mode col-name" placeholder="Enter Name" id="txtNewName" name="txtNewName" required></td>
            <td><input type="text" class="form-control edit-mode col-desc" placeholder="Enter Description" id="txtNewDesc" name="txtNewDesc"></td>
			<td>
                <button type="button" class="btn btn-sm btn-success" id="btnNewSave">SAVE</button>
                <button type="button" class="btn btn-sm btn-danger" id="btnNewAbort">ABORT</button>
            </td>
        `;
        
        tableBody.appendChild(newRow);
        updateRowNumbers(); // 重新排序序号

        btnAdd.classList.add('d-none');
        toggleAllEditButtons(true); // 隐藏所有现有行的 EDIT 按钮

        // --- 绑定新增行的 SAVE 按钮事件 ---
        document.getElementById('btnNewSave').addEventListener('click', function() {
            const codeVal = document.getElementById('txtNewCode').value.trim();
			const nameVal = document.getElementById('txtNewName').value.trim();
            const descVal = document.getElementById('txtNewDesc').value.trim();
			

            // 1. 必填项前端验证
            if (!nameVal || !codeVal) {
                alert("Please Fill Column（Code and Name）！");
                return;
            }

            // 2. 赋值 form-action 操作标示
            if (formAction) formAction.value = 'ADD_DEPT';

            // 3. 提交表单
            const mainForm = document.getElementById('mainForm');
            if (mainForm) {
                mainForm.submit();
            } else {
                console.error("未找到 id='mainForm' 的 <form> 标签！");
            }
        });

        // --- 绑定新增行的 ABORT 按钮事件 ---
        document.getElementById('btnNewAbort').addEventListener('click', function() {
            newRow.remove();              // 1. 删除新增行
            updateRowNumbers();           // 2. 重新更新序号
            btnAdd.classList.remove('d-none'); // 3. 恢复显示 ADD 按钮
            toggleAllEditButtons(false);  // 4. 重新显示所有的 EDIT 按钮
            if (formAction) formAction.value = '';
        });
    });
}
// 序号刷新辅助函数
function updateRowNumbers() {
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach((row, idx) => {
        const noCell = row.querySelector('.row-no');
        if (noCell) noCell.textContent = idx + 1;
    });
}
// 辅助函数定义
function toggleAllEditButtons(hide) {
    const editButtons = tableBody.querySelectorAll('.btn-edit');
    editButtons.forEach(btn => {
        btn.style.display = hide ? 'none' : '';
    });
}
function toggleRowSaveButton(row, hide) {
    const saveBtn = row.querySelector('.btn-save');
    if (saveBtn) {
        if (hide) {
            saveBtn.classList.add('d-none');
        } else {
            saveBtn.classList.remove('d-none');
        }
    }
}
function toggleRowAbortButton(row, hide) {
    const abortBtn = row.querySelector('.btn-abort');
    if (abortBtn) {
        if (hide) {
            abortBtn.classList.add('d-none');
        } else {
            abortBtn.classList.remove('d-none');
        }
    }
}

</script>