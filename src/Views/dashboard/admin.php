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
            <li>Nombre total de réservations cette semaine : 30 </li>
            <li><a disabled class="btn">Créer ou Modifier un Rapport</a></li>
        </ul>
    </div>

    <div class="card">
        <h3 class="title prochaines-reservations">🏋️ Prochaines réservations</h3>
        <ul>
            <li>Prochaine réservation: La salle tennis est reservé de 16h à 18h</li>
            <li><a class="btn" href="/dashboard/booking">Voir les prochaines réservations</a></li>
        </ul>
    </div>
    <div class="card-row">
        <div class="small-card">
            <div class="card-title"><span class="emoji">👥</span> Utilisateurs actifs</div>
            <div class="card-value">120 utilisateurs actifs actuellement</div>
        </div>
        <div class="small-card">
            <div class="card-title"><span class="emoji">📈</span> Nouvelles inscriptions</div>
            <div class="card-value">5 nouvelles inscriptions ont été faites récemment à Sportify !</div>
        </div>
    </div>
    <div class="card">
        <h3 class="title personal-training">🎯 Entraînement personnalisé</h3>
        <p>Recevez un plan d'entraînement adapté à votre profil.</p>
        <a class="btn" href="/dashboard/training/start">Commencer</a>
    </div>
<?php endif; ?>