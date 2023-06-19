function passwordsMatch(idPassword1, idPassword2) {
    var password = document.getElementById(idPassword1);
    var confirm_password = document.getElementById(idPassword2);
    var form = document.getElementsByTagName("form")[0];

    var regex = new RegExp("(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})");

    if (password.value != confirm_password.value) {
        confirm_password.setCustomValidity("Passwords don't match");
    } else if (!regex.test(password.value)) {
        password.setCustomValidity("The password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number and one special character");
    } else {
        password.setCustomValidity('');
        form.submit();
    }
}

function showPasswordsRegister() {
    var password = document.getElementById("password");
    var confirm_password = document.getElementById("confirmPassword");

    if (password.type === "password") {
        password.type = "text";
        confirm_password.type = "text";
    } else {
        password.type = "password";
        confirm_password.type = "password";
    }
}

function togglePasswordVisibility(inputId) {
    var input = document.getElementById(inputId);
    var icon = document.querySelector("#" + inputId + " + .password-toggle-icon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("ri-eye-line");
        icon.classList.add("ri-eye-off-line");
    } else {
        input.type = "password";
        icon.classList.remove("ri-eye-off-line");
        icon.classList.add("ri-eye-line");
    }
}