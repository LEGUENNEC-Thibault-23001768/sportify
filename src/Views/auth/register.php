<h2>Registration</h2>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= $error ?></p>
<?php endif; ?>

<form action="/register" method="POST">
    <label for="email">Email :</label>
    <input type="email" name="email" id="email" required><br>

    <label for="password">Password :</label>
    <input type="password" name="password" id="password" required><br>

    <label for="confirm_password">Confirm password :</label>
    <input type="password" name="confirm_password" id="confirm_password" required><br>

    <button type="submit">Register</button>
</form>

<a href="/google">S'inscrire</a>
