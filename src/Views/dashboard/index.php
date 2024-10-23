<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .navbar {
            background-color: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .profile-icon {
            position: relative;
        }

        .navbar .profile-icon img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }

        .navbar .dropdown {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            background-color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            z-index: 1;
        }

        .navbar .dropdown a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: black;
            border-bottom: 1px solid #ddd;
        }

        .navbar .dropdown a:hover {
            background-color: #f4f4f4;
        }

        .dashboard-content {
            padding: 20px;
        }

        .profile-name {
            margin: 20px 0;
        }

        .alert-success {
            padding: 10px;
            margin: 20px 0;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }


        .subscription-info {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #bd2130;
        }


    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">Dashboard</div>
        
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
        <p class="profile-name">Nom : <?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></p>
        <p>Email : <?= htmlspecialchars($user['email']) ?></p>

        <div class="subscription-info">
            <h2>Informations sur l'abonnement</h2>
            <?php if (!$hasActiveSubscription): ?>
                <p>Vous n'avez pas d'abonnement actif.</p>
                <form action="/create-checkout-session" method="POST">
                    <button type="submit" class="btn">S'abonner</button>
                </form>    
            <?php else: ?>
                <p>Votre abonnement est actif</p>
                <p>Plan : <?= htmlspecialchars($subscription['plan_name']) ?></p>
                <p>Date de début : <?= htmlspecialchars($subscription['start_date']) ?></p>
                <p>Date de fin : <?= htmlspecialchars($subscription['end_date']) ?></p>
                <p>Montant : <?= htmlspecialchars($subscription['amount']) ?> <?= htmlspecialchars($subscription['currency'] ?? '€') ?></p>
                
                <form action="/cancel-subscription" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler votre abonnement ?');">
                    <button type="submit" class="btn btn-danger">Annuler l'abonnement</button>
                </form>
            <?php endif; ?>
            
            <a href="/invoices" class="btn">Voir mes factures</a>
        </div>
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