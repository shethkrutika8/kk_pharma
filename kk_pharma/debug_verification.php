<?php
include_once 'Database/db_config.php';

echo "<h2>Debug Verification</h2>";

// Check all users in the register table
echo "<h3>All Users in Database:</h3>";
$result = $con->query("SELECT id, first_name, last_name, email, verified, vkey FROM register ORDER BY id DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Verified</th><th>VKey</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . ($row['verified'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . htmlspecialchars($row['vkey']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in the database.</p>";
}

// Test verification with a sample key
echo "<h3>Test Verification:</h3>";
if (isset($_GET['test_vkey'])) {
    $test_vkey = $_GET['test_vkey'];
    echo "<p>Testing with key: " . htmlspecialchars($test_vkey) . "</p>";
    
    $stmt = $con->prepare("SELECT verified, vkey FROM register WHERE vkey = ? LIMIT 1");
    $stmt->bind_param("s", $test_vkey);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<p>Query result count: " . $result->num_rows . "</p>";
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        echo "<p>User found! Verified status: " . ($row['verified'] ? 'Yes' : 'No') . "</p>";
    } else {
        echo "<p>No user found with this verification key.</p>";
    }
    $stmt->close();
}

echo "<h3>Manual Verification Test:</h3>";
echo "<form method='get'>";
echo "<input type='text' name='test_vkey' placeholder='Enter verification key to test'>";
echo "<button type='submit'>Test</button>";
echo "</form>";

$con->close();
?>
