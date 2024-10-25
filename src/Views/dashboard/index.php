<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="_assets/css/admin.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> 
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png" alt="Logo Sportify">
        </div>
        <ul>
            <li><a href="#"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Suivi </a></li>
            <li><a href="#"><i class="fas fa-futbol"></i> Terrains</a></li>
            <li><a href="#"><i class="fas fa-user-friends"></i> Entraîneurs</a></li>
            <li><a href="#"><i class="fas fa-trophy"></i> Événements</a></li>
            <li><a href="#" class="management"><i class="fas fa-tasks"></i> Gestion</a></li> 
        </ul>
        <div class="settings-section">
            <a href="/dashboard/profile" class="settings"> Paramètres</a>
            <a href="/logout" class="logout"> Se déconnecter</a>
        </div>
    </div>
    <div class="navbar">
        <div class="logo"></div>
        <p class="profile-name"><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></p>
        <div class="profile-icon">
            <img src="<?= isset($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'https://i.pinimg.com/564x/7e/8c/81/7e8c8119bf240d4971880006afb7e1e6.jpg'; ?>" alt="Profil" id="profile-icon">
            <div class="dropdown" id="dropdown">
                <a href="/dashboard/profile">Mon profil</a>
                <a href="/logout">Déconnexion</a> 
            </div>
        </div>
    </div>
    

    <div class="dashboard-content">
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
                <li><button id="openReportModalBtn">Créer ou Modifier un Rapport</button></li>
            </ul>
        </div>

        <div class="card">
             <h3 class="title prochaines-reservations">🏋️ Prochaines réservations</h3>
             <ul>
                 <li>Entraînement avec [Nom de l'entraîneur] le 15 octobre 2024 à 10h00</li>
                 <li><button id="openCoachModalBtn">Créer ou Modifier un Entraîneur</button></li>
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
        <?php endif; ?>
</div>



    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <script>
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
    </script>

</body>
</html>