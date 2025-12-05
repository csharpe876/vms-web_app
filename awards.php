<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>Awards & Badges</h1>
        <?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN'])): ?>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-award"></i> Award Badge
        </button>
        <?php endif; ?>
    </div>

    <div class="content-section">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Badges</h3>
                <p class="stat-number" id="total-badges">0</p>
            </div>
            <div class="stat-card">
                <h3>Bronze Badges</h3>
                <p class="stat-number" id="bronze-count">0</p>
            </div>
            <div class="stat-card">
                <h3>Silver Badges</h3>
                <p class="stat-number" id="silver-count">0</p>
            </div>
            <div class="stat-card">
                <h3>Gold Badges</h3>
                <p class="stat-number" id="gold-count">0</p>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="section-header">
            <h2>Leaderboard</h2>
        </div>
        <div id="leaderboard"></div>

        <!-- All Awards -->
        <div class="section-header">
            <h2>All Awards</h2>
        </div>
        <div id="awards-list"></div>
    </div>
</div>

<!-- Add Award Modal -->
<?php if (in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN'])): ?>
<div id="awardModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Award Badge</h2>
        <form id="award-form">
            <div class="form-group">
                <label for="volunteer_id">Volunteer *</label>
                <select id="volunteer_id" name="volunteer_id" required>
                    <option value="">Select Volunteer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="badge_tier">Badge Tier *</label>
                <select id="badge_tier" name="badge_tier" required>
                    <option value="BRONZE">🥉 Bronze</option>
                    <option value="SILVER">🥈 Silver</option>
                    <option value="GOLD">🥇 Gold</option>
                    <option value="PLATINUM">💎 Platinum</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reason">Reason *</label>
                <textarea id="reason" name="reason" rows="4" 
                          placeholder="Describe why this volunteer is receiving this badge..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Award Badge</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="assets/js/awards.js"></script>

<?php require_once 'includes/footer.php'; ?>
