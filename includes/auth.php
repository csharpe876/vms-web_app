<?php
/**
 * Authentication Check
 * Protects pages that require login
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Helper functions
function isAdmin() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['SUPER_ADMIN', 'ADMIN']);
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'SUPER_ADMIN';
}

function isCoordinator() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'COORDINATOR';
}

function isVolunteer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'VOLUNTEER';
}

function canEdit() {
    return isAdmin() || isSuperAdmin();
}
?>