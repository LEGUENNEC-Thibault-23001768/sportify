<h2>Connexion</h2>
<?php
    
?>
<form action="/login" method="post">
    <div>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Se connecter</button>
</form>

<a href="/google">Se connecter avec Google</a>
