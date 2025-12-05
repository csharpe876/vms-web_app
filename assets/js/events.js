// Events Management JavaScript

let allEvents = [];

document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
});

// Load all events
function loadEvents() {
    fetch('controllers/EventController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allEvents = data.data;
                displayEvents(allEvents);
            } else {
                showAlert(data.message || 'Failed to load events', 'error');
            }
        })
        .catch(handleFetchError);
}

// Display events
function displayEvents(events) {
    const container = document.getElementById('events-list');
    
    if (events.length === 0) {
        container.innerHTML = '<p class="no-data">No events found.</p>';
        return;
    }
    
    let html = '<div class="events-grid">';
    
    events.forEach(event => {
        const capacity = `${event.registered_volunteers || 0}/${event.max_volunteers}`;
        const capacityPercent = Math.round((event.registered_volunteers || 0) / event.max_volunteers * 100);
        
        html += `
            <div class="event-card">
                <div class="event-header">
                    <h3>${event.event_name}</h3>
                    ${getStatusBadge(event.status)}
                </div>
                <div class="event-body">
                    <p class="event-description">${event.description}</p>
                    <div class="event-details">
                        <div class="event-detail">
                            <i class="fas fa-calendar"></i>
                            <span>${formatDate(event.event_date)}</span>
                        </div>
                        <div class="event-detail">
                            <i class="fas fa-clock"></i>
                            <span>${formatTime(event.start_time)} - ${formatTime(event.end_time)}</span>
                        </div>
                        <div class="event-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${event.location}</span>
                        </div>
                        <div class="event-detail">
                            <i class="fas fa-users"></i>
                            <span>${capacity} volunteers</span>
                        </div>
                    </div>
                    <div class="capacity-bar">
                        <div class="capacity-fill" style="width: ${capacityPercent}%"></div>
                    </div>
                    ${event.coordinator_notes ? `<p class="coordinator-notes"><strong>Notes:</strong> ${event.coordinator_notes}</p>` : ''}
                </div>
                <div class="event-footer">
                    <span class="event-meta">Created by ${event.created_by_name} on ${formatDate(event.created_date)}</span>
                    <div class="event-actions">
                        ${getEventActions(event)}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Get event actions based on permissions
function getEventActions(event) {
    const userRole = document.body.dataset.userRole || '';
    
    if (['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'].includes(userRole)) {
        return `
            <button class="btn btn-sm btn-secondary" onclick="editEvent(${event.id})">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteEvent(${event.id})">
                <i class="fas fa-trash"></i> Delete
            </button>
        `;
    }
    return '';
}

// Filter events
function filterEvents() {
    const statusFilter = document.getElementById('filter-status').value;
    const searchText = document.getElementById('search-events').value.toLowerCase();
    
    let filtered = allEvents;
    
    if (statusFilter) {
        filtered = filtered.filter(event => event.status === statusFilter);
    }
    
    if (searchText) {
        filtered = filtered.filter(event => 
            event.event_name.toLowerCase().includes(searchText) ||
            event.description.toLowerCase().includes(searchText) ||
            event.location.toLowerCase().includes(searchText)
        );
    }
    
    displayEvents(filtered);
}

// Show add modal
function showAddModal() {
    document.getElementById('modal-title').textContent = 'Create Event';
    document.getElementById('event-form').reset();
    document.getElementById('event-id').value = '';
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('event_date').min = today;
    
    document.getElementById('eventModal').style.display = 'block';
}

// Edit event
function editEvent(id) {
    fetch(`controllers/EventController.php?action=getById&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const event = data.data;
                document.getElementById('modal-title').textContent = 'Edit Event';
                document.getElementById('event-id').value = event.id;
                document.getElementById('event_name').value = event.event_name;
                document.getElementById('description').value = event.description;
                document.getElementById('event_date').value = event.event_date;
                document.getElementById('start_time').value = event.start_time;
                document.getElementById('end_time').value = event.end_time;
                document.getElementById('location').value = event.location;
                document.getElementById('max_volunteers').value = event.max_volunteers;
                document.getElementById('status').value = event.status;
                document.getElementById('coordinator_notes').value = event.coordinator_notes || '';
                document.getElementById('eventModal').style.display = 'block';
            } else {
                showAlert(data.message || 'Failed to load event', 'error');
            }
        })
        .catch(handleFetchError);
}

// Delete event
function deleteEvent(id) {
    if (!confirmAction('Are you sure you want to delete this event?')) {
        return;
    }
    
    fetch('controllers/EventController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Event deleted successfully', 'success');
            loadEvents();
        } else {
            showAlert(data.message || 'Failed to delete event', 'error');
        }
    })
    .catch(handleFetchError);
}

// Close modal
function closeModal() {
    document.getElementById('eventModal').style.display = 'none';
}

// Handle form submission
document.getElementById('event-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        event_name: formData.get('event_name'),
        description: formData.get('description'),
        event_date: formData.get('event_date'),
        start_time: formData.get('start_time'),
        end_time: formData.get('end_time'),
        location: formData.get('location'),
        max_volunteers: formData.get('max_volunteers'),
        status: formData.get('status'),
        coordinator_notes: formData.get('coordinator_notes')
    };
    
    // Validate end time is after start time
    if (data.start_time >= data.end_time) {
        showAlert('End time must be after start time', 'error');
        return;
    }
    
    const action = data.id ? 'update' : 'create';
    
    fetch(`controllers/EventController.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`Event ${action === 'create' ? 'created' : 'updated'} successfully`, 'success');
            closeModal();
            loadEvents();
        } else {
            showAlert(data.message || `Failed to ${action} event`, 'error');
        }
    })
    .catch(handleFetchError);
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('eventModal');
    if (event.target === modal) {
        closeModal();
    }
}
