let reservations = [];
const rooms = document.querySelectorAll('.room');
const form = document.getElementById('reservation-container');
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

    form.style.display = 'block';
    document.getElementById('form').setAttribute('data-room', roomName);
}

rooms.forEach(room => {
    room.addEventListener('click', () => {
        const roomName = room.getAttribute('data-room');
        displayRoomReservations(roomName); 
    });
});

function closeReservationForm() {
    form.style.display = 'none';
}

document.getElementById('form').addEventListener('submit', function(event) {
    event.preventDefault();

    const name = document.getElementById('name').value;
    const date = document.getElementById('date').value;
    const startTime = document.getElementById('start-time').value;
    const endTime = document.getElementById('end-time').value;
    const room = document.getElementById('form').getAttribute('data-room');

    if (new Date(`1970-01-01T${startTime}:00`) >= new Date(`1970-01-01T${endTime}:00`)) {
        alert("L'heure de début doit être antérieure à l'heure de fin.");
        return;
    }

    const isReserved = reservations.some(reservation =>
        reservation.room === room && reservation.date === date &&
        (reservation.startTime < endTime && reservation.endTime > startTime)
    );

    if (isReserved) {
        alert(`La salle ${room} est déjà réservée pour le ${date} de ${startTime} à ${endTime}.`);
    } else {
        reservations.push({ name, date, startTime, endTime, room });
        form.reset();
        displayRoomReservations(room);
    }
});

document.getElementById('cancel-button').addEventListener('click', function() {
    closeReservationForm();
});
