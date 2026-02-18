<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | KK Pharma</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, #2c3e50, #4b6584);
}

/* Layout */
.main-wrapper {
    display: flex;
    min-height: 100vh;
}

/* Left Image Section */
.image-section {
    width: 55%;
    background: url('https://images.unsplash.com/photo-1580281657527-47f249e8f4df') center/cover no-repeat;
    position: relative;
}

.image-overlay {
    background: rgba(44, 62, 80, 0.75);
    height: 100%;
    padding: 60px;
    color: white;
}

.image-overlay h1 {
    font-size: 36px;
    margin-bottom: 15px;
}

.image-overlay p {
    font-size: 18px;
    line-height: 1.6;
}

/* Right Form Section */
.form-section {
    width: 45%;
    background: #ecf0f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    width: 380px;
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
}

.container h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 10px;
}

.info {
    text-align: center;
    font-size: 14px;
    color: #666;
    margin-bottom: 25px;
}

label {
    font-weight: bold;
    color: #2c3e50;
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 12px;
    background: #2c3e50;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #1a252f;
}

.error {
    color: red;
    font-size: 13px;
}

.success {
    color: green;
    text-align: center;
    margin-top: 10px;
}

.hidden {
    display: none;
}

.links {
    text-align: center;
    margin-top: 20px;
}

.links a {
    color: #2c3e50;
    font-weight: bold;
    text-decoration: none;
}

/* Mobile Responsive */
@media(max-width: 900px) {
    .image-section {
        display: none;
    }
    .form-section {
        width: 100%;
    }
}
</style>
</head>

<body>

<div class="main-wrapper">

    <!-- LEFT IMAGE / BRAND SECTION -->
    <div class="image-section">
        <div class="image-overlay">
            <h1>KK Pharma</h1>
            <p>
                Trusted Pharmaceutical Solutions <br>
                Quality Medicines • Innovation • Care
            </p>
        </div>
    </div>

    <!-- RIGHT FORM SECTION -->
    <div class="form-section">
        <div class="container">

            <h2>Forgot Password</h2>
            <p class="info">Reset your password using mobile OTP</p>

            <!-- Step 1 -->
            <form id="mobileForm">
                <label>Mobile Number</label>
                <input type="text" id="mobile" placeholder="Enter registered mobile number">
                <div class="error" id="mobileError"></div>

                <button type="submit">Send OTP</button>
            </form>

            <!-- Step 2 -->
            <form id="otpForm" class="hidden">
                <label>Enter OTP</label>
                <input type="text" id="otp" placeholder="Enter OTP">
                <div class="error" id="otpError"></div>

                <button type="submit">Verify OTP</button>
            </form>

            <div class="success" id="successMsg"></div>

            <div class="links">
                <a href="login.php">← Back to Login</a>
            </div>

        </div>
    </div>

</div>

<script>
$(document).ready(function () {

    let generatedOTP = "";

    $("#mobileForm").submit(function (e) {
        e.preventDefault();
        let mobile = $("#mobile").val().trim();
        $("#mobileError").text("");

        if (!/^[6-9]\d{9}$/.test(mobile)) {
            $("#mobileError").text("Enter valid 10-digit mobile number");
            return;
        }

        generatedOTP = Math.floor(100000 + Math.random() * 900000);
        console.log("OTP (Demo): " + generatedOTP);

        $("#mobileForm").hide();
        $("#otpForm").show();
        $("#successMsg").text("OTP sent to your registered mobile number");
    });

    $("#otpForm").submit(function (e) {
        e.preventDefault();
        let otp = $("#otp").val().trim();
        $("#otpError").text("");

        if (otp !== generatedOTP.toString()) {
            $("#otpError").text("Invalid OTP");
            return;
        }

        $("#successMsg").text("OTP verified successfully. Redirecting...");
        $("#otpForm").hide();
    });

});
</script>

</body>
</html>
