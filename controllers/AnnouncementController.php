<?php
/**
 * AnnouncementController
 * Handles announcement operations
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Announcement.php';

class AnnouncementController {
    private $db;
    private $announcement;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->announcement = new Announcement($this->db);
    }

    // Get all announcements
    public function getAll() {
        header('Content-Type: application/json');
        $announcements = $this->announcement->getAll();
        echo json_encode(['success' => true, 'data' => $announcements]);
    }

    // Get active announcements
    public function getActive() {
        header('Content-Type: application/json');
        $announcements = $this->announcement->getActive();
        echo json_encode(['success' => true, 'data' => $announcements]);
    }

    // Get announcement by ID
    public function getById($id) {
        header('Content-Type: application/json');
        $announcement = $this->announcement->getById($id);
        if ($announcement) {
            echo json_encode(['success' => true, 'data' => $announcement]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        }
    }

    // Create announcement
    public function create() {
        header('Content-Type: application/json');
        session_start();

        $this->announcement->title = $_POST['title'] ?? '';
        $this->announcement->message = $_POST['message'] ?? '';
        $this->announcement->priority = $_POST['priority'] ?? 'MEDIUM';
        $this->announcement->target_audience = $_POST['target_audience'] ?? 'ALL';
        $this->announcement->expiry_date = $_POST['expiry_date'] ?? null;
        $this->announcement->is_active = $_POST['is_active'] ?? 1;
        $this->announcement->created_by = $_SESSION['user_id'] ?? null;
        $this->announcement->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->announcement->create()) {
            echo json_encode(['success' => true, 'message' => 'Announcement created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create announcement']);
        }
    }

    // Update announcement
    public function update() {
        header('Content-Type: application/json');
        session_start();

        $this->announcement->id = $_POST['id'] ?? 0;
        $this->announcement->title = $_POST['title'] ?? '';
        $this->announcement->message = $_POST['message'] ?? '';
        $this->announcement->priority = $_POST['priority'] ?? 'MEDIUM';
        $this->announcement->target_audience = $_POST['target_audience'] ?? 'ALL';
        $this->announcement->expiry_date = $_POST['expiry_date'] ?? null;
        $this->announcement->is_active = $_POST['is_active'] ?? 1;
        $this->announcement->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->announcement->update()) {
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update announcement']);
        }
    }

    // Delete announcement
    public function delete() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($this->announcement->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new AnnouncementController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $controller->getAll();
            break;
        case 'getActive':
            $controller->getActive();
            break;
        case 'getById':
            $controller->getById($_GET['id']);
            break;
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
    }
}
?>
