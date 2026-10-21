<?php include_once 'Database/db_config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Images</title>
    <style>
        img { max-width: 300px; margin: 10px; border: 2px solid #ccc; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Image Path Testing</h1>
    
    <div class="test-section">
        <h2>Direct File Tests</h2>
        <h3>gallary/front_!.png</h3>
        <img src="gallary/front_!.png" alt="Front Image">
        
        <h3>gallary/medicine-2.jpg</h3>
        <img src="gallary/medicine-2.jpg" alt="Medicine Image">
        
        <h3>gallary/kk pahrma.png</h3>
        <img src="gallary/kk pahrma.png" alt="KK Pharma Logo">
    </div>
    
    <div class="test-section">
        <h2>get_image() Function Tests</h2>
        <h3>get_image('home', 'carousel1', 'gallary/front_!.png')</h3>
        <p>Function returns: <?php echo get_image('home', 'carousel1', 'gallary/front_!.png'); ?></p>
        <img src="<?php echo get_image('home', 'carousel1', 'gallary/front_!.png'); ?>" alt="Carousel 1">
        
        <h3>get_image('home', 'carousel2', 'gallary/medicine-2.jpg')</h3>
        <p>Function returns: <?php echo get_image('home', 'carousel2', 'gallary/medicine-2.jpg'); ?></p>
        <img src="<?php echo get_image('home', 'carousel2', 'gallary/medicine-2.jpg'); ?>" alt="Carousel 2">
    </div>
    
    <div class="test-section">
        <h2>File Existence Checks</h2>
        <?php
        $test_files = ['gallary/front_!.png', 'gallary/medicine-2.jpg', 'gallary/kk pahrma.png'];
        foreach ($test_files as $file) {
            echo "<p><strong>$file</strong>: " . (file_exists($file) ? "✓ EXISTS" : "❌ NOT FOUND") . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>Database Images</h2>
        <?php
        $images_query = mysqli_query($con, "SELECT * FROM site_images WHERE page_name = 'home'");
        if (mysqli_num_rows($images_query) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Key</th><th>Path</th><th>Image</th></tr>";
            while ($row = mysqli_fetch_assoc($images_query)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['image_key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['image_path']) . "</td>";
                echo "<td><img src='" . htmlspecialchars($row['image_path']) . "' width='100'></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No images found in database for home page</p>";
        }
        ?>
    </div>
</body>
</html>
