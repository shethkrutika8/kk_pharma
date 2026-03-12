<?php
// Start session for potential future use (e.g., login system)
session_start();

// Include database connection
include_once("Database/db_config.php");

// Initialize variables
$errors = [];
$success = "";
$inputs = ['fname' => '', 'lname' => '', 'email' => '', 'mobile' => '', 'dept' => '', 'exp' => '', 'address' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Sanitize and Retrieve Input
    foreach ($inputs as $key => $val) {
        if (isset($_POST[$key])) {
            $inputs[$key] = htmlspecialchars(trim($_POST[$key]));
        }
    }
    $pass = $_POST['pass'] ?? '';
    $cpass = $_POST['cpass'] ?? '';

    // 2. Server-Side Validation
    if (empty($inputs['fname'])) {
        $errors['fname'] = "First Name is required.";
    }
    if (empty($inputs['lname'])) {
        $errors['lname'] = "Last Name is required.";
    }

    if (empty($inputs['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if email already exists
        $checkEmail = $con->prepare("SELECT id FROM register WHERE email = ?");
        $checkEmail->bind_param("s", $inputs['email']);
        $checkEmail->execute();
        $checkEmail->store_result();
        if ($checkEmail->num_rows > 0) {
            $errors['email'] = "This email is already registered.";
        }
        $checkEmail->close();
    }

    if (empty($inputs['mobile']) || !is_numeric($inputs['mobile']) || strlen($inputs['mobile']) != 10) {
        $errors['mobile'] = "Valid 10-digit mobile number required.";
    }

    if (empty($inputs['dept'])) {
        $errors['dept'] = "Please select a department.";
    }

    if (empty($inputs['exp']) || !is_numeric($inputs['exp'])) {
        $errors['exp'] = "Experience must be a number.";
    }

    if (empty($inputs['address'])) {
        $errors['address'] = "Address is required.";
    }

    if (empty($pass)) {
        $errors['pass'] = "Password is required.";
    } elseif (strlen($pass) < 6) {
        $errors['pass'] = "Password must be at least 6 characters.";
    }

    if ($pass !== $cpass) {
        $errors['cpass'] = "Passwords do not match.";
    }

    // 3. Insert into Database if no errors
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO register (first_name, last_name, email, mobile, department, experience, address, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters: s=string
        $stmt->bind_param(
            "ssssssss",
            $inputs['fname'],
            $inputs['lname'],
            $inputs['email'],
            $inputs['mobile'],
            $inputs['dept'],
            $inputs['exp'],
            $inputs['address'],
            $hashed_password
        );

        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            // Clear form inputs after success
            $inputs = array_map(function () {
                return '';
            }, $inputs);
        } else {
            $errors['db'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

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

    /* RED BOX ERROR STYLE */
    .text-danger.small {
        color: #721c24 !important;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 5px 10px;
        border-radius: 4px;
        margin-top: 5px;
        display: block;
        /* Force display for PHP errors */
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
                        <!-- Left Side Info -->
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="register-info">
                                <h2>KK Pharma</h2>
                                <p>Join a trusted pharmaceutical company committed to quality healthcare and innovation.</p>
                                <ul>
                                    <li>✔ WHO-GMP Certified Manufacturing</li>
                                    <li>✔ Global Healthcare Standards</li>
                                    <li>✔ Research & Development Focused</li>
                                    <li>✔ Career Growth Opportunities</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Right Side Form -->
                        <div class="col-lg-8">
                            <div class="register-form">
                                <h3>Create Your Account</h3>

                                <!-- Success Message -->
                                <?php if (!empty($success)): ?>
                                    <div class="alert-success">
                                        <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="post" id="regform" action="register.php" novalidate>
                                    <div class="row">
                                        <!-- First Name -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control <?php echo isset($errors['fname']) ? 'is-invalid' : ''; ?>" name="fname" value="<?php echo $inputs['fname']; ?>">
                                            <?php if (isset($errors['fname'])): ?>
                                                <span class="text-danger small"><?php echo $errors['fname']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Last Name -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control <?php echo isset($errors['lname']) ? 'is-invalid' : ''; ?>" name="lname" value="<?php echo $inputs['lname']; ?>">
                                            <?php if (isset($errors['lname'])): ?>
                                                <span class="text-danger small"><?php echo $errors['lname']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" name="email" value="<?php echo $inputs['email']; ?>">
                                            <?php if (isset($errors['email'])): ?>
                                                <span class="text-danger small"><?php echo $errors['email']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Mobile -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mobile</label>
                                            <input type="text" class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" name="mobile" value="<?php echo $inputs['mobile']; ?>">
                                            <?php if (isset($errors['mobile'])): ?>
                                                <span class="text-danger small"><?php echo $errors['mobile']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Department -->
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

                                        <!-- Experience -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="text" class="form-control <?php echo isset($errors['exp']) ? 'is-invalid' : ''; ?>" name="exp" value="<?php echo $inputs['exp']; ?>">
                                            <?php if (isset($errors['exp'])): ?>
                                                <span class="text-danger small"><?php echo $errors['exp']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Address -->
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" name="address"><?php echo $inputs['address']; ?></textarea>
                                            <?php if (isset($errors['address'])): ?>
                                                <span class="text-danger small"><?php echo $errors['address']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Password -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control <?php echo isset($errors['pass']) ? 'is-invalid' : ''; ?>" id="pass" name="pass">
                                            <?php if (isset($errors['pass'])): ?>
                                                <span class="text-danger small"><?php echo $errors['pass']; ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Confirm Password -->
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
<!-- Assumed you have custom JS for frontend validation logic in layout.js -->
<script src="js/layout.js"></script>

<?php include_once 'footer.php'; ?>