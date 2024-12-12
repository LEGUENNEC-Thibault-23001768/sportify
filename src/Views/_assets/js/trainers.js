document.addEventListener('DOMContentLoaded', function() {
    const reservationForm = document.getElementById('reservation-form');
    const scheduleTable = document.getElementById('schedule-table');
    
    let reservations = [];
    function addReservation(coach, date, time) {
        reservations.push({ coach, date, time });
        updateTable();
    }
    function updateTable() {
        scheduleTable.innerHTML = '';
        reservations.forEach((reservation, index) => {
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td>${reservation.coach}</td>
                <td>${reservation.date}</td>
                <td>${reservation.time}</td>
                <td>
                    <button class="action-btn" onclick="editReservation(${index})">Modifier</button>
                    <button class="action-btn" onclick="deleteReservation(${index})">Supprimer</button>
                </td>
            `;
            scheduleTable.appendChild(row);
        });
    }
    reservationForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const coach = document.getElementById('coach-select').value;
        const date = document.getElementById('date').value;
        const time = document.getElementById('time').value;

        if (coach && date && time) {
            addReservation(coach, date, time);
            reservationForm.reset();
        } else {
            alert('Veuillez remplir tous les champs.');
        }
    });
    window.editReservation = function(index) {
        const reservation = reservations[index];
        document.getElementById('coach-select').value = reservation.coach;
        document.getElementById('date').value = reservation.date;
        document.getElementById('time').value = reservation.time;

        deleteReservation(index); 
    }
    window.deleteReservation = function(index) {
        reservations.splice(index, 1);
        updateTable();
    }
});