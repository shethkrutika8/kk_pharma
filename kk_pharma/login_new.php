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

// Already-logged-in users should not see login page
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: ../user_dashboard/");
    exit();
}
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/Admin_Dashboard.php");
    exit();
}

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_username = trim($_POST['email'] ?? '');
    $password = trim($_POST['pass'] ?? '');

    if (empty($email_or_username) || empty($password)) {
        $login_error = "Please enter both Email/Username and Password.";
    } else {
        // Authenticate user using dedicated login database
        $user = authenticate_user($email_or_username, $password);
        
        if ($user) {
            session_regenerate_id(true);
            
            if ($user['user_type'] === 'admin') {
                // Admin login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];
                
                ob_end_clean();
                header("Location: admin/Admin_Dashboard.php");
                exit();
            } else {
                // User login successful
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['login_success_msg'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";
                
                // Debug: Check if session is set properly
                error_log("Login successful for: " . $email_or_username);
                error_log("Session user_logged_in: " . ($_SESSION['user_logged_in'] ? 'true' : 'false'));
                error_log("Session data: " . print_r($_SESSION, true));
                
                // Ensure no output before redirect
                ob_end_clean();
                header("Location: ../user_dashboard/");
                exit();
            }
        } else {
            $login_error = "Invalid Email/Username or Password.";
            error_log("Login failed for: " . $email_or_username);
        }
    }
}

$success_msg = "";
if (isset($_GET['msg']) && $_GET['msg'] == 'verified') {
    $success_msg = "Account created successfully! You can now login.";
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
        background: url("<?php echo get_image('auth', 'overlay_bg', 'https://images.unsplash.com/photo-1580281657527-47f249e8f4df'); ?>") center/cover no-repeat;
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

            <form id="loginForm" action="login_new.php" method="post">
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
                    <label class="form-label">Email / Username</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Enter email or username">
                    <span id="email_error" class="error"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="pass" name="pass" placeholder="Enter password">
                    <span id="pass_error" class="error"></span>
                </div>

                <button type="submit" class="btn btn-dark w-100">Login</button>

            </form>

            <div class="mt-3 text-center">
                <a href="forgot_password.php">Forgot Password?</a><br>
                Don't have an account? <a href="register.php">Register</a>
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

            if (email === "") {
                $("#email_error").text("Email or Username is required");
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
