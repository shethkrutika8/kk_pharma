<<<<<<< HEAD
<?php
require_once 'kk_pharma/after_login/config.php';
require_once 'kk_pharma/validation.php';

// Check if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: user_dashboard/index.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate login data
    $validation_result = validateLogin($_POST);
    if (!$validation_result['valid']) {
        $errors = $validation_result['errors'];
    } else {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // Check if user exists in register table
        $stmt = $conn->prepare("SELECT id, name, email, password FROM register WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            
            header("Location: user_dashboard/index.php");
            exit();
        } else {
            $errors['login'] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KK Pharma</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="css/dashboard.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 209, 178, 0.25);
        }
        
        .btn-login {
            background: var(--accent-color);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #00b894;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 209, 178, 0.3);
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo">
                <i class="bi bi-hospital"></i> KK Pharma
            </div>
            <h4 class="mb-0">Welcome Back</h4>
            <p class="mb-0 opacity-75">Login to your account</p>
        </div>
        
        <div class="login-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           data-validate="email"
                           data-field-name="Email Address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                           data-validate="password"
                           data-field-name="Password"
                           required>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>
            
            <div class="text-center mt-3">
                <p class="mb-0">
                    Don't have an account? 
                    <a href="register.php" class="text-decoration-none">Register here</a>
                </p>
                <p class="mt-2">
                    <a href="forgot-password.php" class="text-decoration-none">
                        <i class="bi bi-question-circle me-1"></i>Forgot Password?
                    </a>
                </p>
            </div>
            
            <hr class="my-3">
            
            <div class="text-center">
                <p class="mb-2">Are you an admin?</p>
                <a href="kk_pharma/after_login/login.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-shield-lock me-1"></i>Admin Login
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Validation JS -->
    <script src="js/validation.js"></script>
</body>
</html>
=======
<?php
session_start();
include_once 'Database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_id = $_POST['email'] ?? '';
    $password = $_POST['pass'] ?? '';

    if (empty($email_or_id) || empty($password)) {
        $login_error = "Please enter both Email/User ID and Password.";
    } else {
        // 1. Check if it's an admin login
        $stmt_admin = $con->prepare("SELECT id, password FROM admins WHERE user_id = ?");
        $stmt_admin->bind_param("s", $email_or_id);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();

        if ($result_admin->num_rows > 0) {
            $admin = $result_admin->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // Admin login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $email_or_id;
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $login_error = "Invalid Admin ID or Password.";
            }
        } else {
            // 2. Check if it's a regular user login
            $stmt_user = $con->prepare("SELECT id, password, first_name, last_name, verified FROM register WHERE email = ?");
            $stmt_user->bind_param("s", $email_or_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($result_user->num_rows > 0) {
                $user = $result_user->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    if ($user['verified'] == 1) {
                        // User login successful
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_email'] = $email_or_id;
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['login_success_msg'] = "Welcome back, " . htmlspecialchars($user['first_name']) . "!";
                        header("Location: user_dashboard.php");
                        exit();
                    } else {
                        $login_error = "Please verify your email address to login.";
                    }
                } else {
                    $login_error = "Invalid Email or Password.";
                }
            } else {
                $login_error = "No user found with that Email or User ID.";
            }

            $stmt_user->close();
        }
        $stmt_admin->close();
    }
}

$success_msg = "";
if (isset($_GET['msg']) && $_GET['msg'] == 'verified') {
    $success_msg = "Email verified successfully! You can now login.";
} elseif (isset($_COOKIE['success'])) {
    $success_msg = $_COOKIE['success'];
    // Clear the cookie so the message only shows once
    setcookie("success", "", time() - 3600, "/");
}

include_once 'header.php';
?>
<style>
.login-page {
    background: linear-gradient(to right, #2c3e50, #597ca7);
    min-height: calc(100vh - 70px);
    display: flex;
}

.image-section {
    width: 55%;
    background: url('https://images.unsplash.com/photo-1580281657527-47f249e8f4df') center/cover no-repeat;
}

.image-overlay {
    background: rgba(44, 62, 80, 0.75);
    height: 100%;
    padding: 60px;
    color: white;
}

.form-section {
    width: 45%;
    background: #ecf0f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-box {
    width: 380px;
    background: #fff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.login-box h2 {
    text-align: center;
    color: #2c3e50;
}

.error {
    color: red;
    font-size: 13px;
}

@media(max-width: 992px) {
    .image-section {
        display: none;
    }
    .form-section {
        width: 100%;
    }
}
</style>

<div class="login-page">

    <div class="image-section">
        <div class="image-overlay">
            <h1><?php echo htmlspecialchars(get_content('auth_overlay_title', 'KK Pharma')); ?></h1>
            <p><?php echo get_content('auth_overlay_desc', 'Trusted Pharmaceutical Solutions<br>Quality • Safety • Innovation'); ?></p>
        </div>
    </div>

    <div class="form-section">
        <div class="login-box">

            <h2><?php echo htmlspecialchars(get_content('login_box_title', 'Login')); ?></h2>
            <p class="text-center mb-4"><?php echo htmlspecialchars(get_content('login_box_desc', 'Access your KK Pharma account')); ?></p>

            <form id="loginForm" action="login.php" method="post">
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger" role="alert" style="font-size: 14px; padding: 10px;">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success" role="alert" style="font-size: 14px; padding: 10px;">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Email / User ID</label>
                    <input type="text" class="form-control" id="email" name="email">
                    <span id="email_error" class="error"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="pass" name="pass">
                    <span id="pass_error" class="error"></span>
                </div>

                <button type="submit" class="btn btn-dark w-100">Login</button>

            </form>

            <div class="mt-3 text-center">
                <a href="forgot_password.php">Forgot Password?</a><br>
                Don’t have an account? <a href="register.php">Register</a>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    $("#loginForm").on("submit", function(e) {

        let isValid = true;
        $(".error").text("");

        let email = $("#email").val().trim();
        let password = $("#pass").val().trim();
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email === "") {
            $("#email_error").text("Email or User ID is required");
            isValid = false;
        }

        if (password === "") {
            $("#pass_error").text("Password is required");
            isValid = false;
        }

        if (!isValid) e.preventDefault();
    });

});
</script>

</body>
</html>

>>>>>>> c6775067838e23982daa6c2b4f2a0e39c1aeb335
