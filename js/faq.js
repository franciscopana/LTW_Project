const currentSearchParams = new URLSearchParams((location.href.split("?")[1] ?? ''));
var offset = currentSearchParams.get("offset") ?? 0;
var end = false;
var fetchingFAQs = false;
var searchInput = '';

var currentUserType = '';

const limit = 10;

function handleCancelClick(e) {
    e.preventDefault();
    window.location.reload();
}

function handleEditClick(e) {
    e.preventDefault();
    const title = document.querySelector('input[name="title"]');
    const content = document.querySelector('textarea[name="content"]');
    title.readOnly = !content.readOnly;
    content.readOnly = !content.readOnly;

    var editButton = document.getElementById('editButton');
    var saveButton = document.getElementById('saveButton');
    var cancelButton = document.getElementById('cancelButton');

    editButton.style.display = 'none';
    saveButton.style.display = 'inline-block';
    cancelButton.style.display = 'inline-block';
}

function isOnScreen(element) {
    const rect = element.getBoundingClientRect();
    return window.innerHeight > rect.top && rect.top >= 0;

}

const debounce = (func, delay) => {
    let timerId = null;
    return (...args) => {
        clearInterval(timerId);
        timerId = setTimeout(() => func(...args), delay);
    }
}

async function buildResults(result) {
    const faqContent = document.querySelector(".faq-content");

    const question = document.createElement("div");

    const userRes = await fetch(`/api/clients/${encodeURI(result["createdByUser"])}`, { method: "get" });

    if (userRes.status !== 200) {
        console.log(`Error while getting user building new FAQ entry with status ${userRes.status}`);
        return;
    }

    const userJson = await userRes.json();


    const editButton = `<i class="ri-edit-line" onclick="location.href = '/faq/${result["id"]}'"></i>`;
    const deleteButton = `<i class="ri-delete-bin-line" onclick="makeDeleteModal(${result["id"]})"></i>`;


    var contents = result["content"].split("\r\n");
    contents = contents.map((value, _) => "<p>" + value + "</p>");

    question.classList.add("faq-question");

    question.innerHTML = `
    <header>
        <h2>#${result["id"]} - ${result["title"]}</h2>
        <div class="faq-buttons">
            ${currentUserType == "agent" || currentUserType == "admin" ? editButton : ''}
            ${currentUserType == "agent" || currentUserType == "admin" ? deleteButton : ''}
            <i class="ri-add-circle-line open-close"></i>
        </div>
    </header>
    <div class="content">
        ${contents.join("\n")}
        <div class="created-by">
            <p>By:</p>
            <img class="avatar" src="/${userJson["image"]}" alt="user">
            <p class="display-name">${userJson["displayName"]}</p>
        </div>
    `;

    faqContent.appendChild(question);
}

async function searchNewParam(event) {
    searchInput = event.target.value;
    var res = undefined;
    if (searchInput.length === 0) {
        res = await fetch(`/api/faqs?limit=10&offset=0`,
            { method: "GET" });
    }
    if (searchInput.length < 3 && searchInput.length >= 1) return;
    offset = 0;
    end = false;

    fetchingFAQs = true;

    res = await fetch(`/api/faqs?limit=10&offset=${offset}&q=${encodeURI(searchInput)}`,
        { method: "GET" });

    if (res.status !== 200) {
        console.log(`Something went wrong while fetching FAQ search query with status ${res.status}`)
        return;
    }

    const resJson = await res.json();

    const faqContent = document.querySelector(".faq-content");
    while (faqContent.firstChild) {
        faqContent.removeChild(faqContent.lastChild);
    }

    await Promise.all(resJson.map(buildResults));
    addResultClick();
    fetchingFAQs = false;

}

const searchDebounce = debounce(searchNewParam, 300);

const addResultClick = () => {
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach((faqQuestion) => {
        faqQuestion.addEventListener('click', (e) => {
            // Clicking in the content do nothing
            if (e.target.classList.contains('content') ||
                e.target.parentElement.classList.contains('content') ||
                e.target.tagName == "BUTTON" ||
                e.target.classList.contains('ri-edit-line') ||
                e.target.classList.contains('ri-delete-bin-line')
            ) {
                return;
            }

            // Check if faqQuestion is active
            if (!faqQuestion.classList.contains('active')) {
                faqQuestions.forEach((faqQuestion) => {
                    faqQuestion.querySelector('i.open-close').classList.remove('ri-close-circle-line');
                    faqQuestion.querySelector('i.open-close').classList.add('ri-add-circle-line');
                    faqQuestion.classList.remove('active');
                });
            }

            faqQuestion.querySelector('i.open-close').classList.toggle('ri-add-circle-line');
            faqQuestion.querySelector('i.open-close').classList.toggle('ri-close-circle-line');
            faqQuestion.classList.toggle('active');
        });
    })
};


async function getNewFaqs(ev) {
    if (ev.deltaY < 0) return;
    if (end) return;
    const element = document.querySelector(".faq-content")
    if (element === null) return;
    if (element.lastElementChild === null) return;
    if (isOnScreen(element.lastElementChild) && !fetchingFAQs) {
        fetchingFAQs = true;
        fetchingUsers = false;
        const res = await fetch(`/api/faqs?limit=10&offset=${offset + element.children.length}${searchInput !== '' ? "&q=" + encodeURI(searchInput) : ''}`,
            { method: "GET" });
        if (res.status !== 200) {
            console.log(`FAQ list request failed with status ${res.status}`);
        }
        const resJson = await res.json();
        if (resJson.length === 0) {
            end = true;
            return;
        }
        await Promise.all(resJson.map(buildResults));
        addResultClick();
        fetchingFAQs = false;
    }
}

document.addEventListener("DOMContentLoaded", addResultClick);

document.addEventListener("DOMContentLoaded", (ev) => {
    const searchInput = document.querySelector('#fq-search');

    searchInput.addEventListener("input", searchDebounce);
});

document.addEventListener("scroll", getNewFaqs);

document.addEventListener("DOMContentLoaded", async () => {
    const res = await fetch("/api/clients/me", { method: "get" });

    if (res.status !== 200) {
        console.log(`Something went wrong while getting current user type status: ${res.status}`);
        //assume that it is a client if something goes wrong
        currentUserType = "client";
        return;
    }

    const resJson = await res.json();

    currentUserType = resJson["type"];

});


function makeDeleteModal(id) {
    const body = document.querySelector("body");
    body.style.overflow = "hidden";

    const modalElement = document.querySelector(".modal");
    if (modalElement === null) return;

    const modalContentElement = document.querySelector(".modal-content");
    modalContentElement.classList.toggle("delete-user-modal");
    if (modalContentElement === null) return;

    //TODO: inject CSRF token
    modalContentElement.innerHTML = `
    <h1>Delete User</h1>
    <p>Are you sure that you want to delete faq <b>#${id}</b>? This action is irreversible...</p>
    <div class="modal-buttons">
        <button class="cancel-button" onclick="closeModal()"><p>Cancel</p></button>
        <form method="post" action="faq">
            <input type="hidden" name="action" value="deleteFAQ">
            <input type="hidden" name="id" value="${id}">

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