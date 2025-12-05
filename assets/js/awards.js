// Awards Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    loadLeaderboard();
    loadAwards();
    loadVolunteersDropdown();
});

// Load award statistics
function loadStatistics() {
    fetch('controllers/AwardController.php?action=getStats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                document.getElementById('total-badges').textContent = stats.total || 0;
                document.getElementById('bronze-count').textContent = stats.bronze || 0;
                document.getElementById('silver-count').textContent = stats.silver || 0;
                document.getElementById('gold-count').textContent = stats.gold || 0;
            }
        })
        .catch(handleFetchError);
}

// Load leaderboard
function loadLeaderboard() {
    fetch('controllers/AwardController.php?action=getLeaderboard')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaderboard(data.data);
            }
        })
        .catch(handleFetchError);
}

// Display leaderboard
function displayLeaderboard(volunteers) {
    const container = document.getElementById('leaderboard');
    
    if (volunteers.length === 0) {
        container.innerHTML = '<p class="no-data">No leaderboard data available.</p>';
        return;
    }
    
    let html = `
        <table class="data-table leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Volunteer</th>
                    <th>Badges</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    volunteers.forEach((volunteer, index) => {
        const rankClass = index < 3 ? `rank-${index + 1}` : '';
        const rankIcon = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '';
        
        html += `
            <tr class="${rankClass}">
                <td><strong>${rankIcon} ${index + 1}</strong></td>
                <td>${volunteer.volunteer_name}</td>
                <td><strong>${volunteer.badge_count}</strong> badges</td>
                <td>${volunteer.total_hours_worked} hours</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Load all awards
function loadAwards() {
    fetch('controllers/AwardController.php?action=getAll')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAwards(data.data);
            } else {
                showAlert(data.message || 'Failed to load awards', 'error');
            }
        })
        .catch(handleFetchError);
}

// Display awards
function displayAwards(awards) {
    const container = document.getElementById('awards-list');
    
    if (awards.length === 0) {
        container.innerHTML = '<p class="no-data">No awards found.</p>';
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Badge</th>
                    <th>Volunteer</th>
                    <th>Reason</th>
                    <th>Date Awarded</th>
                    <th>Awarded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    awards.forEach(award => {
        html += `
            <tr>
                <td>
                    <span class="badge-tier">${getBadgeTierIcon(award.badge_tier)} ${award.badge_tier}</span>
                </td>
                <td>${award.volunteer_name}</td>
                <td>${award.reason}</td>
                <td>${formatDate(award.date_awarded)}</td>
                <td>${award.awarded_by_name}</td>
                <td class="actions">
                    ${getAwardActions(award)}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Get award actions based on permissions
function getAwardActions(award) {
    const userRole = document.body.dataset.userRole || '';
    
    if (['SUPER_ADMIN', 'ADMIN'].includes(userRole)) {
        return `
            <button class="btn btn-sm btn-danger" onclick="deleteAward(${award.id})" title="Delete">
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
                const select = document.getElementById('volunteer_id');
                if (select) {
                    let options = '<option value="">Select Volunteer</option>';
                    data.data.forEach(volunteer => {
                        options += `<option value="${volunteer.id}">${volunteer.first_name} ${volunteer.last_name}</option>`;
                    });
                    select.innerHTML = options;
                }
            }
        })
        .catch(handleFetchError);
}

// Show add modal
function showAddModal() {
    document.getElementById('award-form').reset();
    document.getElementById('awardModal').style.display = 'block';
}

// Delete award
function deleteAward(id) {
    if (!confirmAction('Are you sure you want to delete this award?')) {
        return;
    }
    
    fetch('controllers/AwardController.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Award deleted successfully', 'success');
            loadStatistics();
            loadLeaderboard();
            loadAwards();
        } else {
            showAlert(data.message || 'Failed to delete award', 'error');
        }
    })
    .catch(handleFetchError);
}

// Close modal
function closeModal() {
    document.getElementById('awardModal').style.display = 'none';
}

// Handle form submission
document.getElementById('award-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        volunteer_id: formData.get('volunteer_id'),
        badge_tier: formData.get('badge_tier'),
        reason: formData.get('reason')
    };
    
    fetch('controllers/AwardController.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Badge awarded successfully', 'success');
            closeModal();
            loadStatistics();
            loadLeaderboard();
            loadAwards();
        } else {
            showAlert(data.message || 'Failed to award badge', 'error');
        }
    })
    .catch(handleFetchError);
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('awardModal');
    if (event.target === modal) {
        closeModal();
    }
}
