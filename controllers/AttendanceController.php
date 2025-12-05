<?php
/**
 * AttendanceController
 * Handles attendance CRUD operations and timesheet creation
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Volunteer.php';
require_once __DIR__ . '/../models/Timesheet.php';

class AttendanceController {
    private $db;
    private $attendance;
    private $event;
    private $volunteer;
    private $timesheet;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->attendance = new Attendance($this->db);
        $this->event = new Event($this->db);
        $this->volunteer = new Volunteer($this->db);
        $this->timesheet = new Timesheet($this->db);
    }

    // Get all attendance records
    public function getAll() {
        header('Content-Type: application/json');
        $records = $this->attendance->getAll();
        echo json_encode(['success' => true, 'data' => $records]);
    }

    // Get attendance by volunteer
    public function getByVolunteer() {
        header('Content-Type: application/json');
        session_start();
        $volunteer_id = $_SESSION['volunteer_id'] ?? $_GET['volunteer_id'] ?? 0;
        $records = $this->attendance->getByVolunteer($volunteer_id);
        echo json_encode(['success' => true, 'data' => $records]);
    }

    // Get attendance by event
    public function getByEvent() {
        header('Content-Type: application/json');
        $event_id = $_GET['event_id'] ?? 0;
        $records = $this->attendance->getByEvent($event_id);
        echo json_encode(['success' => true, 'data' => $records]);
    }

    // Create attendance record
    public function create() {
        header('Content-Type: application/json');
        session_start();

        $volunteer_id = $_POST['volunteer_id'] ?? 0;
        $event_id = $_POST['event_id'] ?? 0;
        $hours_worked = $_POST['hours_worked'] ?? 0;

        // Validate
        if (!$volunteer_id || !$event_id || !$hours_worked) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }

        // Get event details
        $eventData = $this->event->getById($event_id);
        if (!$eventData) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            return;
        }

        // Create attendance record
        $this->attendance->volunteer_id = $volunteer_id;
        $this->attendance->event_id = $event_id;
        $this->attendance->check_in_time = date('Y-m-d H:i:s');
        $this->attendance->hours_worked = $hours_worked;
        $this->attendance->status = 'PRESENT';
        $this->attendance->feedback = $_POST['feedback'] ?? '';
        $this->attendance->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($attendance_id = $this->attendance->create()) {
            // Update event registrations
            $this->event->updateRegistrations($event_id, true);

            // Update volunteer hours
            $this->volunteer->updateHours($volunteer_id, $hours_worked);

            // Create timesheet automatically
            $this->timesheet->volunteer_id = $volunteer_id;
            $this->timesheet->event_id = $event_id;
            $this->timesheet->event_name = $eventData['title'];
            $this->timesheet->period_start_date = $eventData['event_date'];
            $this->timesheet->period_end_date = $eventData['event_date'];
            $this->timesheet->total_hours = $hours_worked;
            $this->timesheet->approved_hours = 0;
            $this->timesheet->approval_status = 'PENDING';
            $this->timesheet->last_modified_by = $_SESSION['user_id'] ?? null;
            $this->timesheet->create();

            echo json_encode(['success' => true, 'message' => 'Attendance recorded and timesheet created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record attendance']);
        }
    }

    // Delete attendance
    public function delete() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        // Get attendance details before deleting
        $attendanceData = $this->attendance->getById($id);
        if ($attendanceData) {
            if ($this->attendance->delete($id)) {
                // Restore event capacity
                $this->event->updateRegistrations($attendanceData['event_id'], false);
                
                // Deduct hours from volunteer
                $this->volunteer->updateHours($attendanceData['volunteer_id'], -$attendanceData['hours_worked']);

                echo json_encode(['success' => true, 'message' => 'Attendance deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete attendance']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new AttendanceController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $controller->getAll();
            break;
        case 'getByVolunteer':
            $controller->getByVolunteer();
            break;
        case 'getByEvent':
            $controller->getByEvent();
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
