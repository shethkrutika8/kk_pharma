$(document).ready(function () {

    function validateInput(field) {

        let value = field.val().trim();
        let name = field.attr("name");
        let errorField = $("#" + name + "_error");

        let rules = field.data("validation") ? field.data("validation").split(" ") : [];
        let min = field.data("min");
        let max = field.data("max");
        let match = field.data("match");

        let error = "";

        // REQUIRED
        if (rules.includes("required")) {
            if (value === "") {
                error = "This field is required";
            }
        }

        // EMAIL
        if (!error && rules.includes("email")) {
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(value)) {
                error = "Enter valid email address";
            }
        }

        // NUMBER ONLY
        if (!error && rules.includes("number")) {
            if (!/^[0-9]+$/.test(value)) {
                error = "Only numbers allowed";
            }
        }

        // MIN LENGTH
        if (!error && rules.includes("min")) {
            if (value.length < min) {
                error = "Minimum " + min + " characters required";
            }
        }

        // MAX LENGTH
        if (!error && rules.includes("max")) {
            if (value.length > max) {
                error = "Maximum " + max + " characters allowed";
            }
        }

        // SELECT VALIDATION
        if (!error && rules.includes("select")) {
            if (value === "") {
                error = "Please select an option";
            }
        }

        // STRONG PASSWORD
        if (!error && rules.includes("strongPassword")) {
            let strongPass = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{6,}$/;
            if (!strongPass.test(value)) {
                error = "Password must contain uppercase, lowercase & number";
            }
        }

        // CONFIRM PASSWORD
        if (!error && rules.includes("confirmPassword")) {
            let original = $("#" + match).val();
            if (value !== original) {
                error = "Passwords do not match";
            }
        }

        // SHOW ERROR OR SUCCESS
        if (error !== "") {
            field.addClass("is-invalid").removeClass("is-valid");
            errorField.text(error).show();
            return false;
        } else {
            field.removeClass("is-invalid").addClass("is-valid");
            errorField.hide();
            return true;
        }
    }

    // Validate on blur
    $("form input, form select, form textarea").on("blur", function () {
        validateInput($(this));
    });

    // Validate on submit
    $("form").on("submit", function (e) {

        let isValid = true;

        $(this).find("input, select, textarea").each(function () {
            if (!validateInput($(this))) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });

});
