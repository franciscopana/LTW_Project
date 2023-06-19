const params = new URLSearchParams(location.href.split("?")[1] ?? '');
var labels = [];
var id = params.get("id");

document.querySelectorAll(".ticket-card").forEach((ticketCard) => {
    ticketCard.addEventListener("click", (e) => {
        const id = e.currentTarget.dataset.id;
        window.location.href = '/ticket?id=' + id;
    });
});

async function makeUserAssignModal(usertype) {
    if (usertype != "agent" && usertype != "admin") {
        return snackbar("Only agents can assign users to tickets", "warning");
    }

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
        <h2>Assign User</h2>

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

        const res = await fetch(`/api/clients?sort=agent&q=${searchValue}`, { method: "GET" });

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
                    const ticketId = document.querySelector("#ticketId").value;
                    form.action = `/ticket?id=${ticketId}`;
                    const input = document.createElement("input");
                    input.name = "action";
                    input.value = "assign";
                    form.appendChild(input);
                    const input2 = document.createElement("input");
                    input2.name = "user";
                    input2.value = user.username;
                    form.appendChild(input2);
                    const input3 = document.createElement("input");
                    input3.name = "ticketId";
                    input3.value = ticketId;
                    form.appendChild(input3);
                    document.body.appendChild(form);
                    form.submit();
                });
                suggestions.appendChild(suggestion);
            });
        } else {
            suggestions.classList.remove("has-suggestions");
        }

    });

    //searchField.addEventListener("blur", (e) => {
    //    suggestions.classList.remove("has-suggestions");
    //});

    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}

async function makeDepartmentAssignModal(usertype) {
    if (usertype != "agent" && usertype != "admin") {
        return snackbar("Only agents can assign departments to tickets", "warning");
    }

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
        <h2>Assign Department</h2>

        <div class="main-edit-content">
            

            <div class="modal-buttons">
                <input type="button" class="cancel-button" onclick="closeModal()" value="Cancel">
            </div>
        </div>
        `;

    const searchField = document.createElement("input");
    searchField.type = "text";
    searchField.placeholder = "Search deparment...";
    searchField.style.zIndex = 1;

    const suggestions = document.createElement("ul");
    suggestions.classList.add("suggestions");

    mainContent = modalContentElement.querySelector(".main-edit-content");
    mainContent.parentNode.insertBefore(searchField, mainContent);
    mainContent.parentNode.insertBefore(suggestions, mainContent);

    searchField.addEventListener("input", async (e) => {
        const searchValue = searchField.value;

        const res = await fetch(`/api/departments?q=${searchValue}`, { method: "GET" });

        if (res.status !== 200) {
            snackbar("Failed to get data", "error");
            console.log(`failed to get data... with status ${res.status}`);
            return;
        }

        const departments = await res.json();

        suggestions.innerHTML = "";
        if (departments.length !== 0) {
            suggestions.classList.add("has-suggestions");
            departments.forEach((department) => {
                const suggestion = document.createElement("li");
                suggestion.classList.add("suggestion");
                suggestion.innerHTML = `
                    <span>${department.name}</span>
                `;
                // Assign an department to a ticket
                suggestion.addEventListener("click", () => {
                    const form = document.createElement("form");
                    form.style.display = "none";
                    form.method = "POST";
                    form.style.display = "none";
                    const ticketId = document.querySelector("#ticketId").value;
                    form.action = `/ticket?id=${ticketId}`;
                    const input = document.createElement("input");
                    input.name = "action";
                    input.value = "changeDepartment";
                    form.appendChild(input);
                    const input2 = document.createElement("input");
                    input2.name = "department";
                    input2.value = department.name;
                    form.appendChild(input2);
                    const input3 = document.createElement("input");
                    input3.name = "ticketId";
                    input3.value = ticketId;
                    form.appendChild(input3);
                    document.body.appendChild(form);
                    form.submit();
                });
                suggestions.appendChild(suggestion);
            });
        } else {
            suggestions.classList.remove("has-suggestions");
        }
    })

    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}

async function makeFaqModal(usertype) {
    if (usertype != "agent" && usertype != "admin") {
        return snackbar("Only agents can close tickets", "warning");
    }

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
        <h2>Which FAQ?</h2>

        <div class="main-edit-content">
            

            <div class="modal-buttons">
                <input type="button" class="cancel-button" onclick="closeModal()" value="Cancel">
            </div>
        </div>
        `;

    const searchField = document.createElement("input");
    searchField.type = "text";
    searchField.placeholder = "Search faq...";
    searchField.style.zIndex = 1;

    const suggestions = document.createElement("ul");
    suggestions.classList.add("suggestions");

    mainContent = modalContentElement.querySelector(".main-edit-content");
    mainContent.parentNode.insertBefore(searchField, mainContent);
    mainContent.parentNode.insertBefore(suggestions, mainContent);

    searchField.addEventListener("input", async (e) => {
        const searchValue = searchField.value;

        const res = await fetch(`/api/faqs?q=${searchValue}`, { method: "GET" });

        if (res.status !== 200) {
            snackbar("Failed to get data", "error");
            console.log(`failed to get data... with status ${res.status}`);
            return;
        }

        const faqs = await res.json();

        suggestions.innerHTML = "";
        if (faqs.length !== 0) {
            suggestions.classList.add("has-suggestions");
            faqs.forEach((faq) => {
                const suggestion = document.createElement("li");
                suggestion.classList.add("suggestion");
                suggestion.innerHTML = `
                    <div>
                        <h3>${faq.title}</h3>
                        <p>${faq.content}</p>
                    </div>
                `;
                // Assign an department to a ticket
                suggestion.addEventListener("click", () => {
                    const form = document.createElement("form");
                    form.style.display = "none";
                    form.method = "POST";
                    form.style.display = "none";
                    const ticketId = document.querySelector("#ticketId").value;
                    form.action = `/ticket?id=${ticketId}`;
                    const input = document.createElement("input");
                    input.name = "action";
                    input.value = "close";
                    form.appendChild(input);
                    const input2 = document.createElement("input");
                    input2.name = "faqId";
                    input2.value = faq.id;
                    form.appendChild(input2);
                    const input3 = document.createElement("input");
                    input3.name = "ticketId";
                    input3.value = ticketId;
                    form.appendChild(input3);
                    document.body.appendChild(form);
                    form.submit();
                });
                suggestions.appendChild(suggestion);
            });
        } else {
            suggestions.classList.remove("has-suggestions");
        }
    })

    modalElement.style.display = "block";
    modalElement.style.opacity = 0;
    modalElement.animate([
        { opacity: 0 },
        { opacity: 1, visbility: "visible" },
    ], { duration: 200, iterations: 1 }).onfinish = (event) => {
        modalElement.style.opacity = 1;
    }
}

