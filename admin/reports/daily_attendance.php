<?php include '../includes/header.php'; ?>

<div class="chart-container">
    <h4 class="mb-4"><i class="fas fa-calendar-check"></i> Daily Attendance Report</h4>
    
    <div class="row mb-4">
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
    
    <!-- Summary Cards -->
    <div class="row mb-4" id="summaryCards">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-blue">
                    <i class="fas fa-users"></i>
                </div>
                <h3 id="totalEmployees" class="mb-1">0</h3>
                <p class="text-muted mb-0">Total Employees</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-green">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 id="presentEmployees" class="mb-1">0</h3>
                <p class="text-muted mb-0">Present</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-card-icon stats-card-red">
                    <i class="fas fa-user-times"></i>
                </div>
                <h3 id="absentEmployees" class="mb-1">0</h3>
                <p class="text-muted mb-0">Absent</p>
            </div>
        </div>
    </div>
    
    <!-- Attendance Table -->
    <div class="chart-container">
        <h5 class="mb-3">Attendance Details</h5>
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Employee Code</th>
                    <th>Name</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="attendanceTable">
                <tr><td colspan="6" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
async function loadReport() {
    const date = document.getElementById('dateSelect').value;
    
    // Load attendance data
    const response = await fetch(`../../api/reports.php?type=daily_attendance&date=${date}`);
    const data = await response.json();
    
    if (data.summary) {
        document.getElementById('totalEmployees').textContent = data.summary.total;
        document.getElementById('presentEmployees').textContent = data.summary.present;
        document.getElementById('absentEmployees').textContent = data.summary.absent;
    }
    
    // Load attendance table
    let tableHtml = '';
    if (data.attendance && data.attendance.length > 0) {
        data.attendance.forEach(record => {
            const totalHours = record.total_hours ? parseFloat(record.total_hours).toFixed(2) : '0.00';
            const status = record.login_time ? 
                '<span class="badge bg-success">Present</span>' : 
                '<span class="badge bg-danger">Absent</span>';
            
            tableHtml += `
                <tr>
                    <td><strong>${record.emp_code}</strong></td>
                    <td>${record.name}</td>
                    <td>${record.login_time ? new Date(record.login_time).toLocaleTimeString() : '-'}</td>
                    <td>${record.logout_time ? new Date(record.logout_time).toLocaleTimeString() : 'Active'}</td>
                    <td>${totalHours} hrs</td>
                    <td>${status}</td>
                </tr>
            `;
        });
    } else {
        tableHtml = '<tr><td colspan="6" class="text-center">No attendance data available</td></tr>';
    }
    
    document.getElementById('attendanceTable').innerHTML = tableHtml;
}

// Load today's report on page load
loadReport();
</script>

<?php include '../includes/footer.php'; ?>
