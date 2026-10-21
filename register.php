<?php
<<<<<<< HEAD
require_once 'kk_pharma/after_login/config.php';
require_once 'kk_pharma/validation.php';

// Check if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: user_dashboard/index.php");
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate registration data
    $validation_result = validateRegistration($_POST);
    if (!$validation_result['valid']) {
        $errors = $validation_result['errors'];
    } else {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $phone = sanitizeInput($_POST['phone'] ?? '');
        
        // Check if email already exists
        $email_check = checkEmailExists($conn, $email, 'register');
        if (!$email_check['valid']) {
            $errors['email'] = $email_check['message'];
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO register (name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $phone);

            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
            } else {
                $errors['register'] = "Registration failed. Please try again.";
            }
        }
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
            padding: 20px 0;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .register-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 209, 178, 0.25);
        }
        
        .btn-register {
            background: var(--accent-color);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
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
    <div class="register-card">
        <div class="register-header">
            <div class="logo">
                <i class="bi bi-hospital"></i> KK Pharma
            </div>
            <h4 class="mb-0">Create Account</h4>
            <p class="mb-0 opacity-75">Join our healthcare community</p>
        </div>
        
        <div class="register-body">
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <div class="text-center">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
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

                <form id="registerForm" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-person me-2"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name"
                               data-validate="name"
                               data-field-name="Full Name"
                               data-min-length="2"
                               data-max-length="50"
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                               required>
                        <div class="invalid-feedback"></div>
                    </div>

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
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone me-2"></i>Phone Number
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               data-validate="phone"
                               data-field-name="Phone Number"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
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
                        <small class="text-muted">Minimum 8 characters with uppercase, lowercase, number, and special character</small>

                        <!-- Password strength indicator -->
                        <div class="progress mt-2" style="height: 5px;">
                            <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-2"></i>Confirm Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               data-validate="confirm_password"
                               data-field-name="Confirm Password"
                               required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-register w-100">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <p class="mb-0">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none">Login here</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Validation JS -->
    <script src="js/validation.js"></script>
</body>
</html>
=======
session_start();
include_once 'mailer.php';
include_once("Database/db_config.php");

$errors = [];
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

    // Validation
    if (empty($inputs['fname'])) {
        $errors['fname'] = "First Name is required.";
    }

    if (empty($inputs['lname'])) {
        $errors['lname'] = "Last Name is required.";
    }

    if (empty($inputs['email'])) {
        $errors['email'] = "Email is required.";
    } 
    elseif (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } 
    else {
        $checkEmail = $con->prepare("SELECT id FROM register WHERE email=?");
        $checkEmail->bind_param("s", $inputs['email']);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $errors['email'] = "Email already registered.";
        }
        $checkEmail->close();
    }

    if (empty($inputs['mobile']) || !is_numeric($inputs['mobile']) || strlen($inputs['mobile']) != 10) {
        $errors['mobile'] = "Enter valid 10 digit mobile number.";
    }

    if (empty($inputs['dept'])) {
        $errors['dept'] = "Please select department.";
    }

    if (empty($inputs['exp']) || !is_numeric($inputs['exp'])) {
        $errors['exp'] = "Experience must be numeric.";
    }

    if (empty($inputs['address'])) {
        $errors['address'] = "Address required.";
    }

    if (empty($pass)) {
        $errors['pass'] = "Password required.";
    } 
    elseif (strlen($pass) < 6) {
        $errors['pass'] = "Password must be minimum 6 characters.";
    }

    if ($pass !== $cpass) {
        $errors['cpass'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $vkey = md5(time() . $inputs['email']);

        $stmt = $con->prepare("INSERT INTO register 
        (first_name, last_name, email, mobile, department, experience, address, password, vkey) 
        VALUES (?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "sssssssss",
            $inputs['fname'],
            $inputs['lname'],
            $inputs['email'],
            $inputs['mobile'],
            $inputs['dept'],
            $inputs['exp'],
            $inputs['address'],
            $hashed_password,
            $vkey
        );

        if ($stmt->execute()) {
            $fullname = $inputs['fname']." ".$inputs['lname'];
            $to = $inputs['email'];
            $subject = "Registration Successful - Please Verify Your Email - KK Pharma";
            
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $script_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $verification_link = $protocol . "://" . $host . $script_dir . "/verify.php?vkey=" . $vkey;

            $body = "<h2>Welcome to KK Pharma, $fullname</h2>
                     <p>Your account has been successfully created.</p>
                     <p>Please click the link below to verify your email address:</p>
                     <p><a href='$verification_link'>Verify Email Address</a></p>";

            sendEmail($to, $subject, $body, "");
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
>>>>>>> c6775067838e23982daa6c2b4f2a0e39c1aeb335
