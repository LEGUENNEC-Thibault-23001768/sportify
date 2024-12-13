<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../_assets/css/profile.css">
    <title>Mon Profil</title>
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

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="top-section">
                <div class="left-section">
                    <label for="first_name">Prénom :</label>
                    <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($user['first_name'] ?? "") ?>" required>

                    <label for="last_name">Nom :</label>
                    <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($user['last_name'] ?? "") ?>" required>

                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? "") ?>" required>
                </div>
                
                <div class="right-section">
                    <div class="profile-picture-container">
                        <label for="profile_picture">
                            <img src="/uploads/profile_pictures/<?= !empty($user['profile_picture']) ? htmlspecialchars(basename($user['profile_picture'])) : 'https://i.pinimg.com/564x/7e/8c/81/7e8c8119bf240d4971880006afb7e1e6.jpg'; ?>" alt="Photo de profil" class="profile-picture">
                            <div class="overlay">
                                <i class="fas fa-camera"></i>
                                <span>Changer la photo</span>
                            </div>
                        </label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;">
                    </div>
                    <input type="hidden" name="current_profile_picture" value="<?= htmlspecialchars($user['profile_picture'] ?? "") ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['member_id'] ?? "") ?>">
                </div>
            </div>

            <div class="bottom-section">
                <label for="birth_date">Date de naissance :</label>
                <input type="date" name="birth_date" id="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? "") ?>">

                <label for="address">Adresse :</label>
                <textarea name="address" id="address"><?= htmlspecialchars($user['address'] ?? "") ?></textarea>

                <label for="phone">Téléphone :</label>
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? "") ?>">

                <?php if ($ifAdminuser['status'] ?? false === 'admin' ): ?>
                    <label for="status">Rôle :</label>
                    <select name="status" id="status">
                        <option value="membre" <?= $user['status'] === 'user' ? 'selected' : '' ?>>Membre</option>
                        <option value="coach" <?= $user['status'] === 'coach' ? 'selected' : '' ?>>Coach</option>
                        <option value="admin" <?= $user['status'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                <?php endif; ?>

                <h2>Changer le mot de passe</h2>

                <label for="current_password">Mot de passe actuel :</label>
                <input type="password" name="current_password" id="current_password">

                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" name="new_password" id="new_password">

                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" name="confirm_password" id="confirm_password">

                <button type="submit" class="profile-update-btn">Mettre à jour</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profilePictureInput = document.getElementById('profile_picture');
            const profilePicture = document.querySelector('.profile-picture');

            profilePicture.addEventListener('click', function() {
                profilePictureInput.click();
            });

            profilePictureInput.addEventListener('change', function(event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePicture.src = e.target.result;
                    }
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        });
    </script>

</body>
</html>