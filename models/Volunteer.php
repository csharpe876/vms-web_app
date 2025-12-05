<?php
/**
 * Volunteer Model
 * Manages volunteer profiles and operations
 */
class Volunteer {
    private $conn;
    private $table_name = "volunteers";

    public $id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address;
    public $skills;
    public $availability;
    public $emergency_contact;
    public $total_hours_worked;
    public $badges_earned;
    public $status;
    public $join_date;
    public $last_modified_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create volunteer
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (user_id, first_name, last_name, email, phone, address, skills, 
                   availability, emergency_contact, status, join_date, last_modified_by)
                  VALUES (:user_id, :first_name, :last_name, :email, :phone, :address, :skills,
                          :availability, :emergency_contact, :status, :join_date, :last_modified_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':skills', $this->skills);
        $stmt->bindParam(':availability', $this->availability);
        $stmt->bindParam(':emergency_contact', $this->emergency_contact);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':join_date', $this->join_date);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all volunteers
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY join_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get volunteer by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get volunteer by user ID
    public function findByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update volunteer
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET first_name = :first_name, last_name = :last_name, email = :email,
                      phone = :phone, address = :address, skills = :skills, 
                      availability = :availability, emergency_contact = :emergency_contact,
                      status = :status, last_modified_by = :last_modified_by
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':skills', $this->skills);
        $stmt->bindParam(':availability', $this->availability);
        $stmt->bindParam(':emergency_contact', $this->emergency_contact);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Update hours worked
    public function updateHours($volunteer_id, $hours) {
        $query = "UPDATE " . $this->table_name . "
                  SET total_hours_worked = total_hours_worked + :hours
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hours', $hours);
        $stmt->bindParam(':id', $volunteer_id);
        return $stmt->execute();
    }

    // Update badge count
    public function updateBadgeCount($volunteer_id) {
        $query = "UPDATE " . $this->table_name . "
                  SET badges_earned = (SELECT COUNT(*) FROM awards WHERE volunteer_id = :id)
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $volunteer_id);
        return $stmt->execute();
    }

    // Delete volunteer
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get volunteer statistics
    public function getStats($volunteer_id) {
        $query = "SELECT 
                    v.total_hours_worked,
                    v.badges_earned,
                    COUNT(DISTINCT a.id) as events_attended,
                    COUNT(DISTINCT t.id) as timesheets_submitted
                  FROM " . $this->table_name . " v
                  LEFT JOIN attendance a ON v.id = a.volunteer_id
                  LEFT JOIN timesheets t ON v.id = t.volunteer_id
                  WHERE v.id = :id
                  GROUP BY v.id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $volunteer_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>