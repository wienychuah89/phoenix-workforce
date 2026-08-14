<?php
    // 检查用户是否已登录，如果没有，则重定向到登录页面
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: login.php');
        exit;
    }
    include 'cnopen.php';
    include 'function.php';
    $msg   = "";
    $action   = isset($_POST['action']) ? $_POST['action'] : '';
    //echo 'action : ' . htmlspecialchars($action) . '<br/>';

    
    $maincate = $pdo->query("SELECT pcateid, pcatename FROM `psalarycate` WHERE pcatestatus='Y' ORDER BY pcateid;")->fetchAll(PDO::FETCH_ASSOC);
	
	// 1. 先把所有子分类一次性查出来（避免在循环中查询数据库）
	$sql = "SELECT pcctncode, pcctndesc, pcctncate, pcatedesc, pcctntype, pcctnval, pcctnrate, pcctntax, pcctnemp,
			pcctnempval, pcctnemprate, pcctnstts 
			FROM pcatecontent 
			LEFT JOIN psalarycate ON pcateid = pcctncate 
			ORDER BY pcctncate, pcctncode";
			
	$allSubcate = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	// 将子分类按 pcctncate 分组
	$subcateGroup = [];
	foreach ($allSubcate as $sub) {
		$subcateGroup[$sub['pcctncate']][] = $sub;
	}
						
?>

<div class="container my-1">
    <div class="text-center text-danger fw-bold mb-2"><?php echo $msg; ?></div>
    <div class="container bg-white p-3 rounded shadow-sm">
        <h6 class="mb-2 fw-bold">Category Distribution</h6>
        
        <form id="mainForm" method="POST" action="">
            <!-- 全局隐藏域 -->
            <input type="hidden" name="action" id="form-action" value="">
			<?php 
			    $cntMain   = 1;
				$cntCate01 = 1; 
				
			    foreach ($maincate as $maincate_item) { 
			?> 
			    <div class="col-md-8">
					<label class="form-label fw-bold" style="color: purple;">
						Category : <?php echo htmlspecialchars($maincate_item['pcateid']) . ' ' . htmlspecialchars($maincate_item['pcatename']); ?>
					</label>
					
					<?php 
					// 【修改点】：必须先从数组中获取当前分类的子分类
					$currentSubcates = $subcateGroup[$maincate_item['pcateid']] ?? [];
					
					// 【修改点】：获取到数据后，再进行是否为空的判断
					if (empty($currentSubcates)): 
					?>
						<div class="text-danger" style="color: red;">No Content Found</div>
					<?php else: ?>
						
						<?php
						// 循环渲染子分类内容
						foreach ($currentSubcates as $subcate) {
							// 输出或渲染您的子分类内容
							echo htmlspecialchars($subcate['pcctndesc']) . '<br>'; // 加个换行方便观察
						} 
						?>

					<?php endif; ?>
				</div>

					
			<?php 
			    $cntMain++;
			    $cntCate01++; 
				}
			?>

            <input type="hidden" name="hidmain" id="hidmain" value="<?php echo $cntMain - 1; ?>"> 
            <input type="hidden" name="hidrow" id="hidrow" value="<?php echo $cntCate01 - 1; ?>"> 

            
        </form>
    </div>
</div>

<script>
// 1. 全局定义变量
const tableBody = document.querySelector('#categoryTable tbody');
const btnAdd = document.getElementById('btnAdd');
const formAction = document.getElementById('form-action');

// 2. 表格内部事件代理（只处理 EDIT, SAVE, ABORT）
tableBody.addEventListener('click', function(e) {

    // --- 处理 EDIT 按钮 ---
    const editBtn = e.target.closest('.btn-edit');
    if (editBtn) {
        const row = editBtn.closest('tr');
        const rowIndex = editBtn.id.split('_')[1];
        console.log('当前编辑的是第 ' + rowIndex + ' 行');

        toggleAllEditButtons(true);         // 隐藏所有的 EDIT 按钮
        toggleRowSaveButton(row, false);     // 显示当前行的 SAVE 按钮
        toggleRowAbortButton(row, false);   // 显示当前行的 ABORT 按钮

        // 开启当前行输入框，排除包含 cateid 的主键字段
        row.querySelectorAll('input, select').forEach(input => {
            if (!input.id.includes('cateid')) {
                input.disabled = false;
            }
        });

        if (btnAdd) btnAdd.disabled = true;
        if (formAction) formAction.value = 'update_category01';
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

        row.querySelectorAll('input, select').forEach(input => {
            input.disabled = true;
        });

        if (btnAdd) btnAdd.disabled = false;
        if (formAction) formAction.value = 'SAVE_CATE01';
        return; 
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
            <td><input type="text" class="form-control edit-mode col-id" placeholder="Enter ID" id="txtNewID" name="txtNewID" required></td>
            <td><input type="text" class="form-control edit-mode col-name" placeholder="Enter Name" id="txtNewName" name="txtNewName" required></td>
            <td><input type="text" class="form-control edit-mode col-desc" placeholder="Enter Description" id="txtNewDesc" name="txtNewDesc" required></td>
            <td>
                <select class="form-select edit-mode col-attribute" id="cboNewAttr" name="cboNewAttr">
                    <option value="A" selected>A - Addition</option>
                    <option value="D">D - Deduction</option>
                </select>
            </td>
            <td>
                <select class="form-select edit-mode col-status" id="cboNewStts" name="cboNewStts">
                    <option value="Y" selected>Active</option>
                    <option value="N">Non-Active</option>
                </select>
            </td>
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
            const idVal   = document.getElementById('txtNewID').value.trim();
            const nameVal = document.getElementById('txtNewName').value.trim();
            const descVal = document.getElementById('txtNewDesc').value.trim();

            // 1. 必填项前端验证
            if (!idVal || !nameVal || !descVal) {
                alert("Please Fill Column（ID、Name、Description）！");
                return;
            }

            // 2. 赋值 form-action 操作标示
            if (formAction) formAction.value = 'ADD_CATE01';

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