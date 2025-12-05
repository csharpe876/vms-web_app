<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Volunteer Management System'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>🌟 VMS</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="volunteers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'volunteers.php' ? 'active' : ''; ?>">Volunteers</a></li>
                <li><a href="events.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">Events</a></li>
                <li><a href="attendance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">Attendance</a></li>
                <li><a href="timesheets.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'timesheets.php' ? 'active' : ''; ?>">Timesheets</a></li>
                <li><a href="awards.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'awards.php' ? 'active' : ''; ?>">Awards</a></li>
                <li><a href="announcements.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>">Announcements</a></li>
            </ul>
            <div class="nav-user">
                <span>👤 <?php echo $_SESSION['full_name'] ?? 'User'; ?> (<?php echo $_SESSION['role'] ?? 'VOLUNTEER'; ?>)</span>
                <a href="controllers/AuthController.php?action=logout" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container"