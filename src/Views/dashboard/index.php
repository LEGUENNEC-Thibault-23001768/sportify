<div data-view="dashboard">
    <h1>Bienvenue sur votre tableau de bord, <?= htmlspecialchars($user['first_name']) ?> !</h1>

    <?php if ($user['status'] === 'membre'): ?>
        <div class="main-section" data-view="member">
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

<script>
    var memberStatusData = {
        labels: [<?php foreach ($memberStatusDistribution as $status) { echo '"' . htmlspecialchars($status['status']) . '",'; } ?>],
        data: [<?php foreach ($memberStatusDistribution as $status) { echo $status['count'] . ','; } ?>]
    };

    var reservationsData = {
        labels: [<?php foreach ($reservationsByDay as $day) { echo '"' . htmlspecialchars($day['day_of_week']) . '",'; } ?>],
        data: [<?php foreach ($reservationsByDay as $day) { echo $day['total_reservations'] . ','; } ?>]
    };
</script>