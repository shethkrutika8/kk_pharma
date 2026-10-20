<?php
require_once '../kk_pharma/after_login/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'User';

// Handle profile updates
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_profile':
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $date_of_birth = $_POST['date_of_birth'] ?? '';
            $gender = $_POST['gender'] ?? '';
            
            if (updateUserProfile($user_id, $first_name, $last_name, $phone, $date_of_birth, $gender)) {
                $_SESSION['success_msg'] = "Profile updated successfully!";
                // Update session name
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            } else {
                $_SESSION['error_msg'] = "Failed to update profile.";
            }
            header("Location: profile.php");
            exit();
            break;
            
        case 'add_address':
            $address_type = $_POST['address_type'] ?? 'home';
            $address_line1 = $_POST['address_line1'] ?? '';
            $address_line2 = $_POST['address_line2'] ?? '';
            $city = $_POST['city'] ?? '';
            $state = $_POST['state'] ?? '';
            $postal_code = $_POST['postal_code'] ?? '';
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            
            if (addUserAddress($user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $is_default)) {
                $_SESSION['success_msg'] = "Address added successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to add address.";
            }
            header("Location: profile.php");
            exit();
            break;
            
        case 'update_address':
            $address_id = $_POST['address_id'] ?? 0;
            $address_type = $_POST['address_type'] ?? 'home';
            $address_line1 = $_POST['address_line1'] ?? '';
            $address_line2 = $_POST['address_line2'] ?? '';
            $city = $_POST['city'] ?? '';
            $state = $_POST['state'] ?? '';
            $postal_code = $_POST['postal_code'] ?? '';
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            
            if (updateUserAddress($address_id, $user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $is_default)) {
                $_SESSION['success_msg'] = "Address updated successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to update address.";
            }
            header("Location: profile.php");
            exit();
            break;
            
        case 'delete_address':
            $address_id = $_POST['address_id'] ?? 0;
            if (deleteUserAddress($address_id, $user_id)) {
                $_SESSION['success_msg'] = "Address deleted successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to delete address.";
            }
            header("Location: profile.php");
            exit();
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if ($new_password !== $confirm_password) {
                $_SESSION['error_msg'] = "New passwords do not match!";
            } elseif (changeUserPassword($user_id, $current_password, $new_password)) {
                $_SESSION['success_msg'] = "Password changed successfully!";
            } else {
                $_SESSION['error_msg'] = "Current password is incorrect or new password is too weak.";
            }
            header("Location: profile.php");
            exit();
            break;
    }
}

// Get user profile data
$user_profile = getUserProfile($user_id);
$user_addresses = getUserAddresses($user_id);

// Profile management functions
function getUserProfile($user_id) {
    $conn = getDBConnection();
    
    // Try to get from user_profiles first
    $stmt = $conn->prepare("SELECT up.*, u.email, u.name as username FROM user_profiles up JOIN register u ON up.user_id = u.id WHERE up.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    
    if (!$profile) {
        // Create default profile if not exists
        $stmt = $conn->prepare("SELECT name, email FROM register WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            $name_parts = explode(' ', $user['name'], 2);
            $first_name = $name_parts[0] ?? '';
            $last_name = $name_parts[1] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $first_name, $last_name, $user['email']);
            $stmt->execute();
            
            return [
                'user_id' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $user['email'],
                'username' => $user['name'],
                'phone' => '',
                'date_of_birth' => '',
                'gender' => ''
            ];
        }
    }
    
    return $profile;
}

function updateUserProfile($user_id, $first_name, $last_name, $phone, $date_of_birth, $gender) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, phone, date_of_birth, gender) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), phone = VALUES(phone), date_of_birth = VALUES(date_of_birth), gender = VALUES(gender), updated_at = NOW()");
    $stmt->bind_param("isssss", $user_id, $first_name, $last_name, $phone, $date_of_birth, $gender);
    
    // Also update the main register table name
    $full_name = $first_name . ' ' . $last_name;
    $stmt2 = $conn->prepare("UPDATE register SET name = ? WHERE id = ?");
    $stmt2->bind_param("si", $full_name, $user_id);
    $stmt2->execute();
    
    return $stmt->execute();
}

function getUserAddresses($user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $addresses = [];
    $result = $stmt->get_result();
    while ($address = $result->fetch_assoc()) {
        $addresses[] = $address;
    }
    
    return $addresses;
}

function addUserAddress($user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $is_default) {
    $conn = getDBConnection();
    
    // If this is set as default, unset other defaults
    if ($is_default) {
        $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, address_type, address_line1, address_line2, city, state, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $is_default);
    
    return $stmt->execute();
}

function updateUserAddress($address_id, $user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $is_default) {
    $conn = getDBConnection();
    
    // If this is set as default, unset other defaults
    if ($is_default) {
        $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND id != ?");
        $stmt->bind_param("ii", $user_id, $address_id);
        $stmt->execute();
    }
    
    $stmt = $conn->prepare("UPDATE user_addresses SET address_type = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, is_default = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssssssii", $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $is_default, $address_id, $user_id);
    
    return $stmt->execute();
}

function deleteUserAddress($address_id, $user_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    
    return $stmt->execute();
}

