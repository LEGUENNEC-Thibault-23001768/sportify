<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/_assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Tableau de bord</title>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <a href="home.html">
            <img src="https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png" alt="Logo Sportify">
        </a>
    </div>
    <ul>
        <li><a href="/dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i> Suivi</a></li>
        <li><a href="/dashboard/booking"><i class="fas fa-futbol"></i> Terrains</a></li>
        <li><a href="#"><i class="fas fa-user-friends"></i> Entraîneurs</a></li>
        <li><a href="/dashboard/events"><i class="fas fa-trophy"></i> Événements</a></li>
        <li><a href="/dashboard/training"><i class="fas fa-calendar-alt"></i> Programme</a></li>
        <li><a href="/dashboard/admin/users" class="management"><i class="fas fa-tasks"></i> Gestion</a></li>
    </ul>
 
    <div class="settings-section">
        <a href="#" class="settings"><i class="fas fa-cog"></i> Paramètres</a>
        <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>
<div class="navbar">
    <div class="logo"></div>
    <div class="profile-info">
        <p class="profile-name"><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></p>
        <div class="subscription-status">
            <?php if (!isset($hasActiveSubscription) || !$hasActiveSubscription): ?>
                <form action="/create-checkout-session" method="POST">
                    <button type="submit" class="subscribe-button">S'abonner</button>
                </form>
            <?php else: ?>
                <p class="active-subscription">Abonnement actif</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="profile-icon">
        <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://i.pinimg.com/564x/7e/8c/81/7e8c8119bf240d4971880006afb7e1e6.jpg'; ?>" alt="Profil" id="profile-icon">
        <div class="dropdown" id="dropdown">
            <a href="/dashboard/profile">Mon profil</a>
            <a href="/logout">Déconnexion</a>
        </div>
    </div>
</div>
 
<div class="dashboard-content">
    <h1>Bienvenue sur votre tableau de bord, <?= htmlspecialchars($user['first_name']) ?> !</h1>
 
    <?php if ($user['status'] === 'membre'): ?>
        <link rel="stylesheet" href="/_assets/css/member.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <div class="main-section">
            <div class="left-section">
                <div class="report-controls">
                    <select name="week" id="week-select">
                        <option value="current">Semaine actuelle</option>
                        <option value="previous">Semaine précédente</option>
                    </select>
                </div>
                <div class="report-section">
                    <div class="report-card performance-card">
                        <div class="report-title">Dernières performances</div>
                        <div class="report-percentage">+5%</div>
                        <div class="report-value">Squat : 120kg</div>
                        <div class="report-previous">Semaine dernière : 115kg</div>
                    </div>
                    <div class="report-card game-time-card">
                        <div class="report-title">Temps de jeu</div>
                        <div class="report-percentage">+10%</div>
                        <div class="report-value">15 heures</div>
                        <div class="report-previous">Semaine dernière : 13 heures</div>
                    </div>
                    <div class="report-card calories-card">
                        <div class="report-title">Calories brûlées</div>
                        <div class="report-percentage">+20%</div>
                        <div class="report-value">650 Kcal</div>
                        <div class="report-subtitle">Objectif : 1500 Kcal</div>
                    </div>
                </div>
            </div>
            <div class="progression-section">
                <div class="task-completion-card">
                    <div class="progression-title">Progression</div>
                    <div class="circle-container">
                        <canvas id="taskCompletionChart"></canvas>
                        <div id="chart-center-text">71%</div>
                    </div>
                </div>
            </div>
        </div>
        <div mbsc-page class="demo-mobile-day-view">
            <div style="">  <div id="demo-mobile-day-view"></div>
            </div>
        </div>
    <?php elseif ($user['status'] === 'coach' || $user['status'] === 'admin'): ?>
        <div class="coach-panel">
        <h2>
            <img src="https://emojigraph.org/media/apple/raising-hands_1f64c.png" alt="Gestion événements" class="emoji-image">
            Gestion événements
        </h2>
        <p>Vous pouvez créer et gérer des événements pour les membres.</p>
        <a href="/dashboard/events" class="btn">Gérer les événements</a>
    </div>
<?php endif; ?>
 
