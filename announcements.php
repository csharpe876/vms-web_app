<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>Announcements</h1>
        <?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN'])): ?>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> New Announcement
        </button>
        <?php endif; ?>
    </div>

    <div class="content-section">
        <div id="announcements-list"></div>
    </div>
</div>

<!-- Add/Edit Announcement Modal -->
<?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN'])): ?>
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title">Add Announcement</h2>
        <form id="announcement-form">
            <input type="hidden" id="announcement-id" name="id">
            
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Priority *</label>
                    <select id="priority" name="priority" required>
                        <option value="LOW">Low</option>
                        <option value="MEDIUM" selected>Medium</option>
                        <option value="HIGH">High</option>
                        <option value="URGENT">Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="target_audience">Target Audience *</label>
                    <select id="target_audience" name="target_audience" required>
                        <option value="ALL" selected>All Users</option>
                        <option value="ADMINS">Admins Only</option>
                        <option value="COORDINATORS">Coordinators</option>
                        <option value="VOLUNTEERS">Volunteers</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="expiry_date">Expiry Date (Optional)</label>
                    <input type="date" id="expiry_date" name="expiry_date">
                </div>

                <div class="form-group">
                    <label for="is_active">Status *</label>
                    <select id="is_active" name="is_active" required>
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Announcement</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="assets/js/announcements.js"></script>

<?php require_once 'includes/footer.php'; ?>
