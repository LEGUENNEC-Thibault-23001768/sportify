function initialize() {
    let reservations = []; 
    const rooms = document.querySelectorAll('.room'); 
    const reservationForm = document.getElementById('reservation-container'); 
    const reservationList = document.getElementById('reservation-list');
    function displayRoomReservations(roomName) {
      reservationList.innerHTML = ''; 
  
      const roomReservations = reservations.filter(reservation => reservation.room === roomName);
  
      if (roomReservations.length === 0) {
        const noReservationItem = document.createElement('li');
        noReservationItem.textContent = `Aucune réservation pour la salle ${roomName}.`;
        reservationList.appendChild(noReservationItem);
      } else {
        roomReservations.forEach(reservation => {
          const reservationItem = document.createElement('li');
          reservationItem.textContent = `${reservation.name} a réservé pour le ${reservation.date} de ${reservation.startTime} à ${reservation.endTime}`;
  
          const deleteButton = document.createElement('button');
          deleteButton.textContent = 'Supprimer';
          deleteButton.onclick = function() {
            reservations = reservations.filter(r => r !== reservation);
            displayRoomReservations(roomName);
          };
  
          reservationItem.appendChild(deleteButton);
          reservationList.appendChild(reservationItem);
        });
      }
    }
  
    rooms.forEach(room => {
      room.addEventListener('click', () => {
        const roomName = room.getAttribute('data-room');
        displayRoomReservations(roomName);
        reservationForm.style.display = 'block'; 
        document.getElementById('form').setAttribute('data-room', roomName); 
      });
    });
  
    function closeReservationForm() {
      reservationForm.style.display = 'none';
    }
  
    const reservationFormElement = document.getElementById('form'); 
  
    reservationFormElement.addEventListener('submit', function(event) {
      event.preventDefault(); 
  
      const name = document.getElementById('member_name').value;
      const date = document.getElementById('date').value;
      const startTime = document.getElementById('selected-time').value; 
      const duration = document.getElementById('duration').value; 
      const endTime = calculateEndTime(startTime, duration);
  
      const room = document.getElementById('form').getAttribute('data-room');
  
      if (validateTime(startTime, endTime)) { 
        const isReserved = reservations.some(reservation =>
          reservation.room === room &&
          reservation.date === date &&
          isTimeConflict(reservation.startTime, reservation.endTime, startTime, endTime)
        );
  
        if (isReserved) {
          alert(`La salle ${room} est déjà réservée pour le ${date} à ${startTime}.`);
        } else {
          reservations.push({ name, date, startTime, endTime, room });
          const formData = new FormData(reservationFormElement);
  
          fetch('/dashboard/booking/store', { 
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
          })
            .then(response => response.json())
            .then(data => {
              if (data.error) {
                alert(data.error);
              } else {
                displayRoomReservations(room);
                reservationFormElement.reset();
              }
            })
            .catch(error => {
              console.error('Erreur:', error);
              alert('Une erreur est survenue. Veuillez réessayer.');
            });
        }
      } else {
        alert("L'heure de début doit être antérieure à l'heure de fin.");
      }
    });
  
    document.getElementById('cancel-button').addEventListener('click', closeReservationForm);
  
    function calculateEndTime(startTime, duration) {
        const startTimeParts = startTime.split(':');
        let hours = parseInt(startTimeParts[0]);
        const minutes = parseInt(startTimeParts[1]);

        hours += parseInt(duration);

        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    }

    function validateTime(startTime, endTime) {
        return new Date(`1970-01-01T${startTime}:00`) < new Date(`1970-01-01T${endTime}:00`);
    }

    function isTimeConflict(existingStart, existingEnd, newStart, newEnd) {
        return existingStart < newEnd && existingEnd > newStart;
    }
}




document.addEventListener('DOMContentLoaded', initialize);