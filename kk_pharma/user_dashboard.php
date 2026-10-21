<?php
ob_start();

// Include config for proper setup
include_once 'Database/db_config.php';

// Auth check BEFORE any HTML output so header() redirect works
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include_once 'header.php';

// Get the connection so we can retrieve user details if needed

$email = $_SESSION['user_email'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Check for upload errors
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $update_stmt = $con->prepare("UPDATE register SET profile_picture = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $new_file_name, $email);
                $update_stmt->execute();
                $update_stmt->close();
                $_SESSION['login_success_msg'] = "Profile picture updated successfully!";
                header("Location: ../user_dashboard/");
                exit();
            }
        } else {
            $_SESSION['login_success_msg'] = "Invalid file format. Please upload JPG, PNG, or GIF.";
        }
    } else {
        $_SESSION['login_success_msg'] = "Error uploading file. Please try again.";
    }
}

// Optionally retrieve the user details
$stmt = $con->prepare("SELECT first_name, last_name, email, mobile, department, experience, address  FROM register WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$stmt->close();
?>
<style>
    .dashboard-container { min-height: calc(100vh - 70px); background: #f8f9fa; padding: 40px 0; }
    .card-dashboard { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); background: #fff; padding: 30px; }
    .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--accent); }
    .profile-container { position: relative; width: 120px; height: 120px; margin: 0 auto 20px auto; }
    .camera-icon { position: absolute; bottom: 0; right: 0; background: var(--primary); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid white; transition: 0.3s; }
    .camera-icon:hover { background: var(--accent); }
    #profile_upload { display: none; }
</style>

<div class="dashboard-container">
    <div class="container">
        <?php if(isset($_SESSION['login_success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <?php 
                echo $_SESSION['login_success_msg']; 
                unset($_SESSION['login_success_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card-dashboard text-center">
                    <form action="user_dashboard.php" method="POST" enctype="multipart/form-data" id="profile_form">
                        <div class="profile-container">
                            <?php if(!empty($user_info['profile_picture'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" class="profile-avatar" alt="Profile Picture">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_info['first_name'] . ' ' . $user_info['last_name']); ?>&background=random" class="profile-avatar" alt="Profile Picture">
                            <?php endif; ?>
                            <label for="profile_upload" class="camera-icon" title="Change Profile Picture">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                    <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                                </svg>
                            </label>
                            <input type="file" name="profile_picture" id="profile_upload" accept="image/*" onchange="document.getElementById('profile_form').submit();">
                        </div>
                    </form>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h4>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user_info['department']); ?></p>
                    <a href="logout.php" class="btn btn-outline-danger w-100">Logout</a>
                    <a href="verification_keys.php" class="btn btn-info w-100 mt-2">View Verification Keys</a>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card-dashboard">
                    <h3 class="mb-4">Profile Information</h3>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Email:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_info['email']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Mobile:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_info['mobile']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Experience:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_info['experience']); ?> Years</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Address:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($user_info['address']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
if(file_exists('footer.php')) {
    include_once 'footer.php';
}
?>
</body>
</html>
