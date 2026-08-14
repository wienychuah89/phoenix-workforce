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
	if($action==="TOGGLE_STATUS"){
		$currentUser = htmlspecialchars($_SESSION['username'] ?? 'SYSTEM'); 
		date_default_timezone_set('Asia/Kuala_Lumpur');
		$currentDate = date('Y-m-d H:i:s');
		$editRow  = trim($_POST['hiderow'] ?? '');
		$editCode = trim($_POST['hidpostcode' . $editRow] ?? '');
		$editStts = trim($_POST['hidpoststts' . $editRow] ?? '');
			
		if ($editStts==="Y"){
			$updateStts = "N";
		}else{
			$updateStts = "Y";
		}
		$updateData = [
			':ppostcode'  => $editCode,
			':ppoststts'   => $updateStts,
			':ppostupdate'=> $currentDate
		];
		try {
			// 1. Prepare the SQL statement with SET clause and WHERE clause
			$sql = "UPDATE pposition 
					SET poststts = :ppoststts, postupdate = :ppostupdate
					WHERE postcode = :ppostcode";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the data array
			$stmt->execute($updateData);

			// 3. Check how many rows were actually changed
			$rowCount = $stmt->rowCount();
			$msg = "Successfully toggle position status of " .$editCode. " to " . $updateStts; 
			
			saveLog(
				$pdo,
				$currentUser, 
				'UPDATE', 
				'pposition', 
				$editCode, 
				"Toggle position code " . $editCode. " to " .$updateStts
			);
		} catch (PDOException $e) {
			$msg = "Toggle failed: " . $e->getMessage();
		}
		$action="";
		$hiderow = "";
	}
	
	if ($action==="SAVE_POST"){
		$currentUser = htmlspecialchars($_SESSION['username'] ?? 'SYSTEM'); 
		date_default_timezone_set('Asia/Kuala_Lumpur');
		// 直接用 date() 函数，传入格式字符串
		$currentDate = date('Y-m-d H:i:s');

		$editRow  = trim($_POST['hiderow'] ?? '');
		$editCode = trim($_POST['hidpostcode' . $editRow] ?? '');
		$editName  = trim($_POST['postname' . $editRow] ?? '');
        $editDesc  = trim($_POST['postdesc' . $editRow] ?? '');
        $updateData = [
			':ppostcode'  => $editCode,
			':ppostname'   => $editName,
			':ppostdesc'  => $editDesc,
			':ppostupdate'=> $currentDate
		];
		try {
			// 1. Prepare the SQL statement with SET clause and WHERE clause
			$sql = "UPDATE pposition 
					SET postname = :ppostname, postdesc = :ppostdesc, postupdate = :ppostupdate
					WHERE postcode = :ppostcode";
			$stmt = $pdo->prepare($sql);

			// 2. Execute by passing the data array
			$stmt->execute($updateData);

			// 3. Check how many rows were actually changed
			$rowCount = $stmt->rowCount();
			$msg = "Successfully update position detail row(s): " .$editRow. " : " . $editCode. " -" .$editName; 
			
			saveLog(
				$pdo,
				$currentUser, 
				'UPDATE', 
				'pposition', 
				$editCode, 
				"Updated position code " . $editCode. " - " .$editName
			);
		} catch (PDOException $e) {
			$msg = "Update failed: " . $e->getMessage();
		}
		$action="";
		$hiderow = "";
	}
    if ($action === 'ADD_POST') {
        $newCode  = trim($_POST['txtNewCode'] ?? '');
		$newName   = trim($_POST['txtNewName'] ?? '');
        $newDesc  = trim($_POST['txtNewDesc'] ?? '');
        // 安全获取用户名
        $currentUser = htmlspecialchars($_SESSION['username'] ?? 'SYSTEM'); 
        date_default_timezone_set('Asia/Kuala_Lumpur');
		// 直接用 date() 函数，传入格式字符串
		$currentDate = date('Y-m-d H:i:s');

        if (!empty($newCode) && !empty($newName)) {
            try {
                // 检查 ID 是否已存在
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pposition WHERE postcode = :id");
                $checkStmt->execute([':id' => $newCode]);
                
                if ($checkStmt->fetchColumn() > 0) {
                    //no action if id already existing!
                } else {
                    // 插入 psalarycate 表
                    $sql = "INSERT INTO pposition 
                            (postcode, postname, postdesc, postupdate) 
                            VALUES 
                            (:ppostcode, :ppostname, :ppostdesc, :ppostupdate)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':ppostcode'     => $newCode,
                        ':ppostname'   => $newName,
						':ppostdesc'   => $newDesc,
                        ':ppostupdate'   => $currentDate
                    ]);
                    saveLog(
						$pdo,
						$currentUser, 
						'INSERT', 
						'pposition', 
						$newCode, 
						"Add new position[pposition] " . $newCode . $newName
					);
					$msg = "Successfully add new position ". $newCode. " - " .$newName; 
                }
            } catch (PDOException $e) {
                $msg = "Save New Position Failed: " . $e->getMessage();
            }
        } else {
            $msg = "Please Column Code, Name To Proceed.";
        }
    }
	$position = $pdo->query("SELECT postid, postcode, postname, postdesc, postupdate, poststts FROM pposition order by postid;")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container my-1">
    <div class="text-center text-danger fw-bold mb-2"><?php echo $msg; ?></div>
	<div class="container bg-white p-3 rounded shadow-sm">
	    <h6 class="mb-2 fw-bold">Position Setup</h6>
		<form id="mainForm" method="POST" action="">
		    <input type="hidden" name="action" id="form-action" value="">
			<!-- 1. NEW: Quick Search Box -->
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body bg-light rounded">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <span class="fw-bold text-secondary">Find Position:</span>
                        </div>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="text" id="search-post" class="form-control" placeholder="Type Position Code/Name and press Enter..." onkeypress="handleSearchKeyPress(event)">
                                <button type="button" class="btn btn-outline-primary" onclick="searchPosition()">Search</button>
                            </div>
                            <div id="search-error" class="text-danger small mt-1 d-none">Position not found!</div>
                        </div>
                    </div>
                </div>
            </div>
			<!-- 核心：用 PHP 循环把所有部门数据渲染成隐藏的 HTML 元素，供 JS 搜索 -->
			<div id="post-data-container" style="display: none;">
				<?php foreach ($position as $post): ?>
					<div class="post-item" 
						 data-id="<?php echo htmlspecialchars($post['postid']); ?>"
						 data-code="<?php echo htmlspecialchars(trim($post['postcode'])); ?>"
						 data-name="<?php echo htmlspecialchars(trim($post['postname'])); ?>"
						 data-desc="<?php echo htmlspecialchars($post['postdesc']); ?>"
						 data-update="<?php echo htmlspecialchars($post['postupdate']); ?>"
						 data-stts="<?php echo htmlspecialchars($post['poststts']); ?>">>
					</div>
				<?php endforeach; ?>
			</div>

			<table class="table table-bordered table-hover align-middle" id="positionTable">
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
				    <?php $cntPost = 1; ?>
					<?php foreach ($position as $index => $post) { ?>
				    <tr>
						<td class="row-no"><?php echo $cntPost; ?></td>  
						<td>
							<input type="text" 
							   id="postcode<?php echo $cntPost; ?>" 
							   name="postcode<?php echo $cntPost; ?>" 
							   class="form-control edit-mode col-code <?php echo ($post['poststts'] === 'N') ? 'text-danger fw-bold' : ''; ?>" 
							   value="<?php echo htmlspecialchars($post['postcode']); ?>" 
							   disabled>
						</td>
						
						<input type="hidden" id="hidpostcode<?php echo $cntPost; ?>" name="hidpostcode<?php echo $cntPost; ?>" value="<?php echo htmlspecialchars($post['postcode']); ?>" >
						<input type="hidden" id="hidpoststts<?php echo $cntPost; ?>" name="hidpoststts<?php echo $cntPost; ?>" value="<?php echo htmlspecialchars($post['poststts']); ?>" >
						
						<td>
							<input type="text" 
							   id="postname<?php echo $cntPost; ?>" 
							   name="postname<?php echo $cntPost; ?>" 
							   class="form-control edit-mode col-name <?php echo ($post['poststts'] === 'N') ? 'text-danger fw-bold' : ''; ?>" 
							   value="<?php echo htmlspecialchars($post['postname']); ?>" 
							   disabled>
						</td>
							   
						<td>
							<input type="text" 
							   id="postdesc<?php echo $cntPost; ?>" 
							   name="postdesc<?php echo $cntPost; ?>" 
							   class="form-control edit-mode col-desc <?php echo ($post['poststts'] === 'N') ? 'text-danger fw-bold' : ''; ?>" 
							   value="<?php echo htmlspecialchars($post['postdesc']); ?>" 
							   disabled>
						</td>
						<td>
							<button type="button" class="btn btn-sm btn-primary btn-edit view-mode" id="postedit_<?php echo $cntPost; ?>" name="postedit_<?php echo $cntPost; ?>">EDIT</button>
							<?php if (htmlspecialchars($post['poststts'])=="Y"){?>
							&nbsp;<button type="button" class="btn btn-sm btn-warning btn-chg edit-mode" id="postchg_<?php echo $cntPost; ?>" name="postchg_<?php echo $cntPost; ?>">⚪ Inactive</button>
							<?php }else{?>
							&nbsp;<button type="button" class="btn btn-sm btn-success btn-chg edit-mode" id="postchg_<?php echo $cntPost; ?>" name="postchg_<?php echo $cntPost; ?>">🟢 Activate</button>
							<?php }?>
							<button type="button" class="btn btn-sm btn-success btn-save edit-mode d-none" id="postsave_<?php echo $cntPost; ?>" name="postsave_<?php echo $cntPost; ?>">SAVE</button>
							<button type="button" class="btn btn-sm btn-danger btn-abort edit-mode d-none" id="postabort_<?php echo $cntPost; ?>" name="postabort_<?php echo $cntPost; ?>">ABORT</button>
						</td>
					
					</tr>
					<?php
                        $cntPost++; 
                    } ?>
				</tbody>
			</table>
			<input type="hidden" name="hidrow" id="hidrow" value="<?php echo $cntPost - 1; ?>"> 
			<input type="hidden" name="hiderow" id="hiderow" value="<?php echo $hiderow;?>"> 
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary" id="btnAdd" name="btnAdd">ADD NEW</button>
            </div>
	    </form>     
	</div>
