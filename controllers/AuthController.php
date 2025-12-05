<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Volunteer.php';

class AuthController {
    private $db;
    private $user;
    private $volunteer;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->volunteer = new Volunteer($this->db);
    }

    // Handle login
    public function login($identifier, $password) {
        $userData = $this->user->findByUsernameOrEmail($identifier);

        if (!$userData) {
            return ['success' => false, 'message' => 'Invalid username/email or password'];
        }

        // Check if account is locked
        if ($userData['account_status'] === 'LOCKED') {
            return ['success' => false, 'message' => 'Account is locked. Contact administrator.'];
        }

        // Check if account is active
        if ($userData['account_status'] !== 'ACTIVE') {
            return ['success' => false, 'message' => 'Account is not active.'];
        }

        // Verify password using password_verify (bcrypt compatible)
        if (!password_verify($password, $userData['password_hash'])) {
            // Increment failed attempts
            $this->user->incrementFailedAttempts($userData['id']);
            
            // Lock account after 5 failed attempts
            if ($userData['failed_login_attempts'] + 1 >= 5) {
                $this->user->lockAccount($userData['id']);
                return ['success' => false, 'message' => 'Account locked due to too many failed attempts.'];
            }
            
            return ['success' => false, 'message' => 'Invalid username/email or password'];
        }
        
        // Check if password needs rehashing (for security upgrades)
        if (password_needs_rehash($userData['password_hash'], PASSWORD_DEFAULT)) {
            $this->user->updatePassword($userData['id'], $password);
        }

        // Successful login
        $this->user->updateLastLogin($userData['id']);
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['first_name'] = $userData['first_name'];
        $_SESSION['last_name'] = $userData['last_name'];
        $_SESSION['role'] = $userData['role'];

        // If user is a volunteer, get volunteer ID
        if ($userData['role'] === 'VOLUNTEER') {
            $volunteerData = $this->volunteer->findByUserId($userData['id']);
            if ($volunteerData) {
                $_SESSION['volunteer_id'] = $volunteerData['id'];
            }
        }

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'role' => $userData['role']
            ]
        ];
    }

    // Handle signup
    public function signup($data) {
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Check if username or email already exists
        $existing = $this->user->findByUsernameOrEmail($data['username']);
        if ($existing) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        $existing = $this->user->findByUsernameOrEmail($data['email']);
        if ($existing) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Create user account with bcrypt hashed password
        $this->user->username = $data['username'];
        $this->user->email = $data['email'];
        $this->user->password_hash = password_hash($data['password'], PASSWORD_DEFAULT); // bcrypt
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->phone = $data['phone'] ?? '';
        $this->user->address = $data['address'] ?? '';
        $this->user->role = 'VOLUNTEER'; // Default role
        $this->user->account_status = 'ACTIVE';

        $user_id = $this->user->create();

        if (!$user_id) {
            return ['success' => false, 'message' => 'Failed to create user account'];
        }

        // Create volunteer profile
        $this->volunteer->user_id = $user_id;
        $this->volunteer->first_name = $data['first_name'];
        $this->volunteer->last_name = $data['last_name'];
        $this->volunteer->email = $data['email'];
        $this->volunteer->phone = $data['phone'] ?? '';
        $this->volunteer->address = $data['address'] ?? '';
        $this->volunteer->skills = $data['skills'] ?? '';
        $this->volunteer->availability = $data['availability'] ?? '';
        $this->volunteer->emergency_contact = $data['emergency_contact'] ?? '';
        $this->volunteer->status = 'ACTIVE';
        $this->volunteer->join_date = date('Y-m-d');
        $this->volunteer->last_modified_by = $user_id;

        $volunteer_id = $this->volunteer->create();

        if (!$volunteer_id) {
            return ['success' => false, 'message' => 'Failed to create volunteer profile'];
        }

        return [
            'success' => true,
            'message' => 'Account created successfully. You can now log in.'
        ];
    }

    // Handle logout
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $result = $controller->login($_POST['identifier'], $_POST['password']);
                echo json_encode($result);
                break;
                
            case 'signup':
                $result = $controller->signup($_POST);
                echo json_encode($result);
                break;
                
            case 'logout':
                $result = $controller->logout();
                echo json_encode($result);
                break;
        }
    }
}
?>