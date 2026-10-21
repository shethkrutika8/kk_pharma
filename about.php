<?php
include_once("header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | KK Pharmaceutical</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #eef2f6;
        }
    
        /* HERO */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 80px;
            background: white;
        }

        .hero-text h2 {
            font-size: 40px;
            margin-bottom: 20px;
        }

        .hero-text p {
            font-size: 18px;
            line-height: 1.8;
            color: #555;
        }

        .hero-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .timeline {
            max-width: 1100px;
            margin: 80px auto;
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            width: 4px;
            height: 100%;
            background: #e74c3c;
        }

        .timeline-box {
            width: 45%;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            margin-bottom: 60px;
            position: relative;
            background: white;
        }

        .timeline-box.left {
            left: 0;
        }

        .timeline-box.right {
            left: 55%;
        }

        .timeline-box::after {
            content: '';
            position: absolute;
            top: 30px;
            width: 20px;
            height: 20px;
            background: #e74c3c;
            border-radius: 50%;
        }

        .timeline-box.left::after {
            right: -50px;
        }

        .timeline-box.right::after {
            left: -50px;
        }

        .timeline-box h3 {
            margin-bottom: 12px;
            font-size: 24px;
        }

        .timeline-box p {
            line-height: 1.7;
            color: #555;
        }

        .values {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 80px;
            color: white;
        }

        .values h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
        }

        .value-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .value-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            transition: 0.4s;
        }

        .value-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.2);
        }

        .value-card span {
            font-size: 36px;
        }

        .value-card h4 {
            margin: 15px 0 10px;
        }

        @media(max-width:900px) {
            .hero {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .timeline::before {
                left: 20px;
            }

            .timeline-box {
                width: 90%;
                left: 40px !important;
            }

            .timeline-box::after {
                left: -40px !important;
            }

            .value-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- HERO -->
    <section class="hero">
        <div class="hero-text">
            <h2><?php echo htmlspecialchars(get_content('about_hero_title', 'Advancing Healthcare with Trust & Innovation')); ?></h2>
            <p><?php echo htmlspecialchars(get_content('about_hero_desc', 'KK Pharmaceutical is a trusted name in the pharmaceutical industry...')); ?></p>
            
            <section class="mission">
                <h2><?php echo htmlspecialchars(get_content('about_section2_title', 'Our Mission, Vision & Commitment')); ?></h2>

                <div class="mission-grid">
                    <div class="mission-card">
                        <h3><?php echo htmlspecialchars(get_content('about_mission_title', '🎯 Our Mission')); ?></h3>
                        <p><?php echo htmlspecialchars(get_content('about_mission_desc', 'To develop, manufacture, and deliver high-quality pharmaceutical products...')); ?></p>
                    </div>

                    <div class="mission-card">
                        <h3><?php echo htmlspecialchars(get_content('about_vision_title', '👁 Our Vision')); ?></h3>
                        <p><?php echo htmlspecialchars(get_content('about_vision_desc', 'To be a globally trusted pharmaceutical company recognized for innovation...')); ?></p>
                    </div>

                    <div class="mission-card">
                        <h3><?php echo htmlspecialchars(get_content('about_commitment_title', '🤝 Our Commitment')); ?></h3>
                        <p><?php echo htmlspecialchars(get_content('about_commitment_desc', 'We are committed to patient safety, regulatory compliance...')); ?></p>
                    </div>
                </div>
            </section>

        </div>
        <div class="hero-image">
            <img src="gallary/largest-pharmaceutical-companies.jpg.webp">
        </div>
    </section>

    <!-- TIMELINE -->
    <section class="timeline">
        <div class="timeline-box left">
            <h3><?php echo htmlspecialchars(get_content('about_timeline1_title', '💊 Healthcare Leadership')); ?></h3>
            <p><?php echo htmlspecialchars(get_content('about_timeline1_desc', 'Our state-of-the-art manufacturing facilities and strict quality control...')); ?></p>
        </div>

        <div class="timeline-box right">
            <h3><?php echo htmlspecialchars(get_content('about_timeline2_title', '🌍 Global Presence')); ?></h3>
            <p><?php echo htmlspecialchars(get_content('about_timeline2_desc', 'KK Pharmaceutical continues to expand its global footprint...')); ?></p>
        </div>
    </section>

    <!-- VALUES -->
    <section class="values">
        <h2><?php echo htmlspecialchars(get_content('about_values_title', 'Our Core Values')); ?></h2>
        <div class="value-grid">
            <div class="value-card">
                <span><?php echo htmlspecialchars(get_content('about_value1_icon', '✔')); ?></span>
                <h4><?php echo htmlspecialchars(get_content('about_value1_title', 'Quality')); ?></h4>
                <p><?php echo htmlspecialchars(get_content('about_value1_desc', 'Uncompromised product excellence')); ?></p>
            </div>

            <div class="value-card">
                <span><?php echo htmlspecialchars(get_content('about_value2_icon', '❤️')); ?></span>
                <h4><?php echo htmlspecialchars(get_content('about_value2_title', 'Patient Focus')); ?></h4>
                <p><?php echo htmlspecialchars(get_content('about_value2_desc', 'Healthcare solutions that matter')); ?></p>
            </div>

            <div class="value-card">
                <span><?php echo htmlspecialchars(get_content('about_value3_icon', '⚙')); ?></span>
                <h4><?php echo htmlspecialchars(get_content('about_value3_title', 'Innovation')); ?></h4>
                <p><?php echo htmlspecialchars(get_content('about_value3_desc', 'Research-driven growth')); ?></p>
            </div>

            <div class="value-card">
                <span><?php echo htmlspecialchars(get_content('about_value4_icon', '🤝')); ?></span>
                <h4><?php echo htmlspecialchars(get_content('about_value4_title', 'Integrity')); ?></h4>
                <p><?php echo htmlspecialchars(get_content('about_value4_desc', 'Ethical & transparent practices')); ?></p>
            </div>
        </div>
    </section>

    <?php include_once("footer.php"); ?>

</body>
</html>