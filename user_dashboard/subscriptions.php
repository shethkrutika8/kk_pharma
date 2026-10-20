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

// Handle subscription actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'pause_subscription':
            $subscription_id = $_POST['subscription_id'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            if (pauseSubscription($subscription_id, $reason)) {
                $_SESSION['success_msg'] = "Subscription paused successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to pause subscription.";
            }
            header("Location: subscriptions.php");
            exit();
            break;
            
        case 'resume_subscription':
            $subscription_id = $_POST['subscription_id'] ?? 0;
            if (resumeSubscription($subscription_id)) {
                $_SESSION['success_msg'] = "Subscription resumed successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to resume subscription.";
            }
            header("Location: subscriptions.php");
            exit();
            break;
            
        case 'cancel_subscription':
            $subscription_id = $_POST['subscription_id'] ?? 0;
            $reason = $_POST['reason'] ?? '';
            if (cancelSubscription($subscription_id, $reason)) {
                $_SESSION['success_msg'] = "Subscription cancelled successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to cancel subscription.";
            }
            header("Location: subscriptions.php");
            exit();
            break;
            
        case 'update_subscription':
            $subscription_id = $_POST['subscription_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            $subscription_type = $_POST['subscription_type'] ?? 'monthly';
            if (updateSubscription($subscription_id, $quantity, $subscription_type)) {
                $_SESSION['success_msg'] = "Subscription updated successfully!";
            } else {
                $_SESSION['error_msg'] = "Failed to update subscription.";
            }
            header("Location: subscriptions.php");
            exit();
            break;
    }
}

// Get subscriptions
$active_subscriptions = getUserSubscriptions($user_id, 'active');
$paused_subscriptions = getUserSubscriptions($user_id, 'paused');
$completed_subscriptions = getUserSubscriptions($user_id, 'completed');

// Subscription management functions
function getUserSubscriptions($user_id, $status = null) {
    $conn = getDBConnection();
    
    $sql = "SELECT s.*, m.name as medicine_name, m.image_url, m.manufacturer, 
                   DATEDIFF(s.next_delivery_date, CURDATE()) as days_until_delivery,
                   CASE 
                       WHEN s.next_delivery_date < CURDATE() THEN 'overdue'
                       WHEN DATEDIFF(s.next_delivery_date, CURDATE()) <= 3 THEN 'due_soon'
                       WHEN DATEDIFF(s.next_delivery_date, CURDATE()) <= 7 THEN 'upcoming'
                       ELSE 'scheduled'
                   END as delivery_status
            FROM subscriptions s 
            JOIN medicines m ON s.medicine_id = m.id 
            WHERE s.user_id = ?";
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $status);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subscriptions = [];
    while ($subscription = $result->fetch_assoc()) {
        $subscriptions[] = $subscription;
    }
    
    return $subscriptions;
}

function pauseSubscription($subscription_id, $reason) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'paused', pause_reason = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $reason, $subscription_id, $_SESSION['user_id']);
    
    return $stmt->execute();
}

function resumeSubscription($subscription_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'active', pause_reason = NULL, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $subscription_id, $_SESSION['user_id']);
    
    return $stmt->execute();
}

function cancelSubscription($subscription_id, $reason) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 'cancelled', cancel_reason = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $reason, $subscription_id, $_SESSION['user_id']);
    
    return $stmt->execute();
}

function updateSubscription($subscription_id, $quantity, $subscription_type) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE subscriptions SET quantity = ?, subscription_type = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("isii", $quantity, $subscription_type, $subscription_id, $_SESSION['user_id']);
    
    return $stmt->execute();
}

