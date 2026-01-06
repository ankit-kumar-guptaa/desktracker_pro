<?php include __DIR__ . '/includes/header.php'; ?>

<div class="chart-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-users"></i> Manage Employees</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fas fa-plus"></i> Add Employee
        </button>
    </div>
    
    <!-- Search Bar -->
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" id="searchEmployee" placeholder="Search by name or code..." onkeyup="filterEmployees()">
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterStatus" onchange="loadEmployees()">
                <option value="">All Status</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>
    
    <table class="table table-hover" id="employeesTable">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="employeesList">
            <tr><td colspan="7" class="text-center">Loading...</td></tr>
        </tbody>
    </table>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm">
                    <div class="mb-3">
                        <label class="form-label">Employee Code</label>
                        <input type="text" class="form-control" id="empCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="empName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="empEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="empPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Create Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeDetails">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm">
                    <input type="hidden" id="editEmpId">
                    <div class="mb-3">
                        <label class="form-label">Employee Code</label>
                        <input type="text" class="form-control" id="editEmpCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editEmpName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmpEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="editEmpPassword">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="editEmpStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Update Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let allEmployees = [];

// Load employees
async function loadEmployees() {
    const response = await fetch('../api/employee_crud.php');
    const result = await response.json();
    
    allEmployees = result.data;
    const filterStatus = document.getElementById('filterStatus').value;
    
    let filteredEmployees = allEmployees;
    if (filterStatus) {
        filteredEmployees = allEmployees.filter(emp => emp.status === filterStatus);
    }
    
    displayEmployees(filteredEmployees);
}

