<?php
/**
 * Attendance Model
 * Manages volunteer attendance at events
 */
class Attendance {
    private $conn;
    private $table_name = "attendance";

    public $id;
    public $volunteer_id;
    public $event_id;
    public $check_in_time;
    public $hours_worked;
    public $status;
    public $feedback;
    public $last_modified_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create attendance record
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (volunteer_id, event_id, check_in_time, hours_worked, status, feedback, last_modified_by)
                  VALUES (:volunteer_id, :event_id, :check_in_time, :hours_worked, :status, :feedback, :last_modified_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':volunteer_id', $this->volunteer_id);
        $stmt->bindParam(':event_id', $this->event_id);
        $stmt->bindParam(':check_in_time', $this->check_in_time);
        $stmt->bindParam(':hours_worked', $this->hours_worked);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':feedback', $this->feedback);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all attendance records
    public function getAll() {
        $query = "SELECT a.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         e.title as event_title,
                         e.event_date
                  FROM " . $this->table_name . " a
                  LEFT JOIN volunteers v ON a.volunteer_id = v.id
                  LEFT JOIN events e ON a.event_id = e.id
                  ORDER BY a.check_in_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get attendance by volunteer
    public function getByVolunteer($volunteer_id) {
        $query = "SELECT a.*, 
                         e.title as event_title,
                         e.event_date
                  FROM " . $this->table_name . " a
                  LEFT JOIN events e ON a.event_id = e.id
                  WHERE a.volunteer_id = :volunteer_id
                  ORDER BY a.check_in_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get attendance by event
    public function getByEvent($event_id) {
        $query = "SELECT a.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         v.email as volunteer_email
                  FROM " . $this->table_name . " a
                  LEFT JOIN volunteers v ON a.volunteer_id = v.id
                  WHERE a.event_id = :event_id
                  ORDER BY a.check_in_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update attendance
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET hours_worked = :hours_worked, status = :status, 
                      feedback = :feedback, last_modified_by = :last_modified_by
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':hours_worked', $this->hours_worked);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':feedback', $this->feedback);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Delete attendance
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get attendance by ID
    public function getById($id) {
        $query = "SELECT a.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         e.title as event_title,
                         e.event_date
                  FROM " . $this->table_name . " a
                  LEFT JOIN volunteers v ON a.volunteer_id = v.id
                  LEFT JOIN events e ON a.event_id = e.id
                  WHERE a.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get total hours for volunteer
    public function getTotalHours($volunteer_id) {
        $query = "SELECT SUM(hours_worked) as total FROM " . $this->table_name . 
                 " WHERE volunteer_id = :volunteer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>