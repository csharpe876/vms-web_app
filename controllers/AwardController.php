<?php
/**
 * AwardController
 * Handles award/badge operations
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Award.php';
require_once __DIR__ . '/../models/Volunteer.php';

class AwardController {
    private $db;
    private $award;
    private $volunteer;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->award = new Award($this->db);
        $this->volunteer = new Volunteer($this->db);
    }

    // Get all awards
    public function getAll() {
        header('Content-Type: application/json');
        $awards = $this->award->getAll();
        echo json_encode(['success' => true, 'data' => $awards]);
    }

    // Get awards by volunteer
    public function getByVolunteer() {
        header('Content-Type: application/json');
        session_start();
        $volunteer_id = $_SESSION['volunteer_id'] ?? $_GET['volunteer_id'] ?? 0;
        $awards = $this->award->getByVolunteer($volunteer_id);
        echo json_encode(['success' => true, 'data' => $awards]);
    }

    // Create award
    public function create() {
        header('Content-Type: application/json');
        session_start();

        $volunteer_id = $_POST['volunteer_id'] ?? 0;
        $this->award->volunteer_id = $volunteer_id;
        $this->award->badge_tier = $_POST['badge_tier'] ?? 'BRONZE';
        $this->award->reason = $_POST['reason'] ?? '';
        $this->award->awarded_by_admin_id = $_SESSION['user_id'] ?? null;

        if ($this->award->create()) {
            // Update volunteer badge count
            $this->volunteer->updateBadgeCount($volunteer_id);
            echo json_encode(['success' => true, 'message' => 'Award issued successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to issue award']);
        }
    }

    // Delete award
    public function delete() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;
        
        // Get award details before deleting
        $awardData = $this->award->getById($id);
        
        if ($this->award->delete($id)) {
            // Update volunteer badge count
            if ($awardData) {
                $this->volunteer->updateBadgeCount($awardData['volunteer_id']);
            }
            echo json_encode(['success' => true, 'message' => 'Award deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete award']);
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new AwardController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $controller->getAll();
            break;
        case 'getByVolunteer':
            $controller->getByVolunteer();
            break;
        case 'create':
            $controller->create();
            break;
        case 'delete':
            $controller->delete();
            break;
    }
}
?>
