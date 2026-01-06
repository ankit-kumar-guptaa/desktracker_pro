<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="chart-container">
    <h4 class="mb-4"><i class="fas fa-clock"></i> Idle Time Analysis</h4>
    
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
    
    <!-- Chart Section -->
    <div class="row">
        <div class="col-md-8">
            <div class="chart-container">
                <h5 class="mb-3">Idle Time by Employee</h5>
                <canvas id="idleChart"></canvas>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="chart-container">
                <h5 class="mb-3">Top Idle Employees</h5>
                <div id="idleList">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let idleChart = null;

async function loadReport() {
    const date = document.getElementById('dateSelect').value;
    
    const response = await fetch(`../../api/reports.php?type=idle_analysis&date=${date}`);
    const data = await response.json();
    
    if (data && data.length > 0) {
        const labels = data.map(emp => emp.name);
        const idleMinutes = data.map(emp => Math.floor(emp.idle_time / 60));
        
        // Destroy previous chart
        if (idleChart) idleChart.destroy();
        
        // Create chart
        const ctx = document.getElementById('idleChart').getContext('2d');
        idleChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Idle Time (minutes)',
                    data: idleMinutes,
                    backgroundColor: '#f97316'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Create list
        let listHtml = '<div class="list-group">';
        data.slice(0, 5).forEach((emp, index) => {
            const minutes = Math.floor(emp.idle_time / 60);
            const percentage = emp.idle_percentage ? emp.idle_percentage.toFixed(1) : 0;
            
            listHtml += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>${index + 1}. ${emp.name}</strong>
                        <span class="badge bg-warning">${minutes} min</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: ${percentage}%"></div>
                    </div>
                    <small class="text-muted">${percentage}% of total time</small>
                </div>
            `;
        });
        listHtml += '</div>';
        
        document.getElementById('idleList').innerHTML = listHtml;
    } else {
        document.getElementById('idleList').innerHTML = '<p class="text-muted">No data available</p>';
    }
}

// Load today's report
loadReport();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
