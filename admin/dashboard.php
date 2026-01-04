<?php include 'includes/header.php'; ?>

<style>
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        border-radius: 15px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
    }
    
    .dashboard-welcome h2 {
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .dashboard-date {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .stats-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .stats-icon-bg {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .bg-soft-primary { background: rgba(102, 126, 234, 0.1); color: #667eea; }
    .bg-soft-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .bg-soft-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .bg-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        height: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
    }

    .activity-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: 600;
        color: #4a5568;
    }
</style>

<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="dashboard-welcome">
                <h2>Welcome back, Admin! 👋</h2>
                <p class="mb-0">Here's what's happening in your workspace today.</p>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="dashboard-date">
                <i class="far fa-calendar-alt me-2"></i>
                <span id="currentDate"></span>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Total Employees</p>
                        <h3 class="mb-0" id="totalUsers">0</h3>
                    </div>
                    <div class="stats-icon-bg bg-soft-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-soft-success rounded-pill">+2 New</span>
                    <span class="text-muted small ms-2">this month</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Active Now</p>
                        <h3 class="mb-0" id="presentUsers">0</h3>
                    </div>
                    <div class="stats-icon-bg bg-soft-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Tracking in real-time</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Avg. Productivity</p>
                        <h3 class="mb-0" id="productivity">0%</h3>
                    </div>
                    <div class="stats-icon-bg bg-soft-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" id="productivityBar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="stats-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1">Absent Users</p>
                        <h3 class="mb-0" id="absentUsers">0</h3>
                    </div>
                    <div class="stats-icon-bg bg-soft-danger">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-danger small">Action needed</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">📊 Productivity Trends</div>
                <select class="form-select form-select-sm w-auto">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
            </div>
            <canvas id="productivityTrendChart" height="100"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">🌐 Top Visited Websites</div>
            </div>
            <canvas id="urlUsageChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Bottom Section -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">📱 Top Applications Used</div>
                <a href="reports/application_usage.php" class="btn btn-sm btn-light">View All</a>
            </div>
            <canvas id="appUsageChart" height="150"></canvas>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">🏆 Top Performers (Today)</div>
            </div>
            <div id="topUsersList" class="activity-list">
                <!-- Loaded via JS -->
                <div class="text-center py-4 text-muted">Loading data...</div>
            </div>
        </div>
    </div>
</div>

<script>
    // Set current date
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);

    // Load dashboard data
    async function loadDashboardData() {
        const date = new Date().toISOString().split('T')[0];
        
        try {
            // 1. Company Stats
            const response = await fetch(`../api/reports.php?type=company_productivity&date=${date}`);
            const data = await response.json();
            
            document.getElementById('totalUsers').textContent = data.total_users;
            document.getElementById('presentUsers').textContent = data.present_users;
            document.getElementById('absentUsers').textContent = data.absent_users;
            document.getElementById('productivity').textContent = data.productivity + '%';
            document.getElementById('productivityBar').style.width = data.productivity + '%';
            
            // 2. Productivity Trend Chart
            const ctxTrend = document.getElementById('productivityTrendChart').getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Productivity %',
                        data: [65, 72, 68, 75, 82, 45, 60], // Placeholder data - ideal to fetch real trend
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#667eea',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { borderDash: [2, 2] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 3. URL Usage Chart (Pie)
            const urlResponse = await fetch(`../api/reports.php?type=url_usage&date=${date}`);
            const urlData = await urlResponse.json();
            
            const urlLabels = urlData.map(u => u.url_title ? u.url_title.substring(0, 20) + '...' : 'Unknown');
            const urlValues = urlData.map(u => u.time_spent);
            const urlColors = ['#667eea', '#764ba2', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899'];

            const ctxUrl = document.getElementById('urlUsageChart').getContext('2d');
            new Chart(ctxUrl, {
                type: 'doughnut',
                data: {
                    labels: urlLabels.length ? urlLabels : ['No Data'],
                    datasets: [{
                        data: urlValues.length ? urlValues : [1],
                        backgroundColor: urlValues.length ? urlColors : ['#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } }
                    }
                }
            });

            // 4. App Usage Chart (Bar)
            const appResponse = await fetch(`../api/reports.php?type=application_usage&date=${date}`);
            const appData = await appResponse.json();
            
            const appLabels = appData.map(a => a.app_name);
            const appValues = appData.map(a => Math.round(a.time_spent / 60)); // Minutes

            const ctxApp = document.getElementById('appUsageChart').getContext('2d');
            new Chart(ctxApp, {
                type: 'bar',
                data: {
                    labels: appLabels.length ? appLabels : ['No Data'],
                    datasets: [{
                        label: 'Time (Minutes)',
                        data: appValues.length ? appValues : [0],
                        backgroundColor: '#764ba2',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 5. Top Performers List
            const topUsersResponse = await fetch(`../api/reports.php?type=top_users&date=${date}`);
            const topUsers = await topUsersResponse.json();
            
            let topUsersHTML = '';
            if (topUsers.length > 0) {
                topUsers.forEach((user, index) => {
                    const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                    topUsersHTML += `
                        <div class="activity-item">
                            <div class="activity-avatar bg-soft-primary text-primary">${initials}</div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">${user.name}</h6>
                                <small class="text-muted">${user.emp_code}</small>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-0 text-success">${user.productivity_percent}%</h6>
                                <small class="text-muted">Productivity</small>
                            </div>
                        </div>
                    `;
                });
            } else {
                topUsersHTML = '<div class="text-center py-4 text-muted">No activity recorded today.</div>';
            }
            document.getElementById('topUsersList').innerHTML = topUsersHTML;

        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }
    
    loadDashboardData();
</script>

<?php include 'includes/footer.php'; ?>