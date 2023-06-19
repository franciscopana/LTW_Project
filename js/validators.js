

const passwordValidator = () => {
    const password = document.forms["resetPasswordForm"]["password"].value;
    const confirmPassword = document.forms["resetPasswordForm"]["confirmPassword"];

    if (password !== confirmPassword.value) {
        if (document.querySelector("p.error") === null) {
            const p = document.createElement("p");
            p.textContent = "Your passwords don't match";
            p.classList.add("error");

            document.forms["resetPasswordForm"].insertBefore(p, document.forms["resetPasswordForm"]["submitButton"]);
        }

        return false;
    }
}
