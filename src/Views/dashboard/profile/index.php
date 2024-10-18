<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
        }

        input[type="text"], input[type="email"], input[type="date"], input[type="password"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .success, .error {
            color: white;
            padding: 10px;
            margin-bottom: 10px;
        }

        .success {
            background-color: green;
        }

        .error {
            background-color: red;
        }

        .profile-update-btn {
            background-color: #333;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .profile-update-btn:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Mon Profil</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <!-- Infos utilisateur -->
            <label for="first_name">Prénom :</label>
            <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($user['first_name'] ?? "") ?>" required>

            <label for="last_name">Nom :</label>
            <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($user['last_name'] ?? "") ?>" required>

            <label for="email">Email :</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? "") ?>" required>

            <label for="birth_date">Date de naissance :</label>
            <input type="date" name="birth_date" id="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? "") ?>">

            <label for="address">Adresse :</label>
            <textarea name="address" id="address"><?= htmlspecialchars($user['address'] ?? "") ?></textarea>

            <label for="phone">Téléphone :</label>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? "") ?>">

            <!-- Section pour changer le mot de passe -->
            <h2>Changer le mot de passe</h2>

            <label for="current_password">Mot de passe actuel :</label>
            <input type="password" name="current_password" id="current_password">

            <label for="new_password">Nouveau mot de passe :</label>
            <input type="password" name="new_password" id="new_password">

            <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
            <input type="password" name="confirm_password" id="confirm_password">

            <button type="submit" class="profile-update-btn">Mettre à jour</button>
        </form>
    </div>

</body>
</html>
