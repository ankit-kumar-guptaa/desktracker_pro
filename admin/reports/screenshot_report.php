<?php include '../includes/header.php'; ?>

<div class="chart-container">
    <h4 class="mb-4"><i class="fas fa-camera"></i> Screenshot Report</h4>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label">Select Employee</label>
            <select class="form-select" id="employeeSelect">
                <option value="">Loading...</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" id="dateSelect" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary w-100" onclick="loadScreenshots()">
                <i class="fas fa-search"></i> Load Screenshots
            </button>
        </div>
    </div>
    
    <div class="row" id="screenshotGallery">
        <p class="text-muted">Select employee and date to view screenshots</p>
    </div>
</div>

<!-- Screenshot Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Screenshot Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Screenshot">
                <p id="modalTime" class="text-muted mt-3"></p>
            </div>
        </div>
    </div>
</div>

<script>
async function loadEmployees() {
    const response = await fetch('../../api/employee_crud.php');
    const result = await response.json();
    
    let html = '<option value="">Select Employee</option>';
    result.data.forEach(emp => {
        html += `<option value="${emp.id}">${emp.name} (${emp.emp_code})</option>`;
    });
    
    document.getElementById('employeeSelect').innerHTML = html;
}

async function loadScreenshots() {
    const empId = document.getElementById('employeeSelect').value;
    const date = document.getElementById('dateSelect').value;
    
    if (!empId) {
        alert('Please select an employee');
        return;
    }
    
    const response = await fetch(`../../api/reports.php?type=employee_details&employee_id=${empId}&date=${date}`);
    const data = await response.json();
    
    const gallery = document.getElementById('screenshotGallery');
    
    if (data.screenshots && data.screenshots.length > 0) {
        let html = '';
        data.screenshots.forEach((screenshot, index) => {
            const imagePath = '../../' + screenshot.image_path.replace('../', '');
            html += `
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm">
                        <img src="${imagePath}" class="card-img-top" alt="Screenshot ${index + 1}" 
                             style="height: 200px; object-fit: cover; cursor: pointer;"
                             onclick="openModal('${imagePath}', '${screenshot.captured_at}')">
                        <div class="card-body">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                ${new Date(screenshot.captured_at).toLocaleString()}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        });
        gallery.innerHTML = html;
    } else {
        gallery.innerHTML = '<div class="col-12"><p class="text-muted text-center">No screenshots available for this date</p></div>';
    }
}

function openModal(imagePath, capturedAt) {
    document.getElementById('modalImage').src = imagePath;
    document.getElementById('modalTime').textContent = 'Captured: ' + new Date(capturedAt).toLocaleString();
    new bootstrap.Modal(document.getElementById('screenshotModal')).show();
}

loadEmployees();
</script>

<?php include '../includes/footer.php'; ?>
