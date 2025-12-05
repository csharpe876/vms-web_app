<?php
class Announcement {
    private $conn;
    private $table_name = "announcements";

    public $id;
    public $title;
    public $message;
    public $priority;
    public $target_audience;
    public $publish_date;
    public $expiry_date;
    public $is_active;
    public $created_by;
    public $last_modified_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create announcement
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (title, message, priority, target_audience, expiry_date, is_active, 
                   created_by, last_modified_by)
                  VALUES (:title, :message, :priority, :target_audience, :expiry_date, :is_active,
                          :created_by, :last_modified_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':target_audience', $this->target_audience);
        $stmt->bindParam(':expiry_date', $this->expiry_date);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':created_by', $this->created_by);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all announcements
    public function getAll() {
        $query = "SELECT a.*, 
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.created_by = u.id
                  ORDER BY a.publish_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get active announcements
    public function getActive() {
        $query = "SELECT a.*, 
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.created_by = u.id
                  WHERE a.is_active = 1 
                  AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())
                  ORDER BY 
                    CASE a.priority
                      WHEN 'URGENT' THEN 1
                      WHEN 'HIGH' THEN 2
                      WHEN 'MEDIUM' THEN 3
                      WHEN 'LOW' THEN 4
                    END,
                    a.publish_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get announcement by ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get announcement by ID for editing
    public function getById($id) {
        $query = "SELECT a.*, 
                         CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.created_by = u.id
                  WHERE a.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update announcement
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET title = :title, message = :message, priority = :priority,
                      target_audience = :target_audience, expiry_date = :expiry_date,
                      is_active = :is_active, last_modified_by = :last_modified_by
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':target_audience', $this->target_audience);
        $stmt->bindParam(':expiry_date', $this->expiry_date);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Delete announcement
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Toggle active status
    public function toggleActive($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = NOT is_active 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
