<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Volunteer Management System</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>🌟 Volunteer Management System</h1>
            <p class="subtitle">Sign in to manage your volunteer activities</p>
            
            <div id="message" class="message" style="display:none;"></div>

            <!-- Login Form -->
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div class="links">
                <a href="#" onclick="showSignupForm(); return false;">Don't have an account? Sign up</a>
            </div>
        </div>

        <!-- Signup Form (Hidden by default) -->
        <div class="login-box" id="signupBox" style="display:none;">
            <h1>📝 Create Account</h1>
            <p class="subtitle">Join our volunteer community</p>
            
            <div id="signupMessage" class="message" style="display:none;"></div>

            <form id="signupForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="signup_first_name">First Name *</label>
                        <input type="text" id="signup_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_last_name">Last Name *</label>
                        <input type="text" id="signup_last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="signup_username">Username *</label>
                    <input type="text" id="signup_username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="signup_email">Email *</label>
                    <input type="email" id="signup_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="signup_password">Password * (min 8 characters)</label>
                    <input type="password" id="signup_password" name="password" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="signup_phone">Phone</label>
                    <input type="tel" id="signup_phone" name="phone">
                </div>

                <div class="form-group">
                    <label for="signup_address">Address</label>
                    <textarea id="signup_address" name="address" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label for="signup_skills">Skills</label>
                    <textarea id="signup_skills" name="skills" rows="2" placeholder="e.g., Teaching, IT, Event Planning"></textarea>
                </div>

                <div class="form-group">
                    <label for="signup_availability">Availability</label>
                    <input type="text" id="signup_availability" name="availability" placeholder="e.g., Weekends, Evenings">
                </div>

                <div class="form-group">
                    <label for="signup_emergency_contact">Emergency Contact</label>
                    <input type="text" id="signup_emergency_contact" name="emergency_contact" placeholder="Name and Phone">
                </div>

                <button type="submit" class="btn-primary">Create Account</button>
                <button type="button" class="btn-secondary" onclick="showLoginForm()">Back to Login</button>
            </form>
        </div>
    </div>

    <script>
        // Show/Hide Forms
        function showSignupForm() {
            document.getElementById('loginForm').parentElement.style.display = 'none';
            document.getElementById('signupBox').style.display = 'block';
        }

        function showLoginForm() {
            document.getElementById('signupBox').style.display = 'none';
            document.querySelector('.login-box').style.display = 'block';
        }

        // Login Form
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'login');

            try {
                const response = await fetch('controllers/AuthController.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                showMessage('message', data.message, data.success);

                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                }
            } catch (error) {
                showMessage('message', 'An error occurred. Please try again.', false);
            }
        });

        // Signup Form
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'signup');

            try {
                const response = await fetch('controllers/AuthController.php?action=signup', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                showMessage('signupMessage', data.message, data.success);

                if (data.success) {
                    setTimeout(() => {
                        showLoginForm();
                        showMessage('message', 'Account created! Please log in.', true);
                    }, 2000);
                }
            } catch (error) {
                showMessage('signupMessage', 'An error occurred. Please try again.', false);
            }
        });

        // Show Message Helper
        function showMessage(elementId, message, isSuccess) {
            const messageEl = document.getElementById(elementId);
            messageEl.textContent = message;
            messageEl.className = 'message ' + (isSuccess ? 'success' : 'error');
            messageEl.style.display = 'block';
        }
    </script>
</body>
</html>