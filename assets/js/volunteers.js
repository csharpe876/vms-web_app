// Volunteers Management JavaScript

let allVolunteers = [];

document.addEventListener('DOMContentLoaded', function() {
    loadVolunteers();
});

// Load all volunteers
function loadVolunteers() {
    fetch('controllers/VolunteerController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allVolunteers = data.data;
                displayVolunteers(allVolunteers);
            } else {
                showAlert(data.message || 'Failed to load volunteers', 'error');
            }
        })
        .catch(handleFetchError);
}

// Display volunteers
function displayVolunteers(volunteers) {
    const container = document.getElementById('volunteers-list');
    
    if (volunteers.length === 0) {
        container.innerHTML = '<p class="no-data">No volunteers found.</p>';
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Skills</th>
                    <th>Status</th>
                    <th>Hours Worked</th>
                    <th>Badges</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    volunteers.forEach(volunteer => {
        html += `
            <tr>
                <td>${volunteer.first_name} ${volunteer.last_name}</td>
                <td>${volunteer.email}</td>
                <td>${volunteer.phone || 'N/A'}</td>
                <td><span class="skills-badge">${volunteer.skills || 'N/A'}</span></td>
                <td>${getStatusBadge(volunteer.status)}</td>
                <td>${volunteer.total_hours_worked || 0} hrs</td>
                <td>${volunteer.badge_count || 0} 🏅</td>
                <td class="actions">
                    ${getVolunteerActions(volunteer)}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Get volunteer actions based on permissions
function getVolunteerActions(volunteer) {
    const userRole = document.body.dataset.userRole || '';
    
    if (['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'].includes(userRole)) {
        return `
            <button class="btn btn-sm btn-info" onclick="viewVolunteer(${volunteer.id})" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-secondary" onclick="editVolunteer(${volunteer.id})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteVolunteer(${volunteer.id})" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `;
    }
    return '-';
}

// Filter volunteers
function filterVolunteers() {
    const statusFilter = document.getElementById('filter-status').value;
    const searchText = document.getElementById('search-volunteers').value.toLowerCase();
    
    let filtered = allVolunteers;
    
    if (statusFilter) {
        filtered = filtered.filter(volunteer => volunteer.status === statusFilter);
    }
    
    if (searchText) {
        filtered = filtered.filter(volunteer => 
            volunteer.first_name.toLowerCase().includes(searchText) ||
            volunteer.last_name.toLowerCase().includes(searchText) ||
            volunteer.email.toLowerCase().includes(searchText) ||
            (volunteer.skills && volunteer.skills.toLowerCase().includes(searchText))
        );
    }
    
    displayVolunteers(filtered);
}

// Show add modal
function showAddModal() {
    document.getElementById('modal-title').textContent = 'Add Volunteer';
    document.getElementById('volunteer-form').reset();
    document.getElementById('volunteer-id').value = '';
    document.getElementById('volunteerModal').style.display = 'block';
}

// View volunteer details
function viewVolunteer(id) {
    fetch(`controllers/VolunteerController.php?action=getById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const volunteer = data.data;
                let html = `
                    <div class="volunteer-details">
                        <h3>${volunteer.first_name} ${volunteer.last_name}</h3>
                        <table class="details-table">
                            <tr><th>Email:</th><td>${volunteer.email}</td></tr>
                            <tr><th>Phone:</th><td>${volunteer.phone || 'N/A'}</td></tr>
                            <tr><th>Address:</th><td>${volunteer.address || 'N/A'}</td></tr>
                            <tr><th>Date of Birth:</th><td>${formatDate(volunteer.date_of_birth)}</td></tr>
                            <tr><th>Skills:</th><td>${volunteer.skills || 'N/A'}</td></tr>
                            <tr><th>Status:</th><td>${getStatusBadge(volunteer.status)}</td></tr>
                            <tr><th>Total Hours:</th><td>${volunteer.total_hours_worked || 0} hours</td></tr>
                            <tr><th>Badge Count:</th><td>${volunteer.badge_count || 0} badges</td></tr>
                            <tr><th>Emergency Contact:</th><td>${volunteer.emergency_contact_name || 'N/A'}</td></tr>
                            <tr><th>Emergency Phone:</th><td>${volunteer.emergency_contact_phone || 'N/A'}</td></tr>
                            <tr><th>Registered:</th><td>${formatDate(volunteer.registration_date)}</td></tr>
                        </table>
                    </div>
                `;
                
                const modal = createInfoModal('Volunteer Details', html);
                document.body.appendChild(modal);
                modal.style.display = 'block';
            } else {
                showAlert(data.message || 'Failed to load volunteer details', 'error');
            }
        })
        .catch(handleFetchError);
}

// Edit volunteer
function editVolunteer(id) {
    fetch(`controllers/VolunteerController.php?action=getById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const volunteer = data.data;
                document.getElementById('modal-title').textContent = 'Edit Volunteer';
                document.getElementById('volunteer-id').value = volunteer.id;
                document.getElementById('first_name').value = volunteer.first_name;
                document.getElementById('last_name').value = volunteer.last_name;
                document.getElementById('email').value = volunteer.email;
                document.getElementById('phone').value = volunteer.phone || '';
                document.getElementById('address').value = volunteer.address || '';
                document.getElementById('date_of_birth').value = volunteer.date_of_birth;
                document.getElementById('skills').value = volunteer.skills || '';
                document.getElementById('status').value = volunteer.status;
                document.getElementById('emergency_contact_name').value = volunteer.emergency_contact_name || '';
                document.getElementById('emergency_contact_phone').value = volunteer.emergency_contact_phone || '';
                document.getElementById('volunteerModal').style.display = 'block';
            } else {
                showAlert(data.message || 'Failed to load volunteer', 'error');
            }
        })
        .catch(handleFetchError);
}

// Delete volunteer
function deleteVolunteer(id) {
    if (!confirmAction('Are you sure you want to delete this volunteer? This will also delete all associated records.')) {
        return;
    }
    
    fetch('controllers/VolunteerController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Volunteer deleted successfully', 'success');
            loadVolunteers();
        } else {
            showAlert(data.message || 'Failed to delete volunteer', 'error');
        }
    })
    .catch(handleFetchError);
}

// Close modal
function closeModal() {
    document.getElementById('volunteerModal').style.display = 'none';
}

// Handle form submission
document.getElementById('volunteer-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        date_of_birth: formData.get('date_of_birth'),
        skills: formData.get('skills'),
        status: formData.get('status'),
        emergency_contact_name: formData.get('emergency_contact_name'),
        emergency_contact_phone: formData.get('emergency_contact_phone')
    };
    
    const action = data.id ? 'update' : 'create';
    
    fetch(`controllers/VolunteerController.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Volunteer ${action === 'create' ? 'added' : 'updated'} successfully`, 'success');
            closeModal();
            loadVolunteers();
        } else {
            showAlert(data.message || `Failed to ${action} volunteer`, 'error');
        }
    })
    .catch(handleFetchError);
});

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

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('volunteerModal');
    if (event.target === modal) {
        closeModal();
    }
}
