(function() {
  let reservations = [];
  let selectedMembers = new Set(); // Déplacé ici pour la portée

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
          let reservationDetails = $(`<span class="reservation-details">
          ${booking.reservation_date} - ${booking.start_time} à ${booking.end_time} - ${booking.court_name} (Réservé par ${booking.member_name})
          </span>`);
          listItem.append(reservationDetails);
          let buttonContainer = $('<div class="button-container"></div>');

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
              buttonContainer.append(editButton);
              buttonContainer.append(deleteButton);
          }
          listItem.append(buttonContainer);
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
              showEditForm(reservation);
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
              loadReservations();
          },
          error: function(xhr, status, error) {
              showErrorToast("Erreur lors de la suppression de la réservation.");
              console.error("Error: " + status + " - " + error);
          }
      });
  }


  function showEditForm(reservation) {
      $.ajax({
          url: '/api/booking/' + reservation.reservation_id,
          type: 'GET',
          success: function(response) {
              console.log(response);
              const reservation = response.reservation;
              $('#edit_reservation_id').val(reservation.reservation_id);
              $('#edit_member_name').val(userName);
              const editDateInput =  $('#edit_date');
              editDateInput.val(reservation.reservation_date);
              const courtId = reservation.court_id;
              $('#edit-reservation-container').show();
              $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
              generateHours(courtId, reservation.reservation_date, 'edit', reservation.start_time.substring(0, 5),reservation.end_time.substring(0, 5));
              $(".tab-button[data-tab='edit-reservation-form']").click();
          },
          error: function(xhr, status, error) {
              showErrorToast("Erreur lors de la récupération des détails de la réservation.");
              console.error("Error: " + status + " - " + error);
          }
      });
  }

  function closeEditReservation() {
      $('#edit-reservation-container').hide();
      $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
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
     //Get the capacity !
    const maxCapacity = parseInt(roomElement.getAttribute('data-max-capacity'), 10); // Assuming base 10
   
   document.getElementById('court_id').value = courtId;
    document.getElementById('member_name').value = userName;
    document.getElementById('member_id').value = window.currentUserId;
    document.getElementById('max_capacity').value = maxCapacity

    const reservationContainer = document.getElementById('reservation-container');
    reservationContainer.querySelector('h3').textContent = `Réserver la salle de ${roomName}`;
    reservationContainer.style.display = 'block';

    $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
    const dateInput = document.getElementById('date');
    const today = new Date();
    dateInput.value = today.toISOString().split('T')[0];
    generateHours(courtId, today.toISOString().split('T')[0],'reserve');
    const reservationsContainer = document.getElementById('reservations');
    reservationsContainer.style.display = 'block';
    displayReservations(reservations, courtId);
    $("#reserve-button").text("Réserver");
    $(".tab-button[data-tab='reservation-form']").click();
  };
  window.closeReservationForm = function() {
      document.getElementById('reservation-container').style.display = 'none';
      const form = document.getElementById('reservation-form');
      if (form && form.tagName === 'FORM') {
          form.reset();
      }
      selectedMembers.clear(); // Vide la sélection des membres
      updateSelectedMembers();
      $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
  };
  function generateHours(courtId, selectedDate, formType = 'reserve', startTime = null, endTime = null) {
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
                          if(formType === 'edit' && startTime && endTime){
                              const startHourValue =  parseInt(startTime.substring(0, 2))
                              const endHourValue = parseInt(endTime.substring(0, 2))
                              if(hour >= startHourValue && hour < endHourValue){
                                  button.classList.add('selected');
                              }
                          }
                          button.addEventListener('click', formType === 'reserve' ? handleHourClick : handleEditHourClick);
                      }
                      availableHoursDiv.appendChild(button);
                  }
                  if(formType === 'edit' && startTime && endTime){
                      const startHourValue =  parseInt(startTime.substring(0, 2))
                      const endHourValue = parseInt(endTime.substring(0, 2))
                      const selectedHours = [];
                      for(let i = startHourValue; i < endHourValue; i++ ){
                          selectedHours.push(i);
                      }
                      if (selectedHours.length > 0) {
                          document.getElementById('edit_start-time').value = selectedHours.map(hour => hour.toString().padStart(2, '0') + ':00').join(',');
                          document.getElementById('edit_end-time').value =  selectedHours.length > 1 ? selectedHours.map(hour => (hour+1).toString().padStart(2, '0') + ':00').slice(-1)[0] :  document.getElementById('edit_end-time').value ;
                      }
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
      const selectedButtons = document.querySelectorAll('#edit-available-hours button.selected');

      if (clickedButton.classList.contains('selected')) {
          clickedButton.classList.remove('selected');
      } else {
          if(selectedButtons.length >= 1){
              alert("Vous ne pouvez sélectionner qu'une seule heure pour la modification.");
              return;
          }
          clickedButton.classList.add('selected');
      }

      const selectedHours = Array.from(document.querySelectorAll('#edit-available-hours button.selected'))
          .map(btn => parseInt(btn.dataset.hour))
          .sort((a, b) => a - b);

      if (selectedHours.length > 0) {
          document.getElementById('edit_start-time').value = selectedHours.map(hour => hour.toString().padStart(2, '0') + ':00').join(',');
          const endHour = selectedHours[0] + 1;
          document.getElementById('edit_end-time').value = endHour.toString().padStart(2, '0') + ':00';

      } else {
          document.getElementById('edit_start-time').value = '';
          document.getElementById('edit_end-time').value = '';
      }
  }


  $('#update-button').click(function() {
      const reservationId = $('#edit_reservation_id').val();
      const startTime = $('#edit_start-time').val();
      const endTime =  $('#edit_end-time').val();

      if (!startTime || !endTime) {
          showErrorToast("Veuillez sélectionner une heure de début et de fin.");
          return;
      }

      const formData = {
          reservation_date: $('#edit_date').val(),
          start_time: startTime,
          end_time: endTime
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
              closeEditReservation();
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


  window.closeEditReservation = function() {
      $('#edit-reservation-container').hide();
      $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
  }

  // Gestion de la recherche de membres
  window.showMemberSearch = async function() { // Assignation à window
      document.getElementById('member-search-modal').style.display = 'block';
      await loadMembers();
  }

  
  async function loadMembers(searchTerm = '') {
    try {
        const response = await fetch(`/api/members/search?term=${searchTerm}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        if (data.error) {
            console.error("Erreur du serveur :", data.error);
            document.getElementById('search-results').innerHTML = `<p>Erreur: ${data.error}</p>`;
            return;
        }

        if (Array.isArray(data)) {

            const maxCapacity =  parseInt(document.getElementById('max_capacity').value, 10) || 4 ;//Default, default
             if (selectedMembers.size > maxCapacity) {
                 return;
            }
            const resultsDiv = document.getElementById('search-results');
            resultsDiv.innerHTML = data.map(member => {
                const isSelected = selectedMembers.has(member.member_id); //Est-ce que l'item est déjà dans le Set?
                return `
                    <div class="member-result" data-id="${member.member_id}">
                        ${member.first_name} ${member.last_name}
                        ${isSelected ? 
                            `<button class="remove-button" onclick="toggleMember(${member.member_id})" data-action="remove">Retirer</button>` : 
                            `<button class="add-button" onclick="toggleMember(${member.member_id})" data-action="add">Ajouter</button>`}
                    </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error("Error fetching members:", error);
        document.getElementById('search-results').innerHTML = "<p>Erreur de connexion au serveur.</p>";
    }
}

window.toggleMember = function(memberId) {
  if (!Number.isInteger(memberId)) {
      console.warn("ID de membre invalide reçu par toggleMember:", memberId);
      return;
  }

  const memberDiv = document.querySelector(`.member-result[data-id="${memberId}"]`);
  if (!memberDiv) return;

  const button = memberDiv.querySelector('button'); // Get the button
  const action = button.dataset.action;  // Get what the button does.

  const maxCapacity = parseInt(document.getElementById('max_capacity').value, 10) || 4; // Récupère la capacité maximale

  if (action === 'add') {
      if (selectedMembers.size >= maxCapacity) {
          alert("La capacité maximale pour cette salle est atteinte.");
          return; // Empêche l'ajout du membre
      }
      selectedMembers.add(memberId);
  } else {
      selectedMembers.delete(memberId);
  }

  loadMembers(); // refresh members display
  updateSelectedMembers(); // Mettre à jour la liste des membres sélectionnés
};

function updateSelectedMembers() {
  const memberList = document.getElementById('member-list');
  memberList.innerHTML = Array.from(selectedMembers).map(memberId => `
      <div class="selected-member" data-id="${memberId}">
          ${getMemberName(memberId)}
          <input type="hidden" name="team_members[]" value="${memberId}">
      </div>
  `).join('');
}
  //fonction qui permet d'appeller le nom d'un membre dans la list de recherche de membre
    //fonction qui permet d'appeller le nom d'un membre dans la list de recherche de membre
    function getMemberName(memberId) {
      if (!Number.isInteger(memberId)) {
          console.warn("ID de membre invalide :", memberId);
          return "Unknown Member"; // Retourne une valeur par défaut immédiatement
      }
  
      let memberName = "Unknown Member"; // Valeur par défaut
      $.ajax({
          url: '/api/members/' + memberId,  // créer une route pour recup le nom du member
          type: 'GET',
          async: false, // Synchronous request (not recommended in general)
          success: function(response) {
              if (response && response.first_name && response.last_name) {
                  memberName = response.first_name + ' ' + response.last_name;
              } else {
                  console.warn("Nom du membre introuvable pour l'ID :", memberId, response);
              }
          },
          error: function(xhr, status, error) {
              console.error("Error fetching member name for ID " + memberId + ": " + error);
          }
      });
      return memberName;
  }
  // fonction pour fermer la searchModal
  window.closeMemberSearch = function() {
      document.getElementById('member-search-modal').style.display = 'none';
  }


  window.initialize = () => {
      console.log("Booking view initialized");
      loadReservations();

      // Gestion du type de réservation
      document.querySelectorAll('input[name="reservation_type"]').forEach(radio => {
          radio.addEventListener('change', (e) => {
              document.getElementById('team-section').style.display =
                  e.target.value === 'team' ? 'block' : 'none';
          });
      });

      $('#reserve-button').click(function() {
        const selectedTime = document.getElementById('selected-time').value;
        if (!selectedTime) {
            alert("Veuillez sélectionner une heure.");
            return;
        }
    
        const teamMembers = Array.from(selectedMembers);
        const maxCapacity = parseInt(document.getElementById('max_capacity').value, 10) || 4;
    
        if (teamMembers.length > maxCapacity) {
            alert("La capacité maximale pour cette salle est atteinte. Veuillez retirer des membres.");
            return; // Arrête la soumission du formulaire
        }
    
        const formData = {
            court_id: $('#court_id').val(),
            member_id: window.currentUserId,
            reservation_date: $('#date').val(),
            start_time: $('#selected-time').val(),
            team_members: teamMembers,
            reservation_type: $('input[name="reservation_type"]:checked').val(),
            team_name: $('#team_name').val()
        };
    
        $.ajax({
            url: '/api/booking',
            type: 'POST',
            data: formData,
            success: function(response) {
                showSuccessToast('Réservation ajoutée avec succès!');
                closeReservationForm();
                loadReservations();
                selectedMembers.clear(); // Vide l'ensemble selectedMembers après la réservation
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
          const startTime = $('#edit_start-time').val();
          const endTime =  $('#edit_end-time').val();
          console.log(startTime, endTime);
          if (!startTime || !endTime) {
              showErrorToast("Veuillez sélectionner une heure de début et de fin.");
              return;
          }
          const formData = {
              reservation_date: $('#edit_date').val(),
              start_time: startTime,
              end_time: endTime
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
                  closeEditReservation();
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
          generateHours(courtId, selectedDate,'reserve');
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