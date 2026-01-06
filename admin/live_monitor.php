<?php include __DIR__ . '/includes/header.php'; ?>

<style>
    /* Scoped variables for Live Monitor to avoid conflicts with global header styles */
    .monitor-wrapper {
        --monitor-sidebar-width: 320px;
        --monitor-viewer-bg: #111827; 
        --monitor-sidebar-bg: #ffffff;
        --monitor-sidebar-border: #e2e8f0;
        --monitor-text-primary: #1e293b;
        --monitor-text-secondary: #64748b;
        --monitor-accent-color: #0d6efd;
        --monitor-hover-bg: #f8fafc;
        --monitor-active-bg: #eef2ff;
    }

    body {
        /* Keep body scroll as is, do not override global body */
    }

    .monitor-container {
        display: flex;
        height: calc(100vh - 140px); 
        margin: -30px;
        width: calc(100% + 60px);
        background: #fff;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
        border-top: 1px solid var(--monitor-sidebar-border);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* --- Sidebar --- */
    .users-sidebar {
        width: var(--monitor-sidebar-width);
        background: var(--monitor-sidebar-bg);
        border-right: 1px solid var(--monitor-sidebar-border);
        display: flex;
        flex-direction: column;
        z-index: 10;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--monitor-sidebar-border);
        background: var(--monitor-sidebar-bg);
    }
    
    .sidebar-header h5 {
        color: var(--monitor-text-primary) !important;
        font-weight: 700;
    }

    .search-box {
        position: relative;
        margin-top: 15px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input {
        width: 100%;
        padding: 10px 10px 10px 35px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
        background: #f8fafc;
        color: var(--monitor-text-primary);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--monitor-accent-color);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        background: #fff;
    }
    
    .search-input::placeholder {
        color: #94a3b8;
    }

    .users-list {
        flex: 1;
        overflow-y: auto;
        padding: 0; 
    }

    /* Scrollbar styling */
    .users-list::-webkit-scrollbar {
        width: 6px;
    }
    .users-list::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .users-list::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 3px;
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 0;
        position: relative;
        color: var(--monitor-text-primary);
        background: #fff;
    }

    .user-item:hover {
        background: var(--monitor-hover-bg);
    }

    .user-item.active {
        background: var(--monitor-active-bg);
        color: var(--monitor-accent-color);
        border-left: 4px solid var(--monitor-accent-color);
        padding-left: 16px; 
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        margin-right: 15px;
        position: relative;
        flex-shrink: 0;
    }

    .user-item.active .user-avatar {
        background: var(--monitor-accent-color);
        color: white;
    }

    .status-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        background: #94a3b8;
    }

    .user-item.online .status-badge {
        background: #10b981;
    }
    
    .user-item.active .status-badge {
        border-color: var(--monitor-active-bg);
    }

    .user-info h6 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: inherit;
    }

    .user-info small {
        color: var(--monitor-text-secondary);
        font-size: 12px;
    }

    /* --- Main Viewer --- */
    .screen-viewer {
        flex: 1;
        background: var(--monitor-viewer-bg);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .viewer-toolbar {
        height: 60px;
        background: rgba(15, 23, 42, 0.95);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 25px;
        color: white;
        backdrop-filter: blur(10px);
        z-index: 20;
    }

    .viewer-info {
        display: flex;
        align-items: center;
    }

    .live-indicator {
        display: inline-flex;
        align-items: center;
        background: rgba(220, 53, 69, 0.2);
        color: #ff6b6b;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        margin-left: 15px;
        border: 1px solid rgba(220, 53, 69, 0.3);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .live-indicator.visible {
        opacity: 1;
    }

    .live-dot {
        width: 6px;
        height: 6px;
        background: #ff6b6b;
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(255, 107, 107, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
    }

    .viewer-controls button {
        background: transparent;
        border: 1px solid rgba(255,255,255,0.2);
        color: rgba(255,255,255,0.8);
        width: 36px;
        height: 36px;
        border-radius: 8px;
        margin-left: 8px;
        transition: all 0.2s;
    }

    .viewer-controls button:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        border-color: rgba(255,255,255,0.4);
    }

    .viewer-canvas {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 20px;
    }

    #liveImage {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        box-shadow: 0 0 50px rgba(0,0,0,0.5);
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        color: rgba(255,255,255,0.3);
    }
    
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    /* Fullscreen Mode */
    .screen-viewer.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
    }

    /* Loader */
    .loader-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }
    
    .loader-overlay.show {
        opacity: 1;
    }

</style>

<div class="monitor-container">
    <!-- Sidebar -->
    <div class="users-sidebar">
        <div class="sidebar-header">
            <h5 class="fw-bold mb-1">Live Monitor</h5>
            <small class="text-muted">Select an employee to view screen</small>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="userSearch" class="search-input" placeholder="Search employee..." onkeyup="filterUsers()">
            </div>
        </div>

        <div class="users-list" id="usersList">
            <div class="text-center py-5">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Loading employees...</p>
            </div>
        </div>
    </div>

    <!-- Main Viewer -->
    <div class="screen-viewer" id="screenViewer">
        <div class="viewer-toolbar">
            <div class="viewer-info">
                <div id="selectedUserInfo" style="display: none;">
                    <span class="fw-bold fs-5 me-2" id="viewerName">Employee Name</span>
                    <span class="text-white-50 small" id="viewerStatus">Last seen: --</span>
                </div>
                <div class="live-indicator" id="liveBadge">
                    <span class="live-dot"></span> LIVE
                </div>
            </div>
            
            <div class="viewer-controls">
                <button onclick="refreshScreen()" title="Force Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button onclick="toggleFit()" title="Toggle Fit/Fill">
                    <i class="fas fa-expand"></i>
                </button>
                <button onclick="toggleFullscreen()" title="Full Screen">
                    <i class="fas fa-compress"></i>
                </button>
            </div>
        </div>

        <div class="viewer-canvas">
            <div class="loader-overlay" id="imgLoader">
                <div class="spinner-border text-light" role="status"></div>
            </div>

            <div class="empty-state" id="emptyState">
                <i class="fas fa-desktop"></i>
                <h3>Select an Employee</h3>
                <p>Choose an online employee from the sidebar<br>to start live screen monitoring.</p>
            </div>
            
            <img id="liveImage" src="" style="display: none;">
        </div>
    </div>
