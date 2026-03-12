<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include_once 'header.php';
// Get the connection so we can retrieve user details if needed
include_once 'Database/db_config.php';

$email = $_SESSION['user_email'];

// Optionally retrieve the user details
$stmt = $con->prepare("SELECT first_name, last_name, email, mobile, department, experience, address, profile_picture FROM register WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();
$stmt->close();
?>
<style>
    .dashboard-container { min-height: calc(100vh - 70px); background: #f8f9fa; padding: 40px 0; }
    .card-dashboard { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); background: #fff; padding: 30px; }
    .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--accent); margin-bottom: 20px; }
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
                    <?php if(!empty($user_info['profile_picture'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" class="profile-avatar" alt="Profile Picture">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_info['first_name'] . ' ' . $user_info['last_name']); ?>&background=random" class="profile-avatar" alt="Profile Picture">
                    <?php endif; ?>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?></h4>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user_info['department']); ?></p>
                    <a href="logout.php" class="btn btn-outline-danger w-100">Logout</a>
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
