-- Create Database
CREATE DATABASE IF NOT EXISTS vms_database;
USE vms_database;

-- Users Table (Admins and User Accounts)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('SUPER_ADMIN', 'ADMIN', 'COORDINATOR', 'VOLUNTEER') DEFAULT 'VOLUNTEER',
    account_status ENUM('ACTIVE', 'SUSPENDED', 'LOCKED', 'INACTIVE') DEFAULT 'ACTIVE',
    failed_login_attempts INT DEFAULT 0,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    last_modified_by INT NULL,
    last_modified_date TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Volunteers Table
CREATE TABLE volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    skills TEXT,
    availability VARCHAR(255),
    emergency_contact VARCHAR(255),
    total_hours_worked DECIMAL(10,2) DEFAULT 0.00,
    badges_earned INT DEFAULT 0,
    status ENUM('ACTIVE', 'INACTIVE', 'ON_LEAVE', 'TERMINATED') DEFAULT 'ACTIVE',
    join_date DATE NOT NULL,
    last_modified_by INT NULL,
    last_modified_date TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(255),
    event_type ENUM('COMMUNITY_SERVICE', 'FUNDRAISING', 'EDUCATION', 'HEALTH', 'ENVIRONMENT', 'SPORTS', 'ARTS') NOT NULL,
    target_audience ENUM('ALL', 'VOLUNTEERS', 'COORDINATORS', 'ADMINS') DEFAULT 'ALL',
    capacity INT NOT NULL,
    current_registrations INT DEFAULT 0,
    status ENUM('DRAFT', 'PUBLISHED', 'COMPLETED', 'CANCELLED') DEFAULT 'DRAFT',
    organizer_id INT,
    last_modified_by INT NULL,
    last_modified_date TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attendance Table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NOT NULL,
    check_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hours_worked DECIMAL(5,2) NOT NULL,
    status ENUM('PRESENT', 'ABSENT', 'LATE', 'EXCUSED') DEFAULT 'PRESENT',
    feedback TEXT,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Timesheets Table
CREATE TABLE timesheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    event_id INT NULL,
    event_name VARCHAR(200),
    period_start_date DATE NOT NULL,
    period_end_date DATE NOT NULL,
    total_hours DECIMAL(10,2) NOT NULL,
    approved_hours DECIMAL(10,2) DEFAULT 0.00,
    approval_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    approved_by_admin_id INT NULL,
    approval_date TIMESTAMP NULL,
    rejection_reason TEXT,
    last_modified_by INT NULL,
    last_modified_date TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by_admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Announcements Table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('LOW', 'MEDIUM', 'HIGH', 'URGENT') DEFAULT 'MEDIUM',
    target_audience ENUM('ALL', 'VOLUNTEERS', 'COORDINATORS', 'ADMINS') DEFAULT 'ALL',
    publish_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    last_modified_by INT NULL,
    last_modified_date TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Awards Table
CREATE TABLE awards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    badge_tier ENUM('BRONZE', 'SILVER', 'GOLD', 'PLATINUM') NOT NULL,
    date_awarded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason TEXT,
    awarded_by_admin_id INT NOT NULL,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    FOREIGN KEY (awarded_by_admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: Run setup.php after importing this schema to create the default admin account
-- with bcrypt password hashing for proper security
-- Default credentials will be: username: admin, password: admin123