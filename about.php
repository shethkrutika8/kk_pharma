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

        /* boc no ::shadow batave mate */
        .hero-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* red colour ni border ne adjust karva mate  */
        .timeline {
            max-width: 1100px;
            margin: 80px auto;
            position: relative;
        }

        .timeline::before {
            content: '';
            position: absolute;
            /* position maintain karva mate */
            left: 50%;
            width: 4px;
            height: 100%;
            /* height full cover karva mate  */
            ;
            background: #e74c3c;
        }

        /* line pase je box che ene proper middle ma adjust karva mate  */
        .timeline-box {
            width: 45%;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            margin-bottom: 60px;
            position: relative;
        }

        .timeline-box.left {
            /* left side na box ne adjust karva mate  */
            left: 0;
        }

        .timeline-box.right {
            /* right side na box ne adjust karva mate  */
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
            /* round karva mate red */
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
            /*our core value ma content set karva mate */
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



        /* RESPONSIVE */
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

    <?php include_once("header.php"); ?>
    <!-- HERO -->
    <section class="hero">
        <div class="hero-text">
            <h2>Advancing Healthcare with Trust & Innovation</h2>
            <p>
                KK Pharmaceutical is a trusted name in the pharmaceutical industry,
                dedicated to manufacturing high-quality, affordable medicines.
                With decades of experience, we combine innovation, ethics,
                and patient-centric care to improve lives worldwide.
            </p>
            <section class="mission">
                <h2>Our Mission, Vision & Commitment</h2>

                <div class="mission-grid">
                    <div class="mission-card">
                        <h3>&#127919; Our Mission</h3>
                        <p>
                            To develop, manufacture, and deliver high-quality pharmaceutical
                            products that improve health outcomes and enhance the quality of life
                            for patients across all regions.
                        </p>
                    </div>

                    <div class="mission-card">
                        <h3>👁 Our Vision</h3>
                        <p>
                            To be a globally trusted pharmaceutical company recognized for
                            innovation, ethical practices, and affordable healthcare solutions.
                        </p>
                    </div>

                    <div class="mission-card">
                        <h3>🤝 Our Commitment</h3>
                        <p>
                            We are committed to patient safety, regulatory compliance, continuous
                            improvement, and sustainable healthcare development.
                        </p>
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
            <h3>💊 Healthcare Leadership</h3>
            <p>
                Our state-of-the-art manufacturing facilities and strict quality
                control systems ensure world-class pharmaceutical products
                that meet international standards.
            </p>
        </div>

        <div class="timeline-box right">
            <h3>🌍 Global Presence</h3>
            <p>
                KK Pharmaceutical continues to expand its global footprint,
                supplying essential medicines through strong international
                partnerships and distribution networks.
            </p>
        </div>

    </section>

    <!-- VALUES -->
    <section class="values">
        <h2>Our Core Values</h2>
        <div class="value-grid">
            <div class="value-card">
                <span>✔</span>
                <h4>Quality</h4>
                <p>Uncompromised product excellence</p>
            </div>

            <div class="value-card">
                <span>❤️</span>
                <h4>Patient Focus</h4>
                <p>Healthcare solutions that matter</p>
            </div>

            <div class="value-card">
                <span>⚙</span>
                <h4>Innovation</h4>
                <p>Research-driven growth</p>
            </div>

            <div class="value-card">
                <span>🤝</span>
                <h4>Integrity</h4>
                <p>Ethical & transparent practices</p>
            </div>
        </div>
    </section>

    <?php include_once("footer.php"); ?>

</body>

</html>