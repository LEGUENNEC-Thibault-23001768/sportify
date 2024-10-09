<h2>Inscription</h2>
<form action="/user/register" method="post">
    <div>
        <label for="lastName">Nom :</label>
        <input type="text" id="lastName" name="lastName" required>
    </div>
    <div>
        <label for="firstName">Prénom :</label>
        <input type="text" id="firstName" name="firstName" required>
    </div>
    <div>
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="birthDate">Date de naissance :</label>
        <input type="date" id="birthDate" name="birthDate" required>
    </div>
    <div>
        <label for="address">Adresse :</label>
        <textarea id="address" name="address" required></textarea>
    </div>
    <div>
        <label for="phone">Téléphone :</label>
        <input type="tel" id="phone" name="phone" required>
    </div>
    <button type="submit">S'inscrire</button>
</form>
