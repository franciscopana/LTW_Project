function snackbar(message, status = "default", duration = 4000) {
    //status: error, success, warning, info, default
    const icons = {
        error: 'close-circle',
        success: 'checkbox-circle',
        warning: 'error-warning',
        info: 'information',
        default: 'info'
    }

    const existingSnackbar = document.querySelector(".snackbar");
    if (existingSnackbar !== null) {
        existingSnackbar.remove();
    }

    const body = document.querySelector("body");
    const snackbar = document.createElement("div");
    snackbar.className = "snackbar";
    snackbar.innerHTML = `
        <i class="ri-${icons[status]}-line"></i>
        <p>${message}</p>
    `;
    snackbar.classList.add(status)
    body.appendChild(snackbar);

    // After 3 seconds, remove the show class from DIV and hide it with animation
    snackbar.style.display = "absolute";
    snackbar.style.opacity = 0;
    snackbar.style.right = "-100%";
    snackbar.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible", right: "0%" },
    ], { duration: 300, iterations: 1 }).onfinish = (event) => {
        snackbar.style.opacity = 1;
        snackbar.style.right = "2rem";
        setTimeout(() => {
            snackbar.remove();
        }, duration);
    }
}