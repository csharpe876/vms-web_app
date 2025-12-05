<?php
/**
 * TimesheetController
 * Handles timesheet CRUD and approval operations
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Timesheet.php';

class TimesheetController {
    private $db;
    private $timesheet;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->timesheet = new Timesheet($this->db);
    }

    // Get all timesheets
    public function getAll() {
        header('Content-Type: application/json');
        $timesheets = $this->timesheet->getAll();
        echo json_encode(['success' => true, 'data' => $timesheets]);
    }

    // Get timesheets by volunteer
    public function getByVolunteer() {
        header('Content-Type: application/json');
        session_start();
        $volunteer_id = $_SESSION['volunteer_id'] ?? $_GET['volunteer_id'] ?? 0;
        $timesheets = $this->timesheet->getByVolunteer($volunteer_id);
        echo json_encode(['success' => true, 'data' => $timesheets]);
    }

    // Get pending timesheets
    public function getPending() {
        header('Content-Type: application/json');
        $timesheets = $this->timesheet->getPending();
        echo json_encode(['success' => true, 'data' => $timesheets]);
    }

    // Create timesheet
    public function create() {
        header('Content-Type: application/json');
        session_start();

        $this->timesheet->volunteer_id = $_POST['volunteer_id'] ?? 0;
        $this->timesheet->event_id = $_POST['event_id'] ?? null;
        $this->timesheet->event_name = $_POST['event_name'] ?? '';
        $this->timesheet->period_start_date = $_POST['period_start_date'] ?? '';
        $this->timesheet->period_end_date = $_POST['period_end_date'] ?? '';
        $this->timesheet->total_hours = $_POST['total_hours'] ?? 0;
        $this->timesheet->approved_hours = 0;
        $this->timesheet->approval_status = 'PENDING';
        $this->timesheet->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->timesheet->create()) {
            echo json_encode(['success' => true, 'message' => 'Timesheet submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit timesheet']);
        }
    }

    // Update timesheet
    public function update() {
        header('Content-Type: application/json');
        session_start();

        $this->timesheet->id = $_POST['id'] ?? 0;
        $this->timesheet->volunteer_id = $_POST['volunteer_id'] ?? 0;
        $this->timesheet->event_id = $_POST['event_id'] ?? null;
        $this->timesheet->event_name = $_POST['event_name'] ?? '';
        $this->timesheet->period_start_date = $_POST['period_start_date'] ?? '';
        $this->timesheet->period_end_date = $_POST['period_end_date'] ?? '';
        $this->timesheet->total_hours = $_POST['total_hours'] ?? 0;
        $this->timesheet->approval_status = $_POST['approval_status'] ?? 'PENDING';
        $this->timesheet->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->timesheet->update()) {
            echo json_encode(['success' => true, 'message' => 'Timesheet updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update timesheet']);
        }
    }

    // Approve timesheet
    public function approve() {
        header('Content-Type: application/json');
        session_start();

        $id = $_POST['id'] ?? 0;
        $approved_hours = $_POST['approved_hours'] ?? 0;
        $admin_id = $_SESSION['user_id'] ?? 0;

        if ($this->timesheet->approve($id, $approved_hours, $admin_id)) {
            echo json_encode(['success' => true, 'message' => 'Timesheet approved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve timesheet']);
        }
    }

    // Reject timesheet
    public function reject() {
        header('Content-Type: application/json');
        session_start();

        $id = $_POST['id'] ?? 0;
        $reason = $_POST['rejection_reason'] ?? '';
        $admin_id = $_SESSION['user_id'] ?? 0;

        if ($this->timesheet->reject($id, $reason, $admin_id)) {
            echo json_encode(['success' => true, 'message' => 'Timesheet rejected']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject timesheet']);
        }
    }

    // Delete timesheet
    public function delete() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($this->timesheet->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Timesheet deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete timesheet']);
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new TimesheetController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $controller->getAll();
            break;
        case 'getByVolunteer':
            $controller->getByVolunteer();
            break;
        case 'getPending':
            $controller->getPending();
            break;
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'approve':
            $controller->approve();
            break;
        case 'reject':
            $controller->reject();
            break;
        case 'delete':
            $controller->delete();
            break;
    }
}
?>
