// Attendance Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadAttendance();
    loadVolunteersDropdown();
    loadEventsDropdown();
});

// Load all attendance records
function loadAttendance() {
    fetch('controllers/AttendanceController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAttendance(data.data);
            } else {
                showAlert(data.message || 'Failed to load attendance records', 'error');
            }
        })
        .catch(handleFetchError);
}

// Display attendance records
function displayAttendance(records) {
    const container = document.getElementById('attendance-list');
    
    if (records.length === 0) {
        container.innerHTML = '<p class="no-data">No attendance records found.</p>';
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Volunteer</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Hours Worked</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    records.forEach(record => {
        html += `
            <tr>
                <td>${record.volunteer_name}</td>
                <td>${record.event_name}</td>
                <td>${formatDate(record.attendance_date)}</td>
                <td>${record.hours_worked} hrs</td>
                <td>${getStatusBadge(record.status)}</td>
                <td>${record.notes || '-'}</td>
                <td>${record.recorded_by_name || 'System'}</td>
                <td class="actions">
                    ${getAttendanceActions(record)}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Get attendance actions based on permissions
function getAttendanceActions(record) {
    const userRole = document.body.dataset.userRole || '';
    
    if (['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'].includes(userRole)) {
        return `
            <button class="btn btn-sm btn-secondary" onclick="editAttendance(${record.id})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteAttendance(${record.id})" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;
    }
    return '-';
}

// Load volunteers for dropdown
function loadVolunteersDropdown() {
    fetch('controllers/VolunteerController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('#volunteer_id, #filter-volunteer');
                selects.forEach(select => {
                    let options = select.id === 'filter-volunteer' ? '<option value="">All Volunteers</option>' : '<option value="">Select Volunteer</option>';
                    data.data.forEach(volunteer => {
                        options += `<option value="${volunteer.id}">${volunteer.first_name} ${volunteer.last_name}</option>`;
                    });
                    select.innerHTML = options;
                });
            }
        })
        .catch(handleFetchError);
}

// Load events for dropdown
function loadEventsDropdown() {
    fetch('controllers/EventController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('#event_id, #filter-event');
                selects.forEach(select => {
                    let options = select.id === 'filter-event' ? '<option value="">All Events</option>' : '<option value="">Select Event</option>';
                    data.data.forEach(event => {
                        options += `<option value="${event.id}">${event.event_name} (${formatDate(event.event_date)})</option>`;
                    });
                    select.innerHTML = options;
                });
            }
        })
        .catch(handleFetchError);
}

// Filter attendance
function filterAttendance() {
    const eventId = document.getElementById('filter-event').value;
    const volunteerId = document.getElementById('filter-volunteer').value;
    
    let url = 'controllers/AttendanceController.php?action=getAll';
    if (eventId) url += `&event_id=${eventId}`;
    if (volunteerId) url += `&volunteer_id=${volunteerId}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAttendance(data.data);
            }
        })
        .catch(handleFetchError);
}

// Show add modal
function showAddModal() {
    document.getElementById('modal-title').textContent = 'Record Attendance';
    document.getElementById('attendance-form').reset();
    document.getElementById('attendance-id').value = '';
    
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('attendance_date').value = today;
    
    document.getElementById('attendanceModal').style.display = 'block';
}

// Edit attendance
function editAttendance(id) {
    fetch(`controllers/AttendanceController.php?action=getById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const record = data.data;
                document.getElementById('modal-title').textContent = 'Edit Attendance';
                document.getElementById('attendance-id').value = record.id;
                document.getElementById('volunteer_id').value = record.volunteer_id;
                document.getElementById('event_id').value = record.event_id;
                document.getElementById('attendance_date').value = record.attendance_date;
                document.getElementById('hours_worked').value = record.hours_worked;
                document.getElementById('status').value = record.status;
                document.getElementById('notes').value = record.notes || '';
                document.getElementById('attendanceModal').style.display = 'block';
            } else {
                showAlert(data.message || 'Failed to load attendance record', 'error');
            }
        })
        .catch(handleFetchError);
}

// Delete attendance
function deleteAttendance(id) {
    if (!confirmAction('Are you sure you want to delete this attendance record?')) {
        return;
    }
    
    fetch('controllers/AttendanceController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Attendance record deleted successfully', 'success');
            loadAttendance();
        } else {
            showAlert(data.message || 'Failed to delete attendance record', 'error');
        }
    })
    .catch(handleFetchError);
}

// Close modal
function closeModal() {
    document.getElementById('attendanceModal').style.display = 'none';
}

// Handle form submission
document.getElementById('attendance-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        volunteer_id: formData.get('volunteer_id'),
        event_id: formData.get('event_id'),
        attendance_date: formData.get('attendance_date'),
        hours_worked: formData.get('hours_worked'),
        status: formData.get('status'),
        notes: formData.get('notes')
    };
    
    const action = data.id ? 'update' : 'create';
    
    fetch(`controllers/AttendanceController.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Attendance ${action === 'create' ? 'recorded' : 'updated'} successfully`, 'success');
            closeModal();
            loadAttendance();
        } else {
            showAlert(data.message || `Failed to ${action} attendance`, 'error');
        }
    })
    .catch(handleFetchError);
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('attendanceModal');
    if (event.target === modal) {
        closeModal();
    }
}