</div>

<script>
let selectedEmployeeId = null;
let screenInterval = null;
let usersInterval = null;
let allUsers = []; // Store users for search filtering

// --- User Management ---

async function loadUsers() {
    try {
        const response = await fetch('../api/get_online_users.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            allUsers = result.data;
            renderUsers(allUsers);
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function renderUsers(users) {
    const listContainer = document.getElementById('usersList');
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    
    // Filter locally if search is active
    const filtered = users.filter(u => 
        u.name.toLowerCase().includes(searchTerm) || 
        u.emp_code.toLowerCase().includes(searchTerm)
    );

    if (filtered.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="far fa-user fa-2x mb-2"></i>
                <p class="small">No employees found</p>
            </div>`;
        return;
    }

    let html = '';
    filtered.forEach(user => {
        const isActive = selectedEmployeeId == user.id ? 'active' : '';
        const isOnline = user.is_online ? 'online' : '';
        const initials = user.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        
        // Status text logic
        let statusText = user.last_seen_text;
        if(user.is_online) statusText = 'Active now';
        
        html += `
        <div class="user-item ${isActive} ${isOnline}" onclick="selectUser(${user.id}, '${user.name}', '${user.emp_code}')">
            <div class="user-avatar">
                ${initials}
                <div class="status-badge"></div>
            </div>
            <div class="user-info flex-grow-1">
                <h6>${user.name}</h6>
                <small>${statusText}</small>
            </div>
            ${user.is_online ? '<i class="fas fa-wifi text-success small"></i>' : ''}
        </div>
        `;
    });
    listContainer.innerHTML = html;
}

function filterUsers() {
    renderUsers(allUsers);
}

function selectUser(id, name, code) {
    selectedEmployeeId = id;
    
    // Update Header
    document.getElementById('selectedUserInfo').style.display = 'block';
    document.getElementById('viewerName').textContent = name;
    document.getElementById('viewerStatus').textContent = 'Connecting...';
    
    // Update List UI (re-render to show active state)
    renderUsers(allUsers);
    
    // Show Image Area
    document.getElementById('emptyState').style.display = 'none';
    const img = document.getElementById('liveImage');
    img.style.display = 'block';
    
    // Show loader
    document.getElementById('imgLoader').classList.add('show');
    
    // Start Fetching
    if (screenInterval) clearInterval(screenInterval);
    fetchScreen(); // Immediate fetch
    screenInterval = setInterval(fetchScreen, 3000); // Auto-refresh
}

// --- Screen Fetching ---

async function fetchScreen() {
    if (!selectedEmployeeId) return;
    
    try {
        const response = await fetch(`../api/get_user_screen.php?employee_id=${selectedEmployeeId}`);
        const result = await response.json();
        
        const img = document.getElementById('liveImage');
        const loader = document.getElementById('imgLoader');
        
        if (result.status === 'success') {
            // Preload image to avoid flickering
            const tempImg = new Image();
            tempImg.onload = () => {
                img.src = tempImg.src;
                loader.classList.remove('show');
            };
            tempImg.src = `data:image/jpeg;base64,${result.image}`;
            
            document.getElementById('viewerStatus').textContent = `Last update: ${result.time_ago}`;
            
            // Live Badge logic
            const badge = document.getElementById('liveBadge');
            if (result.is_live) {
                badge.classList.add('visible');
            } else {
                badge.classList.remove('visible');
            }
        } else {
            // Handle no screen data
            loader.classList.remove('show');
            // Don't hide image immediately to prevent flashing, just update status
            document.getElementById('viewerStatus').textContent = 'Offline / No Signal';
            document.getElementById('liveBadge').classList.remove('visible');
        }
    } catch (error) {
        console.error('Error fetching screen:', error);
        document.getElementById('imgLoader').classList.remove('show');
    }
}

function refreshScreen() {
    if(selectedEmployeeId) {
        document.getElementById('imgLoader').classList.add('show');
        fetchScreen();
    }
}

// --- Viewer Controls ---

function toggleFullscreen() {
    const elem = document.getElementById('screenViewer');
    
    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch(err => {
            alert(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
        });
        elem.classList.add('fullscreen');
    } else {
        document.exitFullscreen();
        elem.classList.remove('fullscreen');
    }
}

// Handle ESC key or browser exit fullscreen
document.addEventListener('fullscreenchange', (event) => {
    const elem = document.getElementById('screenViewer');
    if (!document.fullscreenElement) {
        elem.classList.remove('fullscreen');
    } else {
        elem.classList.add('fullscreen');
    }
});

let isCover = false;
function toggleFit() {
    const img = document.getElementById('liveImage');
    isCover = !isCover;
    img.style.objectFit = isCover ? 'cover' : 'contain';
}

// --- Lifecycle ---

loadUsers();
usersInterval = setInterval(loadUsers, 10000); // Refresh list every 10s

window.addEventListener('beforeunload', () => {
    if (screenInterval) clearInterval(screenInterval);
    if (usersInterval) clearInterval(usersInterval);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
