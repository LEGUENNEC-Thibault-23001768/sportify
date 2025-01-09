<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte de la Salle de Sport</title>
    <link rel="stylesheet" href="../_assets/css/booking.css">
</head>
<body>

    <h2>Carte de la Salle de Sport</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div style="color: green; background-color: #e6ffe6; padding: 10px; margin-bottom: 10px;">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div style="color: red; background-color: #ffe6e6; padding: 10px; margin-bottom: 10px;">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="gym-map">
        <img src="../_assets/img/Map_sportify.png" alt="Carte de la salle de sport" class="gym-image">
        <div class="room tennis" data-room="Tennis" data-court-id="6" onclick="openReservationForm(this)">Tennis</div>
        <div class="room foot" data-room="Foot" data-court-id="1" onclick="openReservationForm(this)">Football</div>
        <div class="room rpm" data-room="RPM" data-court-id="3" onclick="openReservationForm(this)">RPM</div>
        <div class="room musculation" data-room="Musculation" data-court-id="4" onclick="openReservationForm(this)">Musculation</div>
        <div class="room basketball" data-room="Basketball" data-court-id="2" onclick="openReservationForm(this)">Basketball</div>
        <div class="room boxe" data-room="Boxe" data-court-id="5" onclick="openReservationForm(this)">Boxe</div>
    </div>

    <div id="reservation-container" style="display: none;">
    <h3>Réserver une salle</h3>
        <form action="/dashboard/booking/store" method="POST" id="form">
            <input type="hidden" id="court_id" name="court_id">
            <input type="hidden" name="member_id" value="<?php echo $user['member_id']; ?>">
            <label for="member_name">Votre Nom:</label>
            <input type="text" id="member_name" name="member_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>

            <label for="date">Date:</label>
            <input type="date" id="date" name="reservation_date" required onchange="generateHours()">

            <label>Veuillez sélectionner vos horaires :</label> 
            <div id="available-hours">
            </div>

            <input type="hidden" id="selected-time" name="start_time">
            <input type="hidden" id="duration" name="duration">
            <button type="submit">Réserver</button>
            <button type="button" onclick="closeReservationForm()">Annuler</button>
        </form>

        <div id="reservations" class="reservations-container"> <h3>Historique de mes réservations</h3>
            <ul id="reservation-list">
                <?php foreach ($bookings as $booking): ?>
                <li>
                    <?= $booking['reservation_date'] ?> - <?= $booking['start_time'] ?> à <?= $booking['end_time'] ?> -
                    <?= $booking['court_name'] ?> (Réservé par <?= $booking['member_name'] ?>)
                    <?php if ($booking['member_id'] == $user['member_id'] || $user['status'] === 'admin'): ?>
                            <a href="/dashboard/booking/<?= $booking['reservation_id'] ?>/edit">Modifier</a>
                            <form action="/dashboard/booking/<?= $booking['reservation_id'] ?>/delete" method="POST" style="display:inline;">
                        <button type="submit">Supprimer</button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            </div>
        </div>

    <script>   
    const idUtilisateurActuel = <?= $_SESSION['user_id'] ?? 'null' ?>;
    const estAdmin = <?= ($user['status'] === 'admin') ? 'true' : 'false' ?>;

    function handleHourClick(event) {
    const clickedButton = event.target;
    const startHour = parseInt(clickedButton.dataset.hour);
    const selectedButtons = document.querySelectorAll('#available-hours button.selected');

    if (selectedButtons.length >= 2 && !clickedButton.classList.contains('selected')) {
        alert("Vous ne pouvez sélectionner que deux heures maximum.");
        return; 
    }

    clickedButton.classList.toggle('selected'); 

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
        

function generateHours() {
    const selectedDate = document.getElementById('date').value;
    const courtId = document.getElementById('court_id').value; 
    const availableHoursDiv = document.getElementById('available-hours');
    availableHoursDiv.innerHTML = '';

    if (selectedDate && courtId) {
        console.log("Date sélectionnée :", selectedDate);
        console.log("ID du court :", courtId); 
        const today = new Date();
        const selectedDateObj = new Date(selectedDate);

        if (selectedDateObj < today.setHours(0, 0, 0, 0)) {
            alert("Vous ne pouvez pas réserver pour une date dans le passé.");
            document.getElementById('date').value = '';
            return;
        }

        fetch('/dashboard/booking/getBookingsByCourtAndDate?court_id=' + courtId + '&date=' + selectedDate) 
        .then(response => {
                console.log("Réponse brute :", response); 

                if (!response.ok) { 
                    console.error("Erreur HTTP :", response.status, response.statusText);
                    return Promise.reject(new Error(`Erreur HTTP : ${response.status} ${response.statusText}`)); 
                }

                return response.text();
            })
            .then(text => { 
                console.log("Texte de la réponse :", text);

                let existingBookings;

                try {
                    existingBookings = JSON.parse(text);
                    console.log("Réservations existantes :", existingBookings);
                } catch (error) {
                    console.error("Erreur lors du parsing JSON :", error);
                    console.error("Texte qui n'a pas pu être parsé :", text);
                    availableHoursDiv.innerHTML = "<p>Une erreur est survenue lors du chargement des disponibilités.</p>";
                    return; 
                }
                if (!Array.isArray(existingBookings)) {
                    console.error("La réponse du serveur n'est pas un tableau :", existingBookings);
                    availableHoursDiv.innerHTML = "<p>Une erreur est survenue lors du chargement des disponibilités.</p>";
                    return;
                    }

                    if (existingBookings.length === 0) {
                        console.log("Aucune réservation existante pour cette date et ce court.");
                    }

                for (let hour = 8; hour <= 22; hour++) {
                    const hourString = `${hour.toString().padStart(2, '0')}:00`;
                    const isBooked = existingBookings.some(booking => {
                        const bookingStartTime = booking.start_time.substring(0, 5);
                        const bookingEndTime = booking.end_time.substring(0, 5);
                        return hourString >= bookingStartTime && hourString < bookingEndTime;
                    });

                    const button = document.createElement('button');
                    button.textContent = hourString;
                    button.type = 'button';
                    button.dataset.hour = hour;
                    button.disabled = isBooked;
                    if (isBooked) {
                        button.classList.add('booked'); 
                    } else {
                        button.addEventListener('click', handleHourClick);
                    }
                    availableHoursDiv.appendChild(button);
                } 

            })
            .catch(error => console.error('Erreur globale lors de la requête:', error));
        }}
  
            
        function openReservationForm(roomElement) {
        const courtId = roomElement.getAttribute('data-court-id');
        document.getElementById('court_id').value = courtId;
        document.getElementById('reservation-container').style.display = 'block';
        const dateInput = document.getElementById('date');
        if (!dateInput.value) {
            dateInput.valueAsDate = new Date();
        }
        generateHours();
    }

    function closeReservationForm() {
            document.getElementById('reservation-container').style.display = 'none';
            document.getElementById('form').reset();
        }

            document.getElementById('form').addEventListener('submit', function(event) {
            const selectedTime = document.getElementById('selected-time').value;
            if (!selectedTime) {
                alert("Veuillez sélectionner une heure.");
                event.preventDefault(); 
                return;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
        const reservationList = document.getElementById('reservation-list');
        const reservationsContainer = document.getElementById('reservations');
        const reservationContainer = document.getElementById('reservation-container');

function fetchReservations() {
    fetch('/dashboard/booking/getReservations')
        .then(response => {
            if (!response.ok) {
                console.error("Erreur HTTP :", response.status);
                return Promise.reject(new Error(`Erreur HTTP : ${response.status}`));
            }
            return response.json(); 
        })
        .then(bookings => { 
            const reservationList = document.getElementById('reservation-list');
            reservationList.innerHTML = ''; 

            if (bookings.length === 0) {
                reservationList.innerHTML = '<li>Aucune réservation.</li>';
                return; 
            }

            bookings.forEach(booking => {
                const li = document.createElement('li');
                let texteReservation = `${booking.reservation_date} - ${booking.start_time} à ${booking.end_time} - ${booking.court_name} (Réservé par ${booking.member_name})`;
                if (booking.reservation_member_id == idUtilisateurActuel || estAdmin) {
                    const lienModifier = document.createElement('a');
                    lienModifier.href = `/dashboard/booking/${booking.reservation_id}/edit`;
                    lienModifier.textContent = 'Modifier';
                    lienModifier.style.marginRight = '5px'; 

                    const formulaireSupprimer = document.createElement('form');
                    formulaireSupprimer.action = `/dashboard/booking/${booking.reservation_id}/delete`;
                    formulaireSupprimer.method = 'POST';
                    formulaireSupprimer.style.display = 'inline';

                    const boutonSupprimer = document.createElement('button');
                    boutonSupprimer.type = 'submit';
                    boutonSupprimer.textContent = 'Supprimer';
                    formulaireSupprimer.appendChild(boutonSupprimer);

                    li.append(document.createTextNode(texteReservation), lienModifier, formulaireSupprimer); 
                } else {
                    li.textContent = texteReservation;
                }

                reservationList.appendChild(li);
            });
        })
        .catch(error => console.error('Erreur lors de la requête:', error));
}
    fetchReservations();

    if (reservationList.children.length > 0) {
        reservationsContainer.style.display = 'block';
    } else {
        reservationsContainer.style.display = 'none';
    }

    const rooms = document.querySelectorAll('.room');
    rooms.forEach(room => {
        room.addEventListener('click', () => {
            reservationContainer.style.display = 'block';
            fetchReservations();
        });
    });

});

    </script>

</body>
</html>

