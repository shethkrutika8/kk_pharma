<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KK Pharma</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #1f2f3f;
            --secondary: #718497;
            --accent: #00d1b2;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(90deg, var(--primary), #16222e);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }

        .nav-link {
            color: #fff !important;
            margin-left: 18px;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-link:hover {
            color: var(--accent) !important;
        }

        .navbar-brand {
            font-size: 22px;
            letter-spacing: 1px;
        }

        .navbar-toggler {
            box-shadow: none !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand fw-bold" href="Home.php">KK Pharma</a>

        <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto text-center">
                <li class="nav-item"><a class="nav-link" href="Home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
            </ul>
        </div>

    </div>
</nav>
