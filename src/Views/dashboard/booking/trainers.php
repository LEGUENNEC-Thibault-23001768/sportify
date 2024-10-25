<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation de terrain</title>
    <link rel="stylesheet" href="_assets/css/trainers.css">
</head>
<body>
    <h1>Réservation d'entraîneur</h1> 
    <div class="container">
        <form id="reservation-form">
            <label for="coach-select">Choisissez un entraîneur :</label>
            <select id="coach-select" required>
                <option value="" disabled selected>-- Sélectionner un entraîneur --</option>
                <option value="coach1">Entraîneur 1</option>
                <option value="coach2">Entraîneur 2</option>
                <option value="coach3">Entraîneur 3</option>
            </select>

            <label for="date">Choisissez une date :</label>
            <input type="date" id="date" required>

            <label for="time">Choisissez une heure :</label>
            <input type="time" id="time" required>

            <button type="submit">Réserver</button>
        </form>

        <h2 class="disponibility-label">Disponibilité des entraîneurs</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Entraîneur</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="schedule-table">
                <tr>
                    <td>Entraîneur 1</td>
                    <td>2024-10-20</td>
                    <td>10:00</td>
                    <td>
                        <div class="button-group">
                            <button class="action-btn">Modifier</button>
                            <button class="action-btn">Supprimer</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Entraîneur 2</td>
                    <td>2024-10-21</td>
                    <td>11:00</td>
                    <td>
                        <div class="button-group">
                            <button class="action-btn">Modifier</button>
                            <button class="action-btn">Supprimer</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="_assets/js/trainers.js"></script>
</body>
</html>