// Display employees in table
function displayEmployees(employees) {
    let html = '';
    
    if (employees.length === 0) {
        html = '<tr><td colspan="7" class="text-center">No employees found</td></tr>';
    } else {
        employees.forEach(emp => {
            const statusBadge = emp.status === 'active' ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-danger">Inactive</span>';
            
            const statusToggle = emp.status === 'active' ? 
                `<button class="btn btn-sm btn-warning" onclick="toggleStatus(${emp.id}, 'inactive')" title="Deactivate">
                    <i class="fas fa-toggle-on"></i>
                </button>` :
                `<button class="btn btn-sm btn-success" onclick="toggleStatus(${emp.id}, 'active')" title="Activate">
                    <i class="fas fa-toggle-off"></i>
                </button>`;
            
            html += `
                <tr>
                    <td>${emp.id}</td>
                    <td><strong>${emp.emp_code}</strong></td>
                    <td>${emp.name}</td>
                    <td>${emp.email}</td>
                    <td>${statusBadge}</td>
                    <td>${new Date(emp.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewEmployee(${emp.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="editEmployee(${emp.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${statusToggle}
                        <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${emp.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    document.getElementById('employeesList').innerHTML = html;
}

// Filter employees by search
function filterEmployees() {
    const searchValue = document.getElementById('searchEmployee').value.toLowerCase();
    const filtered = allEmployees.filter(emp => 
        emp.name.toLowerCase().includes(searchValue) || 
        emp.emp_code.toLowerCase().includes(searchValue) ||
        emp.email.toLowerCase().includes(searchValue)
    );
    displayEmployees(filtered);
}

// Add employee
document.getElementById('addEmployeeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        emp_code: document.getElementById('empCode').value,
        name: document.getElementById('empName').value,
        email: document.getElementById('empEmail').value,
        password: document.getElementById('empPassword').value
    };
    
    const response = await fetch('../api/employee_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.status === 'success') {
        alert('Employee created successfully!');
        bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal')).hide();
        document.getElementById('addEmployeeForm').reset();
        loadEmployees();
    } else {
        alert('Error: ' + result.message);
    }
});

// View employee details
async function viewEmployee(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
    modal.show();
    
    const employee = allEmployees.find(emp => emp.id === id);
    
    if (!employee) {
        document.getElementById('employeeDetails').innerHTML = '<p class="text-danger">Employee not found</p>';
        return;
    }
    
    // Fetch tracking data
    const today = new Date().toISOString().split('T')[0];
    const response = await fetch(`../api/reports.php?type=employee_details&employee_id=${id}&date=${today}`);
    const data = await response.json();
    
    const tracking = data.tracking || {};
    const formatTime = (seconds) => {
        if (!seconds) return '0h 0m';
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    };
    
    const statusBadge = employee.status === 'active' ? 
        '<span class="badge bg-success">Active</span>' : 
        '<span class="badge bg-danger">Inactive</span>';
    
    const html = `
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="user-avatar" style="width: 120px; height: 120px; font-size: 48px; margin: 0 auto;">
                    ${employee.name.charAt(0).toUpperCase()}
                </div>
                <h5 class="mt-3">${employee.name}</h5>
                <p class="text-muted">${employee.emp_code}</p>
                ${statusBadge}
            </div>
            <div class="col-md-8">
                <h6 class="mb-3">Basic Information</h6>
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Email:</th>
                        <td>${employee.email}</td>
                    </tr>
                    <tr>
                        <th>Employee ID:</th>
                        <td>#${employee.id}</td>
                    </tr>
                    <tr>
                        <th>Joined Date:</th>
                        <td>${new Date(employee.created_at).toLocaleDateString()}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>${employee.status === 'active' ? 'Active' : 'Inactive'}</td>
                    </tr>
                </table>
                
                <h6 class="mt-4 mb-3">Today's Activity</h6>
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted">Total Time</small>
                            <h6 class="mb-0">${formatTime(tracking.total_time)}</h6>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted">Productive Time</small>
                            <h6 class="mb-0 text-success">${formatTime(tracking.productive_time)}</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted">Idle Time</small>
                            <h6 class="mb-0 text-warning">${formatTime(tracking.idle_time)}</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted">Productivity</small>
                            <h6 class="mb-0 text-primary">${tracking.total_time ? Math.round((tracking.productive_time / tracking.total_time) * 100) : 0}%</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('employeeDetails').innerHTML = html;
}

// Edit employee
function editEmployee(id) {
    const employee = allEmployees.find(emp => emp.id === id);
    
    if (!employee) {
        alert('Employee not found');
        return;
    }
    
    document.getElementById('editEmpId').value = employee.id;
    document.getElementById('editEmpCode').value = employee.emp_code;
    document.getElementById('editEmpName').value = employee.name;
    document.getElementById('editEmpEmail').value = employee.email;
    document.getElementById('editEmpStatus').value = employee.status;
    document.getElementById('editEmpPassword').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    modal.show();
}

// Update employee
document.getElementById('editEmployeeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        id: parseInt(document.getElementById('editEmpId').value),
        emp_code: document.getElementById('editEmpCode').value,
        name: document.getElementById('editEmpName').value,
        email: document.getElementById('editEmpEmail').value,
        status: document.getElementById('editEmpStatus').value
    };
    
    const password = document.getElementById('editEmpPassword').value;
    if (password) {
        data.password = password;
    }
    
    const response = await fetch('../api/employee_crud.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.status === 'success') {
        alert('Employee updated successfully!');
        bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal')).hide();
        loadEmployees();
    } else {
        alert('Error: ' + result.message);
    }
});

// Toggle employee status (Active/Inactive)
async function toggleStatus(id, newStatus) {
    const confirmMsg = newStatus === 'active' ? 
        'Are you sure you want to activate this employee?' : 
        'Are you sure you want to deactivate this employee?';
    
    if (!confirm(confirmMsg)) return;
    
    const data = {
        id: id,
        status: newStatus
    };
    
    const response = await fetch('../api/employee_crud.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.status === 'success') {
        loadEmployees();
    } else {
        alert('Error: ' + result.message);
    }
}

// Delete employee
async function deleteEmployee(id) {
    if (!confirm('Are you sure you want to delete this employee? This action cannot be undone.')) return;
    
    const response = await fetch('../api/employee_crud.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    
    if (result.status === 'success') {
        alert('Employee deleted successfully!');
        loadEmployees();
    } else {
        alert('Error: ' + result.message);
    }
}

// Load employees on page load
loadEmployees();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
