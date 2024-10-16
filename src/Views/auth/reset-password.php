<h2>Réinitialiser le mot de passe</h2>
<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form action="/reset-password" method="post">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <div>
        <label for="password">Nouveau mot de passe :</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirmer le mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit">Réinitialiser le mot de passe</button>
</form>