<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Footer</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background: linear-gradient(90deg, #2c3e50, #1f2f3f);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand img {
            height: 40px;
        }

        .nav-link {
            color: #fff !important;
            margin-left: 18px;
            font-weight: 500;
            white-space: nowrap;
            /* keep links in one line */
        }

        .nav-link:hover {
            color: #00d1b2 !important;
        }

        /* Force single row layout */
        .navbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #1f2f3f;
            color: #fff;
        }

        footer img {
            height: 40px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER / NAVBAR (Single Line) ===== -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">

            <!-- Logo + Name -->
            <a class="navbar-brand d-flex align-items-center text-white" href="#">
                <img src="gallary/kk pahrma.png" class="me-2">
                KK Pharma
            </a>

            <!-- Menu in single line -->
            <div class="d-flex">
                <a class="nav-link" href="Home.php">Home</a>
                <a class="nav-link" href="about.php">About</a>
                <a class="nav-link" href="gallery.php">Gallery</a>
                <a class="nav-link" href="contact.php">Contact</a>
                <a class="nav-link" href="login.php">Login</a>
                <a class="nav-link" href="register.php">Register</a>
            </div>

        </div>
    </nav>


    <!-- ===== FOOTER ===== -->
    <footer class="text-center py-4 mt-5">
        <img src="gallary/kk pahrma.png">
        <p class="mb-0">© 2026 KK Pharma. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>