<?php
class Timesheet {
    private $conn;
    private $table_name = "timesheets";

    public $id;
    public $volunteer_id;
    public $event_id;
    public $event_name;
    public $period_start_date;
    public $period_end_date;
    public $total_hours;
    public $approved_hours;
    public $approval_status;
    public $approved_by_admin_id;
    public $approval_date;
    public $rejection_reason;
    public $last_modified_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create timesheet
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (volunteer_id, event_id, event_name, period_start_date, period_end_date, 
                   total_hours, approved_hours, approval_status, last_modified_by)
                  VALUES (:volunteer_id, :event_id, :event_name, :period_start_date, :period_end_date,
                          :total_hours, :approved_hours, :approval_status, :last_modified_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':volunteer_id', $this->volunteer_id);
        $stmt->bindParam(':event_id', $this->event_id);
        $stmt->bindParam(':event_name', $this->event_name);
        $stmt->bindParam(':period_start_date', $this->period_start_date);
        $stmt->bindParam(':period_end_date', $this->period_end_date);
        $stmt->bindParam(':total_hours', $this->total_hours);
        $stmt->bindParam(':approved_hours', $this->approved_hours);
        $stmt->bindParam(':approval_status', $this->approval_status);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all timesheets
    public function getAll() {
        $query = "SELECT t.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name,
                         CONCAT(u.first_name, ' ', u.last_name) as approved_by_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN volunteers v ON t.volunteer_id = v.id
                  LEFT JOIN users u ON t.approved_by_admin_id = u.id
                  ORDER BY t.created_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get timesheets by volunteer
    public function getByVolunteer($volunteer_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE volunteer_id = :volunteer_id 
                  ORDER BY period_start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get pending timesheets
    public function getPending() {
        $query = "SELECT t.*, 
                         CONCAT(v.first_name, ' ', v.last_name) as volunteer_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN volunteers v ON t.volunteer_id = v.id
                  WHERE t.approval_status = 'PENDING'
                  ORDER BY t.created_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get timesheet by ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update timesheet
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET period_start_date = :period_start_date, 
                      period_end_date = :period_end_date,
                      total_hours = :total_hours,
                      approval_status = :approval_status,
                      last_modified_by = :last_modified_by
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':period_start_date', $this->period_start_date);
        $stmt->bindParam(':period_end_date', $this->period_end_date);
        $stmt->bindParam(':total_hours', $this->total_hours);
        $stmt->bindParam(':approval_status', $this->approval_status);
        $stmt->bindParam(':last_modified_by', $this->last_modified_by);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Approve timesheet
    public function approve($id, $approved_hours, $admin_id) {
        $query = "UPDATE " . $this->table_name . "
                  SET approval_status = 'APPROVED',
                      approved_hours = :approved_hours,
                      approved_by_admin_id = :admin_id,
                      approval_date = NOW()
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':approved_hours', $approved_hours);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Reject timesheet
    public function reject($id, $admin_id, $reason) {
        $query = "UPDATE " . $this->table_name . "
                  SET approval_status = 'REJECTED',
                      approved_by_admin_id = :admin_id,
                      approval_date = NOW(),
                      rejection_reason = :reason
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Delete timesheet
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Get total hours for a volunteer
    public function getTotalHoursByVolunteer($volunteer_id) {
        $query = "SELECT COALESCE(SUM(approved_hours), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE volunteer_id = :volunteer_id AND approval_status = 'APPROVED'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':volunteer_id', $volunteer_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Get total hours across all volunteers
    public function getTotalHoursAll() {
        $query = "SELECT COALESCE(SUM(approved_hours), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE approval_status = 'APPROVED'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>
