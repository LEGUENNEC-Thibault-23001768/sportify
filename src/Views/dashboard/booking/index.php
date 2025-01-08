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
    </div>

    <div id="reservations">
        <h3>Historique des Réservations</h3>
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

    <script>
        function openReservationForm(roomElement) {
            const courtId = roomElement.getAttribute('data-court-id');
            document.getElementById('court_id').value = courtId;
            document.getElementById('reservation-container').style.display = 'block';
        }

        function closeReservationForm() {
            document.getElementById('reservation-container').style.display = 'none';
            document.getElementById('form').reset();
        }
    </script>

    <script>

           
    function handleHourClick(event) {
        const clickedButton = event.target;
        const startHour = parseInt(clickedButton.dataset.hour);
        const selectedButtons = document.querySelectorAll('#available-hours button.selected');
        selectedButtons.forEach(btn => btn.classList.remove('selected'));
        clickedButton.classList.add('selected');
        document.getElementById('selected-time').value = `${startHour.toString().padStart(2, '0')}:00`;
        document.getElementById('duration').value = 1;
        if (startHour <= 21) {
            const nextButton = clickedButton.nextElementSibling;
            if (nextButton) {
                if (confirm("Cliquez sur annuler pour réserver 1h et ok pour un créneau de 2h !")) {
                    document.getElementById('duration').value = 2;
                    nextButton.classList.add('selected');
                }
            }
        }
    }
        

    function generateHours() {
        const selectedDate = document.getElementById('date').value;
        const availableHoursDiv = document.getElementById('available-hours');
        availableHoursDiv.innerHTML = '';

        if (selectedDate) {
            for (let hour = 8; hour <= 22; hour++) {
                const button = document.createElement('button');
                button.textContent = `${hour.toString().padStart(2, '0')}:00`;
                button.type = 'button';
                button.dataset.hour = hour;
                button.addEventListener('click', handleHourClick); 
                availableHoursDiv.appendChild(button);
            }
        }
    }
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
    </script>

</body>
</html>

