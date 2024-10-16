<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../assets/profile.css">
</head>
<body>
    <div class="logo-container">
        <img src="../../assets/logo.png" alt="Logo" class="logo">
    </div>

    <aside class="sidebar">
        <ul>
            <li><a href="#">Aperçu</a></li>
            <li><a href="#">Abonnement</a></li>
            <li><a href="#">Paiements</a></li>
            <li><a href="#">Mes données personnelles</a></li>
            <li><a href="#">Visites</a></li>
            <li><a href="#">Entraînements vidéo</a></li>
            <li><a href="#">S'entraîner ensemble</a></li>
            <li><a href="#">Se déconnecter</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php
        $userName = "Thibault"; 
        ?>
        <section class="welcome-section">
            <h1>Bienvenue <?php echo $userName; ?>!</h1>
            <p>Gère et modifie ton abonnement où et quand tu le veux via notre application mobile.</p>
        </section>

        <section class="stats-section">
            <div class="stat-box">
                <h3>Total</h3>
                <p>84 Visites</p>
            </div>
            <div class="stat-box">
                <h3>Semaine Passée</h3>
                <p>0 Visites</p>
            </div>
            <div class="stat-box">
                <h3>Cette Semaine</h3>
                <p>1 Visite</p>
            </div>
            <div class="stat-box">
                <h3>Entraînements</h3>
                <p>0 Vues</p>
            </div>
        </section>
    </main>
</body>
</html>
