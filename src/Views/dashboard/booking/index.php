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

    <div class="gym-map">
        <img src="../_assets/img/Map_sportify.png" alt="Carte de la salle de sport" class="gym-image">
        <div class="room tennis" data-room="Tennis" data-court-id="6" onclick="openReservationForm(this)">Tennis</div>
        <div class="room foot" data-room="Foot" data-court-id="1" onclick="openReservationForm(this)">Football</div>
        <div class="room rpm" data-room="RPM" data-court-id="3" onclick="openReservationForm(this)">RPM</div>
        <div class="room yoga" data-room="Yoga" data-court-id="4" onclick="openReservationForm(this)">Yoga</div>
        <div class="room basketball" data-room="Basketball" data-court-id="2" onclick="openReservationForm(this)">Basketball</div>
        <div class="room boxe" data-room="Boxe" data-court-id="5" onclick="openReservationForm(this)">Boxe</div>
    </div>

    <div id="reservation-container" style="display: none;">
        <h3>Réserver une salle</h3>
        <?php if (!empty($error)) : ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="/dashboard/booking/store" method="POST" id="form">
            <label for="member_name">Votre Nom:</label>
            <input type="hidden" id="court_id" name="court_id">
            <input type="text" id="member_name" name="member_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>
            <input type="hidden" name="member_id" value="<?php echo $user['member_id']; ?>">

            <label for="date">Date:</label>
            <input type="date" id="date" name="reservation_date" required>

            <label for="start-time">Heure de Début:</label>
            <input type="time" id="start-time" name="start_time" required>

            <label for="end-time">Heure de Fin:</label>
            <input type="time" id="end-time" name="end_time" required>

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
                    <form action="/dashboard/booking/<?= $booking['reservation_id'] ?>/delete" method="POST" style="display:inline;">
                        <button type="submit">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        // Open reservation form with selected court_id
        function openReservationForm(roomElement) {
            const courtId = roomElement.getAttribute('data-court-id');  // Get the data-court-id
            document.getElementById('court_id').value = courtId;       // Set the court_id in hidden input
            document.getElementById('reservation-container').style.display = 'block'; // Show the form
        }

        // Close reservation form
        function closeReservationForm() {
            document.getElementById('reservation-container').style.display = 'none';
            document.getElementById('form').reset();
        }
    </script>

</body>
</html>
