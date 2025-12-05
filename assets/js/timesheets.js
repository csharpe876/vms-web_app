// Timesheets Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadTimesheets();
});

// Load all timesheets
function loadTimesheets() {
    fetch('controllers/TimesheetController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTimesheets(data.data);
            } else {
                showAlert(data.message || 'Failed to load timesheets', 'error');
            }
        })
        .catch(handleFetchError);
}

// Display timesheets
function displayTimesheets(timesheets) {
    const container = document.getElementById('timesheets-list');
    
    if (timesheets.length === 0) {
        container.innerHTML = '<p class="no-data">No timesheets found.</p>';
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Volunteer</th>
                    <th>Event</th>
                    <th>Period</th>
                    <th>Total Hours</th>
                    <th>Approved Hours</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    timesheets.forEach(timesheet => {
        html += `
            <tr>
                <td>${timesheet.volunteer_name}</td>
                <td>${timesheet.event_name}</td>
                <td>${formatDate(timesheet.period_start_date)} - ${formatDate(timesheet.period_end_date)}</td>
                <td>${timesheet.total_hours} hrs</td>
                <td>${timesheet.approved_hours || 0} hrs</td>
                <td>${getStatusBadge(timesheet.approval_status)}</td>
                <td>${timesheet.approved_by_name || '-'}</td>
                <td class="actions">
                    ${getTimesheetActions(timesheet)}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Get timesheet actions based on permissions and status
function getTimesheetActions(timesheet) {
    const userRole = document.body.dataset.userRole || '';
    let actions = '';
    
    if (['SUPER_ADMIN', 'ADMIN'].includes(userRole)) {
        if (timesheet.approval_status === 'PENDING') {
            actions += `
                <button class="btn btn-sm btn-success" onclick="approveTimesheet(${timesheet.id})" title="Approve">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="rejectTimesheet(${timesheet.id})" title="Reject">
                    <i class="fas fa-times"></i>
                </button>
            `;
        }
        actions += `
            <button class="btn btn-sm btn-secondary" onclick="viewTimesheet(${timesheet.id})" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
        `;
    }
    
    return actions || '-';
}

// View timesheet details
function viewTimesheet(id) {
    fetch(`controllers/TimesheetController.php?action=getById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const timesheet = data.data;
                let html = `
                    <div class="timesheet-details">
                        <h3>Timesheet Details</h3>
                        <table class="details-table">
                            <tr><th>Volunteer:</th><td>${timesheet.volunteer_name}</td></tr>
                            <tr><th>Event:</th><td>${timesheet.event_name}</td></tr>
                            <tr><th>Period:</th><td>${formatDate(timesheet.period_start_date)} to ${formatDate(timesheet.period_end_date)}</td></tr>
                            <tr><th>Total Hours:</th><td>${timesheet.total_hours} hours</td></tr>
                            <tr><th>Approved Hours:</th><td>${timesheet.approved_hours || 0} hours</td></tr>
                            <tr><th>Status:</th><td>${getStatusBadge(timesheet.approval_status)}</td></tr>
                            ${timesheet.approved_by_name ? `<tr><th>Approved By:</th><td>${timesheet.approved_by_name}</td></tr>` : ''}
                            ${timesheet.approval_date ? `<tr><th>Approval Date:</th><td>${formatDateTime(timesheet.approval_date)}</td></tr>` : ''}
                            ${timesheet.rejection_reason ? `<tr><th>Rejection Reason:</th><td>${timesheet.rejection_reason}</td></tr>` : ''}
                            <tr><th>Created:</th><td>${formatDateTime(timesheet.created_date)}</td></tr>
                        </table>
                    </div>
                `;
                
                const modal = createInfoModal('Timesheet Details', html);
                document.body.appendChild(modal);
                modal.style.display = 'block';
            } else {
                showAlert(data.message || 'Failed to load timesheet details', 'error');
            }
        })
        .catch(handleFetchError);
}

// Approve timesheet
function approveTimesheet(id) {
    const approvedHours = prompt('Enter approved hours:');
    
    if (approvedHours === null) return; // User cancelled
    
    if (!approvedHours || isNaN(approvedHours) || parseFloat(approvedHours) < 0) {
        showAlert('Please enter a valid number of hours', 'error');
        return;
    }
    
    fetch('controllers/TimesheetController.php?action=approve', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            id: id,
            approved_hours: parseFloat(approvedHours)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Timesheet approved successfully', 'success');
            loadTimesheets();
        } else {
            showAlert(data.message || 'Failed to approve timesheet', 'error');
        }
    })
    .catch(handleFetchError);
}

// Reject timesheet
function rejectTimesheet(id) {
    const reason = prompt('Enter rejection reason:');
    
    if (reason === null) return; // User cancelled
    
    if (!reason.trim()) {
        showAlert('Please provide a rejection reason', 'error');
        return;
    }
    
    fetch('controllers/TimesheetController.php?action=reject', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            id: id,
            reason: reason.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Timesheet rejected', 'success');
            loadTimesheets();
        } else {
            showAlert(data.message || 'Failed to reject timesheet', 'error');
        }
    })
    .catch(handleFetchError);
}

// Create info modal
function createInfoModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close" onclick="this.closest('.modal').remove()">&times;</span>
            <h2>${title}</h2>
            ${content}
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Close</button>
            </div>
        </div>
    `;
    
    // Close when clicking outside
    modal.onclick = function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    };
    
    return modal;
}
