<?php include '../includes/header.php'; ?>

<style>
    /* Custom Styling for Screenshot Gallery */
    .filter-card {
        background: white;
        border-radius: 15px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .filter-label {
        font-weight: 600;
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border-radius: 10px;
        padding: 12px 15px;
        border-color: #e2e8f0;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(0, 51, 102, 0.1);
    }

    .btn-search {
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        background: var(--primary-color);
        border: none;
        transition: all 0.2s;
    }
    
    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 51, 102, 0.3);
        background: #004080;
    }

    /* Gallery Grid */
    .screenshot-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        background: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        position: relative;
        cursor: pointer;
    }
    
    .screenshot-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .screenshot-img-wrapper {
        position: relative;
        overflow: hidden;
        padding-top: 60%; /* Aspect Ratio 16:10 */
        background: #f1f5f9;
    }
    
    .screenshot-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    
    .screenshot-card:hover .screenshot-img {
        transform: scale(1.05);
    }
    
    .screenshot-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 51, 102, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .screenshot-card:hover .screenshot-overlay {
        opacity: 1;
    }
    
    .btn-zoom {
        background: white;
        color: var(--primary-color);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transform: scale(0.8);
        transition: transform 0.3s;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .screenshot-card:hover .btn-zoom {
        transform: scale(1);
    }
    
    .screenshot-info {
        padding: 15px;
        border-top: 1px solid #f1f5f9;
    }
    
    .time-badge {
        background: rgba(0, 51, 102, 0.1);
        color: var(--primary-color);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #94a3b8;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 30px;
    }
    
    .modal-body {
        padding: 0;
        background: #1e293b;
        position: relative;
    }
    
    .modal-img {
        max-height: 85vh;
        width: 100%;
        object-fit: contain;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-camera text-primary me-2"></i>Screenshot Gallery</h3>
            <p class="text-muted mb-0">Visual timeline of employee activity</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                <i class="fas fa-calendar me-2 text-primary"></i> <?php echo date('F j, Y'); ?>
            </span>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="filter-label">Select Employee</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <select class="form-select border-start-0 ps-0" id="employeeSelect">
                        <option value="">Loading employees...</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="filter-label">Select Date</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                    <input type="date" class="form-control border-start-0 ps-0" id="dateSelect" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary btn-search w-100" onclick="loadScreenshots()">
                    <span id="btnText"><i class="fas fa-search me-2"></i> View Screenshots</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2" style="display:none"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Gallery Grid -->
    <div class="row g-4" id="screenshotGallery">
        <div class="col-12">
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-images"></i></div>
                <h4>Ready to Explore</h4>
                <p>Select an employee and date above to view their activity timeline.</p>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Screenshot Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0">Screenshot Detail</h5>
                    <small class="text-muted" id="modalTimeDisplay">Captured at: --:--</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center d-flex align-items-center justify-content-center" style="min-height: 400px;">
                <div class="spinner-border text-light position-absolute" role="status" id="imgLoader"></div>
                <img id="modalImage" src="" class="modal-img shadow-sm" onload="document.getElementById('imgLoader').style.display='none'">
            </div>
            <div class="modal-footer bg-light border-top-0 justify-content-between">
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <a id="downloadLink" href="#" download class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-download me-2"></i> Download Image
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Load employees dropdown
async function loadEmployees() {
    try {
        const response = await fetch('../../api/employee_crud.php');
        const result = await response.json();
        
        let html = '<option value="">Choose an employee...</option>';
        if (result.data) {
            result.data.forEach(emp => {
                html += `<option value="${emp.id}">${emp.name} (${emp.emp_code})</option>`;
            });
        }
        document.getElementById('employeeSelect').innerHTML = html;
    } catch (e) {
        console.error("Error loading employees", e);
    }
}

// Load Screenshots
async function loadScreenshots() {
    const empId = document.getElementById('employeeSelect').value;
    const date = document.getElementById('dateSelect').value;
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    
    if (!empId) {
        alert('Please select an employee first.');
        return;
    }
    
    // Loading State
    btnText.textContent = 'Loading...';
    btnSpinner.style.display = 'inline-block';
    
    try {
        const response = await fetch(`../../api/reports.php?type=employee_details&employee_id=${empId}&date=${date}`);
        const data = await response.json();
        
        const gallery = document.getElementById('screenshotGallery');
        
        if (data.screenshots && data.screenshots.length > 0) {
            let html = '';
            data.screenshots.forEach((screenshot, index) => {
                // Adjust path logic based on your folder structure
                // Assuming stored path is relative like 'uploads/...' or '../uploads/...'
                let cleanPath = screenshot.image_path;
                // If path starts with ../ remove it for display in admin/reports (which is 2 levels deep)
                // Actually, if image_path is saved as '../uploads/screen.png' relative to API
                // And we are in admin/reports, we need '../../uploads/screen.png'
                // Let's sanitize:
                if (cleanPath.startsWith('../')) cleanPath = cleanPath.substring(3);
                const displayPath = '../../' + cleanPath;
                
                const timeStr = new Date(screenshot.captured_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const dateStr = new Date(screenshot.captured_at).toLocaleDateString();

                html += `
                    <div class="col-sm-6 col-lg-4 col-xl-3 fade-in">
                        <div class="screenshot-card" onclick="openModal('${displayPath}', '${screenshot.captured_at}')">
                            <div class="screenshot-img-wrapper">
                                <img src="${displayPath}" class="screenshot-img" loading="lazy" onerror="this.src='../../assets/img/placeholder.png'">
                                <div class="screenshot-overlay">
                                    <button class="btn-zoom"><i class="fas fa-search-plus"></i></button>
                                </div>
                            </div>
                            <div class="screenshot-info d-flex justify-content-between align-items-center bg-white">
                                <span class="time-badge">
                                    <i class="far fa-clock"></i> ${timeStr}
                                </span>
                                <small class="text-muted fw-bold" style="font-size: 0.75rem;">#${index + 1}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            gallery.innerHTML = html;
            
            // Add simple fade-in animation
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, i) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, i * 50);
            });
            
        } else {
            gallery.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-icon text-warning"><i class="far fa-folder-open"></i></div>
                        <h5 class="text-dark">No Activity Found</h5>
                        <p>No screenshots were recorded for this employee on ${date}.</p>
                    </div>
                </div>`;
        }
    } catch (error) {
        console.error(error);
        alert('Error fetching data');
    } finally {
        // Reset Button
        btnText.innerHTML = '<i class="fas fa-search me-2"></i> View Screenshots';
        btnSpinner.style.display = 'none';
    }
}

function openModal(imagePath, capturedAt) {
    document.getElementById('modalImage').src = imagePath;
    document.getElementById('imgLoader').style.display = 'block';
    
    const dateObj = new Date(capturedAt);
    const formattedTime = dateObj.toLocaleDateString() + ' at ' + dateObj.toLocaleTimeString();
    
    document.getElementById('modalTimeDisplay').textContent = formattedTime;
    document.getElementById('downloadLink').href = imagePath;
    
    new bootstrap.Modal(document.getElementById('screenshotModal')).show();
}

loadEmployees();
</script>

<?php include '../includes/footer.php'; ?>
