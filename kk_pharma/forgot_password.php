<?php
include_once 'Database/db_config.php';
include_once 'header.php';
include_once 'mailer.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your registered email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Find user
        $stmt = $con->prepare("SELECT id, first_name FROM register WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate OTP
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes'));

            // Update DB
            $update_stmt = $con->prepare("UPDATE register SET reset_otp = ?, reset_otp_expiry = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $otp, $expiry, $email);
            
            if ($update_stmt->execute()) {
                // Send email
                $subject = "Password Reset OTP - KK Pharma";
                $body = "<h2>Password Reset</h2>
                         <p>Dear " . htmlspecialchars($user['first_name']) . ",</p>
                         <p>Your OTP for resetting your password is: <strong>$otp</strong></p>
                         <p>This OTP is valid for 15 minutes.</p>
                         <br>
                         <p>If you did not request a password reset, please ignore this email.</p>";
                
                $mail_status = sendEmail($email, $subject, $body);

                if ($mail_status === true) {
                    $_SESSION['reset_email'] = $email;
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $error = "Failed to send email. " . $mail_status;
                }
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error = "No user found with that email address.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | KK Pharma</title>

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

            <h2>Forgot Password</h2>
            <p class="text-center mb-4 text-muted">Enter your registered email and we'll send you an OTP to reset your password.</p>

            <form id="forgotForm" action="forgot_password.php" method="post">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert" style="font-size: 14px; padding: 10px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold" style="color: #2c3e50;">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    <span id="email_error" class="error-text"></span>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-dark w-100 py-2 mt-2">Send OTP</button>
            </form>

            <div class="mt-4 text-center">
                <a href="login.php" class="text-decoration-none fw-bold" style="color: #2c3e50;">← Back to Login</a>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#forgotForm").on("submit", function(e) {
        let email = $("#email").val().trim();
        let isValid = true;
        
        $("#email_error").text("");
        
        if (email === "") {
            $("#email_error").text("Email is required");
            isValid = false;
        }

        if (isValid) {
            // Show sending state
            let btn = $("#submitBtn");
            btn.prop("disabled", true);
            btn.text("Sending...");
        } else {
            e.preventDefault();
        }
    });
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
