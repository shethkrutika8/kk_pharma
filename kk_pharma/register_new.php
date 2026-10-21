<?php
ob_start();

// Session must be started before header.php to check auth
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

include_once 'Database/login_db_config.php';

// Redirect already-logged-in users
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: ../user_dashboard/");
    exit();
}
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/Admin_Dashboard.php");
    exit();
}

$success = "";
$errors = [];

$inputs = [
    'username' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    foreach ($inputs as $key => $val) {
        if (isset($_POST[$key])) {
            $inputs[$key] = htmlspecialchars(trim($_POST[$key]));
        }
    }

    // Validation
    if (empty($inputs['username'])) {
        $errors['username'] = "Username is required.";
    } elseif (strlen($inputs['username']) < 3) {
        $errors['username'] = "Username must be at least 3 characters.";
    }

    if (empty($inputs['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if email already exists
        $existing_user = get_user_by_email($inputs['email']);
        if ($existing_user) {
            $errors['email'] = "Email already registered.";
        }
    }

    if (empty($inputs['password'])) {
        $errors['password'] = "Password required.";
    } elseif (strlen($inputs['password']) < 6) {
        $errors['password'] = "Password must be minimum 6 characters.";
    }

    if (empty($inputs['confirm_password'])) {
        $errors['confirm_password'] = "Please confirm password.";
    } elseif ($inputs['password'] !== $inputs['confirm_password']) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Create new user in login database
        $user_id = create_login_user($inputs['username'], $inputs['email'], $inputs['password']);
        
        if ($user_id) {
            $success = "Registration successful! You can now login.";
            
            // Log successful registration
            error_log("New user registered: " . $inputs['email']);
        } else {
            $errors['general'] = "Registration failed. Please try again.";
        }
    }
}

include_once 'header.php';
?>
<style>
    :root {
        --primary: #1f2f3f;
        --secondary: #718497;
        --accent: #00d1b2;
    }

    body {
        background: linear-gradient(120deg, var(--primary), var(--secondary));
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }

    .register-section {
        padding: 80px 0;
    }

    .register-wrapper {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    }

    .register-info {
        background: var(--primary);
        color: white;
        padding: 50px;
        height: 100%;
    }

    .register-info h2 {
        font-weight: 700;
        margin-bottom: 20px;
    }

    .register-info ul {
        padding-left: 18px;
        margin-top: 25px;
    }

    .register-info li {
        margin-bottom: 10px;
    }

    .register-form {
        padding: 50px;
    }

    .register-form h3 {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: var(--primary);
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        height: 45px;
    }

    .text-danger.small {
        color: #721c24 !important;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 5px 10px;
        border-radius: 4px;
        margin-top: 5px;
        display: block;
        font-weight: 500;
    }

    .is-invalid {
        border: 1px solid #dc3545 !important;
    }

    .btn-register:hover {
        background: #16222e;
        transform: translateY(-2px);
    }

    .login-link {
        margin-top: 15px;
        text-align: center;
    }

    .login-link a {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
        text-align: center;
        font-weight: 600;
    }
</style>

<section class="register-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="register-wrapper">
                    <div class="row g-0">
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="register-info">
                                <h2><?php echo htmlspecialchars(get_content('auth_overlay_title', 'KK Pharma')); ?></h2>
                                <p><?php echo get_content('auth_overlay_desc', 'Trusted Pharmaceutical Solutions<br>Quality • Safety • Innovation'); ?></p>
                                <ul>
                                    <li>✔ WHO-GMP Certified Manufacturing</li>
                                    <li>✔ Global Healthcare Standards</li>
                                    <li>✔ Research & Development Focused</li>
                                    <li>✔ Career Growth Opportunities</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="register-form">
                                <h3><?php echo htmlspecialchars(get_content('register_box_title', 'Create Account')); ?></h3>

                                <?php if (!empty($success)): ?>
                                    <div class="alert-success">
                                        <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="post" id="regform" action="register_new.php" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" name="username" value="<?php echo $inputs['username']; ?>">
                                            <?php if (isset($errors['username'])): ?>
                                                <span class="text-danger small"><?php echo $errors['username']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" name="email" value="<?php echo $inputs['email']; ?>">
                                            <?php if (isset($errors['email'])): ?>
                                                <span class="text-danger small"><?php echo $errors['email']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" name="password" id="pass">
                                            <?php if (isset($errors['password'])): ?>
                                                <span class="text-danger small"><?php echo $errors['password']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" name="confirm_password" id="cpass">
                                            <?php if (isset($errors['confirm_password'])): ?>
                                                <span class="text-danger small"><?php echo $errors['confirm_password']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-dark w-100 btn-register">Register Now</button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="login-link">
                                    Already have an account? <a href="login_new.php">Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#regform").on("submit", function(e) {
        let isValid = true;
        $(".text-danger.small").text("");
        $(".is-invalid").removeClass("is-invalid");
        
        let username = $("#username").val().trim();
        let email = $("#email").val().trim();
        let password = $("#pass").val().trim();
        let confirmPass = $("#cpass").val().trim();
        
        if (username === "") {
            $("#username").addClass("is-invalid");
            $("#username").next(".text-danger.small").text("Username is required");
            isValid = false;
        }
        
        if (email === "") {
            $("#email").addClass("is-invalid");
            $("#email").next(".text-danger.small").text("Email is required");
            isValid = false;
        } else if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            $("#email").addClass("is-invalid");
            $("#email").next(".text-danger.small").text("Invalid email format");
            isValid = false;
        }
        
        if (password === "") {
            $("#pass").addClass("is-invalid");
            $("#pass").next(".text-danger.small").text("Password is required");
            isValid = false;
        } else if (password.length < 6) {
            $("#pass").addClass("is-invalid");
            $("#pass").next(".text-danger.small").text("Password must be minimum 6 characters");
            isValid = false;
        }
        
        if (confirmPass === "") {
            $("#cpass").addClass("is-invalid");
            $("#cpass").next(".text-danger.small").text("Please confirm password");
            isValid = false;
        } else if (password !== confirmPass) {
            $("#cpass").addClass("is-invalid");
            $("#cpass").next(".text-danger.small").text("Passwords do not match");
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

<?php include_once 'footer.php'; ?>
