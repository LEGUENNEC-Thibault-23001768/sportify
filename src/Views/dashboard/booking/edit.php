<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Réservation</title>
    <link rel="stylesheet" href="../_assets/css/booking.css">
</head>
<body>

    <h2>Modifier la Réservation</h2>

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

    <form action="/dashboard/booking/<?= $reservation['reservation_id'] ?>/update" method="POST">
        <label for="member_name">Votre Nom:</label>
        <input type="text" id="member_name" name="member_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>

        <label for="date">Date:</label>
        <input type="date" id="date" name="reservation_date" value="<?= $reservation['reservation_date'] ?>" required>

        <label for="start-time">Heure de Début:</label>
        <input type="time" id="start-time" name="start_time" value="<?= $reservation['start_time'] ?>" required>

        <label for="end-time">Heure de Fin:</label>
        <input type="time" id="end-time" name="end_time" value="<?= $reservation['end_time'] ?>" required>

        <button type="submit">Mettre à jour</button>
        <a href="/dashboard/booking">Annuler</a>
    </form>
</body>
</html>