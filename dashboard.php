<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
$pageTitle = 'Dashboard';
include 'includes/header.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Count active volunteers
$query = "SELECT COUNT(*) as count FROM volunteers WHERE status = 'ACTIVE'";
$stmt = $db->query($query);
$stats['active_volunteers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Count upcoming events
$query = "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE() AND status = 'PUBLISHED'";
$stmt = $db->query($query);
$stats['upcoming_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total hours
if (isAdmin()) {
    $query = "SELECT SUM(total_hours_worked) as total FROM volunteers";
    $stmt = $db->query($query);
    $stats['total_hours'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} else {
    $query = "SELECT total_hours_worked as total FROM volunteers WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $stats['total_hours'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

// Get badges earned
if (isAdmin()) {
    $query = "SELECT COUNT(*) as count FROM awards";
    $stmt = $db->query($query);
    $stats['badges_earned'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} else {
    $query = "SELECT badges_earned as count FROM volunteers WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $stats['badges_earned'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
}

// Get recent announcements
$query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
          FROM announcements a
          LEFT JOIN users u ON a.created_by = u.id
          WHERE a.is_active = 1 
          AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())
          ORDER BY 
            CASE a.priority 
                WHEN 'URGENT' THEN 1
                WHEN 'HIGH' THEN 2
                WHEN 'MEDIUM' THEN 3
                WHEN 'LOW' THEN 4
            END,
            a.publish_date DESC
          LIMIT 5";
$stmt = $db->query($query);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>📊 Dashboard</h1>
<p>Welcome back, <?php echo $_SESSION['full_name']; ?>!</p>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-details">
            <h3><?php echo $stats['active_volunteers']; ?></h3>
            <p>Active Volunteers</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-details">
            <h3><?php echo $stats['upcoming_events']; ?></h3>
            <p>Upcoming Events</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-details">
            <h3><?php echo number_format($stats['total_hours'], 2); ?></h3>
            <p><?php echo isAdmin() ? 'Total Hours (All)' : 'My Hours'; ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-details">
            <h3><?php echo $stats['badges_earned']; ?></h3>
            <p><?php echo isAdmin() ? 'Total Badges' : 'My Badges'; ?></p>
        </div>
    </div>
</div>

<!-- Recent Announcements -->
<div class="section">
    <h2>📢 Recent Announcements</h2>
    <?php if (count($announcements) > 0): ?>
        <div class="announcements-list">
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card priority-<?php echo strtolower($announcement['priority']); ?>">
                    <div class="announcement-header">
                        <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <span class="priority-badge <?php echo strtolower($announcement['priority']); ?>">
                            <?php echo $announcement['priority']; ?>
                        </span>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                    <div class="announcement-footer">
                        <small>By <?php echo htmlspecialchars($announcement['created_by_name']); ?> on <?php echo date('M d, Y', strtotime($announcement['publish_date'])); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-data">No active announcements at this time.</p>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="section">
    <h2>⚡ Quick Actions</h2>
    <div class="quick-actions">
        <?php if (canEdit()): ?>
            <a href="events.php" class="action-btn">➕ Create Event</a>
            <a href="announcements.php" class="action-btn">📣 New Announcement</a>
        <?php endif; ?>
        <a href="attendance.php" class="action-btn">✅ Record Attendance</a>
        <a href="timesheets.php" class="action-btn">📝 Submit Timesheet</a>
        <a href="events.php" class="action-btn">🔍 View Events</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>