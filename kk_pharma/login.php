<?php
ob_start();

// Include config for proper setup
include_once 'Database/db_config.php';
require_once 'validation.php';

// Already-logged-in users should not see login page
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: ../user_dashboard/");
    exit();
}
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/Admin_Dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    $email_or_id = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['pass'] ?? '';

    // Validate inputs
    if (empty($email_or_id)) {
        $errors['email'] = "Email or User ID is required.";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if (empty($errors)) {
        // 1. Check if it's an admin login
        $stmt_admin = $con->prepare("SELECT id, password FROM admins WHERE user_id = ?");
        $stmt_admin->bind_param("s", $email_or_id);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();

        if ($result_admin->num_rows > 0) {
            $admin = $result_admin->fetch_assoc();
            // Support both plain text (legacy) and hashed passwords
            if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                // If password is plain text, hash it now
                if ($password === $admin['password'] && !password_verify($password, $admin['password'])) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $con->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $admin['id']);
                    $update_stmt->execute();
                }
                // Admin login successful
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $email_or_id;
                $_SESSION['admin_username'] = $email_or_id; // For compatibility
                ob_end_clean();
                header("Location: admin/Admin_Dashboard.php");
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
                // Support both plain text (legacy) and hashed passwords
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    // If password is plain text, hash it now
                    if ($password === $user['password'] && !password_verify($password, $user['password'])) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = $con->prepare("UPDATE register SET password = ? WHERE id = ?");
                        $update_stmt->bind_param("si", $hashed_password, $user['id']);
                        $update_stmt->execute();
                    }
                    if ($user['verified'] == 1) {
                        // User login successful
                        session_regenerate_id(true);
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_email'] = $email_or_id;
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['login_success_msg'] = "Welcome back, " . htmlspecialchars($user['first_name']) . "!";

                        header("Location: ../user_dashboard/");
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
    } else {
        $login_error = reset($errors);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KK Pharma</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* styles here */
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
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