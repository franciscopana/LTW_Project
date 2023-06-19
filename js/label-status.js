function addBorderTag(){
    const tags = document.querySelectorAll(".tag");
    tags.forEach((element) => {
        if(element.style.backgroundColor === "rgb(255, 255, 255)"){
            element.style.border = "1px solid #e8e8e9";
        }
    })
}

function rerenderPreview(ev) {
    const modalForm = document.forms["modalForm"];
    if(modalForm === undefined);
    const name = modalForm.name.value;
    const backgroundColor = modalForm.backgroundColor.value;
    const color = modalForm.color.value;

    const preview = document.querySelector(".preview");

    preview.style.backgroundColor = backgroundColor;
    preview.style.color = color;
    preview.textContent = name;

    addBorderTag();


}

function makeNewModal(action, title){
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.querySelector(".modal");
    if (modalElement === null) return;

    const modalContentElement = document.querySelector(".modal-content");
    modalContentElement.classList.toggle("make-new-modal");
    if (modalContentElement === null) return;

    //TODO: inject CSRF token
    modalContentElement.innerHTML = `
    <h1>${title}</h1>
    <form method="post" id="modalForm">
        <input type="hidden" name="action" value="${action}">
        <label for="name">
            <p>Name:</p>
        </label>
        <input type="text" name="name" placeholder="Name" required>
        <label for="color">
            <p>Text color:</p>
        </label>
        <input type="color" name="color" value="#ffffff">
        <label for="backgroundColor">
            <p>Background color:</p>
        </label>
        <input type="color" name="backgroundColor" value="#000000">

    </form>
    <p>Preview:</p>
    <div class="preview tag"></div>
    <div class="modal-buttons">
        <button class="cancel-button" onclick="closeModal()">Cancel</button>
        <button type="submit" form="modalForm" class="cancel-button primary">Add</button>
    </div>`;

    document.querySelectorAll("#modalForm > input")
        .forEach(element => element.addEventListener("input", rerenderPreview));

    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}

const rgb2hex = (rgb) => `#${rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/).slice(1).map(n => parseInt(n, 10).toString(16).padStart(2, '0')).join('')}`

function makeEditModal(action, element){
    const name = element.textContent.trim();
    const color = rgb2hex(element.style.color);
    const backgroundColor = rgb2hex(element.style.backgroundColor);
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.querySelector(".modal");
    if (modalElement === null) return;

    const modalContentElement = document.querySelector(".modal-content");
    modalContentElement.classList.toggle("make-new-modal");
    if (modalContentElement === null) return;

    const deleteAction = action === "editStatus" ? "deleteStatus" : "deleteLabel"

    //TODO: inject CSRF token
    modalContentElement.innerHTML = `
    <h1>Edit label/status</h1>
    <form method="post" id="modalForm">
        <input type="hidden" name="action" value="${action}">
        <p>Name:</p>
        <p class="label-status-name">${name}</p>
        <input type="hidden" name="name" value="${name}">
        <label for="color">
            <p>Text color:</p>
        </label>
        <input type="color" name="color" value="${color}">
        <label for="backgroundColor">
            <p>Background color:</p>
        </label>
        <input type="color" name="backgroundColor" value="${backgroundColor}">

    </form>
    <p>Preview:</p>
    <div class="preview tag"></div>
    <div class="modal-buttons">
        <button class="cancel-button" onclick="closeModal()">Cancel</button>
        <form method="post">
            <input type="hidden" name="action" value="${deleteAction}">
            <input type="hidden" name="name" value="${name}">
            <button type="submit" class="delete-button">Delete</button>
        </form>
        <button type="submit" form="modalForm" class="primary">Edit</button>
    </div>`;

    document.querySelectorAll("#modalForm > input")
        .forEach(element => element.addEventListener("input", rerenderPreview));

    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}


document.addEventListener("DOMContentLoaded", () => {
    addBorderTag();
})