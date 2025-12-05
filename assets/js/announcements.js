// Announcements Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadAnnouncements();
});

// Load all announcements
function loadAnnouncements() {
    fetch('controllers/AnnouncementController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAnnouncements(data.data);
            } else {
                showAlert(data.message || 'Failed to load announcements', 'error');
            }
        })
        .catch(handleFetchError);
}

// Display announcements
function displayAnnouncements(announcements) {
    const container = document.getElementById('announcements-list');
    
    if (announcements.length === 0) {
        container.innerHTML = '<p class="no-data">No announcements found.</p>';
        return;
    }
    
    let html = '<div class="announcements-container">';
    
    announcements.forEach(announcement => {
        const isExpired = announcement.expiry_date && new Date(announcement.expiry_date) < new Date();
        const cardClass = isExpired ? 'announcement-card expired' : 'announcement-card';
        
        html += `
            <div class="${cardClass}">
                <div class="announcement-header">
                    <div>
                        <h3>${announcement.title}</h3>
                        <span class="announcement-meta">
                            Posted by ${announcement.created_by_name || 'System'} on ${formatDate(announcement.publish_date)}
                        </span>
                    </div>
                    <div class="announcement-badges">
                        ${getPriorityBadge(announcement.priority)}
                        ${announcement.is_active == 1 ? getStatusBadge('ACTIVE') : getStatusBadge('INACTIVE')}
                        ${isExpired ? '<span class="badge badge-danger">EXPIRED</span>' : ''}
                    </div>
                </div>
                <div class="announcement-body">
                    <p>${announcement.message}</p>
                </div>
                <div class="announcement-footer">
                    <div class="announcement-info">
                        <span><strong>Audience:</strong> ${announcement.target_audience}</span>
                        ${announcement.expiry_date ? `<span><strong>Expires:</strong> ${formatDate(announcement.expiry_date)}</span>` : ''}
                    </div>
                    <div class="announcement-actions" id="actions-${announcement.id}">
                        ${getAnnouncementActions(announcement)}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Get announcement actions based on permissions
function getAnnouncementActions(announcement) {
    const userRole = document.body.dataset.userRole || '';
    
    if (['SUPER_ADMIN', 'ADMIN'].includes(userRole)) {
        return `
            <button class="btn btn-sm btn-secondary" onclick="editAnnouncement(${announcement.id})">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteAnnouncement(${announcement.id})">
                <i class="fas fa-trash"></i> Delete
            </button>
        `;
    }
    return '';
}

// Show add modal
function showAddModal() {
    document.getElementById('modal-title').textContent = 'Add Announcement';
    document.getElementById('announcement-form').reset();
    document.getElementById('announcement-id').value = '';
    document.getElementById('announcementModal').style.display = 'block';
}

// Edit announcement
function editAnnouncement(id) {
    fetch(`controllers/AnnouncementController.php?action=getById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const announcement = data.data;
                document.getElementById('modal-title').textContent = 'Edit Announcement';
                document.getElementById('announcement-id').value = announcement.id;
                document.getElementById('title').value = announcement.title;
                document.getElementById('message').value = announcement.message;
                document.getElementById('priority').value = announcement.priority;
                document.getElementById('target_audience').value = announcement.target_audience;
                document.getElementById('expiry_date').value = announcement.expiry_date || '';
                document.getElementById('is_active').value = announcement.is_active;
                document.getElementById('announcementModal').style.display = 'block';
            } else {
                showAlert(data.message || 'Failed to load announcement', 'error');
            }
        })
        .catch(handleFetchError);
}

// Delete announcement
function deleteAnnouncement(id) {
    if (!confirmAction('Are you sure you want to delete this announcement?')) {
        return;
    }
    
    fetch('controllers/AnnouncementController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Announcement deleted successfully', 'success');
            loadAnnouncements();
        } else {
            showAlert(data.message || 'Failed to delete announcement', 'error');
        }
    })
    .catch(handleFetchError);
}

// Close modal
function closeModal() {
    document.getElementById('announcementModal').style.display = 'none';
}

// Handle form submission
document.getElementById('announcement-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        title: formData.get('title'),
        message: formData.get('message'),
        priority: formData.get('priority'),
        target_audience: formData.get('target_audience'),
        expiry_date: formData.get('expiry_date') || null,
        is_active: formData.get('is_active')
    };
    
    const action = data.id ? 'update' : 'create';
    
    fetch(`controllers/AnnouncementController.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Announcement ${action === 'create' ? 'created' : 'updated'} successfully`, 'success');
            closeModal();
            loadAnnouncements();
        } else {
            showAlert(data.message || `Failed to ${action} announcement`, 'error');
        }
    })
    .catch(handleFetchError);
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('announcementModal');
    if (event.target === modal) {
        closeModal();
    }
}
