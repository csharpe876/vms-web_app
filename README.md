# Volunteer Management System (VMS) - Web Application

A comprehensive web-based volunteer management system built with **PHP**, **MySQL/MariaDB**, **HTML**, **CSS**, and **JavaScript** with **AJAX** for dynamic interactions.

## 🌟 Features

### Authentication & Security
- **Secure Login System** with bcrypt password hashing (`password_hash()` and `password_verify()`)
- Login with **username OR email**
- Role-based access control (SUPER_ADMIN, ADMIN, COORDINATOR, VOLUNTEER)
- Account lockout after 5 failed login attempts
- Session management
- Password rehashing for security upgrades

### Core Functionality

#### Dashboard
- Real-time statistics with interactive cards
- Active volunteers count
- Upcoming events count
- Total hours (personal for volunteers, system-wide for admins)
- Badges earned (personal for volunteers, system-wide for admins)
- Recent announcements with priority display
- Quick action buttons

#### Volunteer Management
- Full CRUD operations for volunteer profiles
- Track volunteer information (skills, availability, emergency contacts)
- Monitor total hours worked and badges earned
- Status management (ACTIVE, INACTIVE, ON_LEAVE, TERMINATED)
- Volunteer statistics and history

#### Event Management
- Create and manage volunteer events
- Event details (title, description, date, location, type)
- Capacity tracking and registration management
- Event status (DRAFT, PUBLISHED, COMPLETED, CANCELLED)
- Upcoming and past events views
- Target audience filtering

#### Attendance Tracking
- Direct hours entry system
- Record volunteer participation at events
- Automatic timesheet creation with event details
- Update event registration counts automatically
- Track attendance status (PRESENT, ABSENT, LATE, EXCUSED)
- Attendance history per volunteer

#### Timesheet Management
- Automatic timesheet creation from attendance
- Includes event ID and event name
- Approval workflow (PENDING, APPROVED, REJECTED)
- Admin approval/rejection capabilities
- Track approved hours and approval history
- Rejection reasons

#### Awards & Badges
- Issue recognition badges to volunteers
- Badge tiers (BRONZE, SILVER, GOLD, PLATINUM)
- Track awards by volunteer
- Automatic badge count updates
- Award history and reasons

#### Announcements
- Create and manage system announcements
- Priority levels (LOW, MEDIUM, HIGH, URGENT)
- Target audience (ALL, VOLUNTEERS, COORDINATORS, ADMINS)
- Expiry date management
- Active/inactive status
- Priority-based display and styling

## 🏗️ Architecture

### Project Structure
```
vms-webapp/
├── assets/
│   ├── css/
│   │   ├── login.css          # Login page styles
│   │   └── style.css          # Main application styles
│   └── js/
│       ├── announcements.js   # Announcements functionality
│       ├── attendance.js      # Attendance functionality
│       ├── awards.js          # Awards functionality
│       ├── events.js          # Events functionality
│       ├── main.js            # Common utilities
│       ├── timesheets.js      # Timesheets functionality
│       └── volunteers.js      # Volunteers functionality
├── config/
│   └── database.php           # Database connection
├── controllers/
│   ├── AnnouncementController.php
│   ├── AttendanceController.php
│   ├── AuthController.php
│   ├── AwardController.php
│   ├── EventController.php
│   ├── TimesheetController.php
│   └── VolunteerController.php
├── includes/
│   ├── auth.php               # Authentication check
│   ├── footer.php             # Common footer
│   └── header.php             # Common header with navigation
├── models/
│   ├── Announcement.php
│   ├── Attendance.php
│   ├── Award.php
│   ├── Event.php
│   ├── Timesheet.php
│   ├── User.php
│   └── Volunteer.php
├── sql/
│   └── schema.sql             # Database schema
├── index.php                  # Login page
├── dashboard.php              # Main dashboard
├── volunteers.php             # Volunteers management
├── events.php                 # Events management
├── attendance.php             # Attendance tracking
├── timesheets.php            # Timesheet management
├── awards.php                 # Awards management
├── announcements.php          # Announcements
└── logout.php                 # Logout handler
```

### Design Pattern: MVC (Model-View-Controller)

#### Models (`models/`)
- Pure data classes representing database entities
- CRUD operations using PDO prepared statements
- Business logic for data manipulation
- **Password Security**: Uses `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)
- **Password Verification**: Uses `password_verify()` for MariaDB compatibility

#### Controllers (`controllers/`)
- Handle HTTP requests (POST/GET)
- Process form data and call model methods
- Return JSON responses for AJAX calls
- Session management
- Role-based access control

#### Views (PHP pages)
- Display HTML content
- Include header, footer, and authentication
- Use AJAX for dynamic updates
- Client-side form validation
- Responsive design

## 🔐 Security Features

### Password Hashing
```php
// Registration - Create password hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Login - Verify password
if (password_verify($password, $stored_hash)) {
    // Password correct
}

