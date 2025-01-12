(function() {
    let reservations = [];

    function loadReservations() {
        $.ajax({
            url: '/api/booking',
            type: 'GET',
            success: function(response) {
                console.log("Reservations data:", response);
                reservations = response.bookings;
                displayReservations(response.bookings);
            },
            error: function(xhr, status, error) {
                console.error("Error: " + status + " - " + error);
            }
        });
    }

    function displayReservations(bookings, courtId = null) {
        const list = $('#reservation-list');
        list.empty();

        let filteredBookings = bookings;
        if (courtId) {
            filteredBookings = bookings.filter(booking => booking.court_id == courtId);
        }

        if (filteredBookings.length === 0) {
            list.append('<li>Aucune réservation trouvée.</li>');
            return;
        }

        filteredBookings.forEach(booking => {
            let listItem = $('<li></li>');
            listItem.text(`${booking.reservation_date} - ${booking.start_time} à ${booking.end_time} - ${booking.court_name} (Réservé par ${booking.member_name})`);

            if (booking.member_id == window.currentUserId || window.memberStatus === 'admin') {
                let editButton = $(`<button class="edit-button" data-id="${booking.reservation_id}">Modifier</button>`);
                const bookingDate = new Date(booking.reservation_date + ' ' + booking.start_time)
                 if(bookingDate < new Date()) {
                    editButton.prop('disabled', true);
                    editButton.addClass('past');
                } else {
                     editButton.click(function() {
                         editReservation(booking.reservation_id);
                    });
                 }

                let deleteButton = $(`<button class="delete-button" data-id="${booking.reservation_id}">Annuler</button>`);
                deleteButton.click(function() {
                    deleteReservation(booking.reservation_id);
                });

                listItem.append(editButton);
                listItem.append(deleteButton);
            }

            list.append(listItem);
        });
    }

    function editReservation(reservationId) {
       $.ajax({
            url: '/api/booking/' + reservationId,
            type: 'GET',
            success: function(response) {
                 console.log(response);
                const reservation = response.reservation;
                const courtId = reservation.court_id;
                const selectedDate = reservation.reservation_date;
                 document.getElementById('court_id').value = courtId;
                 document.getElementById('member_name').value = userName;
                document.getElementById('member_id').value = window.currentUserId;
                const reservationContainer = document.getElementById('reservation-container');
                reservationContainer.querySelector('h3').textContent = `Modifier la salle de ${reservation.court_name}`;
                reservationContainer.style.display = 'block';
                 $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
                 const dateInput = document.getElementById('date');
                   dateInput.value = selectedDate;
                   generateHours(courtId, selectedDate, 'reserve');
                const reservationsContainer = document.getElementById('reservations');
                 reservationsContainer.style.display = 'block';
                displayReservations(reservations, courtId);
                $("#reserve-button").text("Modifier");
                 $(".tab-button[data-tab='reservation-form']").click();
            },
            error: function(xhr, status, error) {
                showErrorToast("Erreur lors de la récupération des détails de la réservation.");
                console.error("Error: " + status + " - " + error);
            }
        });
    }

    function deleteReservation(reservationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette réservation?')) {
            return;
        }

        $.ajax({
            url: '/api/booking/' + reservationId,
            type: 'DELETE',
            success: function(response) {
                console.log(response);
                showSuccessToast('Réservation supprimée avec succès!');
                loadReservations(); // Refresh the list after deletion
            },
            error: function(xhr, status, error) {
                showErrorToast("Erreur lors de la suppression de la réservation.");
                console.error("Error: " + status + " - " + error);
            }
        });
    }

    function showEditForm(reservation) {
          $('#edit_reservation_id').val(reservation.reservation_id);
        $('#edit_member_name').val(userName);
          const editDateInput =  $('#edit_date');
        editDateInput.val(reservation.reservation_date);
        $('#edit_start-time').val(reservation.start_time);
        $('#edit_end-time').val(reservation.end_time);
        const courtId = reservation.court_id;
        $('#edit-reservation-container').show();

        $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
        generateHours(courtId, reservation.reservation_date, 'edit');
    }

    function hideEditForm() {
         $('#edit-reservation-container').hide();
        $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
    }

    function cancelEditReservation() {
         hideEditForm();
    }

    function showSuccessToast(message) {
        const toastContainer = $('#toast-container');
        const toast = $(`<div class="toast success">${message}</div>`);
        toastContainer.append(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function showErrorToast(message) {
        const toastContainer = $('#toast-container');
        const toast = $(`<div class="toast error">${message}</div>`);
        toastContainer.append(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    window.openReservationForm = function(roomElement) {
        const courtId = roomElement.getAttribute('data-court-id');
        const roomName = roomElement.getAttribute('data-room');
        document.getElementById('court_id').value = courtId;
        document.getElementById('member_name').value = userName;
        document.getElementById('member_id').value = window.currentUserId;
         const reservationContainer = document.getElementById('reservation-container');
          reservationContainer.querySelector('h3').textContent = `Réserver la salle de ${roomName}`;
        reservationContainer.style.display = 'block';

        $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
        const dateInput = document.getElementById('date');
        const today = new Date();
        dateInput.value = today.toISOString().split('T')[0];
        generateHours(courtId, today.toISOString().split('T')[0]);
         const reservationsContainer = document.getElementById('reservations');
          reservationsContainer.style.display = 'block';
        displayReservations(reservations, courtId);
         $("#reserve-button").text("Réserver");
        $(".tab-button[data-tab='reservation-form']").click();
    };
    
    window.closeReservationForm = function() {
         document.getElementById('reservation-container').style.display = 'none';
         document.getElementById('reservation-form').reset();
         $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
    };

       function generateHours(courtId, selectedDate, formType = 'reserve') {
        const availableHoursDiv = document.getElementById(formType === 'reserve' ? 'available-hours' : 'edit-available-hours');
        availableHoursDiv.innerHTML = '';
    
            if (selectedDate && courtId) {
          
            const today = new Date();
            const selectedDateObj = new Date(selectedDate);

            if (selectedDateObj < today.setHours(0, 0, 0, 0)) {
                alert("Vous ne pouvez pas réserver pour une date dans le passé.");
                 document.getElementById(formType === 'reserve' ? 'date' : 'edit_date').value = '';
                 availableHoursDiv.innerHTML = '';
                return;
            }
            fetch('/api/booking/available-hours?court_id=' + courtId + '&date=' + selectedDate)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(existingBookings => {
                    if (!Array.isArray(existingBookings)) {
                        console.error("La réponse du serveur n'est pas un tableau :", existingBookings);
                          availableHoursDiv.innerHTML = "<p>Une erreur est survenue lors du chargement des disponibilités.</p>";
                        return;
                    }
                    if (existingBookings.length === 0) {
                        console.log("Aucune réservation existante pour cette date et ce court.");
                    }
                    const currentTime = new Date();
                     for (let hour = 8; hour <= 22; hour++) {
                        const hourString = `${hour.toString().padStart(2, '0')}:00`;
                        const isPast = selectedDateObj.toDateString() === today.toDateString() && hour < currentTime.getHours();
                         const isBooked = existingBookings.some(booking => {
                            const bookingStartTime = booking.start_time.substring(0, 5);
                            const bookingEndTime = booking.end_time.substring(0, 5);
                             return hourString >= bookingStartTime && hourString < bookingEndTime;
                        });

                        const button = document.createElement('button');
                        button.textContent = hourString;
                        button.type = 'button';
                        button.dataset.hour = hour;
                         button.disabled = isBooked || isPast;
                        if (isBooked) {
                           button.classList.add('booked'); 
                        } else if(isPast){
                             button.classList.add('past');
                        } else {
                           button.addEventListener('click', formType === 'reserve' ? handleHourClick : handleEditHourClick);
                        }
                        availableHoursDiv.appendChild(button);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des heures disponibles:', error);
                     availableHoursDiv.innerHTML = "<p>Une erreur est survenue lors du chargement des disponibilités.</p>";
                });
             }
        }
       function handleHourClick(event) {
            const clickedButton = event.target;
            const startHour = parseInt(clickedButton.dataset.hour);
             const selectedButtons = document.querySelectorAll('#available-hours button.selected');


            if (clickedButton.classList.contains('selected')) {
                   clickedButton.classList.remove('selected');
               } else {
                  if(selectedButtons.length >= 2){
                      alert("Vous ne pouvez sélectionner que deux heures maximum.");
                       return;
                 }
                 clickedButton.classList.add('selected');
            }

             const selectedHours = Array.from(document.querySelectorAll('#available-hours button.selected'))
                .map(btn => parseInt(btn.dataset.hour))
                .sort((a, b) => a - b);

            if (selectedHours.length > 0) {
                document.getElementById('selected-time').value = selectedHours.map(hour => hour.toString().padStart(2, '0') + ':00').join(',');
                 document.getElementById('duration').value = selectedHours.length;
                 if (selectedHours.length === 2 && selectedHours[1] - selectedHours[0] !== 1) {
                    alert("Veuillez sélectionner des heures consécutives.");
                     selectedButtons.forEach(btn => btn.classList.remove('selected'));
                    document.getElementById('selected-time').value = '';
                    document.getElementById('duration').value = '';
                }
            } else {
                document.getElementById('selected-time').value = '';
                document.getElementById('duration').value = '';
            }
        }
    function handleEditHourClick(event) {
            const clickedButton = event.target;
            const startHour = parseInt(clickedButton.dataset.hour);
             const selectedButtons = document.querySelectorAll('#edit-available-hours button.selected');


            if (clickedButton.classList.contains('selected')) {
                   clickedButton.classList.remove('selected');
               } else {
                  if(selectedButtons.length >= 2){
                      alert("Vous ne pouvez sélectionner que deux heures maximum.");
                       return;
                 }
                 clickedButton.classList.add('selected');
            }

             const selectedHours = Array.from(document.querySelectorAll('#edit-available-hours button.selected'))
                .map(btn => parseInt(btn.dataset.hour))
                .sort((a, b) => a - b);

            if (selectedHours.length > 0) {
                document.getElementById('edit_start-time').value = selectedHours.map(hour => hour.toString().padStart(2, '0') + ':00').join(',');
                document.getElementById('edit_end-time').value =  selectedHours.length > 1 ? selectedHours.map(hour => (hour+1).toString().padStart(2, '0') + ':00').slice(-1)[0] :  document.getElementById('edit_end-time').value ;

                if (selectedHours.length === 2 && selectedHours[1] - selectedHours[0] !== 1) {
                    alert("Veuillez sélectionner des heures consécutives.");
                     selectedButtons.forEach(btn => btn.classList.remove('selected'));
                     document.getElementById('edit_start-time').value = '';
                     document.getElementById('edit_end-time').value = '';
                }
            } else {
                document.getElementById('edit_start-time').value = '';
                 document.getElementById('edit_end-time').value = '';
            }
        }


    window.initialize = () => {
        console.log("Booking view initialized");
        loadReservations();

        $('#reserve-button').click(function() {
             const selectedTime = document.getElementById('selected-time').value;
            if (!selectedTime) {
                alert("Veuillez sélectionner une heure.");
                return;
            }

            const formData = {
                court_id: $('#court_id').val(),
                member_id: window.currentUserId,
                reservation_date: $('#date').val(),
                start_time: $('#selected-time').val(),
            };

            $.ajax({
                url: '/api/booking',
                type: 'POST',
                data: formData,
                success: function(response) {
                    showSuccessToast('Réservation ajoutée avec succès!');
                    closeReservationForm();
                     loadReservations();
                },
                error: function(xhr, status, error) {
                    let errorMessage = "Erreur lors de l'ajout de la réservation.";
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                        errorMessage = xhr.responseJSON.data.error;
                    }
                    showErrorToast(errorMessage);
                    console.error("Error: " + status + " - " + error);
                }
            });
        });

        $('#update-button').click(function() {
            const reservationId = $('#edit_reservation_id').val();
            const formData = {
                reservation_date: $('#edit_date').val(),
                start_time: $('#edit_start-time').val(),
                end_time: $('#edit_end-time').val()
            };

            $.ajax({
                url: '/api/booking/' + reservationId,
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                     if (response.data && response.data.message) {
                        showSuccessToast(response.data.message);
                    } else {
                        showSuccessToast('Réservation mise à jour avec succès!');
                    }
                    hideEditForm();
                     loadReservations();
                },
                error: function(xhr, status, error) {
                    let errorMessage = "Erreur lors de la mise à jour de la réservation.";
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                        errorMessage = xhr.responseJSON.data.error;
                    }
                    showErrorToast(errorMessage);
                    console.error("Error: " + status + " - " + error);
                }
            });
        });
          $('#date').change(function() {
              const selectedDate = this.value;
               const courtId = document.getElementById('court_id').value;
               generateHours(courtId, selectedDate);
        });
         $('#edit_date').change(function() {
              const selectedDate = this.value;
               const courtId =  $('#edit_reservation_id').val()
               $.ajax({
                   url: '/api/booking/' + courtId,
                   type: 'GET',
                   success: function(response) {
                       if(response.reservation){
                           generateHours(response.reservation.court_id, selectedDate, 'edit');
                       }
                   },
                   error: function(xhr, status, error) {
                        console.error("Error: " + status + " - " + error);
                   }
               });
        });
          $(".tab-button").click(function() {
            $(".tab-button").removeClass("active");
            $(this).addClass("active");
            $(".tab-content").removeClass("active").hide();
            $("#" + $(this).data("tab")).addClass("active").show();
            $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
            if($(this).data('tab') === 'reservations'){
                const courtId = document.getElementById('court_id').value;
                displayReservations(reservations, courtId)
             }
        });
    }
    
    })();