<?php if ($user['status'] === 'admin'): ?>
    <div class="admin-panel">
        <h2>
            <img src="https://emojigraph.org/media/apple/busts-in-silhouette_1f465.png" alt="Gestion utilisateurs" class="emoji-image">
            Gestion utilisateurs
        </h2>
        <p>Vous pouvez gérer tous les utilisateurs et accéder aux paramètres globaux du système.</p>
        <a href="/dashboard/admin/users" class="btn btn-danger">Gérer les utilisateurs</a>
    </div>
    <div class="card">
        <h3 class="title rapport-activite">
            <img src="https://emojigraph.org/media/apple/bar-chart_1f4ca.png" alt="Rapport d'activité" class="emoji-image">
            Rapport d'activité
        </h3>
        <ul>
            <li>Nombre total d'utilisateurs inscrits : <b><?= $totalUsers ?? 0 ?></b></li>
            <li>Nombre d'inscriptions cette semaine : <b><?= array_sum(array_column($recentRegistrations, 'registrations')) ?></b></li>
            <li>Nombre d'abonnements actifs : <b><?= $activeSubscriptions ?? 0 ?></b></li>
            <li>Taux d'occupation global des terrains (moyenne sur la dernière semaine) : <b><?= number_format($globalOccupancyRate, 2) ?? 0 ?>%</b></li>
            <li>Âge moyen des membres : <b><?= number_format($averageMemberAge, 1) ?? 0 ?> ans</b></li>
            <li>Taux de rétention des membres (6 derniers mois) : <b><?= number_format($retentionRate, 2) ?? 0 ?>%</b></li>
        </ul>
    </div>
 
    <div class="card">
        <h3 class="title prochaines-reservations">
            <img src="https://emojigraph.org/media/apple/person-lifting-weights_1f3cb-fe0f.png" alt="Top 5 des Activités" class="emoji-image">
            Top 5 des Activités (7 derniers jours)
        </h3>
        <ul>
            <?php foreach ($topActivities as $activity): ?>
                <li><?= htmlspecialchars($activity['activity_type']) ?> : <b><?= $activity['total_reservations'] ?> réservations</b></li>
            <?php endforeach; ?>
        </ul>
    </div>
 
    <div class="card-row">
        <div class="small-card">
            <div class="card-title">
                <img src="https://emojigraph.org/media/apple/busts-in-silhouette_1f465.png" alt="Répartition des membres par statut" class="emoji-image">
                Répartition des membres par statut
            </div>
            <canvas id="memberStatusChart"></canvas>
        </div>
        <div class="small-card">
            <div class="card-title">
                <img src="https://emojigraph.org/media/google/calendar_1f4c5.png" alt="Réservations par jour" class="emoji-image">
                Réservations par jour (7 derniers jours)
            </div>
            <canvas id="reservationsByDayChart"></canvas>
        </div>
    </div>
 
    <?php endif; ?>
</div>
 
