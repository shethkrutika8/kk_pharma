<?php include_once 'header.php'; ?>

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

    .register-section {
        padding: 80px 0;
    }

    .register-wrapper {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    }

    .register-info {
        background: var(--primary);
        color: white;
        padding: 50px;
        height: 100%;
    }

    .register-info h2 {
        font-weight: 700;
        margin-bottom: 20px;
    }

    .register-info ul {
        padding-left: 18px;
        margin-top: 25px;
    }

    .register-info li {
        margin-bottom: 10px;
    }

    .register-form {
        padding: 50px;
    }

    .register-form h3 {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: var(--primary);
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        height: 45px;
    }

    textarea.form-control {
        height: 90px;
        resize: none;
    }

    /* RED BOX ERROR STYLE */
    .text-danger.small {
        color: #721c24 !important;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 5px 10px;
        border-radius: 4px;
        margin-top: 5px;
        display: none;
        /* Hidden until error exists */
        font-weight: 500;
    }

    .is-invalid {
        border: 1px solid #dc3545 !important;
    }

    .is-valid {
        border: 1px solid #28a745 !important;
    }

    .btn-register:hover {
        background: #16222e;
        transform: translateY(-2px);
    }

    .login-link {
        margin-top: 15px;
        text-align: center;
    }

    .login-link a {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
    }
</style>

<section class="register-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="register-wrapper">
                    <div class="row g-0">
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="register-info">
                                <h2>KK Pharma</h2>
                                <p>Join a trusted pharmaceutical company committed to quality healthcare and innovation.</p>
                                <ul>
                                    <li>✔ WHO-GMP Certified Manufacturing</li>
                                    <li>✔ Global Healthcare Standards</li>
                                    <li>✔ Research & Development Focused</li>
                                    <li>✔ Career Growth Opportunities</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="register-form">
                                <h3>Create Your Account</h3>
                                <form method="post" id="regform" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="fname" data-validation="required min max" data-min="2" data-max="20">
                                            <span id="fname_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="lname" data-validation="required min" data-min="2">
                                            <span id="lname_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" data-validation="required email">
                                            <span id="email_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Mobile</label>
                                            <input type="text" class="form-control" name="mobile" data-validation="required number min max" data-min="10" data-max="10">
                                            <span id="mobile_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Department</label>
                                            <select class="form-select" name="dept" data-validation="select">
                                                <option value="">Select</option>
                                                <option>Production</option>
                                                <option>Quality Control</option>
                                                <option>R&D</option>
                                                <option>Marketing</option>
                                            </select>
                                            <span id="dept_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="text" class="form-control" name="exp" data-validation="required number">
                                            <span id="exp_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="address" data-validation="required min" data-min="10"></textarea>
                                            <span id="address_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control" id="pass" name="pass" data-validation="required strongPassword">
                                            <span id="pass_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" name="cpass" data-validation="required confirmPassword" data-match="pass">
                                            <span id="cpass_error" class="text-danger small"></span>
                                        </div>

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-dark w-100 btn-register">Register Now</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="login-link">
                                    Already have an account? <a href="login.php">Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/layout.js"></script>

<?php include_once 'footer.php'; ?>