function getSubscriptionDeliveries($subscription_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM subscription_deliveries WHERE subscription_id = ? ORDER BY delivery_date DESC");
    $stmt->bind_param("i", $subscription_id);
    $stmt->execute();
    
    $deliveries = [];
    $result = $stmt->get_result();
    while ($delivery = $result->fetch_assoc()) {
        $deliveries[] = $delivery;
    }
    
    return $deliveries;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions | KK Pharma</title>
    
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
                            <a class="nav-link active" href="subscriptions.php">
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
                            <a class="nav-link" href="profile.php">
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
                        <i class="bi bi-arrow-repeat me-2"></i>My Subscriptions
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../products.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>New Subscription
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subscription Tabs -->
                <ul class="nav nav-tabs mb-4" id="subscriptionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                            Active (<?php echo count($active_subscriptions); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="paused-tab" data-bs-toggle="tab" data-bs-target="#paused" type="button" role="tab">
                            Paused (<?php echo count($paused_subscriptions); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                            Completed (<?php echo count($completed_subscriptions); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="subscriptionTabsContent">
                    <!-- Active Subscriptions -->
                    <div class="tab-pane fade show active" id="active" role="tabpanel">
                        <?php if (!empty($active_subscriptions)): ?>
                            <div class="row">
                                <?php foreach ($active_subscriptions as $subscription): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card shadow h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="m-0 font-weight-bold">
                                                    <?php echo htmlspecialchars($subscription['medicine_name']); ?>
                                                </h6>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <?php if ($subscription['image_url']): ?>
                                                            <img src="<?php echo htmlspecialchars($subscription['image_url']); ?>" alt="<?php echo htmlspecialchars($subscription['medicine_name']); ?>" class="img-fluid rounded">
                                                        <?php else: ?>
                                                            <img src="https://via.placeholder.com/120x120/00d1b2/ffffff?text=Med" alt="<?php echo htmlspecialchars($subscription['medicine_name']); ?>" class="img-fluid rounded">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted">Type:</small>
                                                                <div class="fw-bold"><?php echo ucfirst($subscription['subscription_type']); ?></div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Quantity:</small>
                                                                <div class="fw-bold"><?php echo $subscription['quantity']; ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted">Price:</small>
                                                                <div class="fw-bold price"><?php echo formatPrice($subscription['price']); ?></div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Auto-renew:</small>
                                                                <div class="fw-bold"><?php echo $subscription['auto_renew'] ? 'Yes' : 'No'; ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <small class="text-muted">Next Delivery:</small>
                                                                <div class="fw-bold">
                                                                    <?php echo formatDate($subscription['next_delivery_date']); ?>
                                                                    <?php
                                                                    $days = $subscription['days_until_delivery'];
                                                                    if ($days <= 0) {
                                                                        echo '<span class="badge bg-danger ms-2">Overdue</span>';
                                                                    } elseif ($days <= 3) {
                                                                        echo '<span class="badge bg-warning ms-2">Due Soon</span>';
                                                                    } elseif ($days <= 7) {
                                                                        echo '<span class="badge bg-info ms-2">Upcoming</span>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="editSubscription(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-warning flex-fill" onclick="pauseSubscription(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-pause"></i> Pause
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger flex-fill" onclick="cancelSubscription(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-x"></i> Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-arrow-repeat"></i>
                                <h4>No Active Subscriptions</h4>
                                <p class="text-muted">You don't have any active subscriptions. Subscribe to your regular medicines for automatic delivery!</p>
                                <a href="../products.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Create Subscription
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Paused Subscriptions -->
                    <div class="tab-pane fade" id="paused" role="tabpanel">
                        <?php if (!empty($paused_subscriptions)): ?>
                            <div class="row">
                                <?php foreach ($paused_subscriptions as $subscription): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card shadow h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="m-0 font-weight-bold">
                                                    <?php echo htmlspecialchars($subscription['medicine_name']); ?>
                                                </h6>
                                                <span class="badge bg-warning">Paused</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <?php if ($subscription['image_url']): ?>
                                                            <img src="<?php echo htmlspecialchars($subscription['image_url']); ?>" alt="<?php echo htmlspecialchars($subscription['medicine_name']); ?>" class="img-fluid rounded">
                                                        <?php else: ?>
                                                            <img src="https://via.placeholder.com/120x120/00d1b2/ffffff?text=Med" alt="<?php echo htmlspecialchars($subscription['medicine_name']); ?>" class="img-fluid rounded">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted">Type:</small>
                                                                <div class="fw-bold"><?php echo ucfirst($subscription['subscription_type']); ?></div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Quantity:</small>
                                                                <div class="fw-bold"><?php echo $subscription['quantity']; ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <small class="text-muted">Pause Reason:</small>
                                                                <div class="fw-bold"><?php echo htmlspecialchars($subscription['pause_reason'] ?? 'Not specified'); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-success flex-fill" onclick="resumeSubscription(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-play"></i> Resume
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger flex-fill" onclick="cancelSubscription(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-x"></i> Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-pause"></i>
                                <h4>No Paused Subscriptions</h4>
                                <p class="text-muted">You don't have any paused subscriptions.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Completed Subscriptions -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        <?php if (!empty($completed_subscriptions)): ?>
                            <div class="row">
                                <?php foreach ($completed_subscriptions as $subscription): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card shadow h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="m-0 font-weight-bold">
                                                    <?php echo htmlspecialchars($subscription['medicine_name']); ?>
                                                </h6>
                                                <span class="badge bg-secondary">Completed</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <?php if ($subscription['image_url']): ?>
                                                            <img src="<?php echo htmlspecialchars($subscription['image_url']); ?>" alt="<?php echo htmlspecialchars($subscription['medicine_name']); ?>" class="img-fluid rounded">
                                                        <?php else: ?>
                                                            <img src="https://via.placeholder.com/120x120/00d1b2/ffffff?text=Med" alt="<?php echo htmlspecialchars($subscription['medicine_name']); ?>" class="img-fluid rounded">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted">Type:</small>
                                                                <div class="fw-bold"><?php echo ucfirst($subscription['subscription_type']); ?></div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Total Deliveries:</small>
                                                                <div class="fw-bold"><?php echo $subscription['total_deliveries']; ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-6">
                                                                <small class="text-muted">Start Date:</small>
                                                                <div class="fw-bold"><?php echo formatDate($subscription['start_date']); ?></div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">End Date:</small>
                                                                <div class="fw-bold"><?php echo formatDate($subscription['end_date']); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-primary flex-fill" onclick="renewSubscription(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-arrow-clockwise"></i> Renew
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="viewDetails(<?php echo $subscription['id']; ?>)">
                                                                <i class="bi bi-eye"></i> Details
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                <h4>No Completed Subscriptions</h4>
                                <p class="text-muted">You don't have any completed subscriptions yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <!-- Pause Subscription Modal -->
    <div class="modal fade" id="pauseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Pause Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="pause_subscription">
                        <input type="hidden" name="subscription_id" id="pause_subscription_id">
                        <div class="mb-3">
                            <label for="pause_reason" class="form-label">Reason for pausing:</label>
                            <textarea class="form-control" id="pause_reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Pause Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this subscription? This action cannot be undone.</p>
                        <input type="hidden" name="action" value="cancel_subscription">
                        <input type="hidden" name="subscription_id" id="cancel_subscription_id">
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Reason for cancellation:</label>
                            <textarea class="form-control" id="cancel_reason" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                        <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function pauseSubscription(subscriptionId) {
            document.getElementById('pause_subscription_id').value = subscriptionId;
            new bootstrap.Modal(document.getElementById('pauseModal')).show();
        }

        function cancelSubscription(subscriptionId) {
            document.getElementById('cancel_subscription_id').value = subscriptionId;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }

        function resumeSubscription(subscriptionId) {
            if (confirm('Are you sure you want to resume this subscription?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="resume_subscription">
                    <input type="hidden" name="subscription_id" value="${subscriptionId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editSubscription(subscriptionId) {
            // This would open an edit modal or redirect to edit page
            alert('Edit functionality would be implemented here');
        }

        function renewSubscription(subscriptionId) {
            if (confirm('Would you like to renew this subscription?')) {
                // This would create a new subscription based on the completed one
                alert('Renew functionality would be implemented here');
            }
        }

        function viewDetails(subscriptionId) {
            // This would show detailed subscription history
            alert('Details view would be implemented here');
        }
    </script>
</body>
</html>
