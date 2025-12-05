<?php
/**
 * VolunteerController
 * Handles volunteer CRUD operations
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Volunteer.php';

class VolunteerController {
    private $db;
    private $volunteer;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->volunteer = new Volunteer($this->db);
    }

    // Get all volunteers
    public function getAll() {
        header('Content-Type: application/json');
        $volunteers = $this->volunteer->getAll();
        echo json_encode(['success' => true, 'data' => $volunteers]);
    }

    // Get volunteer by ID
    public function getById($id) {
        header('Content-Type: application/json');
        $volunteer = $this->volunteer->getById($id);
        if ($volunteer) {
            echo json_encode(['success' => true, 'data' => $volunteer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Volunteer not found']);
        }
    }

    // Create volunteer
    public function create() {
        header('Content-Type: application/json');
        session_start();

        $this->volunteer->user_id = $_POST['user_id'] ?? null;
        $this->volunteer->first_name = $_POST['first_name'] ?? '';
        $this->volunteer->last_name = $_POST['last_name'] ?? '';
        $this->volunteer->email = $_POST['email'] ?? '';
        $this->volunteer->phone = $_POST['phone'] ?? '';
        $this->volunteer->address = $_POST['address'] ?? '';
        $this->volunteer->skills = $_POST['skills'] ?? '';
        $this->volunteer->availability = $_POST['availability'] ?? '';
        $this->volunteer->emergency_contact = $_POST['emergency_contact'] ?? '';
        $this->volunteer->status = $_POST['status'] ?? 'ACTIVE';
        $this->volunteer->join_date = $_POST['join_date'] ?? date('Y-m-d');
        $this->volunteer->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->volunteer->create()) {
            echo json_encode(['success' => true, 'message' => 'Volunteer created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create volunteer']);
        }
    }

    // Update volunteer
    public function update() {
        header('Content-Type: application/json');
        session_start();

        $this->volunteer->id = $_POST['id'] ?? 0;
        $this->volunteer->first_name = $_POST['first_name'] ?? '';
        $this->volunteer->last_name = $_POST['last_name'] ?? '';
        $this->volunteer->email = $_POST['email'] ?? '';
        $this->volunteer->phone = $_POST['phone'] ?? '';
        $this->volunteer->address = $_POST['address'] ?? '';
        $this->volunteer->skills = $_POST['skills'] ?? '';
        $this->volunteer->availability = $_POST['availability'] ?? '';
        $this->volunteer->emergency_contact = $_POST['emergency_contact'] ?? '';
        $this->volunteer->status = $_POST['status'] ?? 'ACTIVE';
        $this->volunteer->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->volunteer->update()) {
            echo json_encode(['success' => true, 'message' => 'Volunteer updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update volunteer']);
        }
    }

    // Delete volunteer
    public function delete() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($this->volunteer->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Volunteer deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete volunteer']);
        }
    }

    // Get volunteer stats
    public function getStats() {
        header('Content-Type: application/json');
        session_start();
        $volunteer_id = $_SESSION['volunteer_id'] ?? $_GET['volunteer_id'] ?? 0;
        
        $stats = $this->volunteer->getStats($volunteer_id);
        echo json_encode(['success' => true, 'data' => $stats]);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new VolunteerController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $controller->getAll();
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
        case 'getStats':
            $controller->getStats();
            break;
    }
}
?>
