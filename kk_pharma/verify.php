<?php
// Start session if needed for any session operations
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Include database connection
include_once 'Database/db_config.php';

// Handle verification logic before any HTML output
if (isset($_GET['vkey'])) {
    $email = mysqli_real_escape_string($con, urldecode($_GET['vkey']));
    
    // Debug: Log verification email
    error_log("Verification attempt with email: " . $email);
    
    // Check user by email address
    $stmt = $con->prepare("SELECT verified, vkey, user_friendly_code FROM register WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    error_log("Query result count: " . $result->num_rows);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['verified'] == 1) {
            $stmt->close();
            header("Location: login.php?msg=verified");
            exit();
        }
        
        // Update verification status
        $update = $con->prepare("UPDATE register SET verified = 1 WHERE email = ?");
        $update->bind_param("s", $email);
        
        if ($update->execute()) {
            $update->close();
            $stmt->close();
            header("Location: login.php?msg=verified");
            exit();
        } else {
            $verification_error = "Verification Failed";
            $verification_message = "Something went wrong during verification. Please try again later.";
        }
        $update->close();
    } else {
        $verification_error = "Invalid Account";
        $verification_message = "No account was found with this email address.";
        $show_entered_code = true;
    }
    $stmt->close();
} else {
    $verification_error = "Bad Request";
    $verification_message = "No email address was provided in URL.";
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
    }

    .verify-section {
        padding: 100px 0;
        margin-top: 50px;
        margin-bottom: 50px;
    }

    .verify-wrapper {
        background: #fff;
        border-radius: 18px;
        padding: 50px;
        text-align: center;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    }

    .verify-icon {
        font-size: 60px;
        margin-bottom: 20px;
        width: 100px;
        height: 100px;
        line-height: 100px;
        border-radius: 50%;
        display: inline-block;
    }

    .verify-success .verify-icon {
        color: #28a745;
        background-color: #d4edda;
    }

    .verify-error .verify-icon {
        color: #dc3545;
        background-color: #f8d7da;
    }
    
    .verify-wrapper h2 {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .verify-wrapper p {
        color: #555;
        font-size: 16px;
        margin-bottom: 25px;
    }
    
    .btn-verify {
        padding: 10px 30px;
        font-size: 16px;
        border-radius: 8px;
    }
    
    .verification-display {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin: 15px 0;
        text-align: center;
    }
    
    .verification-code {
        font-family: 'Courier New', monospace;
        font-size: 24px;
        font-weight: bold;
        background: #e3f2fd;
        color: #003087;
        padding: 15px 20px;
        border-radius: 8px;
        border: 2px solid #0056b3;
        letter-spacing: 3px;
        margin: 10px 0;
    }
    
    .user-friendly-code {
        background: #28a745;
        color: white;
        padding: 20px 30px;
        border-radius: 10px;
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 2px;
        margin: 15px 0;
    }
    
    .code-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
    }
</style>

<section class="verify-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="verify-wrapper">
                    <?php if (isset($verification_error)): ?>
                        <div class='verify-error'>
                            <div class='verify-icon'>✖</div>
                            <h2><?php echo htmlspecialchars($verification_error); ?></h2>
                            <p><?php echo htmlspecialchars($verification_message); ?></p>
                            <?php if (isset($show_entered_code) && $show_entered_code): ?>
                                <div class='verification-display'>
                                    <div class='code-label'>You Entered:</div>
                                    <div class='verification-code'><?php echo htmlspecialchars($email); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>