</div>
<script>
const tableBody = document.querySelector('#positionTable tbody');
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
		document.getElementById('postname'+rowIndex).disabled = false;
		document.getElementById('postdesc'+rowIndex).disabled = false;
        toggleAllEditButtons(true);         // 隐藏所有的 EDIT 按钮
        toggleRowSaveButton(row, false);     // 显示当前行的 SAVE 按钮
        toggleRowAbortButton(row, false);   // 显示当前行的 ABORT 按钮
		toggleAllChgButtons(true);

        if (btnAdd) btnAdd.disabled = true;
        if (formAction) formAction.value = 'update_position';
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
		toggleAllChgButtons(false);

        //row.querySelectorAll('input, select').forEach(input => {
            //input.disabled = true;
        //});

        if (btnAdd) btnAdd.disabled = false;
        if (formAction) formAction.value = 'SAVE_POST';
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
	// --- 处理 TERMINATE/ACTIVE 按钮 ---
    const chgBtn = e.target.closest('.btn-chg');
    if (chgBtn) {
        const row = chgBtn.closest('tr');
        const rowIndex = chgBtn.id.split('_')[1];
        document.getElementById('hiderow').value=rowIndex;
        toggleAllEditButtons(false);        // 恢复显示所有 EDIT 按钮
        toggleRowSaveButton(row, true);     // 隐藏当前行的 SAVE 按钮
        toggleRowAbortButton(row, true);    // 隐藏当前行的 ABORT 按钮
		toggleAllChgButtons(false);

        if (btnAdd) btnAdd.disabled = false;
        if (formAction) formAction.value = 'TOGGLE_STATUS';
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
        toggleAllChgButtons(false);
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
		toggleAllChgButtons(true);

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
            if (formAction) formAction.value = 'ADD_POST';

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
			toggleAllChgButtons(false);
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
function toggleAllChgButtons(hide) {
    const chgButtons = tableBody.querySelectorAll('.btn-chg');
    chgButtons.forEach(btn => {
        btn.style.display = hide ? 'none' : '';
    });
}

function handleSearchKeyPress(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchPosition();
    }
}

