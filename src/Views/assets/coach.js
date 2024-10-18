const modals = {
    reserv: {
        openBtn: document.getElementById("openBookingModalBtn"),
        modal: document.getElementById("bookingModal"), 
        closeBtn: document.getElementById("bookingModal").querySelector(".close"),
        infoContainer: document.getElementById("bookingInfoContainer"),
        form: document.getElementById("bookingForm")
    },
    report: {
        openBtn: document.getElementById("openReportModalBtn"),
        modal: document.getElementById("reportModal"),
        closeBtn: document.getElementById("reportModal").querySelector(".close"),
        infoContainer: document.getElementById("reportInfoContainer"),
        form: document.getElementById("reportForm")
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
        if (key === 'report') {
            const reportTitle = document.getElementById("reportTitle").value;
            const reportContent = document.getElementById("reportContent").value;

            infoHtml = `
                <h3>Informations du Rapport</h3>
                <p>Titre du Rapport : ${reportTitle}</p>
                <p>Contenu : ${reportContent}</p>
            `;
        } else if (key === 'reserv') {
            const bookingDate = document.getElementById("bookingDate").value;
            const bookingTime = document.getElementById("bookingTime").value;

            infoHtml = `
                <h3>Détails de la Réservation</h3>
                <p>Date : ${bookingDate}</p>
                <p>Heure : ${bookingTime}</p>
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