<?php
if (isset($_SESSION['message'])) {
    echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['message']) . "</div>";
    unset($_SESSION['message']);
}
?>
<script src="/_assets/js/mobiscroll.min.js"></script>
<link rel="stylesheet" href="/_assets/css/mobiscroll.min.css">
 
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
    });
    document.getElementById('profile-icon').addEventListener('click', function() {
        const dropdown = document.getElementById('dropdown');
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    });
 
    window.onclick = function(event) {
        if (!event.target.matches('#profile-icon')) {
            const dropdown = document.getElementById('dropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        }
    };
    mobiscroll.setOptions({
        locale: mobiscroll.localeFr,
        theme: 'ios',
        themeVariant: 'dark'
    });
 
    var inst = mobiscroll.eventcalendar('#demo-mobile-day-view', {
        view: {
            schedule: {
                type: 'week',
                startTime: '06:00',
                endTime: '24:00',
                allDay: false
            }
        },
        scrollable: false,
        dragToCreate: true,
        dragToMove: true,
        dragToResize: true,
        renderEvent: function (event) {
            return {
                html: `
                    <div>
                        <div class="mbsc-event-title">${event.title}</div>
                        ${event.description ? `<div class="mbsc-event-desc">${event.description}</div>` : ''}
                    </div>
                `
            };
        },
        onEventClick: function (args) {
            var form = document.getElementById('event-form');
            var calendarBounds = document.getElementById('demo-mobile-day-view').getBoundingClientRect();
            var popupWidth = form.offsetWidth;
            var popupHeight = form.offsetHeight;
 
            let popupX = args.domEvent.clientX;
            let popupY = args.domEvent.clientY;
 
            if (popupX + popupWidth > calendarBounds.right) {
                popupX = args.domEvent.clientX - popupWidth - 10;
            }
 
            if (popupY + popupHeight > calendarBounds.bottom) {
                popupY = args.domEvent.clientY - popupHeight - 10;
            }
 
            popupX = Math.max(calendarBounds.left + 10, Math.min(popupX, calendarBounds.right - popupWidth - 10));
            popupY = Math.max(calendarBounds.top + 10, Math.min(popupY, calendarBounds.bottom - popupHeight - 10));
 
            form.style.display = 'block';
            form.style.left = popupX + 'px';
            form.style.top = popupY + 'px';
 
            var titleInput = document.getElementById('event-title');
            var descriptionInput = document.getElementById('event-description');
 
            titleInput.value = args.event.title || '';
            descriptionInput.value = args.event.description || '';
 
            titleInput.oninput = function () {
                args.event.title = this.value;
            };
 
            descriptionInput.oninput = function () {
                args.event.description = this.value;
            };
 
            // Sauvegarde et mise à jour quand on appuie sur Entrée
            titleInput.onkeydown = descriptionInput.onkeydown = function (e) {
                if (e.key === 'Enter') {
                    args.event.title = titleInput.value;
                    args.event.description = descriptionInput.value;
                    inst.updateEvent(args.event);
                    form.style.display = 'none'; // Fermer le popup après sauvegarde
                }
            };
 
            // Mise à jour des couleurs
            document.querySelectorAll('.color-option').forEach(option => {
                option.onclick = function () {
                    document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    args.event.color = this.getAttribute('data-color');
                    inst.updateEvent(args.event);
                };
                option.classList.toggle('selected', option.getAttribute('data-color') === args.event.color);
            });
 
            form.onmousedown = function (e) {
                if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && !e.target.closest('.color-option')) {
                    e.preventDefault();
                    var offsetX = e.offsetX;
                    var offsetY = e.offsetY;
 
                    function moveAt(pageX, pageY) {
                        form.style.left = Math.max(calendarBounds.left, Math.min(pageX - offsetX, calendarBounds.right - popupWidth)) + 'px';
                        form.style.top = Math.max(calendarBounds.top, Math.min(pageY - offsetY, calendarBounds.bottom - popupHeight)) + 'px';
                    }
 
                    function onMouseMove(event) {
                        moveAt(event.pageX, event.pageY);
                    }
 
                    document.addEventListener('mousemove', onMouseMove);
 
                    form.onmouseup = function () {
                        document.removeEventListener('mousemove', onMouseMove);
                        form.onmouseup = null;
                    };
                }
            };
        },
        onEventDelete: function () {
            var form = document.getElementById('event-form');
            form.style.display = 'none'; // Cache le popup uniquement lors de la suppression de l'événement
        },
        data: [
            { color: '#4981d6', start: '2024-11-16T08:00', end: '2024-11-16T09:00', title: 'Entraînement cardio', description: 'Séance intense pour améliorer l\'endurance.' },
            { color: '#C1FF72', start: '2024-11-16T12:00', end: '2024-11-16T13:30', title: 'Séance de musculation', description: 'Focus sur les jambes et les épaules.' },
            { color: '#ff5e57', start: '2024-11-16T15:00', end: '2024-11-16T16:00', title: 'Yoga', description: 'Séance de relaxation et étirements.' },
            { color: '#ffa726', start: '2024-11-16T18:00', end: '2024-11-16T19:00', title: 'Boxe', description: 'Entraînement de boxe avec sparring.' }
        ]
    });
 
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('event-form').style.display = 'none';
        var ctx = document.getElementById('taskCompletionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Complet', 'Manqué'],
                datasets: [
                    {
                        data: [71, 29],
                        backgroundColor: ['rgba(255, 105, 180, 0.8)', '#666'],
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                elements: {
                    arc: {
                        borderRadius: 20
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });
        var chartCanvas = document.getElementById('taskCompletionChart');
        chartCanvas.style.filter = 'drop-shadow(0px 0px 15px rgba(255, 105, 180, 0.7))';
    });
 
    var form = document.getElementById('event-form');
    form.onmouseleave = null;
 
    document.addEventListener('click', function (e) {
        if (!form.contains(e.target) && !e.target.closest('.mbsc-schedule-event')) {
            form.style.display = 'none';
        }
    });
</script>
 
</body>
</html>