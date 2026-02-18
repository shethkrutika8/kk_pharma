<?php include_once 'header.php'; ?>

<style>
.login-page {
    background: linear-gradient(to right, #2c3e50, #597ca7);
    min-height: calc(100vh - 70px);
    display: flex;
}

.image-section {
    width: 55%;
    background: url('https://images.unsplash.com/photo-1580281657527-47f249e8f4df') center/cover no-repeat;
}

.image-overlay {
    background: rgba(44, 62, 80, 0.75);
    height: 100%;
    padding: 60px;
    color: white;
}

.form-section {
    width: 45%;
    background: #ecf0f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-box {
    width: 380px;
    background: #fff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.login-box h2 {
    text-align: center;
    color: #2c3e50;
}

.error {
    color: red;
    font-size: 13px;
}

@media(max-width: 992px) {
    .image-section {
        display: none;
    }
    .form-section {
        width: 100%;
    }
}
</style>

<div class="login-page">

    <div class="image-section">
        <div class="image-overlay">
            <h1>KK Pharma</h1>
            <p>Trusted Pharmaceutical Solutions<br>
               Quality • Safety • Innovation</p>
        </div>
    </div>

    <div class="form-section">
        <div class="login-box">

            <h2>Login</h2>
            <p class="text-center mb-4">Access your KK Pharma account</p>

            <form id="loginForm" action="login.php" method="post">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email">
                    <span id="email_error" class="error"></span>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="pass" name="pass">
                    <span id="pass_error" class="error"></span>
                </div>

                <button type="submit" class="btn btn-dark w-100">Login</button>

            </form>

            <div class="mt-3 text-center">
                <a href="forgot_password.php">Forgot Password?</a><br>
                Don’t have an account? <a href="register.php">Register</a>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {

    $("#loginForm").on("submit", function(e) {

        let isValid = true;
        $(".error").text("");

        let email = $("#email").val().trim();
        let password = $("#pass").val().trim();
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email === "") {
            $("#email_error").text("Email is required");
            isValid = false;
        } else if (!emailPattern.test(email)) {
            $("#email_error").text("Enter valid email");
            isValid = false;
        }

        if (password === "") {
            $("#pass_error").text("Password is required");
            isValid = false;
        }

        if (!isValid) e.preventDefault();
    });

});
</script>

</body>
</html>
