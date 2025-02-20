<div id="profile-content" data-view="profile">
    <div class="container">
        <h1>Mon Profil</h1>

        <div id="profile-message"></div>

        <form id="profileForm" enctype="multipart/form-data">
            <div class="top-section">
                <div class="left-section">
                    <label for="first_name">Prénom :</label>
                    <input type="text" name="first_name" id="first_name"
                           value="<?= htmlspecialchars($user['first_name'] ?? "") ?>" required>

                    <label for="last_name">Nom :</label>
                    <input type="text" name="last_name" id="last_name"
                           value="<?= htmlspecialchars($user['last_name'] ?? "") ?>" required>

                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? "") ?>"
                           required readonly>
                </div>

                <div class="right-section">
                    <div class="profile-picture-container">
                        <label for="profile_picture">
                            <img src="<?= !empty($user['profile_picture']) ? "/uploads/profile_pictures/" . htmlspecialchars(basename($user['profile_picture'])) : 'https://i.pinimg.com/564x/7e/8c/81/7e8c8119bf240d4971880006afb7e1e6.jpg'; ?>"
                                 alt="Photo de profil" class="profile-picture">
                            <div class="overlay">
                                <i class="fas fa-camera"></i>
                                <span>Changer la photo</span>
                            </div>
                        </label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                               style="display: none;">
                    </div>
                    <input type="hidden" name="current_profile_picture"
                           value="<?= htmlspecialchars($user['profile_picture'] ?? "") ?>">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['member_id'] ?? "") ?>">
                </div>
            </div>

            <div class="bottom-section">
                <label for="birth_date">Date de naissance :</label>
                <input type="date" name="birth_date" id="birth_date"
                       value="<?= htmlspecialchars($user['birth_date'] ?? "") ?>">

                <label for="address">Adresse :</label>
                <textarea name="address" id="address"><?= htmlspecialchars($user['address'] ?? "") ?></textarea>

                <label for="phone">Téléphone :</label>
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? "") ?>">

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
</div>