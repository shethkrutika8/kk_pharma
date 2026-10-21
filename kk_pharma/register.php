<?php
ob_start();
include_once 'Database/db_config.php';
include_once 'mailer.php';
require_once 'validation.php';

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

$inputs = [
    'fname' => '',
    'lname' => '',
    'email' => '',
    'mobile' => '',
    'dept' => '',
    'exp' => '',
    'address' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    foreach ($inputs as $key => $val) {
        if (isset($_POST[$key])) {
            $inputs[$key] = htmlspecialchars(trim($_POST[$key]));
        }
    }

    $pass = $_POST['pass'] ?? '';
    $cpass = $_POST['cpass'] ?? '';

    // Validation using new validation functions
    $fname_result = validateName($inputs['fname'], 'First Name');
    if (!$fname_result['valid']) {
        $errors['fname'] = $fname_result['message'];
    }

    $lname_result = validateName($inputs['lname'], 'Last Name');
    if (!$lname_result['valid']) {
        $errors['lname'] = $lname_result['message'];
    }

    $email_result = validateEmail($inputs['email']);
    if (!$email_result['valid']) {
        $errors['email'] = $email_result['message'];
    } else {
        $checkEmail = $con->prepare("SELECT id FROM register WHERE email=?");
        $checkEmail->bind_param("s", $inputs['email']);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $errors['email'] = "Email already registered.";
        }
        $checkEmail->close();
    }

    $phone_result = validatePhone($inputs['mobile']);
    if (!$phone_result['valid']) {
        $errors['mobile'] = $phone_result['message'];
    }

    if (empty($inputs['dept'])) {
        $errors['dept'] = "Please select department.";
    }

    $exp_result = validateNumber($inputs['exp'], 'Experience', 0, 50);
    if (!$exp_result['valid']) {
        $errors['exp'] = $exp_result['message'];
    }

    $address_result = validateAddress($inputs['address'], 'Address');
    if (!$address_result['valid']) {
        $errors['address'] = $address_result['message'];
    }

    $pass_result = validatePassword($pass, $cpass);
    if (!$pass_result['valid']) {
        $errors['pass'] = $pass_result['message'];
    }

    if (empty($errors)) {
        $plain_password = $pass;
        $vkey = md5(time() . $inputs['email']);
        
        // Generate user-friendly verification code
        $user_friendly_code = strtoupper(substr(md5($inputs['email']), 0, 8) . substr(md5(time()), 0, 4));
        
        // Debug: Log verification key
        error_log("Generated verification key: " . $vkey);
        error_log("User-friendly code: " . $user_friendly_code);
        error_log("Email for verification: " . $inputs['email']);

        $stmt = $con->prepare("INSERT INTO register 
        (first_name, last_name, email, mobile, department, experience, address, password, vkey, user_friendly_code) 
        VALUES (?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "ssssssssss",
            $inputs['fname'],
            $inputs['lname'],
            $inputs['email'],
            $inputs['mobile'],
            $inputs['dept'],
            $inputs['exp'],
            $inputs['address'],
            $plain_password,
            $vkey,
            $user_friendly_code
        );

        if ($stmt->execute()) {
            // Debug: Log successful insertion
            error_log("User registered successfully with ID: " . $con->insert_id);
            error_log("Verification key stored: " . $vkey);
            $fullname = $inputs['fname']." ".$inputs['lname'];
            $to = $inputs['email'];
            $subject = "Registration Successful - Please Verify Your Email - KK Pharma";
            
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $script_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $verification_link = "$protocol://$host$script_dir/verify.php?vkey=" . urlencode($inputs['email']);

            $body = "<h2>Welcome to KK Pharma, $fullname</h2>
                     <p>Your account has been successfully created.</p>
                     <p>Please click the link below to verify your email address:</p>
                     <p><a href='$verification_link'>Verify Email Address</a></p>";

            // Debug: Log the verification link
            error_log("Verification link: " . $verification_link);
            
            $mail_result = sendEmail($to, $subject, $body, "");
            error_log("Mail result: " . print_r($mail_result, true));
            setcookie("success", "Registration successful! Please check your email to verify your account.", time() + 5, "/");
            
            echo "<script>window.location.href='login.php';</script>";
            exit;
        } else {
            setcookie("error", "Registration failed!", time() + 5, "/");
            echo "<script>window.location.href='register.php';</script>";
            exit;
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
    <title>Register | KK Pharma</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include_once 'header.php'; ?>

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

    textarea.form-control {
        height: 90px;
        resize: none;
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

                                <form method="post" id="regform" action="register.php" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control <?php echo isset($errors['fname']) ? 'is-invalid' : ''; ?>" name="fname" value="<?php echo $inputs['fname']; ?>">
                                            <?php if (isset($errors['fname'])): ?>
                                                <span class="text-danger small"><?php echo $errors['fname']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control <?php echo isset($errors['lname']) ? 'is-invalid' : ''; ?>" name="lname" value="<?php echo $inputs['lname']; ?>">
                                            <?php if (isset($errors['lname'])): ?>
                                                <span class="text-danger small"><?php echo $errors['lname']; ?></span>
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
                                            <label class="form-label">Mobile</label>
                                            <input type="text" class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" name="mobile" value="<?php echo $inputs['mobile']; ?>">
                                            <?php if (isset($errors['mobile'])): ?>
                                                <span class="text-danger small"><?php echo $errors['mobile']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Department</label>
                                            <select class="form-select <?php echo isset($errors['dept']) ? 'is-invalid' : ''; ?>" name="dept">
                                                <option value="">Select</option>
                                                <option value="Production" <?php echo ($inputs['dept'] == 'Production') ? 'selected' : ''; ?>>Production</option>
                                                <option value="Quality Control" <?php echo ($inputs['dept'] == 'Quality Control') ? 'selected' : ''; ?>>Quality Control</option>
                                                <option value="R&D" <?php echo ($inputs['dept'] == 'R&D') ? 'selected' : ''; ?>>R&D</option>
                                                <option value="Marketing" <?php echo ($inputs['dept'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                            </select>
                                            <?php if (isset($errors['dept'])): ?>
                                                <span class="text-danger small"><?php echo $errors['dept']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="text" class="form-control <?php echo isset($errors['exp']) ? 'is-invalid' : ''; ?>" name="exp" value="<?php echo $inputs['exp']; ?>">
                                            <?php if (isset($errors['exp'])): ?>
                                                <span class="text-danger small"><?php echo $errors['exp']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" name="address"><?php echo $inputs['address']; ?></textarea>
                                            <?php if (isset($errors['address'])): ?>
                                                <span class="text-danger small"><?php echo $errors['address']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control <?php echo isset($errors['pass']) ? 'is-invalid' : ''; ?>" id="pass" name="pass">
                                            <?php if (isset($errors['pass'])): ?>
                                                <span class="text-danger small"><?php echo $errors['pass']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control <?php echo isset($errors['cpass']) ? 'is-invalid' : ''; ?>" name="cpass">
                                            <?php if (isset($errors['cpass'])): ?>
                                                <span class="text-danger small"><?php echo $errors['cpass']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-dark w-100 btn-register">Register Now</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="login-link">
                                    Already have an account? <a href="login.php">Login</a>
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
<script src="js/layout.js"></script>

<?php include_once 'footer.php'; ?>
</body>
</html>