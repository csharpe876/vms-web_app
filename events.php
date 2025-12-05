<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>Events Management</h1>
        <?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'])): ?>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Create Event
        </button>
        <?php endif; ?>
    </div>

    <div class="content-section">
        <div class="filter-section">
            <div class="form-row">
                <div class="form-group">
                    <label for="filter-status">Filter by Status</label>
                    <select id="filter-status" onchange="filterEvents()">
                        <option value="">All Statuses</option>
                        <option value="UPCOMING">Upcoming</option>
                        <option value="IN_PROGRESS">In Progress</option>
                        <option value="COMPLETED">Completed</option>
                        <option value="CANCELLED">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="search-events">Search Events</label>
                    <input type="text" id="search-events" placeholder="Search by name..." onkeyup="filterEvents()">
                </div>
            </div>
        </div>

        <div id="events-list"></div>
    </div>
</div>

<!-- Add/Edit Event Modal -->
<?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'])): ?>
<div id="eventModal" class="modal">
    <div class="modal-content large">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title">Create Event</h2>
        <form id="event-form">
            <input type="hidden" id="event-id" name="id">
            
            <div class="form-group">
                <label for="event_name">Event Name *</label>
                <input type="text" id="event_name" name="event_name" required>
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Event Date *</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time *</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time *</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Location *</label>
                <input type="text" id="location" name="location" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="max_volunteers">Max Volunteers *</label>
                    <input type="number" id="max_volunteers" name="max_volunteers" min="1" required>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="UPCOMING" selected>Upcoming</option>
                        <option value="IN_PROGRESS">In Progress</option>
                        <option value="COMPLETED">Completed</option>
                        <option value="CANCELLED">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="coordinator_notes">Coordinator Notes (Optional)</label>
                <textarea id="coordinator_notes" name="coordinator_notes" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Event</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="assets/js/events.js"></script>

<?php require_once 'includes/footer.php'; ?>
