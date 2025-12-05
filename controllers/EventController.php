<?php
/**
 * EventController
 * Handles event CRUD operations
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Event.php';

class EventController {
    private $db;
    private $event;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->event = new Event($this->db);
    }

    // Get all events
    public function getAll() {
        header('Content-Type: application/json');
        $events = $this->event->getAll();
        echo json_encode(['success' => true, 'data' => $events]);
    }

    // Get upcoming events
    public function getUpcoming() {
        header('Content-Type: application/json');
        $events = $this->event->getUpcoming();
        echo json_encode(['success' => true, 'data' => $events]);
    }

    // Get past events
    public function getPast() {
        header('Content-Type: application/json');
        $events = $this->event->getPast();
        echo json_encode(['success' => true, 'data' => $events]);
    }

    // Get event by ID
    public function getById($id) {
        header('Content-Type: application/json');
        $event = $this->event->getById($id);
        if ($event) {
            echo json_encode(['success' => true, 'data' => $event]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
        }
    }

    // Create event
    public function create() {
        header('Content-Type: application/json');
        session_start();

        $this->event->title = $_POST['title'] ?? '';
        $this->event->description = $_POST['description'] ?? '';
        $this->event->event_date = $_POST['event_date'] ?? '';
        $this->event->location = $_POST['location'] ?? '';
        $this->event->event_type = $_POST['event_type'] ?? 'COMMUNITY_SERVICE';
        $this->event->target_audience = $_POST['target_audience'] ?? 'ALL';
        $this->event->capacity = $_POST['capacity'] ?? 0;
        $this->event->current_registrations = 0;
        $this->event->status = $_POST['status'] ?? 'PUBLISHED';
        $this->event->organizer_id = $_SESSION['user_id'] ?? null;
        $this->event->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->event->create()) {
            echo json_encode(['success' => true, 'message' => 'Event created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create event']);
        }
    }

    // Update event
    public function update() {
        header('Content-Type: application/json');
        session_start();

        $this->event->id = $_POST['id'] ?? 0;
        $this->event->title = $_POST['title'] ?? '';
        $this->event->description = $_POST['description'] ?? '';
        $this->event->event_date = $_POST['event_date'] ?? '';
        $this->event->location = $_POST['location'] ?? '';
        $this->event->event_type = $_POST['event_type'] ?? 'COMMUNITY_SERVICE';
        $this->event->target_audience = $_POST['target_audience'] ?? 'ALL';
        $this->event->capacity = $_POST['capacity'] ?? 0;
        $this->event->status = $_POST['status'] ?? 'PUBLISHED';
        $this->event->last_modified_by = $_SESSION['user_id'] ?? null;

        if ($this->event->update()) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update event']);
        }
    }

    // Delete event
    public function delete() {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? 0;

        if ($this->event->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new EventController();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'getAll':
            $controller->getAll();
            break;
        case 'getUpcoming':
            $controller->getUpcoming();
            break;
        case 'getPast':
            $controller->getPast();
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
