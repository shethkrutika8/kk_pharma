<?php
session_start();
include_once 'Database/db_config.php';

// Redirect if there's no reset email session
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';
$success = '';
$otp_verified = $_SESSION['otp_verified'] ?? false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Step 1: Verify OTP
    if (isset($_POST['verify_otp'])) {
        $otp = trim($_POST['otp'] ?? '');
        
        if (empty($otp)) {
            $error = "Please enter the OTP.";
        } else {
            $stmt = $con->prepare("SELECT reset_otp, reset_otp_expiry FROM register WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if ($user['reset_otp'] === $otp) {
                    $expiry_time = strtotime($user['reset_otp_expiry']);
                    if (time() <= $expiry_time) {
                        // OTP is correct and not expired
                        $_SESSION['otp_verified'] = true;
                        $otp_verified = true;
                        $success = "OTP verified. You can now reset your password.";
                    } else {
                        $error = "OTP has expired. Please request a new one.";
                    }
                } else {
                    $error = "Invalid OTP. Please try again.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        }
    }
    
    // Step 2: Reset Password
    if (isset($_POST['reset_password'])) {
        $new_pass = $_POST['new_pass'] ?? '';
        $confirm_pass = $_POST['confirm_pass'] ?? '';

        if (!$otp_verified) {
            $error = "Please verify your OTP first.";
        } elseif (empty($new_pass) || empty($confirm_pass)) {
            $error = "Please fill in all password fields.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_pass) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

            // Update user record and clear OTP
            $update_stmt = $con->prepare("UPDATE register SET password = ?, reset_otp = NULL, reset_otp_expiry = NULL WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);
            
            if ($update_stmt->execute()) {
                // Clear session variables related to reset
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                
                // Set success message cookie
                setcookie("success", "Password has been successfully changed! You can login now.", time() + 300, "/");

                header("Location: login.php");
                exit();
            } else {
                $error = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        }
    }
}

// Check if user requested to resend OTP
if (isset($_GET['resend']) && $_GET['resend'] == 1) {
    // Generate new OTP
    include_once 'mailer.php';

    $stmt = $con->prepare("SELECT first_name FROM register WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        $update_stmt = $con->prepare("UPDATE register SET reset_otp = ?, reset_otp_expiry = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $otp, $expiry, $email);
        
        if ($update_stmt->execute()) {
            $subject = "Resend OTP - Password Reset - KK Pharma";
            $body = "<h2>Password Reset</h2>
                     <p>Dear " . htmlspecialchars($user['first_name']) . ",</p>
                     <p>Your new OTP for resetting your password is: <strong>$otp</strong></p>
                     <p>This OTP is valid for 15 minutes.</p>";
            
            $mail_status = sendEmail($email, $subject, $body);

            if ($mail_status === true) {
                $success = "A new OTP has been sent to your email.";
            } else {
                $error = "Failed to resend email. " . $mail_status;
            }
        }
        $update_stmt->close();
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP | KK Pharma</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
.login-page {
    background: linear-gradient(to right, #2c3e50, #597ca7);
    min-height: 100vh;
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

.error-text {
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
</head>

<body>

<div class="login-page">

    <div class="image-section">
        <div class="image-overlay">
            <h1>KK Pharma</h1>
            <p>Trusted Pharmaceutical Solutions<br>
               Quality • Safety • Innovation</p>
        </div>
    </div>

    <div class="form-section">
        <div class="login-box">

            <h2>Password Reset</h2>
            <p class="text-center mb-4 text-muted">
                <?php echo $otp_verified ? "Enter your new password below." : "Enter the OTP sent to: <br><strong>" . htmlspecialchars($email) . "</strong>"; ?>
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert" style="font-size: 14px; padding: 10px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert" style="font-size: 14px; padding: 10px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!$otp_verified): ?>
            <!-- Step 1: OTP Verification Form -->
            <form id="otpForm" action="verify_otp.php" method="post">
                <div class="mb-3">
                    <label class="form-label fw-bold" style="color: #2c3e50;">Enter OTP</label>
                    <input type="text" class="form-control" id="otp" name="otp" placeholder="6-digit OTP" maxlength="6" required>
                    <span id="otp_error" class="error-text"></span>
                </div>

                <input type="hidden" name="verify_otp" value="1">
                <button type="submit" class="btn btn-dark w-100 py-2 mt-2">Verify OTP</button>
            </form>

            <div class="mt-3 text-center">
                <a href="verify_otp.php?resend=1" class="text-decoration-none" style="color: #597ca7;">Resend OTP</a><br>
                <a href="login.php" class="text-decoration-none fw-bold mt-2 d-inline-block" style="color: #2c3e50;">Cancel</a>
            </div>

            <script>
            $(document).ready(function() {
                $("#otpForm").on("submit", function(e) {
                    let otp = $("#otp").val().trim();
                    if (otp === "" || otp.length < 6) {
                        e.preventDefault();
                        $("#otp_error").text("Please enter a valid 6-digit OTP.");
                    }
                });
            });
            </script>

            <?php else: ?>
            <!-- Step 2: New Password Form -->
            <form id="resetForm" action="verify_otp.php" method="post">
                <div class="mb-3">
                    <label class="form-label fw-bold" style="color: #2c3e50;">New Password</label>
                    <input type="password" class="form-control" id="new_pass" name="new_pass" placeholder="Enter new password" required>
                    <span id="new_pass_error" class="error-text"></span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold" style="color: #2c3e50;">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" placeholder="Confirm new password" required>
                    <span id="confirm_pass_error" class="error-text"></span>
                </div>

                <input type="hidden" name="reset_password" value="1">
                <button type="submit" class="btn btn-success w-100 py-2 mt-2">Change Password</button>
            </form>

            <div class="mt-3 text-center">
                <a href="login.php" class="text-decoration-none fw-bold" style="color: #2c3e50;">Cancel</a>
            </div>

            <script>
            $(document).ready(function() {
                $("#resetForm").on("submit", function(e) {
                    let isValid = true;
                    $(".error-text").text("");
                    
                    let newPass = $("#new_pass").val().trim();
                    let confirmPass = $("#confirm_pass").val().trim();
                    
                    if (newPass.length < 6) {
                        $("#new_pass_error").text("Password must be at least 6 characters.");
                        isValid = false;
                    }
                    
                    if (newPass !== confirmPass) {
                        $("#confirm_pass_error").text("Passwords do not match.");
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            });
            </script>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
