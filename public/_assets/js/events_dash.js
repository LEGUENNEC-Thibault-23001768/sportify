(function() {
    let tempEventId = null;
    let calendar;
    let createEventPopup;
    let popupOverlay;

    async function initCalendar() {
        const calendarData = await getCalendarData();
        mobiscroll.setOptions({
            locale: mobiscroll.localeFr,
            theme: 'ios',
            themeVariant: 'dark'
        });
        calendar = mobiscroll.eventcalendar('#myCalendar', {
            view: {
                schedule: {
                    type: 'week',
                    startTime: '08:00',
                    endTime: '22:00',
                    startDay: 1,
                    endDay: 0
                }
            },
            data: calendarData,
            clickToCreate: true,
            dragToCreate: false,
            dragToMove: false,
            dragToResize: false,
            eventDelete: true,
            onEventClick: function(args) {
                if (args.event.type === 'booking') return;
                showEventDetails(args.event);
            },
             onEventDeleted: async function(args) {
                try {
                    await deleteEvent(args.event.id);
                    showToast("Événement supprimé avec succès", 'success');
                    calendar.removeEvent(args.event.id);
                } catch (error) {
                    console.error("Erreur lors de la suppression de l'événement:", error);
                    showToast("Erreur lors de la suppression de l'événement", 'error');
                }
            },
            eventTemplate: function(event) {
                if (event.type === 'booking') {
                    return `<div class="md-booking-event-template" style="background-color: ${event.color}; color: white;">
                                <div class="md-booking-event-title">${event.title}</div>
                             </div>`;
                } else {
                    return `<div class="md-event-template">
                                <div class="md-event-title">${event.title}</div>
                                 <div class="md-event-time">${formatTime(event.start_time)} - ${formatTime(event.end_time)}</div>
                             </div>`;
                }
            }
        });
        createEventPopup = document.getElementById('createEventPopup');
        popupOverlay = document.getElementById('popupOverlay');


         // Initialiser le sélecteur de date pour event_date (caché mais nécessaire pour appeler la fonction)
        mobiscroll.datepicker('#event_date', {
            controls: ['calendar'],
            display: 'center',
            dateFormat: 'YYYY-MM-DD',
        });
         // Écouteur d'événement pour fermer la fenêtre contextuelle des détails de l'événement
        document.getElementById('closeDetailsButton').addEventListener('click', closeEventDetailsPopup);
           $('#location').on('change', async function() {
            const selectedOption = $(this).find('option:selected');
            const courtName = selectedOption.data('court-name');
            const locationDate = selectedOption.data('reservation-date');
            const startTime = selectedOption.data('start-time');
            const endTime = selectedOption.data('end-time');
           await fetchLocationTimes(courtName, locationDate, startTime, endTime);
        });
    }

    window.openCreateEventPopup = async function() {
         if (window.memberStatus === 'coach' || window.memberStatus === 'admin') {
            // Réinitialiser les valeurs du formulaire
            $('#event_name').val('');
            $('#description').val('');
            $('#location-times').html('');
            $('#location-times').removeData('locationTimes');
            $('#max_participants').val('5');
            $('#invitations').val('');
              $('#location').prop('selectedIndex', 0).trigger('change');

            // Afficher la fenêtre contextuelle de création d'événement
            createEventPopup.style.display = 'block';
            popupOverlay.style.display = 'block';
        }
    }
    
    async function getCalendarData() {
        try {
            const eventsData = await getCachedEvents();
            const bookingsData = (await getBookings()).bookings;
            const formattedBookings = await formatBookings(bookingsData);
            return JSON.parse(eventsData).concat(formattedBookings);
        } catch (error) {
            console.error('Erreur lors de la récupération des données du calendrier:', error);
            showToast("Erreur lors de la récupération des données du calendrier.", 'error');
            return [];
        }
    }

    async function getCachedEvents() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/events',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    resolve(JSON.stringify(data));
                },
                 error: function(error) {
                    console.error("Erreur lors de la récupération des données des événements:", error);
                     showToast("Erreur lors de la récupération des données des événements", 'error');
                    reject(error)
                }
            });
        });
    }

    async function getBookings() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/booking',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    resolve(data);
                },
                 error: function(error) {
                    console.error("Erreur lors de la récupération des données de réservation:", error);
                    showToast("Erreur lors de la récupération des données de réservation", 'error');
                    reject(error)
                }
            });
        });
    }

    async function formatBookings(bookings) {
        return new Promise((resolve) => {
            const formattedBookings = bookings.map(booking => ({
                id: booking.reservation_id,
                title: booking.court_name + ' (Réservation)',
                start: booking.reservation_date + 'T' + booking.start_time,
                end: booking.reservation_date + 'T' + booking.end_time,
                color: '#52b788',
                type: 'booking'
            }));
            resolve(formattedBookings)
        });
    }

    let eventDetailsCache = {}

    // Fonction pour afficher les détails de l'événement
    async function showEventDetails(event) {
          const isAuthorizedToDelete = window.memberStatus === 'coach' || window.memberStatus === 'admin';
          const isEventCreator = event.created_by === window.currentUserId;
          if (event.participants === undefined) event.participants = [];
        try {
            const eventData = await fetchEvent(event.id);
            populateEventDetailsPopup(eventData, isAuthorizedToDelete, isEventCreator);
        } catch (error) {
            console.error("Erreur lors de l'affichage des détails de l'événement:", error);
           showToast("Erreur lors de l'affichage des détails de l'événement", 'error');
        }
    }

    async function fetchEvent(eventId) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/events/' + eventId,
                method: 'GET',
                dataType: 'json',
                success: function(eventData) {
                    resolve(eventData);
                },
                error: function(error) {
                   console.error("Erreur lors de la récupération des détails de l'événement:", error);
                    showToast("Erreur lors de la récupération des détails de l'événement", 'error');
                    reject(error);
                }
            });
        });
    }

     function populateEventDetailsPopup(eventData, isAuthorizedToDelete, isEventCreator) {
          let deleteButton = isAuthorizedToDelete ? `<button class="btn btn-danger" onclick="deleteEvent(${eventData.id})">Supprimer</button>` : '';
        const canInvite = isAuthorizedToDelete || isEventCreator;
       let joinLeaveButton = '';
        if (eventData.is_registered) {
          joinLeaveButton = `<button class="btn btn-secondary" onclick="leaveEvent(${eventData.id})">Quitter l'événement</button>`;
      } else if (!isEventCreator) {
           joinLeaveButton = `<button class="btn btn-success" onclick="joinEvent(${eventData.id})">Rejoindre l'événement</button>`;
        }
         let participantsList = '<p>Aucun participant pour l\'instant.</p>';
        if (canInvite && eventData.participants && eventData.participants.length > 0) {
            participantsList = '<ul>';
            for (let participant of eventData.participants) {
               participantsList += `<li>${participant.first_name} ${participant.last_name}</li>`;
            }
           participantsList += '</ul>';
       } else if (!canInvite) {
            participantsList = '<p>La liste des participants est privée.</p>';
       }
       let inviteForm = canInvite ? `
       <div class="form-group">
            <label for="invite_email">Inviter par Email</label>
          <input type="email" id="invite_email" name="invite_email" class="form-control" required>
           <button type="button" class="btn btn-primary" onclick="sendInvite(${eventData.id})">Envoyer l'invitation</button>
      </div>
  ` : '';

        const startDate = new Date(eventData.event_date);
        const startTime = new Date(eventData.event_date + 'T' + eventData.start_time);
        const endTime = new Date(eventData.event_date + 'T' + eventData.end_time);

         const eventContent = `
            <h2>${eventData.event_name}</h2>
           <p><strong>Date:</strong> ${formatDate(startDate)}</p>
          <p><strong>Heure de début:</strong> ${formatTime(startTime)}</p>
           <p><strong>Heure de fin:</strong> ${formatTime(endTime)}</p>
            <p><strong>Lieu:</strong> ${eventData.location}</p>
            <p><strong>Description:</strong> ${eventData.description}</p>
         <p><strong>Nombre max. de participants:</strong> ${eventData.max_participants}</p>
              ${canInvite ? '<p><strong>Participants:</strong></p>' : ''}
            ${canInvite ? participantsList : ''}
             ${inviteForm}
            ${joinLeaveButton}
           ${deleteButton}
        `;
        $('#eventDetailsContent').html(eventContent);
          const eventDetailsPopup = document.getElementById('eventDetailsPopup');
        const popupOverlay = document.getElementById('popupOverlay');
         eventDetailsPopup.style.display = 'block';
        popupOverlay.style.display = 'block';
    }


    // Fonction pour fermer la fenêtre contextuelle des détails de l'événement
     function closeEventDetailsPopup() {
        const eventDetailsPopup = document.getElementById('eventDetailsPopup');
        const popupOverlay = document.getElementById('popupOverlay');
        eventDetailsPopup.style.display = 'none';
        popupOverlay.style.display = 'none';
    }

    window.closeCreateEventPopup = function() {
        createEventPopup.style.display = 'none';
        popupOverlay.style.display = 'none';
        $('#eventForm')[0].reset();
        if (tempEventId) {
            calendar.removeEvent(tempEventId);
            tempEventId = null;
        }
    }

    // Fonction pour supprimer un événement
    async function deleteEvent(eventId) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/events/' + eventId,
                method: 'DELETE',
                success: function(response) {
                    console.log("Événement supprimé avec succès");
                    resolve(response);
                },
               error: function(error) {
                    console.error("Erreur lors de la suppression de l'événement:", error.responseJSON.error);
                    showToast("Erreur lors de la suppression de l'événement: " + error.responseJSON.error, 'error');
                    reject(error);
                }
            });
        });
    }

    // Fonction pour envoyer une invitation
    window.sendInvite = function(eventId) {
        var email = $('#invite_email').val();
        if (email) {
            $.ajax({
                url: '/api/events/' + eventId + '/invite',
                method: 'POST',
                dataType: 'json',
                data: {
                    email: email
                },
                success: function(response) {
                    console.log("Invitation envoyée avec succès");
                    $('#invite_email').val('');
                   showToast("Invitation envoyée avec succès", 'success');
                  closeEventDetailsPopup();
                },
               error: function(error) {
                   console.error("Erreur lors de l'envoi de l'invitation:", error);
                    showToast("Erreur lors de l'envoi de l'invitation: " + error.responseJSON.error, 'error');
                }
            });
        } else {
            showToast("Veuillez saisir une adresse e-mail.", 'error');
        }
    }

     window.joinEvent = async function(eventId) {
        try {
           const response = await fetch('/api/events/join/' + eventId, {
                method: 'POST'
            });
            if (!response.ok) {
                console.log(response);
                throw new Error(`HTTP error! status: ${response.status}`);
           }
            const data = await response.json();
           if (data.message) {
               showToast(data.message, 'success');
           }
           await calendar.removeEvent(eventId);
            await calendar.addEvent(data.event);
            closeEventDetailsPopup();
       } catch (error) {
            console.error("Erreur lors de l'inscription à l'événement:", error);
            showToast("Erreur lors de l'inscription à l'événement", 'error');
        }
    };

    window.leaveEvent = async function(eventId) {
        try {
           const response = await fetch('/api/events/leave/' + eventId, {
                method: 'POST'
           });
            if (!response.ok) {
               throw new Error(`HTTP error! status: ${response.status}`);
            }
           const data = await response.json();
            if (data.message) {
               showToast(data.message, 'success');
           }
            await calendar.removeEvent(eventId);
           await calendar.addEvent(data.event);
          closeEventDetailsPopup();
        } catch (error) {
           console.error("Erreur lors du désistement de l'événement:", error);
            showToast("Erreur lors du désistement de l'événement", 'error');
        }
    };
     async function fetchLocationTimes(courtName, locationDate, startTime, endTime) {
      if (courtName && locationDate && startTime && endTime) {
            try {
                const bookingsData = (await getBookings()).bookings;
                let booking = bookingsData.find(booking =>
                    booking.court_name === courtName &&
                    booking.reservation_date === locationDate &&
                    booking.start_time === startTime &&
                    booking.end_time === endTime
                );
                  if (booking) {
                    // Vérifier si la réservation est entre 8h et 22h
                    if (startTime >= '08:00' && endTime <= '22:00') {
                         $('#location-times').text(`Lieu disponible: ${startTime} - ${endTime}`);
                        $('#location-times').data('locationTimes', booking);
                       $('#event_date').val(booking.reservation_date);
                       return;
                    } else {
                         $('#location-times').text("Lieu non disponible pour cette heure.");
                        $('#location-times').removeData('locationTimes');
                         return;
                    }
               } else {
                   $('#location-times').text("Lieu non réservé pour cette date.");
                   $('#location-times').removeData('locationTimes');
                    return;
                }
           } catch (error) {
                console.error("Erreur lors de la récupération des heures du lieu:", error);
               showToast("Erreur lors de la récupération des heures du lieu", 'error');
                 $('#location-times').removeData('locationTimes');
                 return;
            }
       } else {
            $('#location-times').text("Veuillez sélectionner un lieu et une date");
            $('#location-times').removeData('locationTimes');
            return;
        }
    }

    // Formater la date en AAAA-MM-JJ
   function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2)
           month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [year, month, day].join('-');
    }

   // Formater l'heure en HH:mm
    function formatTime(date) {
       if (date instanceof Date) {
            var hours = '' + date.getHours();
           var minutes = '' + date.getMinutes();
        } else {
           var d = new Date(date);
            var hours = '' + d.getHours();
           var minutes = '' + d.getMinutes();
        }

        if (hours.length < 2)
            hours = '0' + hours;
       if (minutes.length < 2)
           minutes = '0' + minutes;
        return [hours, minutes].join(':');
    }

   // Fonction pour afficher une notification toast
    function showToast(message, type = 'success') {
       // Créer l'élément toast
        var toast = $(`<div class="toast-message toast-${type}">` + message + '<span class="close-toast">×</span></div>');

        // Ajouter au conteneur
       $('#toast-container').append(toast);

        // Afficher le toast
       toast.fadeIn(400).delay(3000).fadeOut(400, function() {
            $(this).remove();
        });
        // Fonctionnalité du bouton de fermeture
        toast.find('.close-toast').click(function() {
           toast.remove();
        });
    }
   window.initialize = function() {
        initCalendar();
         $('#saveEvent').on('click', async function() {
            var eventName = $('#event_name').val();
           var description = $('#description').val();
           var maxParticipants = $('#max_participants').val();
           var location = $('#location').val();
           var invitations = $('#invitations').val();
           const locationTimes = $('#location-times').data('locationTimes');
    
           if (!locationTimes) {
               showToast('Erreur: Veuillez sélectionner un lieu valide.', 'error');
                 return;
            }
            const eventDate = locationTimes.reservation_date;
            const startTime = locationTimes.start_time;
            const endTime = locationTimes.end_time;
    
            var start = new Date(eventDate + 'T' + startTime);
             if (start < new Date()) {
                 showToast('Erreur: L\'heure de début de l\'événement ne peut pas être dans le passé.', 'error');
                return;
           }
    
            var end = new Date(eventDate + 'T' + endTime);
           var durationInHours = (end - start) / 1000 / 60 / 60;
            if (durationInHours > 2) {
                showToast('Erreur: La durée de l\'événement ne peut pas dépasser 2 heures.', 'error');
               return;
            }
    
            if (maxParticipants > 100) {
                showToast('Erreur: Le nombre maximum ne peut pas être supérieur à 100.', 'error');
               return;
           }
    
            const eventData = {
                event_name: eventName,
                event_date: eventDate,
               start_time: startTime,
               end_time: endTime,
               description: description,
                max_participants: maxParticipants,
               location: location,
                invitations: invitations
            };
    
            $.ajax({
                url: '/api/events',
                method: 'POST',
               dataType: 'json',
               data: eventData,
                success: function(response) {
                    if (response && response.id) {
                       showToast("Événement créé avec succès", 'success');
                       calendar.addEvent({
                                id: response.id,
                                title: eventName,
                                start: eventDate + 'T' + startTime,
                                end: eventDate + 'T' + endTime,
                                description: description,
                                location: location,
                                max_participants: maxParticipants,
                                created_by: window.currentUserId
                            });
                        closeCreateEventPopup();
                    } else {
                       console.error("Erreur: Format de réponse invalide", response);
                  }
               },
                error: function(error) {
                   console.error("Erreur lors de la création de l'événement:", error);
                   if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.error) {
                       showToast("Erreur lors de la création de l'événement: " + error.responseJSON.data.error, 'error');
                    } else {
                       showToast("Erreur lors de la création de l'événement: " + error, 'error');
                    }
                }
            });
        });
    };
})();