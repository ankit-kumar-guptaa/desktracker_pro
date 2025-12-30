<?php include 'includes/header.php'; ?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-card-icon stats-card-blue">
                <i class="fas fa-users"></i>
            </div>
            <h3 id="totalUsers" class="mb-1">0</h3>
            <p class="text-muted mb-0">Total Users</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-card-icon stats-card-green">
                <i class="fas fa-user-check"></i>
            </div>
            <h3 id="presentUsers" class="mb-1">0</h3>
            <p class="text-muted mb-0">Present Users</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-card-icon stats-card-red">
                <i class="fas fa-user-times"></i>
            </div>
            <h3 id="absentUsers" class="mb-1">0</h3>
            <p class="text-muted mb-0">Absent Users</p>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-card-icon stats-card-orange">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 id="productivity" class="mb-1">0%</h3>
            <p class="text-muted mb-0">Productivity</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Time Distribution Chart -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-3">Time Distribution</h5>
            <canvas id="timeDistributionChart"></canvas>
        </div>
    </div>
    
    <!-- Productivity Trend -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-3">7-Day Productivity Trend</h5>
            <canvas id="productivityTrendChart"></canvas>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Most Productive Users -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-trophy text-warning"></i> Most Productive Users</h5>
            <div id="topUsersList"></div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-clock"></i> Recent Activity</h5>
            <div id="recentActivity">
                <p class="text-muted">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Load dashboard data
    async function loadDashboardData() {
        const date = new Date().toISOString().split('T')[0];
        
        // Company productivity stats
        const response = await fetch(`../api/reports.php?type=company_productivity&date=${date}`);
        const data = await response.json();
        
        document.getElementById('totalUsers').textContent = data.total_users;
        document.getElementById('presentUsers').textContent = data.present_users;
        document.getElementById('absentUsers').textContent = data.absent_users;
        document.getElementById('productivity').textContent = data.productivity + '%';
        
        // Time Distribution Pie Chart
        const ctx1 = document.getElementById('timeDistributionChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Productive Time', 'Idle Time', 'Unproductive Time'],
                datasets: [{
                    data: [65, 20, 15],
                    backgroundColor: ['#84fab0', '#fa709a', '#f5576c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        
        // Productivity Trend Line Chart
        const ctx2 = document.getElementById('productivityTrendChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Productivity %',
                    data: [65, 70, 68, 75, 72, 55, 60],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
        
        // Load top users
        const topUsersResponse = await fetch(`../api/reports.php?type=top_users&date=${date}`);
        const topUsers = await topUsersResponse.json();
        
        let topUsersHTML = '<div class="list-group">';
        topUsers.forEach((user, index) => {
            topUsersHTML += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary rounded-circle me-2">${index + 1}</span>
                        <strong>${user.name}</strong> <small class="text-muted">(${user.emp_code})</small>
                    </div>
                    <span class="badge bg-success">${user.productivity_percent}%</span>
                </div>
            `;
        });
        topUsersHTML += '</div>';
        document.getElementById('topUsersList').innerHTML = topUsersHTML;
    }
    
    loadDashboardData();
</script>

<?php include 'includes/footer.php'; ?>
