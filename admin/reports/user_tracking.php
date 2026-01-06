<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="chart-container">
    <h4 class="mb-4"><i class="fas fa-user-clock"></i> User Tracking Report</h4>
    
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
            <button class="btn btn-primary w-100" onclick="loadReport()">
                <i class="fas fa-search"></i> Generate Report
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4" id="statsCards" style="display: none;">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-blue">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 id="totalTime" class="mb-1">0h 0m</h3>
                <p class="text-muted mb-0">Total Time</p>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 id="productiveTime" class="mb-1">0h 0m</h3>
                <p class="text-muted mb-0">Productive Time</p>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-orange">
                    <i class="fas fa-coffee"></i>
                </div>
                <h3 id="idleTime" class="mb-1">0h 0m</h3>
                <p class="text-muted mb-0">Idle Time</p>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-green">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 id="productivity" class="mb-1">0%</h3>
                <p class="text-muted mb-0">Productivity</p>
            </div>
        </div>
    </div>
    
    <!-- Break History -->
    <div class="chart-container" id="breaksTable" style="display: none; margin-top: 20px;">
        <h5 class="mb-3"><i class="fas fa-mug-hot"></i> Break History</h5>
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Break ID</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="breaksList">
                <tr><td colspan="5" class="text-center">No data available</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Login/Logout Sessions -->
    <div class="chart-container" id="sessionsTable" style="display: none;">
        <h5 class="mb-3"><i class="fas fa-sign-in-alt"></i> Login/Logout Sessions</h5>
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Session ID</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="sessionsList">
                <tr><td colspan="5" class="text-center">No data available</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Load employees dropdown
async function loadEmployees() {
    const response = await fetch('../../api/employee_crud.php');
    const result = await response.json();
    
    let html = '<option value="">Select Employee</option>';
    result.data.forEach(emp => {
        html += `<option value="${emp.id}">${emp.name} (${emp.emp_code})</option>`;
    });
    
    document.getElementById('employeeSelect').innerHTML = html;
}

// Load report
async function loadReport() {
    const empId = document.getElementById('employeeSelect').value;
    const date = document.getElementById('dateSelect').value;
    
    if (!empId) {
        alert('Please select an employee');
        return;
    }
    
    // Show stats cards
    document.getElementById('statsCards').style.display = 'flex';
    document.getElementById('sessionsTable').style.display = 'block';
    document.getElementById('breaksTable').style.display = 'block';
    
    // Fetch tracking data
    const response = await fetch(`../../api/reports.php?type=employee_details&employee_id=${empId}&date=${date}`);
    const data = await response.json();
    
    if (data.tracking) {
        const tracking = data.tracking;
        
        // Format time
        const formatTime = (seconds) => {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}m`;
        };
        
        const totalTime = tracking.total_time || 0;
        const productiveTime = tracking.productive_time || 0;
        const idleTime = tracking.idle_time || 0;
        const productivity = totalTime > 0 ? Math.round((productiveTime / totalTime) * 100) : 0;
        
        document.getElementById('totalTime').textContent = formatTime(totalTime);
        document.getElementById('productiveTime').textContent = formatTime(productiveTime);
        document.getElementById('idleTime').textContent = formatTime(idleTime);
        document.getElementById('productivity').textContent = productivity + '%';
    }
    
    // Load break history
    const breaksResponse = await fetch(`../../api/reports.php?type=break_logs&employee_id=${empId}&date=${date}`);
    const breaks = await breaksResponse.json();
    
    let breaksHtml = '';
    if (breaks && breaks.length > 0) {
        breaks.forEach(brk => {
            const duration = brk.duration ? Math.floor(brk.duration / 60) + ' min' : 'Active';
            const status = brk.end_time ? '<span class="badge bg-success">Completed</span>' : '<span class="badge bg-warning">On Break</span>';
            
            breaksHtml += `
                <tr>
                    <td>#${brk.id}</td>
                    <td>${new Date(brk.start_time).toLocaleTimeString()}</td>
                    <td>${brk.end_time ? new Date(brk.end_time).toLocaleTimeString() : '-'}</td>
                    <td>${duration}</td>
                    <td>${status}</td>
                </tr>
            `;
        });
    } else {
        breaksHtml = '<tr><td colspan="5" class="text-center">No breaks recorded for this date</td></tr>';
    }
    document.getElementById('breaksList').innerHTML = breaksHtml;

    // Load login sessions
    const sessionsResponse = await fetch(`../../api/reports.php?type=login_sessions&employee_id=${empId}&date=${date}`);
    const sessions = await sessionsResponse.json();
    
    let sessionsHtml = '';
    if (sessions && sessions.length > 0) {
        sessions.forEach(session => {
            const duration = session.session_duration ? Math.floor(session.session_duration / 60) + ' min' : 'Active';
            const status = session.logout_time ? '<span class="badge bg-success">Completed</span>' : '<span class="badge bg-primary">Active</span>';
            
            sessionsHtml += `
                <tr>
                    <td>#${session.id}</td>
                    <td>${new Date(session.login_time).toLocaleString()}</td>
                    <td>${session.logout_time ? new Date(session.logout_time).toLocaleString() : '-'}</td>
                    <td>${duration}</td>
                    <td>${status}</td>
                </tr>
            `;
        });
    } else {
        sessionsHtml = '<tr><td colspan="5" class="text-center">No sessions found for this date</td></tr>';
    }
    
    document.getElementById('sessionsList').innerHTML = sessionsHtml;
}

loadEmployees();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
