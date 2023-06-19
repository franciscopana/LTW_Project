
const currentSearchParams = new URLSearchParams((location.href.split("?")[1] ?? ''));
var offset = currentSearchParams.get("offset") ?? 0;
var end = false;
var fetchingUsers = false;
const limit = 10;

function isOnScreen(element) {
    const rect = element.getBoundingClientRect();
    return window.innerHeight > rect.top && rect.top >= 0;

}


function drawNewRow(jsonObject) {
    const element = document.querySelector(".user-table-body");
    const tr = document.createElement("tr");
    const date = new Date(jsonObject["createdAt"] * 1000);
    tr.classList.add("user-entry");
    tr.innerHTML = `                  
    <td class="user-info">
        <img class="user-photo" src="${jsonObject["image"]}" alt="user">
        <div class="user-name">
            <p>${jsonObject["displayName"]}</p>
            <p>${jsonObject["username"]}</p>
        </div>
    </td>
    <td>
        <a href="mailto:${jsonObject["email"]}" >${jsonObject["email"]}</a>
    </td>
    <td>
        <p class="role ${jsonObject["type"]}">${jsonObject["type"].charAt(0).toUpperCase() + jsonObject["type"].slice(1)}</p>
    </td>
    <td>
        <p>${date.getHours().toString().padStart(2, "0") + ":" + date.getMinutes().toString().padStart(2, "0") + " " + date.getDate().toString().padStart(2, "0") + "/" + (date.getMonth() + 1).toString().padStart(2, "0") + "/" + date.getFullYear().toString().padStart(2, "0")}</p>
    </td>
    <td>
        <i class="ri-edit-line icon" onclick="makeEditModal('${jsonObject["username"]}')"></i>
    </td>
    <td>
        <i class="ri-delete-bin-line icon" style="color: var(--delete-color)" onclick="makeDeleteModal('${jsonObject["username"]}')"></i>
    </td>`
    element.appendChild(tr);


}

const getNewTableData = async (ev) => {
    if (ev.deltaY < 0) return;
    if (end) return;
    const element = document.querySelector(".user-table-body")
    if (element === null) return;
    if (element.lastElementChild === null) return;
    if (isOnScreen(element.lastElementChild) && !fetchingUsers) {
        fetchingUsers = true;
        console.log("fetching new user data...");
        //fetch
        const sortBy = currentSearchParams.get("sort");
        const res = await fetch(`/api/clients?limit=10&offset=${offset + element.children.length}${sortBy !== null ? "&sort=" + sortBy : ''}`,
            { method: "GET" });

        if (res.status !== 200) {
            console.log(`Users list request failed with status ${res.status}`);
        }
        const resJson = await res.json();
        if (resJson.length === 0) {
            end = true;
            return;
        }
        //draw
        resJson.forEach(drawNewRow);
        fetchingUsers = false;

    }
};
document.addEventListener("scroll", getNewTableData);



function makeDeleteModal(username) {
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.createElement("div");
    modalElement.classList.add("modal");
    modalElement.onclick = (event) => {
        if (event.target === modalElement) closeModal();
    }

    const modalContentElement = document.createElement("div");
    modalContentElement.classList.add("modal-content");
    modalContentElement.classList.add("delete-user-modal");

    modalElement.appendChild(modalContentElement);
    body.appendChild(modalElement);

    //TODO: inject CSRF token

    modalContentElement.innerHTML = `
    <h1>Delete user</h1>
    <p>Are you sure that you want to delete user <b>${username}</b>? <br/> This action is irreversible!</p>
    <div class="modal-buttons">
        <button class="cancel-button" onclick="closeModal()"><p>Cancel</p></button>
        <form method="post" action="admin">
            <input type="hidden" name="action" value="deleteUser">
            <input type="hidden" name="username" value="${username}">
            <input type="hidden" name="lastHref" value="${location.href}">


            <input type="submit" class="delete-button" value="Delete">
        </form>
        </div>`;
    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}

async function makeEditModal(username) {
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const res = await fetch(`/api/clients/${username}`, { method: "GET" });

    if (res.status !== 200) {
        console.log(`failed to get ${username} data... with status ${res.status}`);
        return;
    }

    const resJson = await res.json();

    const modalElement = document.createElement("div");
    modalElement.classList.add("modal");
    modalElement.onclick = (event) => {
        if (event.target === modalElement) closeModal();
    }

    const modalContentElement = document.createElement("div");
    modalContentElement.classList.add("modal-content");
    modalContentElement.classList.add("edit-user-modal");

    modalElement.appendChild(modalContentElement);
    body.appendChild(modalElement);

    //TODO: get csrf token

    modalContentElement.innerHTML = `
        <h2>Edit user</h2>
        <div class="main-edit-content">
            <div class="image-username">
                <img class="user-photo" src="${resJson["image"]}" alt="user">
                <p class="username">${resJson["username"]}</p>
            </div>
            <form name="editUserForm", action="admin" class="edit-user-form" method="post">
                <input type="hidden" name="action" value="editUser">
                <input type="hidden" name="username" value="${username}">
                <input type="hidden" name="lastHref" value="${location.href}">

                <label for="displayName">
                    <p>Display Name:</p>
                </label>
                <input type="text" name="displayName" value="${resJson["displayName"]}">
                <label for="email">
                    <p>Email:</p>
                </label>
                <input type="text" name="email" value="${resJson["email"]}">
                <label for="password">
                    <p>New password (single-time use):</p>
                </label>
                <input type="password" name="password">
                <label for="role">
                    <p>Role:</p>
                </label>
                <select name="role">
                    <option value="client" ${resJson["type"] === "client" ? "selected" : ""}>Client</option>
                    <option value="agent" ${resJson["type"] === "agent" ? "selected" : ""}>Agent</option>
                    <option value="admin" ${resJson["type"] === "admin" ? "selected" : ""}>Admin</option>
                </select>


                <div class="modal-buttons">
                    <input type="button" class="cancel-button" onclick="closeModal()" value="Cancel">
                    <input type="submit" class="primary" value="Save">

                </div>
            </form>
        </div>
    `;
    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}