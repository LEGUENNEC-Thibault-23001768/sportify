<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/_assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Suivi des Performances</title>
</head>
<body>
    <div class="container">
        <h1>Suivi des Performances</h1>

        <?php if (isset($user) && $user['status'] === 'admin'): ?>
            <h2>Statistiques Administrateur</h2>
            <p>Nombre total d'utilisateurs inscrits : <?= $totalUsers ?? 0 ?></p>

            <h3>Inscriptions des 4 dernières semaines :</h3>
            <div class="chart-container">
                <canvas id="registrationsChart" width="400" height="200"></canvas>
            </div>

            <p>Nombre d'abonnements actifs : <?= $activeSubscriptions ?? 0 ?></p>
            <p>Taux d'occupation global des terrains (moyenne sur la dernière semaine) : <?= number_format($globalOccupancyRate, 2) ?? 0 ?>%</p>

            <h3>Top 5 des activités les plus réservées (7 derniers jours) :</h3>
            <div class="chart-container">
                <canvas id="topActivitiesChart" width="400" height="200"></canvas>
            </div>

            <h3>Répartition des membres par statut :</h3>
            <div class="chart-container">
                <canvas id="memberStatusChart" width="400" height="200"></canvas>
            </div>

            <h3>Nombre de réservations par jour de la semaine (7 derniers jours) :</h3>
            <div class="chart-container">
                <canvas id="reservationsByDayChart" width="400" height="200"></canvas>
            </div>

            <p>Âge moyen des membres : <?= number_format($averageMemberAge, 1) ?? 0 ?> ans</p>
            <p>Taux de rétention des membres (6 derniers mois) : <?= number_format($retentionRate, 2) ?? 0 ?>%</p>

            <script>
                // Graphique pour les inscriptions des 4 dernières semaines (histogramme)
                var ctxRegistrations = document.getElementById('registrationsChart').getContext('2d');
                var registrationsChart = new Chart(ctxRegistrations, {
                    type: 'bar', // Type de graphique : histogramme
                    data: {
                        labels: [<?php foreach ($recentRegistrations as $registration) { echo '"Semaine ' . $registration['week_number'] . '",'; } ?>],
                        datasets: [{
                            label: 'Inscriptions',
                            data: [<?php foreach ($recentRegistrations as $registration) { echo $registration['registrations'] . ','; } ?>],
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Graphique pour le top 5 des activités (camembert)
                var ctxTopActivities = document.getElementById('topActivitiesChart').getContext('2d');
                var topActivitiesChart = new Chart(ctxTopActivities, {
                    type: 'pie', // Type de graphique : camembert
                    data: {
                        labels: [<?php foreach ($topActivities as $activity) { echo '"' . htmlspecialchars($activity['activity_type']) . '",'; } ?>],
                        datasets: [{
                            data: [<?php foreach ($topActivities as $activity) { echo $activity['total_reservations'] . ','; } ?>],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(54, 162, 235, 0.5)',
                                'rgba(255, 206, 86, 0.5)',
                                'rgba(75, 192, 192, 0.5)',
                                'rgba(153, 102, 255, 0.5)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                });

                // Graphique pour la répartition des membres par statut (camembert)
                var ctxMemberStatus = document.getElementById('memberStatusChart').getContext('2d');
                var memberStatusChart = new Chart(ctxMemberStatus, {
                    type: 'pie',
                    data: {
                        labels: [<?php foreach ($memberStatusDistribution as $status) { echo '"' . htmlspecialchars($status['status']) . '",'; } ?>],
                        datasets: [{
                            data: [<?php foreach ($memberStatusDistribution as $status) { echo $status['count'] . ','; } ?>],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(54, 162, 235, 0.5)',
                                'rgba(255, 206, 86, 0.5)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                });

                // Graphique pour le nombre de réservations par jour de la semaine (histogramme)
                var ctxReservationsByDay = document.getElementById('reservationsByDayChart').getContext('2d');
                var reservationsByDayChart = new Chart(ctxReservationsByDay, {
                    type: 'bar',
                    data: {
                        labels: [<?php foreach ($reservationsByDay as $day) { echo '"' . htmlspecialchars($day['day_of_week']) . '",'; } ?>],
                        datasets: [{
                            label: 'Nombre de réservations',
                            data: [<?php foreach ($reservationsByDay as $day) { echo $day['total_reservations'] . ','; } ?>],
                            backgroundColor: 'rgba(153, 102, 255, 0.5)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>

        <?php else: ?>
            <h2>Vos Statistiques</h2>

            <h3>Top 3 Activités les plus pratiquées</h3>
            <ul>
                <?php foreach ($topActivities as $activity): ?>
                    <li><?= htmlspecialchars($activity['activity_type']) ?> : <?= $activity['total_reservations'] ?> réservations</li>
                <?php endforeach; ?>
            </ul>

            <h3>Évolution des performances (Football)</h3>
            <canvas id="chartFootball"></canvas>

            <h3>Évolution des performances (Basketball)</h3>
            <canvas id="chartBasketball"></canvas>

            <h3>Évolution des performances (Musculation - Développé couché)</h3>
            <canvas id="chartMusculation"></canvas>

            <script>
                var ctxFootball = document.getElementById('chartFootball').getContext('2d');
                var chartFootball = new Chart(ctxFootball, {
                    type: 'line',
                    data: {
                        labels: [<?php foreach ($performanceDataFootball as $data) { echo '"' . $data['performance_date'] . '",'; } ?>],
                        datasets: [{
                            label: 'Score au Football',
                            data: [<?php foreach ($performanceDataFootball as $data) { echo $data['score'] . ','; } ?>],
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    },
                });

                var ctxBasketball = document.getElementById('chartBasketball').getContext('2d');
                var chartBasketball = new Chart(ctxBasketball, {
                    type: 'line',
                    data: {
                        labels: [<?php foreach ($performanceDataBasketball as $data) { echo '"' . $data['performance_date'] . '",'; } ?>],
                        datasets: [{
                            label: 'Score au Basketball',
                            data: [<?php foreach ($performanceDataBasketball as $data) { echo $data['score'] . ','; } ?>],
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1
                        }]
                    },
                });

                var ctxMusculation = document.getElementById('chartMusculation').getContext('2d');
                var chartMusculation = new Chart(ctxMusculation, {
                    type: 'line',
                    data: {
                        labels: [<?php foreach ($performanceDataMusculation as $data) { echo '"' . $data['performance_date'] . '",'; } ?>],
                        datasets: [{
                            label: 'Poids soulevé (kg)',
                            data: [<?php foreach ($performanceDataMusculation as $data) { echo $data['score'] . ','; } ?>],
                            borderColor: 'rgb(255, 205, 86)',
                            tension: 0.1
                        }]
                    },
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>