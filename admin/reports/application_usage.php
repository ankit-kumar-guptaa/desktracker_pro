<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-desktop me-2"></i>Usage & Activity Report</h4>
        <p class="text-muted mb-0">Detailed analysis of application and website usage.</p>
    </div>
</div>

<div class="chart-container mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold text-primary">Select Employee</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-primary border-end-0"><i class="fas fa-user"></i></span>
                <select class="form-select border-start-0 ps-0" id="employeeSelect">
                    <option value="">All Employees</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold text-primary">Select Date</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-primary border-end-0"><i class="fas fa-calendar-alt"></i></span>
                <input type="date" class="form-control border-start-0 ps-0" id="dateSelect" value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100" onclick="loadReport()">
                <i class="fas fa-search me-2"></i> Generate Detailed Report
            </button>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Application Usage -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <h5 class="mb-3 fw-bold text-dark border-bottom pb-2">
                <i class="fas fa-window-maximize text-primary me-2"></i> Top Applications
            </h5>
            <div style="height: 300px;">
                <canvas id="appChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Website Usage -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <h5 class="mb-3 fw-bold text-dark border-bottom pb-2">
                <i class="fas fa-globe text-danger me-2"></i> Top Websites
            </h5>
            <div style="height: 300px;">
                <canvas id="urlChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Detailed Application List -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold text-dark">Application Details</h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill">Top 10</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">Application Name</th>
                            <th scope="col" class="text-end pe-3">Duration</th>
                            <th scope="col" class="text-end pe-3">Usage %</th>
                        </tr>
                    </thead>
                    <tbody id="appListBody">
                        <tr><td colspan="3" class="text-center text-muted py-4">Select criteria to view data</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Detailed Website List -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold text-dark">Website Details</h5>
                <span class="badge bg-danger-subtle text-danger rounded-pill">Top 10</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">Website URL</th>
                            <th scope="col" class="text-end pe-3">Duration</th>
                            <th scope="col" class="text-end pe-3">Category</th>
                        </tr>
                    </thead>
                    <tbody id="urlListBody">
                        <tr><td colspan="3" class="text-center text-muted py-4">Select criteria to view data</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let appChart = null;
let urlChart = null;

// Format seconds to readable string
function formatDuration(seconds) {
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    if (hrs > 0) return `${hrs}h ${mins}m`;
    return `${mins}m`;
}

async function loadEmployees() {
    try {
        const response = await fetch('../../api/employee_crud.php');
        const result = await response.json();
        
        let html = '<option value="">All Employees</option>';
        if (result.data) {
            result.data.forEach(emp => {
                html += `<option value="${emp.id}">${emp.name} (${emp.emp_code})</option>`;
            });
        }
        document.getElementById('employeeSelect').innerHTML = html;
        
        // Auto load report on first load
        loadReport();
    } catch (e) {
        console.error("Error loading employees", e);
    }
}

async function loadReport() {
    const empId = document.getElementById('employeeSelect').value;
    const date = document.getElementById('dateSelect').value;
    const btn = document.querySelector('button[onclick="loadReport()"]');
    
    // Show loading state
    const originalBtnText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Loading...';
    btn.disabled = true;

    try {
        // 1. Fetch Application Data
        const appUrl = empId ? 
            `../../api/reports.php?type=application_usage&employee_id=${empId}&date=${date}` :
            `../../api/reports.php?type=application_usage&date=${date}`;
        
        const appRes = await fetch(appUrl);
        const apps = await appRes.json();
        
        // 2. Fetch URL Data
        const urlUrl = empId ? 
            `../../api/reports.php?type=url_tracking&employee_id=${empId}&date=${date}` :
            `../../api/reports.php?type=url_tracking&date=${date}`;
            
        const urlRes = await fetch(urlUrl);
        const urls = await urlRes.json();
        
        updateAppChart(apps);
        updateUrlChart(urls);
        updateAppTable(apps);
        updateUrlTable(urls);
        
    } catch (error) {
        console.error("Error generating report:", error);
        alert("Failed to load report data. Please try again.");
    } finally {
        btn.innerHTML = originalBtnText;
        btn.disabled = false;
    }
}

function updateAppChart(data) {
    if (appChart) appChart.destroy();
    
    const ctx = document.getElementById('appChart').getContext('2d');
    
    if (!data || data.length === 0) {
        // Render empty state or handle
        return;
    }

    const labels = data.map(d => d.app_name);
    const values = data.map(d => Math.round(d.time_spent / 60)); // Minutes

    appChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Time (Minutes)',
                data: values,
                backgroundColor: '#003366',
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                y: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
}

function updateUrlChart(data) {
    if (urlChart) urlChart.destroy();
    
    const ctx = document.getElementById('urlChart').getContext('2d');
    
    if (!data || data.length === 0) return;

    const labels = data.map(d => d.url_title.length > 20 ? d.url_title.substring(0, 20) + '...' : d.url_title);
    const values = data.map(d => Math.round(d.time_spent / 60)); // Minutes

    urlChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Time (Minutes)',
                data: values,
                backgroundColor: '#d32f2f',
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                y: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
}

function updateAppTable(data) {
    const tbody = document.getElementById('appListBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No application usage data found</td></tr>';
        return;
    }
    
    const totalTime = data.reduce((sum, item) => sum + parseInt(item.time_spent), 0);
    
    let html = '';
    data.forEach(item => {
        const percent = totalTime > 0 ? Math.round((item.time_spent / totalTime) * 100) : 0;
        html += `
            <tr>
                <td class="ps-3 fw-semibold text-dark">${item.app_name}</td>
                <td class="text-end pe-3 font-monospace">${formatDuration(item.time_spent)}</td>
                <td class="text-end pe-3">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <span class="small text-muted">${percent}%</span>
                        <div class="progress" style="width: 50px; height: 4px;">
                            <div class="progress-bar bg-primary" style="width: ${percent}%"></div>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function updateUrlTable(data) {
    const tbody = document.getElementById('urlListBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No website usage data found</td></tr>';
        return;
    }
    
    let html = '';
    data.forEach(item => {
        let categoryBadge = '<span class="badge bg-secondary-subtle text-secondary">General</span>';
        if (item.category === 'productive') categoryBadge = '<span class="badge bg-success-subtle text-success">Productive</span>';
        if (item.category === 'unproductive') categoryBadge = '<span class="badge bg-danger-subtle text-danger">Unproductive</span>';
        
        html += `
            <tr>
                <td class="ps-3">
                    <div class="text-truncate" style="max-width: 200px;" title="${item.url_title}">
                        ${item.url_title}
                    </div>
                </td>
                <td class="text-end pe-3 font-monospace">${formatDuration(item.time_spent)}</td>
                <td class="text-end pe-3">${categoryBadge}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

loadEmployees();
</script>

<?php include '../includes/footer.php'; ?>