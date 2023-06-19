
const currentSearchParams = new URLSearchParams((location.href.split("?")[1] ?? ''));
var offset = currentSearchParams.get("offset") ?? 0;
var end = false;
var fetchingDepartments = false;
const limit = 10;

function makeAddDepartmentModal() {
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.createElement("div");
    modalElement.classList.add("modal");
    modalElement.onclick = (event) => {
        if (event.target === modalElement) closeModal();
    }

    const modalContentElement = document.createElement("div");
    modalContentElement.classList.add("modal-content");
    modalContentElement.classList.add("add-new-department-modal");

    modalElement.appendChild(modalContentElement);
    body.appendChild(modalElement);

    modalContentElement.innerHTML = `
    <h1>Add new department</h1>
    <form method="post">
        <input type="hidden" name="action" value="newDepartment">
        <input type="hidden" name="lastHref" value="${location.href}">


        <label for="name">
            <p>Name:</p>
        </label>
        <input type="text" name="name" required>
        <label for="description">
            <p>Description:</p>
        </label>
        <input type="hidden" name=members"> 
        <textarea type="text" rows="10" name="description" required></textarea> 
        <div class="modal-buttons">
            <input type="button" class="button cancel" onclick="closeModal()" value="Cancel">
            <input type="submit" class="button primary" value="Create">
        </div>
    </form>
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


function makeDeleteModal(department) {
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
    <h1>Delete department</h1>
    <p>Are you sure that you want to delete department <b>${department}</b>? <br/> This action is irreversible!</p>
    <div class="modal-buttons">
        <button class="button cancel" onclick="closeModal()"><p>Cancel</p></button>
        <form method="post" action="admin">
            <input type="hidden" name="action" value="deleteDepartment">
            <input type="hidden" name="name" value="${department}">
            <input type="hidden" name="lastHref" value="${location.href}">


            <input type="submit" class="button delete" value="Delete">
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


async function makeEditModal(department) {

    const res = await fetch(`/api/departments/${department}`, { method: "GET" });

    if (res.status !== 200) {
        console.log(`failed to get ${department} data... with status ${res.status}`);
        return;
    }

    const resJson = await res.json();

    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.createElement("div");
    modalElement.classList.add("modal");
    modalElement.onclick = (event) => {
        if (event.target === modalElement) closeModal();
    }

    const modalContentElement = document.createElement("div");
    modalContentElement.classList.add("modal-content");
    modalContentElement.classList.add("add-new-department-modal");

    modalElement.appendChild(modalContentElement);
    body.appendChild(modalElement);

    modalContentElement.innerHTML = `
    <h1>Edit department</h1>
    <form method="post">
        <input type="hidden" name="action" value="editDepartment">
        <input type="hidden" name="lastHref" value="admin?tab=departments">

        <label for="name">
            <p>Name:</p>
        </label>
        <input type="text" name="name" required value=${resJson["name"]} readonly>
        <label for="description">
            <p>Description:</p>
        </label>
        <input type="hidden" name=members"> 
        <textarea type="text" rows="10" name="description" required>${resJson["description"]}</textarea> 
        <div class="modal-buttons">
            <input type="button" class="button cancel" onclick="closeModal()" value="Cancel">
            <input type="submit" class="button primary" value="Save">

        </div>
    </form>
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

function isOnScreen(element) {
    const rect = element.getBoundingClientRect();
    return window.innerHeight > rect.top && rect.top >= 0;

}

function drawNewRow(department) {
    const element = document.querySelector(".department-tbody");
    const tr = document.createElement("tr");

    tr.classList.add("department-entry");

    tr.innerHTML = `
        <td>
            <p class="department-name">${department.name}</p>
        </td>
        <td>
            <p class="department-description">${department.description}</p>
        </td>
        <td>
            <div class="department-members">
            ${department.clients.length === 0 ? "0 members" : "2"}
            <header>
                ${department.clients
            .map(
                (member) =>
                    `<img src="${member.image}" alt="${member.displayName}" class="department-member">`
            )
            .join("")}
            </header>
            ${department.clients.length !== 0
            ? `<ul>
                    ${department.clients
                .map(
                    (member) => `
                        <li>
                            <div>
                                <img src="${member.image}" alt="${member.displayName}" class="department-member">
                                <p>${member.displayName}</p>
                            </div>
                            <form method="post" action="/admin">
                                <input type="hidden" name="department" value="${department.name}">
                                <input type="hidden" name="user" value="${member.username}">
                                <input type="hidden" name="action" value="removeMember">
                                <input type="hidden" name="lastHref" value="/admin?tab=departments">
                                <i class="ri-close-line" onclick="submitFatherForm(this)"></i>
                            </form>
                        </li>
                        `
                )
                .join("")}
                    </ul>`
            : ""
        }
            </div>
        </td>
        <td>
            <button class="white" onclick="makeUserModal('${department.name}')">
                <i class="ri-user-add-line"></i>
                Add member
            </button>
        </td>
        <td>
            <i class="ri-edit-line icon" onclick="makeEditModal('${department.name}')"></i>
        </td>
        <td>
            <i class="ri-delete-bin-line icon delete" onclick="makeDeleteModal('${department.name}')"></i>
        </td>
    `;

    element.appendChild(tr);
}


const getNewTableData = async (ev) => {
    if (ev.deltaY < 0) return;
    if (end) return;
    const element = document.querySelector(".department-tbody")
    if (element === null) return;
    if (element.lastElementChild === null) return;
    if (isOnScreen(element.lastElementChild) && !fetchingDepartments) {
        fetchingDepartments = true;
        console.log("fetching new user data...");
        //fetch
        const sortBy = currentSearchParams.get("sort");
        const res = await fetch(`/api/departments?returnClients=true&limit=10&offset=${offset + element.children.length}${sortBy !== null ? "&sort=" + sortBy : ''}`,
            { method: "GET" });

        if (res.status !== 200) {
            console.log(`Department list request failed with status ${res.status}`);
        }
        const resJson = await res.json();
        if (resJson.length === 0) {
            end = true;
            return;
        }
        //draw
        resJson.forEach(drawNewRow);
        fetchingDepartments = false;

    }
};
document.addEventListener("scroll", getNewTableData);

async function makeUserModal(departmentId) {
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.createElement("div");
    modalElement.classList.add("modal");
    modalElement.onclick = (event) => {
        if (event.target === modalElement) closeModal();
    }

    const modalContentElement = document.createElement("div");
    modalContentElement.classList.add("modal-content");

    modalElement.appendChild(modalContentElement);
    body.appendChild(modalElement);

    //TODO: get csrf token

    modalContentElement.innerHTML = `
        <h2>Add a member</h2>

        <div class="main-edit-content">
            

            <div class="modal-buttons">
                <input type="button" class="cancel-button" onclick="closeModal()" value="Cancel">
            </div>
        </div>
        `;

    const searchField = document.createElement("input");
    searchField.type = "text";
    searchField.placeholder = "Search user...";
    searchField.style.zIndex = 1;

    const suggestions = document.createElement("ul");
    suggestions.classList.add("suggestions");

    mainContent = modalContentElement.querySelector(".main-edit-content");
    mainContent.parentNode.insertBefore(searchField, mainContent);
    mainContent.parentNode.insertBefore(suggestions, mainContent);

    searchField.addEventListener("input", async (e) => {
        const searchValue = searchField.value;

        const res = await fetch(`/api/clients?q=${searchValue}`, { method: "GET" });

        if (res.status !== 200) {
            snackbar("Failed to get data", "error");
            console.log(`failed to get data... with status ${res.status}`);
            return;
        }

        const users = await res.json();

        suggestions.innerHTML = "";
        if (users.length !== 0) {
            suggestions.classList.add("has-suggestions");
            users.forEach((user) => {
                const suggestion = document.createElement("li");
                suggestion.classList.add("suggestion");
                suggestion.innerHTML = `
                    <img src="${user.image}" alt="user">
                    <span>${user.displayName}</span>
                `;
                suggestion.addEventListener("click", () => {
                    const form = document.createElement("form");
                    form.style.display = "none";
                    form.method = "POST";
                    form.style.display = "none";
                    form.action = '/admin'
                    const input = document.createElement("input");
                    input.name = "action";
                    input.value = "addMember";
                    form.appendChild(input);
                    const input2 = document.createElement("input");
                    input2.name = "user";
                    input2.value = user.username;
                    form.appendChild(input2);
                    const input3 = document.createElement("input");
                    input3.name = "departmentId";
                    input3.value = departmentId;
                    form.appendChild(input3);
                    const input4 = document.createElement("input");
                    input4.name = "lastHref";
                    input4.value = "/admin?tab=departments"
                    form.appendChild(input4);
                    document.body.appendChild(form);
                    form.submit();
                });
                suggestions.appendChild(suggestion);
            });
        } else {
            suggestions.classList.remove("has-suggestions");
        }

    });

    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}

function submitFatherForm(element) {
    const form = element.parentNode;
    form.submit();
}