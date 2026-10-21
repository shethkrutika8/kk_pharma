<?php
session_start();

// Ensure the user is logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<?php include_once 'header.php'; ?>

<style>
    .admin-dashboard {
        min-height: calc(100vh - 70px);
        background: #f4f6f8;
        padding: 50px 20px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .welcome-card {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .welcome-card h1 {
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .welcome-card p {
        color: #7f8c8d;
        font-size: 18px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .stat-card h3 {
        color: #2c3e50;
        font-size: 36px;
        margin-bottom: 10px;
    }

    .stat-card p {
        color: #7f8c8d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .logout-btn {
        display: inline-block;
        margin-top: 30px;
        padding: 12px 30px;
        background: #e74c3c;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .logout-btn:hover {
        background: #c0392b;
        color: white;
    }
</style>

<div class="admin-dashboard">
    <div class="dashboard-container">
        <div class="welcome-card">
            <h1>Welcome to Admin Dashboard</h1>
            <p>You are logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_user_id']); ?></strong></p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Admin</h3>
                <p>System Users</p>
            </div>
            <div class="stat-card">
                <h3>Active</h3>
                <p>Status</p>
            </div>
            <div class="stat-card">
                <h3>Secure</h3>
                <p>Connection</p>
            </div>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>
