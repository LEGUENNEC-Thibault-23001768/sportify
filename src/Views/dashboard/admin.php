<h1>Bienvenue sur votre tableau de bord, <?= htmlspecialchars($user['first_name']) ?> !</h1>

<?php if ($user['status'] === 'coach' || $user['status'] === 'admin'): ?>
    <div class="coach-panel">
        <h2> 📅 Gestion événements</h2>
        <p>Vous pouvez créer et gérer des événements pour les membres.</p>
        <a href="/dashboard/events" class="btn">Gérer les événements</a>
    </div>
<?php endif; ?>

<?php if ($user['status'] === 'admin'): ?>
    <div class="admin-panel">
        <h2> 👥 Gestion utilisateurs</h2>
        <p>Vous pouvez gérer tous les utilisateurs et accéder aux paramètres globaux du système.</p>
        <a href="/dashboard/admin/users" class="btn btn-danger">Gérer les utilisateurs</a>
    </div>

    <div class="card">
        <h3 class="title rapport-activite">📊 Rapport d'activité</h3>
        <ul>
            <li>Nombre total d'utilisateurs inscrits : <?= $totalUsers ?></li>
            <li>Nombre d'inscriptions cette semaine : <?= array_sum(array_column($recentRegistrations, 'registrations')) ?></li>
            <li>Nombre d'abonnements actifs : <?= $activeSubscriptions ?></li>
            <li>Taux d'occupation global des terrains (moyenne sur la dernière semaine) : <?= number_format($globalOccupancyRate, 2) ?>%</li>
            <li>Âge moyen des membres : <?= number_format($averageMemberAge, 1) ?> ans</li>
            <li>Taux de rétention des membres (6 derniers mois) : <?= number_format($retentionRate, 2) ?>%</li>
        </ul>
    </div>

    <div class="card">
        <h3 class="title prochaines-reservations">🏋️ Top 5 des Activités (7 derniers jours)</h3>
        <ul>
            <?php foreach ($topActivities as $activity): ?>
                <li><?= htmlspecialchars($activity['activity_type']) ?> : <?= $activity['total_reservations'] ?> réservations</li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-row">
        <div class="small-card">
            <div class="card-title"><span class="emoji">👥</span> Répartition des membres par statut</div>
            <canvas id="memberStatusChart" width="300" height="150"></canvas>
        </div>
        <div class="small-card">
            <div class="card-title"><span class="emoji">📅</span> Réservations par jour (7 derniers jours)</div>
            <canvas id="reservationsByDayChart" width="300" height="150"></canvas>
        </div>
    </div>
<?php endif; ?>