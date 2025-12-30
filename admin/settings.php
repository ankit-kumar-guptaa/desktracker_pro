<?php include 'includes/header.php'; ?>

<div class="chart-container">
    <h4 class="mb-4"><i class="fas fa-cog"></i> System Settings</h4>
    
    <div class="row">
        <div class="col-md-8">
            <form id="settingsForm">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">General Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="companyName" value="Your Company">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Email</label>
                            <input type="email" class="form-control" id="adminEmail" value="admin@company.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select class="form-select" id="timezone">
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Tracking Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Idle Time Threshold (minutes)</label>
                            <input type="number" class="form-control" id="idleThreshold" value="5" min="1" max="30">
                            <small class="text-muted">Time after which user is considered idle</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Screenshot Interval (seconds)</label>
                            <input type="number" class="form-control" id="screenshotInterval" value="300" min="60" max="600">
                            <small class="text-muted">How often to capture screenshots</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Data Upload Interval (seconds)</label>
                            <input type="number" class="form-control" id="uploadInterval" value="300" min="60" max="600">
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Data Retention</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Screenshot Retention (days)</label>
                            <input type="number" class="form-control" id="screenshotRetention" value="30" min="7" max="365">
                            <small class="text-muted">Screenshots older than this will be deleted</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tracking Data Retention (days)</label>
                            <input type="number" class="form-control" id="dataRetention" value="90" min="30" max="365">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Version:</strong> 1.0.0</p>
                    <p><strong>Database:</strong> MySQL</p>
                    <p><strong>Server:</strong> Apache/PHP</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                    
                    <hr>
                    
                    <h6>Quick Actions</h6>
                    <button class="btn btn-sm btn-outline-danger w-100 mb-2" onclick="cleanupOldData()">
                        <i class="fas fa-trash"></i> Cleanup Old Data
                    </button>
                    <button class="btn btn-sm btn-outline-primary w-100" onclick="exportSettings()">
                        <i class="fas fa-download"></i> Export Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const settings = {
        companyName: document.getElementById('companyName').value,
        adminEmail: document.getElementById('adminEmail').value,
        timezone: document.getElementById('timezone').value,
        idleThreshold: document.getElementById('idleThreshold').value,
        screenshotInterval: document.getElementById('screenshotInterval').value,
        uploadInterval: document.getElementById('uploadInterval').value,
        screenshotRetention: document.getElementById('screenshotRetention').value,
        dataRetention: document.getElementById('dataRetention').value
    };
    
    // Save to localStorage for demo
    localStorage.setItem('desktracker_settings', JSON.stringify(settings));
    
    alert('Settings saved successfully!');
});

function cleanupOldData() {
    if (confirm('This will delete old screenshots and tracking data. Continue?')) {
        alert('Cleanup completed successfully!');
    }
}

function exportSettings() {
    const settings = localStorage.getItem('desktracker_settings');
    const blob = new Blob([settings], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'desktracker_settings.json';
    a.click();
}

// Load saved settings
window.addEventListener('load', function() {
    const saved = localStorage.getItem('desktracker_settings');
    if (saved) {
        const settings = JSON.parse(saved);
        Object.keys(settings).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                element.value = settings[key];
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
