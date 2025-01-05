let selectedTrainer;

function showTrainerDetails(trainer, user) {
    function convertToArray(value) {
        if (value && value.trim() !== "") {
            return value.split(',').map(item => item.trim());
        }
        return [];
    }

    selectedTrainer = trainer;

    const container = $('.container'); 
    container.html(`
        <div class="cv-container">
            <div class="cv-header">
                <h2>${trainer.first_name} ${trainer.last_name}</h2>
                <p><strong>Spécialité:</strong> ${trainer.specialty || "Non renseignée"}</p>
                <p><strong>Années d'expérience 🕒:</strong> ${trainer.experience || "Non renseignées"}</p>
                <img src="${trainer.image || 'https://via.placeholder.com/150'}" alt="${trainer.last_name}" class="trainer-image-cv">
            </div>
            <div class="cv-body">
                <div class="cv-details">
                    <p><strong>Description 📝:</strong> ${trainer.description || "Non renseignée"}</p>
                    <p><strong>Certifications 🎓:</strong> ${convertToArray(trainer.certifications).length > 0 ? convertToArray(trainer.certifications).join(', ') : "Aucune certification fournie"}</p>
                    <p><strong>Réalisations 🏆:</strong> ${convertToArray(trainer.achievements).length > 0 ? convertToArray(trainer.achievements).join(', ') : "Aucune réalisation partagée"}</p>
                    <p><strong>Qualités ⭐:</strong> ${convertToArray(trainer.qualities).length > 0 ? convertToArray(trainer.qualities).join(', ') : "Aucune qualité mentionnée"}</p>
                </div>
            </div>
            <div class="cv-footer">
                <div class="cv-footer-left">
                    <div class="cv-footer-item">
                        <img src="https://gl-sport.com/wp-content/uploads/2021/09/glsport_prise-de-masse.png" alt="Icône halt" class="halt-icon">
                        <p class="prise-de-masse-label">Prise de masse</p>
                    </div>
                    <div class="cv-footer-item">
                        <img src="https://gl-sport.com/wp-content/uploads/2021/09/glsport_remise-en-forme.png" alt="Icône balance" class="balance-icon">
                        <p class="balance-label">Remise en forme</p>
                    </div>
                    <div class="cv-footer-item">
                        <img src="https://gl-sport.com/wp-content/uploads/2021/09/glsport_remise-en-forme.png" alt="Icône remise en forme" class="remise-icon">
                        <p class="remise-en-forme-label">Perte de poids</p>
                    </div>
                </div>
                <div class="cv-footer-buttons">
                    <button class="cv-back-btn">Retour</button>
                    <button class="cv-reserve-btn" id="reserve-btn-${trainer.coach_id}">Réserver</button>
                </div>
            </div>
        </div>
    `);

    $('.cv-back-btn').on('click', function() {
        location.reload();
    }); 

    mobiscroll.setOptions({
        locale: mobiscroll.localeFr, 
        theme: 'ios', 
        themeVariant: 'dark', 
        newEventText: 'Nom du client'
    });

    $(`#reserve-btn-${trainer.coach_id}`).on('click', function() {
        const selectedTrainer = trainer;
        console.log("Coach sélectionné :", selectedTrainer);
    
        if (user) {
            const clientName = `${user.first_name} ${user.last_name}`;
            console.log("Nom du client :", clientName);
    
            document.querySelectorAll('.cv-container').forEach(cv => cv.style.display = 'none');
            const container = $('.container');
            container.html(`
                <div id="calendar-section">
                    <h3>Calendrier de réservation du coach <span class="math-inline">${trainer.first_name} ${trainer.last_name}</span> (${trainer.specialty})</h3>
                    <div id="demo-mobile-day-view" style="height: 500px;"></div>
                    <div id="event-form" style="display: none; position: fixed; background: rgba(255, 255, 255, 0.9); padding: 20px; border: 1px solid #ddd;
                        top: 50%; left: 50%; transform: translate(-50%, -50%);
                        width: 300px; z-index: 999;">
                        <span id="close-form" style="position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px;">&times;</span>
                        <label for="event-title">Nom du client</label>
                        <input type="text" id="event-title" value="${clientName}" placeholder="Nom du client">
                        <label for="event-date">Date et heure de réservation</label>
                        <input type="datetime-local" id="event-date">
                        <label for="event-end-time">Heure de fin</label>
                        <input type="time" id="event-end-time" placeholder="Heure de fin">
                        <label for="event-activity">Activité</label>
                        <input type="text" id="event-activity" value="${trainer.specialty}" readonly>
                        <div id="color-options">
                            <div class="color-option" data-color="#4981d6" style="background-color: #4981d6; width: 20px; height: 20px; display: inline-block;"></div>
                            <div class="color-option" data-color="#C1FF72" style="background-color: #C1FF72; width: 20px; height: 20px; display: inline-block;"></div>
                            <div class="color-option" data-color="#ff5e57" style="background-color: #ff5e57; width: 20px; height: 20px; display: inline-block;"></div>
                            <div class="color-option" data-color="#ffa726" style="background-color: #ffa726; width: 20px; height: 20px; display: inline-block;"></div>
                        </div>
                        <button id="save-btn" style="margin-top: 10px;">Enregistrer la réservation</button>
                    </div>
                    <button class="back-btn" style="margin-top: 20px;">Retour</button>
                </div>
            `);
    
            $('.back-btn').on('click', function() {
                console.log("Retour au détail du coach...");
                showTrainerDetails(selectedTrainer);
            });
    
            document.getElementById('close-form').addEventListener('click', function () {
                const form = document.getElementById('event-form');
                console.log("Fermeture du formulaire...");
                form.style.display = 'none';
            });
    
            document.getElementById('save-btn').addEventListener('click', function () {
                const eventDateValue = document.getElementById('event-date').value;
                console.log("Valeur de la date de réservation :", eventDateValue);
            
                if (!eventDateValue || !eventDateValue.includes('T')) {
                    console.error("Format invalide ou champ vide pour #event-date:", eventDateValue);
                    alert("Veuillez sélectionner une date et une heure valides.");
                    return;
                }
            
                const reservationDate = eventDateValue.split('T')[0];
                const startTime = eventDateValue.split('T')[1];
                const endTime = document.getElementById('event-end-time').value;
            
                if (!endTime) {
                    console.error("Heure de fin non définie:", endTime);
                    alert("Veuillez sélectionner une heure de fin.");
                    return;
                }
            
                const [startHours, startMinutes] = startTime.split(':').map(Number);
                const [endHours, endMinutes] = endTime.split(':').map(Number);
                const durationInMinutes = (endHours * 60 + endMinutes) - (startHours * 60 + startMinutes);
            
                if (durationInMinutes !== 60 && durationInMinutes !== 120) {
                    alert("La durée de réservation doit être soit de 1 heure, soit de 2 heures.");
                    return;
                }
            
                const now = new Date(); 
                const reservationDateTime = new Date(`${reservationDate}T${startTime}`);
            
                if (reservationDateTime < now) {
                    alert("Impossible de réserver dans le passé. Veuillez choisir une date et une heure valides.");
                    return;
                }
            
                const activity = document.getElementById('event-activity').value;
                const coachId = trainer.coach_id;
                const selectedColor = document.querySelector('.color-option.selected')?.getAttribute('data-color');
    
                console.log("Couleur sélectionnée :", selectedColor);
                console.log("Données de réservation :", {
                    memberId, activity, reservationDate, startTime, endTime, coachId, selectedColor
                });
            
                fetch('/api/reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        member_id: memberId, 
                        activity,
                        reservation_date: reservationDate,
                        start_time: startTime,
                        end_time: endTime,
                        coach_id: coachId,
                        color: selectedColor || '#4981d6'
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Réservation enregistrée avec succès !');
            
                        const newEvent = {
                            title: `Réservation: ${activity}`,
                            start: `${reservationDate}T${startTime}`,
                            end: `${reservationDate}T${endTime}`,
                            color: selectedColor || '#4981d6'
                        };
                        inst.addEvent(newEvent);
                        console.log("Événement ajouté au calendrier : ", newEvent);
    
                        const form = document.getElementById('event-form');
                        if (form) {
                            console.log('Formulaire trouvé, fermeture...');
                            form.style.display = 'none';
                        } else {
                            console.error('Formulaire introuvable ! Vérifiez l\'ID #event-form.');
                        }
    
                    } else {
                        console.error("Erreur serveur :", data);
                        alert('Erreur lors de la réservation. Veuillez réessayer.');
                    }
                })
                .catch(error => {
                    console.error("Erreur réseau :", error);
                    alert('Impossible de contacter le serveur.');
                });
            });
    
            const inst = mobiscroll.eventcalendar('#demo-mobile-day-view', {
                view: {
                    schedule: {
                        type: 'week',
                        startTime: '08:00',
                        endTime: '20:00',
                        allDay: false
                    }
                },
                dragToCreate: true,
                dragToMove: true,
                dragToResize: true,
                onEventClick: function (args) {
                    console.log("Événement cliqué :", args.event);
                    const form = document.getElementById('event-form');
                    form.style.display = 'block';
                    document.getElementById('event-title').value = args.event.title === "Nom du client" ? clientName : args.event.title;
            
                    document.querySelectorAll('.color-option').forEach(option => {
                        if (option.getAttribute('data-color') === args.event.color) {
                            option.classList.add('selected');
                        } else {
                            option.classList.remove('selected');
                        }
                    });
    
                    document.getElementById('event-title').oninput = function () {
                        args.event.title = this.value;
                        inst.updateEvent(args.event);
                    };
            
                    document.querySelectorAll('.color-option').forEach(option => {
                        option.onclick = function () {
                            document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                            this.classList.add('selected');
                            args.event.color = this.getAttribute('data-color');
                            inst.updateEvent(args.event);
                        };
                    });
                },
                data: [] 
            });
    
            console.log("Coach ID:", selectedTrainer.coach_id);
    
            $.ajax({
                url: `/api/reservations/${selectedTrainer.coach_id}`,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log("Réponse du serveur :", data);
    
                    if (Array.isArray(data) && data.length > 0) {
                        const events = data.map(reservation => ({
                            title: ` ${reservation.title}`,
                            start: new Date(reservation.start), 
                            end: new Date(reservation.end),
                            color: reservation.color || '#4981d6'     
                        }));
                        
                        console.log("Événements formatés :", events);
    
                        if (typeof inst !== 'undefined' && inst.setEvents) {
                            inst.setEvents(events);
                            console.log("Événements ajoutés au calendrier");
                        } else {
                            console.error("Le calendrier Mobiscroll n'est pas initialisé correctement.");
                        }
                    } else {
                        console.error("Aucun événement trouvé ou les données sont invalides.");
                    }
                },
                error: function(xhr, _, error) {
                    console.error('Erreur lors de la récupération des réservations :', error);
                    console.log('Réponse complète du serveur :', xhr.responseText);
                }
            });
        } else {
            alert('Utilisateur non connecté');
        }
    });
    
}

function initialize() {
    $('.trainers-list').on('click', '.trainer-card', function () {
        const trainerId = $(this).data('id');

        const container = $('.container');
        container.html('<p>Chargement des détails...</p>');
        $.ajax({
            url: `/api/trainers/${trainerId}`,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.error) {
                    container.html(`<p>Erreur : ${data.error}</p>`);
                } else {
                    showTrainerDetails(data.trainer, data.user);
                }
            },
            error: function () {
                container.html('<p>Erreur lors du chargement des données.</p>');
            }
        });
    });
}


