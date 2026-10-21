<?php
include_once 'Database/db_config.php';

echo "<h2>Debug Image Function</h2>";

// Check if site_images table exists and has data
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'site_images'");
if (mysqli_num_rows($table_check) > 0) {
    echo "<p>✓ site_images table exists</p>";
    
    // Show all images in the database
    $images_query = mysqli_query($con, "SELECT * FROM site_images");
    echo "<h3>Images in database:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Page</th><th>Key</th><th>Path</th><th>Alt Text</th></tr>";
    
    while ($row = mysqli_fetch_assoc($images_query)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['page_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['image_key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['image_path']) . "</td>";
        echo "<td>" . htmlspecialchars($row['alt_text']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ site_images table does not exist</p>";
}

echo "<h3>Testing get_image function:</h3>";
$test_result = get_image('home', 'carousel1', 'default.png');
echo "<p>get_image('home', 'carousel1', 'default.png') returns: " . htmlspecialchars($test_result) . "</p>";

// Check if the default file exists
$default_file = 'gallary/front_!.png';
if (file_exists($default_file)) {
    echo "<p>✓ Default file exists: " . htmlspecialchars($default_file) . "</p>";
} else {
    echo "<p>❌ Default file does not exist: " . htmlspecialchars($default_file) . "</p>";
    echo "<p>Current working directory: " . getcwd() . "</p>";
    
    // List files in gallary directory
    if (is_dir('gallary')) {
        echo "<p>Files in gallary directory:</p>";
        $files = scandir('gallary');
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "<p>- " . htmlspecialchars($file) . "</p>";
            }
        }
    } else {
        echo "<p>❌ gallary directory does not exist</p>";
    }
}
?>