// Automatic rehashing for security upgrades
if (password_needs_rehash($stored_hash, PASSWORD_DEFAULT)) {
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
}
```

### Features:
- **Bcrypt hashing** with `PASSWORD_DEFAULT`
- **Automatic rehashing** when algorithm improves
- **MariaDB compatible** - works with MariaDB password functions
- **Failed login tracking** - locks account after 5 attempts
- **Session-based authentication**
- **SQL injection protection** with PDO prepared statements
- **XSS protection** with `htmlspecialchars()`

## 📊 Database Schema

### Tables
1. **users** - User accounts with authentication
2. **volunteers** - Volunteer profiles and information
3. **events** - Volunteer events and activities
4. **attendance** - Attendance records at events
5. **timesheets** - Hour tracking and approvals
6. **awards** - Recognition badges
7. **announcements** - System announcements

All tables include audit fields:
- `created_date` / `last_modified_date`
- `created_by` / `last_modified_by`

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- XAMPP, WAMP, or LAMP stack

### Installation Steps

1. **Clone the repository**
```bash
git clone https://github.com/csharpe876/vms-web_app.git
cd vms-web_app
```

2. **Configure Database**
   - Edit `config/database.php` with your database credentials:
```php
private $host = "localhost";
private $db_name = "vms_database";
private $username = "root";
private $password = "";
```

3. **Create Database**
   - Import the database schema:
```bash
mysql -u root -p < sql/schema.sql
```
   - Or execute the SQL file in phpMyAdmin

4. **Place in Web Root**
   - Copy the project to your web server directory:
     - XAMPP: `C:\xampp\htdocs\vms-webapp`
     - WAMP: `C:\wamp64\www\vms-webapp`
     - Linux: `/var/www/html/vms-webapp`

5. **Set Permissions** (Linux/Mac)
```bash
chmod -R 755 vms-webapp/
```

6. **Access Application**
   - Open browser: `http://localhost/vms-webapp`

### Default Admin Account
The system includes a default admin account. Update the password after first login:
- **Username**: `admin`
- **Email**: `admin@vms.com`
- **Password**: `admin123`
- **Role**: SUPER_ADMIN

## 🎯 Usage

### For Administrators
1. **Login** with admin credentials
2. **Create Events** from dashboard or events page
3. **Manage Volunteers** - view, edit, delete volunteer records
4. **Review Timesheets** - approve or reject volunteer hours
5. **Issue Awards** - recognize volunteer achievements
6. **Post Announcements** - communicate with volunteers

### For Volunteers
1. **Sign up** for an account (automatically assigned VOLUNTEER role)
2. **Login** with your credentials
3. **View Events** - see upcoming volunteer opportunities
4. **Record Attendance** - log your participation and hours
5. **Submit Timesheets** - track your volunteer hours
6. **View Badges** - see your earned recognition

## 🔧 Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **PDO** - Database abstraction layer
- **Session Management** - User authentication
- **Password Hashing** - bcrypt with `password_hash()`

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Responsive styling with flexbox/grid
- **JavaScript ES6+** - Client-side logic
- **AJAX (Fetch API)** - Asynchronous data loading

### Database
- **MySQL 5.7+** or **MariaDB 10.3+**
- **InnoDB engine** - ACID compliance
- **Foreign Keys** - Referential integrity
- **Prepared Statements** - SQL injection prevention

## 📱 Responsive Design
- Mobile-friendly interface
- Flexible grid layouts
- Touch-optimized buttons
- Responsive navigation menu

## 🎨 UI/UX Features
- Modern gradient design
- Smooth animations and transitions
- Priority-based color coding for announcements
- Interactive statistics cards
- Clean, intuitive navigation
- Loading states and feedback messages

## 🔄 AJAX Operations
All data operations use AJAX for seamless user experience:
- No page reloads for CRUD operations
- Real-time data updates
- JSON response handling
- Error handling and user feedback

## 🛡️ Role-Based Access Control

### Roles
1. **SUPER_ADMIN** - Full system access
2. **ADMIN** - Manage volunteers, events, approvals
3. **COORDINATOR** - Limited management capabilities
4. **VOLUNTEER** - Personal data and attendance only

### Permission Matrix
| Feature | VOLUNTEER | COORDINATOR | ADMIN | SUPER_ADMIN |
|---------|-----------|-------------|-------|-------------|
| View Dashboard | ✅ | ✅ | ✅ | ✅ |
| Record Attendance | ✅ | ✅ | ✅ | ✅ |
| Submit Timesheets | ✅ | ✅ | ✅ | ✅ |
| Create Events | ❌ | ❌ | ✅ | ✅ |
| Approve Timesheets | ❌ | ❌ | ✅ | ✅ |
| Manage Volunteers | ❌ | ❌ | ✅ | ✅ |
| Issue Awards | ❌ | ❌ | ✅ | ✅ |
| Post Announcements | ❌ | ❌ | ✅ | ✅ |

## 📄 License
This project is open source and available under the MIT License.

## 👥 Contributing
Contributions, issues, and feature requests are welcome!

## 📧 Contact
For questions or support, please open an issue on GitHub.

---

**Built with ❤️ for volunteer organizations**