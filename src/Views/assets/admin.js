const modals = {
    user: {
        openBtn: document.getElementById("openModalBtn"),
        modal: document.getElementById("userModal"),
        closeBtn: document.getElementById("userModal").querySelector(".close"),
        infoContainer: document.getElementById("userInfoContainer"),
        form: document.getElementById("userForm")
    },
    event: {
        openBtn: document.getElementById("openEventModalBtn"),
        modal: document.getElementById("eventModal"),
        closeBtn: document.getElementById("eventModal").querySelector(".close"),
        infoContainer: document.getElementById("eventInfoContainer"),
        form: document.getElementById("eventForm")
    },
    report: {
        openBtn: document.getElementById("openReportModalBtn"),
        modal: document.getElementById("reportModal"),
        closeBtn: document.getElementById("reportModal").querySelector(".close"),
        infoContainer: document.getElementById("reportInfoContainer"),
        form: document.getElementById("reportForm")
    },
    coach: {  
        openBtn: document.getElementById("openCoachModalBtn"),
        modal: document.getElementById("coachModal"),
        closeBtn: document.getElementById("coachModal").querySelector(".close"),
        infoContainer: document.getElementById("coachInfoContainer"),
        form: document.getElementById("coachForm")
    }

};
function openModal(modal) {
    modal.classList.add("show");
}

function closeModal(modal) {
    modal.classList.remove("show");
}

for (const key in modals) {
    const { openBtn, modal, closeBtn, infoContainer, form } = modals[key];

    openBtn.onclick = function() {
        openModal(modal);
    }

    closeBtn.onclick = function() {
        closeModal(modal);
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            closeModal(modal);
        }
    }
    form.onsubmit = function(event) {
        event.preventDefault();
        
        let infoHtml = "";
        if (key === 'user') {
            const username = document.getElementById("username").value;
            const email = document.getElementById("email").value;
            const role = document.getElementById("role").value;

            infoHtml = `
                <h3>Informations de l'utilisateur</h3>
                <p>Nom d'utilisateur : ${username}</p>
                <p>Email : ${email}</p>
                <p>Rôle : ${role}</p>
            `;
        } else if (key === 'event') {
            const eventName = document.getElementById("eventName").value;
            const eventDate = document.getElementById("eventDate").value;
            const eventDescription = document.getElementById("eventDescription").value;

            infoHtml = `
                <h3>Informations de l'Événement</h3>
                <p>Nom de l'Événement : ${eventName}</p>
                <p>Date : ${eventDate}</p>
                <p>Description : ${eventDescription}</p>
            `;
        } else if (key === 'report') {
            const reportTitle = document.getElementById("reportTitle").value;
            const reportContent = document.getElementById("reportContent").value;

            infoHtml = `
                <h3>Informations du Rapport</h3>
                <p>Titre du Rapport : ${reportTitle}</p>
                <p>Contenu : ${reportContent}</p>
            `;
        } else if (key === 'coach') { 
            const coachName = document.getElementById("coachName").value;
            const coachSpeciality = document.getElementById("coachSpeciality").value;
            const coachExperience = document.getElementById("coachExperience").value;

            infoHtml = `
                <h3>Informations de l'entraîneur</h3>
                <p>Nom de l'entraîneur : ${coachName}</p>
                <p>Spécialité : ${coachSpeciality}</p>
                <p>Années d'expérience : ${coachExperience}</p>
            `;
        }

        infoContainer.innerHTML = infoHtml;
        closeModal(modal);
        showInfo(infoContainer);
    }
}

function showInfo(container) {
    container.classList.add("show-info");
}

function resetContainer(container) {
    container.innerHTML = "<p>Aucune modification récente</p>";
}

for (const key in modals) {
    resetContainer(modals[key].infoContainer);
}
