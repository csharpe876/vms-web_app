<?php
/**
 * Database Setup Script
 * Run this file once to create the default admin account
 */

require_once 'config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

echo "<h1>VMS Database Setup</h1>";

try {
    // Check if admin user already exists
    $query = "SELECT id FROM users WHERE username = 'admin' LIMIT 1";
    $stmt = $db->query($query);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Admin user already exists. Skipping creation.</p>";
    } else {
        // Create default admin user
        $query = "INSERT INTO users 
                  (username, email, password_hash, first_name, last_name, role, account_status) 
                  VALUES (:username, :email, :password_hash, :first_name, :last_name, :role, :account_status)";
        
        $stmt = $db->prepare($query);
        
        $username = 'admin';
        $email = 'admin@vms.com';
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT); // bcrypt
        $first_name = 'System';
        $last_name = 'Administrator';
        $role = 'SUPER_ADMIN';
        $account_status = 'ACTIVE';
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':account_status', $account_status);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Default admin user created successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Email:</strong> admin@vms.com</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "<p><strong>Role:</strong> SUPER_ADMIN</p>";
            echo "<p style='color: red;'><strong>⚠️ IMPORTANT:</strong> Please change the admin password after first login!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin user.</p>";
        }
    }
    
    // Create a sample volunteer (optional)
    $query = "SELECT id FROM users WHERE username = 'volunteer1' LIMIT 1";
    $stmt = $db->query($query);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Sample volunteer already exists. Skipping creation.</p>";
    } else {
        // Create sample volunteer user
        $query = "INSERT INTO users 
                  (username, email, password_hash, first_name, last_name, role, account_status) 
                  VALUES (:username, :email, :password_hash, :first_name, :last_name, :role, :account_status)";
        
        $stmt = $db->prepare($query);
        
        $username = 'volunteer1';
        $email = 'volunteer1@example.com';
        $password_hash = password_hash('volunteer123', PASSWORD_DEFAULT);
        $first_name = 'John';
        $last_name = 'Doe';
        $role = 'VOLUNTEER';
        $account_status = 'ACTIVE';
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':account_status', $account_status);
        
        if ($stmt->execute()) {
            $user_id = $db->lastInsertId();
            
            // Create volunteer profile
            $query = "INSERT INTO volunteers 
                      (user_id, first_name, last_name, email, phone, skills, status, join_date) 
                      VALUES (:user_id, :first_name, :last_name, :email, :phone, :skills, :status, :join_date)";
            
            $stmt = $db->prepare($query);
            
            $phone = '555-0123';
            $skills = 'Event Planning, Communication';
            $status = 'ACTIVE';
            $join_date = date('Y-m-d');
            
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':skills', $skills);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':join_date', $join_date);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Sample volunteer created successfully!</p>";
                echo "<p><strong>Username:</strong> volunteer1</p>";
                echo "<p><strong>Password:</strong> volunteer123</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h2>✅ Setup Complete!</h2>";
    echo "<p><a href='index.php' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 20px;'>Go to Login Page</a></p>";
    echo "<p style='margin-top: 20px; color: #999; font-size: 12px;'>You can delete this setup.php file after successful setup for security.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure the database schema has been imported from sql/schema.sql</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #f5f7fa;
}

h1 {
    color: #333;
    border-bottom: 3px solid #667eea;
    padding-bottom: 10px;
}

h2 {
    color: #667eea;
}

p {
    line-height: 1.6;
    margin: 10px 0;
}
</style>