window.searchPosition = function(forcedValue = null) {
    const searchInput = document.getElementById('search-post');
    const searchVal = (forcedValue ? forcedValue : searchInput.value).trim().toLowerCase();
    const errorDiv = document.getElementById('search-error');
    
    // 1. 直接获取表格主体（tbody）里的所有行
    const tableBody = document.querySelector('#positionTable tbody');
    if (!tableBody) return;
    const rows = tableBody.querySelectorAll('tr');
    
    // 情况 A：如果搜索框被清空，直接显示所有行并退出
    if (searchVal === '') {
        rows.forEach(row => row.classList.remove('d-none'));
        if (errorDiv) errorDiv.classList.add('d-none');
        return;
    }

    // 格式化输入框文字为大写（比如你输入 prd 自动变 PRD）
    if (searchInput) {
        searchInput.value = searchVal.toUpperCase();
    }

    let matchCount = 0;

    // 情况 B：遍历每一行，直接去读里面的 input 的真实 value
    rows.forEach(row => {
        // 通过 class 或是 input 的相对位置找到 Code 和 Name 的输入框
        const codeInput = row.querySelector('.col-code') || row.querySelector('td:nth-child(2) input');
        const nameInput = row.querySelector('.col-name') || row.querySelector('td:nth-child(3) input');

        // 安全地获取输入框内的真实文本值
        const postcode = codeInput ? codeInput.value.trim().toLowerCase() : '';
        const postname = nameInput ? nameInput.value.trim().toLowerCase() : '';

        // 核心匹配逻辑：Code 刚好相等，或者 Name 里面包含了输入的关键词
        if (postcode === searchVal || postname.includes(searchVal)) {
            row.classList.remove('d-none'); // 找到了就显示这行
            matchCount++;
        } else {
            row.classList.add('d-none');    // 没找到就隐藏这行
        }
    });

    // 2. 根据最终的匹配数量决定是否显示 "Department not found!"
    if (matchCount === 0) {
        if (errorDiv) errorDiv.classList.remove('d-none');
    } else {
        if (errorDiv) errorDiv.classList.add('d-none');
    }
};


</script>