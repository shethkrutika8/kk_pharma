<?php
ob_start();

// Include config for proper setup
include_once 'Database/db_config.php';

// Auth check - only logged in users can view verification keys
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Function to format hash key into readable format
function format_vkey($vkey) {
    if (strlen($vkey) == 32) {
        // Format: XXXX-XXXX-XXXX-XXXX
        return substr($vkey, 0, 4) . '-' . 
               substr($vkey, 8, 4) . '-' . 
               substr($vkey, 16, 4) . '-' . 
               substr($vkey, 24, 8);
    } else {
        // For other lengths, format in chunks of 4
        $formatted = '';
        for ($i = 0; $i < strlen($vkey); $i += 4) {
            $formatted .= substr($vkey, $i, 4);
            if ($i + 4 < strlen($vkey)) {
                $formatted .= '-';
            }
        }
        return $formatted;
    }
}

// Function to generate user-friendly verification code
function generate_user_friendly_code($email) {
    // Create a shorter, more readable code based on email and timestamp
    $timestamp = time();
    $email_hash = substr(md5($email), 0, 8);
    $time_hash = substr(md5($timestamp), 0, 4);
    return strtoupper($email_hash . $time_hash);
}

// Get user verification keys
$user_keys = [];
$email = '';

// Check if user is logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $email = $_SESSION['user_email'];
    
    // Get from main register table
    $stmt = $con->prepare("SELECT vkey, verified FROM register WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_keys['register'] = [
            'vkey' => $user['vkey'],
            'formatted' => format_vkey($user['vkey']),
            'verified' => $user['verified'],
            'user_friendly' => generate_user_friendly_code($email)
        ];
    }
    $stmt->close();
}

// Also check login database if it exists
if (file_exists('Database/login_db_config.php')) {
    include_once 'Database/login_db_config.php';
    
    $stmt = $con->prepare("SELECT id, username, email, created_at FROM login_users WHERE email = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_keys['login_users'][] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'created_at' => $row['created_at'],
            'user_friendly' => generate_user_friendly_code($row['email'])
        ];
    }
    $stmt->close();
}

// Handle verification key generation request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_new'])) {
    if (!empty($email)) {
        $new_vkey = md5(time() . $email);
        $user_friendly = generate_user_friendly_code($email);
        
        // Update in register table
        $stmt = $con->prepare("UPDATE register SET vkey = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_vkey, $email);
        $stmt->execute();
        $stmt->close();
        
        $success_msg = "New verification key generated successfully!";
        
        // Refresh user keys
        header("Location: verification_keys.php?success=1");
        exit();
    }
}

$success_msg = isset($_GET['success']) ? "New verification key generated!" : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Keys | KK Pharma</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include_once 'header.php'; ?>

<style>
    .verification-page {
        background: #f8f9fa;
        min-height: calc(100vh - 70px);
        padding: 40px 0;
    }
    
    .key-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }
    
    .key-display {
        font-family: 'Courier New', monospace;
        font-size: 18px;
        font-weight: bold;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        margin: 10px 0;
        word-break: break-all;
    }
    
    .formatted-key {
        color: #007bff;
        font-size: 20px;
        letter-spacing: 2px;
    }
    
    .user-friendly {
        background: #e3f2fd;
        color: #003087;
        padding: 20px;
        border-radius: 10px;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin: 15px 0;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-verified {
        background: #28a745;
        color: white;
    }
    
    .status-pending {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-generate {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
    }
    
    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
</style>

<div class="verification-page">
    <div class="container">
        <h2 class="text-center mb-4">Your Verification Keys</h2>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($user_keys)): ?>
            <div class="alert alert-info text-center">
                <h4>No Verification Keys Found</h4>
                <p>Please <a href="register.php">register</a> or <a href="login.php">login</a> to view your verification keys.</p>
            </div>
        <?php else: ?>
            
            <?php if (isset($user_keys['register'])): ?>
                <div class="key-card">
                    <h4>Registration Verification Key</h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Hash Format:</strong>
                            <div class="key-display"><?php echo htmlspecialchars($user_keys['register']['vkey']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <strong>Formatted:</strong>
                            <div class="key-display formatted-key"><?php echo htmlspecialchars($user_keys['register']['formatted']); ?></div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>User-Friendly Code:</strong>
                            <div class="user-friendly"><?php echo htmlspecialchars($user_keys['register']['user_friendly']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="status-badge <?php echo $user_keys['register']['verified'] ? 'status-verified' : 'status-pending'; ?>">
                                <?php echo $user_keys['register']['verified'] ? 'Verified' : 'Pending'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <form method="post">
                            <button type="submit" name="generate_new" class="btn-generate">
                                Generate New Key
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($user_keys['login_users']) && !empty($user_keys['login_users'])): ?>
                <div class="key-card">
                    <h4>Login Database Keys</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>User-Friendly Code</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_keys['login_users'] as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <div class="user-friendly" style="font-size: 14px; padding: 8px;">
                                                <?php echo htmlspecialchars($user['user_friendly']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="../user_dashboard/" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
</body>
</html>