async function makeLabelsModal(usertype) {
    if (usertype != "agent" && usertype != "admin") {
        return snackbar("Only agents can add labels", "warning");
    }

    const body = document.querySelector("body");
    body.style.overflow = "hidden";


    const res = await fetch(`/api/tickets/${params.get("id")}`, { method: "GET" });

    labels = (await res.json())["labels"].map((element) => element["label"]);


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
        <h2>Adding a label</h2>

        <div class="main-edit-content">
            
            <input type="text" class="label-search" placeholder="Search label...">
            <ul class="suggestions">
            </ul>
            <div class="modal-buttons">
                <input type="button" class="cancel-button" onclick="location.reload()" value="Cancel">
            </div>
        </div>
        `;

    const searchField = document.querySelector(".label-search");

    searchField.addEventListener("input", async (e) => {
        const search = searchField.value;

        //TODO: 

        const res = await fetch(`/api/labels?q=${search}`, { method: "GET" });

        if (res.status !== 200) {
            console.log(`failed to get data... with status ${res.status}`);
            return;
        }

        const resJson = await res.json();
        console.log(resJson);
        const suggestions = document.querySelector(".suggestions");
        if(resJson.length === 0){
            suggestions.classList.remove("has-suggestions")
        }
        suggestions.classList.add('has-suggestions');

        innerHTMLString = '';
        resJson.forEach((element) =>{
            innerHTMLString += `<li class="suggestion-element" onclick="toggleLabel('${element["label"]}', this)" ${labels.includes(element["label"]) ? `style="background-color:#27ba06; color=white;"`: ''}>${element["label"]}</li>`
        })
        suggestions.innerHTML = innerHTMLString;

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



function submitFatherForm(e, element) {
    e.preventDefault();

    const form = element.parentNode;
    form.submit();
}

function submitGrandFatherForm(e, element) {
    e.preventDefault();

    const form = element.parentNode.parentNode;
    form.submit();
}

async function toggleLabel(labelName, element){
    const removing = labels.includes(labelName);
    const res = await fetch(`/api/tickets/${id}/labels`, 
        {
            method: labels.includes(labelName) ? "delete": "post", 
            body: JSON.stringify({"label":labelName})
        }
    );

    if(res.status !== 200){
        console.log(`Something went wrong adding/removing label from ticket with status ${res.status}`);
        return;
    }
    if(removing){
        labels = labels.filter(label => label !== labelName);
        element.style.backgroundColor = "white";
    } else {
        labels.push(labelName);
        element.style.backgroundColor = "#27ba06";

    }




}

//TODO: use this code for change status
window.addEventListener("load", async () => {
    const departmentSelect = document.querySelector("#departmentSelect");
    departmentSelect.addEventListener("change", (e) => {
        const newDepartment = e.target.value;
        const ticketId = document.querySelector("#ticketId").value;
        let formData = new FormData();
        formData.append('action', 'changeDepartment');
        formData.append('department', newDepartment);
        formData.append('ticketId', ticketId);

        fetch(`/ticket?id=${ticketId}`, {
            method: "POST",
            body: formData
        }).then(async (res) => {
            if (res.status === 200) {
                return snackbar("Department changed", "success");
            }

            const errorMessage = await res.text() ?? "Failed to change department";
            snackbar(errorMessage, "error");
        });
    });
});