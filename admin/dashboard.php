<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-primary">Admin Dashboard</h4>
        <p class="text-muted mb-0">Overview of company performance and employee activity.</p>
    </div>
    <div class="date-display">
        <i class="fas fa-calendar-alt text-secondary me-2"></i>
        <span class="fw-bold" id="currentDateDisplay"></span>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Users</p>
                    <h3 id="totalUsers" class="fw-bold mb-0">0</h3>
                </div>
                <div class="stats-card-icon stats-card-blue">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-primary-subtle text-primary rounded-pill">Active Employees</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Present Today</p>
                    <h3 id="presentUsers" class="fw-bold mb-0">0</h3>
                </div>
                <div class="stats-card-icon stats-card-green">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-success-subtle text-success rounded-pill">Online Now</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Absent Today</p>
                    <h3 id="absentUsers" class="fw-bold mb-0">0</h3>
                </div>
                <div class="stats-card-icon stats-card-red">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-danger-subtle text-danger rounded-pill">Not Logged In</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Productivity</p>
                    <h3 id="productivity" class="fw-bold mb-0">0%</h3>
                </div>
                <div class="stats-card-icon stats-card-orange">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-warning-subtle text-warning rounded-pill">Avg. Score</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Productivity Trend -->
    <div class="col-md-8">
        <div class="chart-container h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Productivity Trend (7 Days)</h5>
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3">View Report</button>
            </div>
            <div style="height: 300px;">
                <canvas id="productivityTrendChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Time Distribution -->
    <div class="col-md-4">
        <div class="chart-container h-100">
            <h5 class="mb-4 fw-bold"><i class="fas fa-clock text-primary me-2"></i>Time Distribution</h5>
            <div style="height: 250px; position: relative;">
                <canvas id="timeDistributionChart"></canvas>
            </div>
            <div class="mt-4 text-center">
                <div class="d-flex justify-content-center gap-3">
                    <div class="text-center">
                        <span class="d-block fw-bold text-primary" id="prodTimePercent">0%</span>
                        <small class="text-muted" style="font-size: 0.7rem;">Productive</small>
                    </div>
                    <div class="text-center">
                        <span class="d-block fw-bold text-warning" id="idleTimePercent">0%</span>
                        <small class="text-muted" style="font-size: 0.7rem;">Idle</small>
                    </div>
                    <div class="text-center">
                        <span class="d-block fw-bold text-danger" id="unprodTimePercent">0%</span>
                        <small class="text-muted" style="font-size: 0.7rem;">Unproductive</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Applications -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <h5 class="mb-3 fw-bold"><i class="fas fa-desktop text-primary me-2"></i>Top Applications Used</h5>
            <div style="height: 300px;">
                <canvas id="appUsageChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Websites -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <h5 class="mb-3 fw-bold"><i class="fas fa-globe text-primary me-2"></i>Top Websites Visited</h5>
            <div style="height: 300px;">
                <canvas id="webUsageChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Most Productive Users -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-trophy text-warning me-2"></i>Top Performers</h5>
                <a href="employees.php" class="text-decoration-none text-sm fw-semibold">View All</a>
            </div>
            <div id="topUsersList" class="custom-list">
                <!-- Content loaded via JS -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-6">
        <div class="chart-container h-100">
            <h5 class="mb-3 fw-bold"><i class="fas fa-bolt text-primary me-2"></i>Live Activity Feed</h5>
            <div id="recentActivity" class="activity-feed">
                <!-- Mock Activity Feed -->
                <div class="activity-item d-flex gap-3 mb-3">
                    <div class="activity-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="fas fa-login"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-semibold text-dark">System Update</p>
                        <small class="text-muted">Real-time activity feed coming soon...</small>
                    </div>
                    <small class="text-muted ms-auto">Just now</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Set current date
    document.getElementById('currentDateDisplay').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    // Format seconds to readable time
    function formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
    }

    // Load dashboard data
    async function loadDashboardData() {
        const date = new Date().toISOString().split('T')[0];
        
        try {
            // 1. Company productivity stats
            const response = await fetch(`../api/reports.php?type=company_productivity&date=${date}`);
            const data = await response.json();
            
            document.getElementById('totalUsers').textContent = data.total_users;
            document.getElementById('presentUsers').textContent = data.present_users;
            document.getElementById('absentUsers').textContent = data.absent_users;
            document.getElementById('productivity').textContent = data.productivity + '%';
            
            // 2. Time Distribution Pie Chart
            const ctx1 = document.getElementById('timeDistributionChart').getContext('2d');
            
            // Update percentage texts
            // Note: These are mocked calculations based on the hardcoded chart data for now, 
            // ideally should come from backend if available in breakdown
            document.getElementById('prodTimePercent').textContent = '65%';
            document.getElementById('idleTimePercent').textContent = '20%';
            document.getElementById('unprodTimePercent').textContent = '15%';

            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: ['Productive', 'Idle', 'Unproductive'],
                    datasets: [{
                        data: [65, 20, 15], // Static for demo as backend aggregates aren't fully split yet
                        backgroundColor: ['#003366', '#f59e0b', '#d32f2f'], 
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            
            // 3. Productivity Trend Line Chart
            const trendResponse = await fetch(`../api/reports.php?type=productivity_trend&date=${date}`);
            const trendData = await trendResponse.json();
            
            // Process trend data
            const trendLabels = trendData.map(d => new Date(d.date).toLocaleDateString('en-US', {weekday: 'short'}));
            const trendValues = trendData.map(d => d.productivity);

            const ctx2 = document.getElementById('productivityTrendChart').getContext('2d');
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: trendLabels.length ? trendLabels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Productivity %',
                        data: trendValues.length ? trendValues : [0, 0, 0, 0, 0, 0, 0],
                        borderColor: '#003366',
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, 'rgba(0, 51, 102, 0.2)');
                            gradient.addColorStop(1, 'rgba(0, 51, 102, 0.0)');
                            return gradient;
                        },
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#003366',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            max: 100,
                            grid: { borderDash: [4, 4], color: '#e2e8f0' },
                            ticks: { callback: function(value) { return value + '%' } }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13 },
                            bodyFont: { size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Productivity: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    }
                }
            });
            
            // 4. Top Applications Chart
            const appResponse = await fetch(`../api/reports.php?type=application_usage&date=${date}`);
            const appData = await appResponse.json();
            
            const appLabels = appData.map(a => a.app_name).slice(0, 5);
            const appValues = appData.map(a => Math.round(a.time_spent / 60)).slice(0, 5); // Minutes

            const ctxApp = document.getElementById('appUsageChart').getContext('2d');
            new Chart(ctxApp, {
                type: 'bar',
                data: {
                    labels: appLabels,
                    datasets: [{
                        label: 'Time (Minutes)',
                        data: appValues,
                        backgroundColor: '#003366',
                        borderRadius: 4,
                        barThickness: 30
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

            // 5. Top Websites Chart
            const webResponse = await fetch(`../api/reports.php?type=url_tracking&date=${date}`);
            const webData = await webResponse.json();
            
            const webLabels = webData.map(w => w.url_title.substring(0, 20) + '...').slice(0, 5);
            const webValues = webData.map(w => Math.round(w.time_spent / 60)).slice(0, 5); // Minutes

            const ctxWeb = document.getElementById('webUsageChart').getContext('2d');
            new Chart(ctxWeb, {
                type: 'bar',
                data: {
                    labels: webLabels,
                    datasets: [{
                        label: 'Time (Minutes)',
                        data: webValues,
                        backgroundColor: '#d32f2f', // Red for websites
                        borderRadius: 4,
                        barThickness: 30
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

            // 6. Top Users List
            const topUsersResponse = await fetch(`../api/reports.php?type=top_users&date=${date}`);
            const topUsers = await topUsersResponse.json();
            
            let topUsersHTML = '<div class="list-group list-group-flush">';
            if (topUsers.length > 0) {
                topUsers.forEach((user, index) => {
                    topUsersHTML += `
                        <div class="list-group-item px-0 py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">${index + 1}</div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">${user.name}</h6>
                                    <small class="text-muted">${user.emp_code}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success rounded-pill px-3 py-2">${user.productivity_percent}%</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                topUsersHTML += '<div class="text-center text-muted py-3">No data available yet</div>';
            }
            topUsersHTML += '</div>';
            document.getElementById('topUsersList').innerHTML = topUsersHTML;

        } catch (error) {
            console.error("Error loading dashboard data:", error);
        }
    }
    
    loadDashboardData();
</script>

<?php include 'includes/footer.php'; ?>