<?php
conn = $db;
}

// Find user by username or email
public function findByUsernameOrEmail($identifier) {
    $query = "SELECT * FROM " . $this->table_name . " 
              WHERE username = :identifier OR email = :identifier 
              LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':identifier', $identifier);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Create new user
public function create() {
    $query = "INSERT INTO " . $this->table_name . "
              (username, email, password_hash, first_name, last_name, phone, address, role, account_status)
              VALUES (:username, :email, :password_hash, :first_name, :last_name, :phone, :address, :role, :account_status)";
    
    $stmt = $this->conn->prepare($query);
    
    $stmt->bindParam(':username', $this->username);
    $stmt->bindParam(':email', $this->email);
    $stmt->bindParam(':password_hash', $this->password_hash);
    $stmt->bindParam(':first_name', $this->first_name);
    $stmt->bindParam(':last_name', $this->last_name);
    $stmt->bindParam(':phone', $this->phone);
    $stmt->bindParam(':address', $this->address);
    $stmt->bindParam(':role', $this->role);
    $stmt->bindParam(':account_status', $this->account_status);
    
    if ($stmt->execute()) {
        return $this->conn->lastInsertId();
    }
    return false;
}

// Update last login
public function updateLastLogin($user_id) {
    $query = "UPDATE " . $this->table_name . " 
              SET last_login = NOW(), failed_login_attempts = 0 
              WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $user_id);
    return $stmt->execute();
}

// Increment failed login attempts
public function incrementFailedAttempts($user_id) {
    $query = "UPDATE " . $this->table_name . " 
              SET failed_login_attempts = failed_login_attempts + 1 
              WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $user_id);
    return $stmt->execute();
}

// Lock account
public function lockAccount($user_id) {
    $query = "UPDATE " . $this->table_name . " 
              SET account_status = 'LOCKED' 
              WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $user_id);
    return $stmt->execute();
}

// Update password with bcrypt hash
public function updatePassword($user_id, $new_password) {
    $query = "UPDATE " . $this->table_name . " 
              SET password_hash = :password_hash 
              WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt->bindParam(':password_hash', $hashed_password);
    $stmt->bindParam(':id', $user_id);
    return $stmt->execute();
}

// Get all users
public function getAll() {
    $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_date DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Delete attendance
public function delete() {
    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $this->id);
    return $stmt->execute();
}

// Get attendance by ID
public function findById($id) {
    $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>