<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>Attendance Management</h1>
        <?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'])): ?>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Record Attendance
        </button>
        <?php endif; ?>
    </div>

    <div class="content-section">
        <div class="filter-section">
            <div class="form-row">
                <div class="form-group">
                    <label for="filter-event">Filter by Event</label>
                    <select id="filter-event" onchange="filterAttendance()">
                        <option value="">All Events</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-volunteer">Filter by Volunteer</label>
                    <select id="filter-volunteer" onchange="filterAttendance()">
                        <option value="">All Volunteers</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="attendance-list"></div>
    </div>
</div>

<!-- Add/Edit Attendance Modal -->
<?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN', 'COORDINATOR'])): ?>
<div id="attendanceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title">Record Attendance</h2>
        <form id="attendance-form">
            <input type="hidden" id="attendance-id" name="id">
            
            <div class="form-group">
                <label for="volunteer_id">Volunteer *</label>
                <select id="volunteer_id" name="volunteer_id" required>
                    <option value="">Select Volunteer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="event_id">Event *</label>
                <select id="event_id" name="event_id" required>
                    <option value="">Select Event</option>
                </select>
            </div>

            <div class="form-group">
                <label for="attendance_date">Attendance Date *</label>
                <input type="date" id="attendance_date" name="attendance_date" required>
            </div>

            <div class="form-group">
                <label for="hours_worked">Hours Worked *</label>
                <input type="number" id="hours_worked" name="hours_worked" 
                       min="0" step="0.5" required>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="PRESENT" selected>Present</option>
                    <option value="ABSENT">Absent</option>
                    <option value="LATE">Late</option>
                    <option value="EXCUSED">Excused</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Attendance</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="assets/js/attendance.js"></script>

<?php require_once 'includes/footer.php'; ?>
