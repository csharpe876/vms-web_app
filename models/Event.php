<?php
/**
 * Event Model
 * Manages volunteer events
 */
class Event {
    private $conn;
    private $table_name = "events";

    public $id;
    public $title;
    public $description;
    public $event_date;
    public $location;
    public $event_type;
    public $target_audience;
    public $capacity;
    public $current_registrations;
    public $status;
    public $organizer_id;
    public $last_modified_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create event
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (title, description, event_date, location, event_type, target_audience,
                   capacity, current_registrations, status, organizer_id, last_modified_by)
                  VALUES (:title, :description, :event_date, :location, :event_type, :target_audience,
                          :capacity, :current_registrations, :status, :organizer_id, :last_modified_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':event_date', $this->event_date);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':event_type', $this->event_type);
        $stmt->bindParam(':target_audience', $this->target_audience);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':current_registrations', $this->current_registrations);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':organizer_id', $this->organizer_id);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all events
    public function getAll() {
        $query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN users u ON e.organizer_id = u.id
                  ORDER BY e.event_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get event by ID
    public function getById($id) {
        $query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN users u ON e.organizer_id = u.id
                  WHERE e.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get upcoming events
    public function getUpcoming() {
        $query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN users u ON e.organizer_id = u.id
                  WHERE e.event_date >= CURDATE() AND e.status = 'PUBLISHED'
                  ORDER BY e.event_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get past events
    public function getPast() {
        $query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN users u ON e.organizer_id = u.id
                  WHERE e.event_date < CURDATE() AND e.status IN ('PUBLISHED', 'COMPLETED')
                  ORDER BY e.event_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update event
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET title = :title, description = :description, event_date = :event_date,
                      location = :location, event_type = :event_type, target_audience = :target_audience,
                      capacity = :capacity, status = :status, last_modified_by = :last_modified_by
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':event_date', $this->event_date);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':event_type', $this->event_type);
        $stmt->bindParam(':target_audience', $this->target_audience);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Update registration count
    public function updateRegistrations($event_id, $increment = true) {
        $operator = $increment ? '+' : '-';
        $query = "UPDATE " . $this->table_name . "
                  SET current_registrations = current_registrations " . $operator . " 1
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $event_id);
        return $stmt->execute();
    }

    // Delete event
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Count events by status
    public function countByStatus($status) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>