function changeUserPassword($user_id, $current_password, $new_password) {
    $conn = getDBConnection();
    
    // Validate current password
    $stmt = $conn->prepare("SELECT password FROM register WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        return false;
    }
    
    // Validate new password
    if (strlen($new_password) < 6) {
        return false;
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE register SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | KK Pharma</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../Home.php">
                <i class="bi bi-hospital me-2"></i>KK Pharma
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../Home.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($user_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi bi-cart3 me-2"></i>Cart
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="bi bi-box-seam me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="subscriptions.php">
                                <i class="bi bi-arrow-repeat me-2"></i>Subscriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="wishlist.php">
                                <i class="bi bi-heart me-2"></i>Wishlist
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="prescriptions.php">
                                <i class="bi bi-file-medical me-2"></i>Prescriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="profile.php">
                                <i class="bi bi-person me-2"></i>Profile
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Messages -->
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-person me-2"></i>Profile Settings
                    </h1>
                </div>

                <!-- Profile Tabs -->
                <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                            <i class="bi bi-person me-2"></i>Personal Information
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button" role="tab">
                            <i class="bi bi-geo-alt me-2"></i>Addresses
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                            <i class="bi bi-shield-lock me-2"></i>Security
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabsContent">
                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">Personal Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_profile['first_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_profile['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_profile['email'] ?? ''); ?>" readonly>
                                            <small class="text-muted">Email cannot be changed here. Contact support for changes.</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_profile['phone'] ?? ''); ?>" placeholder="+91 98765 43210">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user_profile['date_of_birth'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="gender" class="form-label">Gender</label>
                                            <select class="form-select" id="gender" name="gender">
                                                <option value="">Select Gender</option>
                                                <option value="male" <?php echo ($user_profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo ($user_profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo ($user_profile['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses Tab -->
                    <div class="tab-pane fade" id="addresses" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>My Addresses</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="bi bi-plus-circle me-2"></i>Add Address
                            </button>
                        </div>

                        <?php if (!empty($user_addresses)): ?>
                            <div class="row">
                                <?php foreach ($user_addresses as $address): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card shadow h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">
                                                        <?php echo ucfirst($address['address_type']); ?>
                                                        <?php if ($address['is_default']): ?>
                                                            <span class="badge bg-primary ms-1">Default</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <button class="dropdown-item" onclick="editAddress(<?php echo htmlspecialchars(json_encode($address)); ?>)">
                                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                                </button>
                                                            </li>
                                                            <?php if (!$address['is_default']): ?>
                                                                <li>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="action" value="delete_address">
                                                                        <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Delete this address?')">
                                                                            <i class="bi bi-trash me-2"></i>Delete
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="card-text">
                                                    <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                                    <?php if (!empty($address['address_line2'])): ?>
                                                        <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?><br>
                                                    <?php echo htmlspecialchars($address['postal_code']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-geo-alt"></i>
                                <h5>No Addresses Added</h5>
                                <p class="text-muted">Add your delivery addresses for faster checkout.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    <i class="bi bi-plus-circle me-2"></i>Add Your First Address
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                                        <small class="text-muted">Password must be at least 6 characters long.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-shield-lock me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Account Security</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-envelope-check text-success me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <h6 class="mb-0">Email Verification</h6>
                                                <small class="text-muted">Your email is verified</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-phone text-warning me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <h6 class="mb-0">Phone Verification</h6>
                                                <small class="text-muted">Phone not verified</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-shield-check text-success me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <h6 class="mb-0">Two-Factor Authentication</h6>
                                                <small class="text-muted">Not enabled</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-clock-history text-info me-3" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <h6 class="mb-0">Last Login</h6>
                                                <small class="text-muted">Today at <?php echo date('h:i A'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_address">
                        <div class="mb-3">
                            <label for="address_type" class="form-label">Address Type</label>
                            <select class="form-select" id="address_type" name="address_type" required>
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                        </div>
                        <div class="mb-3">
                            <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                            <input type="text" class="form-control" id="address_line2" name="address_line2">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                            <label class="form-check-label" for="is_default">
                                Set as default address
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_address">
                        <input type="hidden" name="address_id" id="edit_address_id">
                        <div class="mb-3">
                            <label for="edit_address_type" class="form-label">Address Type</label>
                            <select class="form-select" id="edit_address_type" name="address_type" required>
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="edit_address_line1" name="address_line1" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address_line2" class="form-label">Address Line 2 (Optional)</label>
                            <input type="text" class="form-control" id="edit_address_line2" name="address_line2">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="edit_city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_state" class="form-label">State</label>
                                <input type="text" class="form-control" id="edit_state" name="state" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="edit_postal_code" name="postal_code" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_default" name="is_default">
                            <label class="form-check-label" for="edit_is_default">
                                Set as default address
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function editAddress(address) {
            document.getElementById('edit_address_id').value = address.id;
            document.getElementById('edit_address_type').value = address.address_type;
            document.getElementById('edit_address_line1').value = address.address_line1;
            document.getElementById('edit_address_line2').value = address.address_line2 || '';
            document.getElementById('edit_city').value = address.city;
            document.getElementById('edit_state').value = address.state;
            document.getElementById('edit_postal_code').value = address.postal_code;
            document.getElementById('edit_is_default').checked = address.is_default == 1;
            
            new bootstrap.Modal(document.getElementById('editAddressModal')).show();
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
