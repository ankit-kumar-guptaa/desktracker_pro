<?php include '../includes/header.php'; ?>

<div class="chart-container">
    <h4 class="mb-4"><i class="fas fa-desktop"></i> Application Usage Report</h4>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label">Select Employee</label>
            <select class="form-select" id="employeeSelect">
                <option value="">All Employees</option>
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
    
    <!-- Application Chart -->
    <div class="row">
        <div class="col-md-8">
            <div class="chart-container">
                <h5 class="mb-3">Top Applications by Time Spent</h5>
                <canvas id="appChart"></canvas>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="chart-container">
                <h5 class="mb-3">Application List</h5>
                <div id="appList">
                    <p class="text-muted">No data available</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let appChart = null;

async function loadEmployees() {
    const response = await fetch('../../api/employee_crud.php');
    const result = await response.json();
    
    let html = '<option value="">All Employees</option>';
    result.data.forEach(emp => {
        html += `<option value="${emp.id}">${emp.name} (${emp.emp_code})</option>`;
    });
    
    document.getElementById('employeeSelect').innerHTML = html;
}

async function loadReport() {
    const empId = document.getElementById('employeeSelect').value;
    const date = document.getElementById('dateSelect').value;
    
    const url = empId ? 
        `../../api/reports.php?type=application_usage&employee_id=${empId}&date=${date}` :
        `../../api/reports.php?type=application_usage&date=${date}`;
    
    const response = await fetch(url);
    const apps = await response.json();
    
    if (apps && apps.length > 0) {
        const labels = apps.map(app => app.app_name);
        const data = apps.map(app => Math.floor(app.time_spent / 60)); // Convert to minutes
        
        // Destroy previous chart
        if (appChart) appChart.destroy();
        
        // Create new chart
        const ctx = document.getElementById('appChart').getContext('2d');
        appChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Time Spent (minutes)',
                    data: data,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#4facfe',
                        '#43e97b', '#fa709a', '#30cfd0', '#a8edea'
                    ]
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Create list
        let listHtml = '<div class="list-group">';
        apps.forEach((app, index) => {
            const minutes = Math.floor(app.time_spent / 60);
            listHtml += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${index + 1}. ${app.app_name}</strong>
                    </div>
                    <span class="badge bg-primary">${minutes} min</span>
                </div>
            `;
        });
        listHtml += '</div>';
        
        document.getElementById('appList').innerHTML = listHtml;
    } else {
        document.getElementById('appList').innerHTML = '<p class="text-muted">No application data found</p>';
    }
}

loadEmployees();
</script>

<?php include '../includes/footer.php'; ?>
