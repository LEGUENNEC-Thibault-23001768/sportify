
<header>
    <img id="header-logo" src="https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png" alt="Logo Sportify" class="logo">
</header>

<main class="content">
    <section class="forgot-password-section">
        <h1>Mot de passe oublié</h1>
        <p>Entrez votre adresse e-mail pour réinitialiser votre mot de passe.</p>
        <form action="/forgot-password" method="post" class="forgot-password-form">
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn-submit">Envoyer</button>
        </form>
        <a href="/login" class="btn-back">Retour à la connexion</a>
    </section>
</main>