<?php
include_once("header.php");
// db_config is already included in header.php but we can leave it if we want.
// Since header.php includes it, get_content is available.
$statusMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    if (isset($con)) {
        $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $subject = mysqli_real_escape_string($con, $_POST['subject']);
        $message = mysqli_real_escape_string($con, $_POST['message']);

        $query = "INSERT INTO contact_inquiries (firstname, email, subject, message) VALUES ('$firstname', '$email', '$subject', '$message')";
        if (mysqli_query($con, $query)) {
            $statusMessage = "<div style='color:green; padding:10px; background:#e8f5e9; border-radius:5px; margin-bottom:15px;'>Thank you! Your inquiry has been submitted successfully.</div>";
        } else {
            $statusMessage = "<div style='color:red; padding:10px; background:#ffebee; border-radius:5px; margin-bottom:15px;'>Error: Could not submit your inquiry. Please try again.</div>";
        }
    } else {
        $statusMessage = "<div style='color:red; padding:10px; background:#ffebee; border-radius:5px; margin-bottom:15px;'>Database connection error.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us | KK Pharmaceutical</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI";
        }

        body {
            background: #f4f6f8;
            color: #2c3e50;
        }

        .section {
            max-width: 1400px;
            margin: 50px auto;
            padding: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            margin-bottom: 15px;
        }

        .card p {
            line-height: 1.7;
            margin-bottom: 15px;
            color: #34495e;
        }

        .list li {
            margin-bottom: 10px;
            list-style: none;
        }

        form input,
        form textarea {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        textarea {
            resize: none;
            height: 130px;
        }

        button {
            background: #2c3e50;
            color: white;
            padding: 15px;
            font-size: 20px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background: #1f2d3a;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        @media(max-width:900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="section">

        <div class="grid">

            <div class="card">
                <h2><?php echo htmlspecialchars(get_content('contact_page_about_title', 'About KK Pharmaceutical')); ?></h2>
                <p><?php echo htmlspecialchars(get_content('contact_page_about_desc1', 'KK Pharmaceutical is a research-driven pharmaceutical company...')); ?></p>
                <p><?php echo htmlspecialchars(get_content('contact_page_about_desc2', 'Our operations comply with WHO-GMP standards...')); ?></p>

                <h3><?php echo htmlspecialchars(get_content('contact_page_corporate_title', '📍 Corporate Office')); ?></h3>
                <p><?php echo htmlspecialchars(get_content('contact_page_corporate_desc', 'Pharma Industrial Estate, India')); ?></p>

                <h3><?php echo htmlspecialchars(get_content('contact_page_hours_title', '🕒 Business Hours')); ?></h3>
                <ul class="list">
                    <li><?php echo htmlspecialchars(get_content('contact_hours1', 'Monday – Friday: 9:00 AM – 6:00 PM')); ?></li>
                    <li><?php echo htmlspecialchars(get_content('contact_hours2', 'Saturday: 9:00 AM – 2:00 PM')); ?></li>
                    <li><?php echo htmlspecialchars(get_content('contact_hours3', 'Sunday: Closed')); ?></li>
                </ul>
            </div>

            <div class="card">
                <h2>Contact Form</h2>

                <?php if(!empty($statusMessage)) echo $statusMessage; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" id="firstname" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <input type="text" name="subject" id="subject" placeholder="Subject" required>
                    <textarea name="message" id="message" placeholder="Write your message" required></textarea>
                    <button type="submit" name="submit_contact">Submit Inquiry</button>
                </form>
            </div>
        </div>

        <!-- DEPARTMENTS -->
        <div class="info-grid">
            <div class="card">
                <h3><?php echo htmlspecialchars(get_content('contact_dept1_title', '📞 Sales & Marketing')); ?></h3>
                <p><?php echo htmlspecialchars(get_content('contact_dept1_email', 'sales@kkpharma.com')); ?><br><?php echo htmlspecialchars(get_content('contact_dept1_phone', '+91 98765 11111')); ?></p>
            </div>

            <div class="card">
                <h3><?php echo htmlspecialchars(get_content('contact_dept2_title', '🧪 Medical & QA')); ?></h3>
                <p><?php echo htmlspecialchars(get_content('contact_dept2_email', 'medical@kkpharma.com')); ?><br><?php echo htmlspecialchars(get_content('contact_dept2_phone', '+91 98765 22222')); ?></p>
            </div>

            <div class="card">
                <h3><?php echo htmlspecialchars(get_content('contact_dept3_title', '📦 Distribution')); ?></h3>
                <p><?php echo htmlspecialchars(get_content('contact_dept3_email', 'distribution@kkpharma.com')); ?><br><?php echo htmlspecialchars(get_content('contact_dept3_phone', '+91 98765 33333')); ?></p>
            </div>

            <div class="card">
                <h3><?php echo htmlspecialchars(get_content('contact_dept4_title', '⚠️ Pharmacovigilance')); ?></h3>
                <p>
                    <?php echo htmlspecialchars(get_content('contact_dept4_desc', 'Report adverse drug reactions')); ?><br>
                    <?php echo htmlspecialchars(get_content('contact_dept4_email', 'safety@kkpharma.com')); ?>
                </p>
            </div>
        </div>

        <!-- FAQ -->
        <div class="card" style="margin-top:40px;">
            <h2><?php echo htmlspecialchars(get_content('contact_faq_title', 'Frequently Asked Questions')); ?></h2>
            <p><strong><?php echo htmlspecialchars(get_content('contact_faq1_q', 'Do you offer third-party manufacturing?')); ?></strong><br><?php echo htmlspecialchars(get_content('contact_faq1_a', 'Yes, we provide...')); ?></p>
            <p><strong><?php echo htmlspecialchars(get_content('contact_faq2_q', 'How can I become a distributor?')); ?></strong><br><?php echo htmlspecialchars(get_content('contact_faq2_a', 'Contact our sales department...')); ?></p>
            <p><strong><?php echo htmlspecialchars(get_content('contact_faq3_q', 'Are your products regulatory approved?')); ?></strong><br><?php echo htmlspecialchars(get_content('contact_faq3_a', 'Yes, all products comply...')); ?></p>
        </div>

    </div>

    <?php include_once("footer.php"); ?>

</body>
</html>
