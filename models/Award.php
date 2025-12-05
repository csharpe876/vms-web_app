<?php
class Award {
    private $conn;
    private $table_name = "awards";

    public $id;
    public $volunteer_id;
    public $badge_tier;
    public $reason;
    public $date_awarded;
    public $awarded_by_admin_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create award
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (volunteer_id, badge_tier, reason, awarded_by_admin_id)
                  VALUES (:volunteer_id, :badge_tier, :reason, :awarded_by_admin_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':volunteer_id', $this->volunteer_id);
        $stmt->bindParam(':badge_tier', $this->badge_tier);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':awarded_by_admin_id', $this->awarded_by_admin_id);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all awards
    public function getAll() {
        $query = "SELECT a.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         CONCAT(u.first_name, ' ', u.last_name) as awarded_by_name
                  FROM " . $this->table_name . " a
                  LEFT JOIN volunteers v ON a.volunteer_id = v.id
                  LEFT JOIN users u ON a.awarded_by_admin_id = u.id
                  ORDER BY a.date_awarded DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get awards by volunteer
    public function getByVolunteer($volunteer_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE volunteer_id = :volunteer_id 
                  ORDER BY date_awarded DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count badges by volunteer
    public function countByVolunteer($volunteer_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE volunteer_id = :volunteer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Get total badges count
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Get leaderboard (top volunteers by badges)
    public function getLeaderboard($limit = 10) {
        $query = "SELECT v.id, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         COUNT(a.id) as badge_count,
                         v.total_hours_worked
                  FROM volunteers v
                  LEFT JOIN " . $this->table_name . " a ON v.id = a.volunteer_id
                  GROUP BY v.id
                  ORDER BY badge_count DESC, v.total_hours_worked DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get award by ID
    public function getById($id) {
        $query = "SELECT a.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         CONCAT(u.first_name, ' ', u.last_name) as awarded_by_name
                  FROM " . $this->table_name . " a
                  LEFT JOIN volunteers v ON a.volunteer_id = v.id
                  LEFT JOIN users u ON a.awarded_by_admin_id = u.id
                  WHERE a.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete award
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
