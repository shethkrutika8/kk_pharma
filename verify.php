<?php
session_start();
include_once 'Database/db_config.php';
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
</style>

<section class="verify-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="verify-wrapper">
                    <?php
                    if (isset($_GET['vkey'])) {
                        $vkey = mysqli_real_escape_string($con, $_GET['vkey']);
                        
                        $stmt = $con->prepare("SELECT verified, vkey FROM register WHERE verified = 0 AND vkey = ? LIMIT 1");
                        $stmt->bind_param("s", $vkey);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows == 1) {
                            $update = $con->prepare("UPDATE register SET verified = 1 WHERE vkey = ? LIMIT 1");
                            $update->bind_param("s", $vkey);
                            if ($update->execute()) {
                                header("Location: login.php?msg=verified");
                                exit();
                            } else {
                                echo "<div class='verify-error'>";
                                echo "<div class='verify-icon'>✖</div>";
                                echo "<h2>Verification Failed</h2>";
                                echo "<p>Something went wrong during verification. Please try again later.</p>";
                                echo "</div>";
                            }
                        } else {
                            // Check if already verified
                            $stmt2 = $con->prepare("SELECT verified FROM register WHERE vkey = ? LIMIT 1");
                            $stmt2->bind_param("s", $vkey);
                            $stmt2->execute();
                            $result2 = $stmt2->get_result();
                            
                            if ($result2->num_rows == 1) {
                                $row = $result2->fetch_assoc();
                                if ($row['verified'] == 1) {
                                    header("Location: login.php?msg=verified");
                                    exit();
                                } else {
                                    echo "<div class='verify-error'>";
                                    echo "<div class='verify-icon'>✖</div>";
                                    echo "<h2>Invalid Key</h2>";
                                    echo "<p>The verification key is invalid. Please make sure you copied the entire link.</p>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<div class='verify-error'>";
                                echo "<div class='verify-icon'>✖</div>";
                                echo "<h2>Invalid Account</h2>";
                                echo "<p>No account was found with this verification key.</p>";
                                echo "</div>";
                            }
                        }
                    } else {
                        echo "<div class='verify-error'>";
                        echo "<div class='verify-icon'>✖</div>";
                        echo "<h2>Bad Request</h2>";
                        echo "<p>No verification key was provided in the URL.</p>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>
