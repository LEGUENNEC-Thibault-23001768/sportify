<header>
    <img id="header-logo" src="https://cdn.discordapp.com/attachments/1291132446837837896/1329089651671044279/Sport_400_x_250_px_300_x_100_px-removebg-preview.png?ex=678912b2&is=6787c132&hm=4776819918522806860f8164b469734aba60eeb11458f5aa23d5b3ee0c5b2cb3&" alt="Logo Sportify" class="logo">
</header>

<main class="content">
    <section class="cta-section">
        <h1>Contactez-nous</h1>
        <p>Vous avez des questions ou souhaitez en savoir plus sur nos services ? Remplissez le formulaire ci-dessous ou utilisez les informations de contact disponibles.</p>
    </section>

    <section class="contact-form">
        <h2>Formulaire de Contact</h2>
        <form action="submit_form.php" method="post">
            <div class="form-group">
                <label for="name">Nom :</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="message">Message :</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>

            <button type="submit" class="submit-btn">Envoyer</button>
        </form>
    </section>

    <section class="contact-info">
        <h2>Nos Coordonnées</h2>
        <p><strong>Adresse :</strong> Gaston Berger, 13100 Aix-en-Provence, France</p>
        <p><strong>Téléphone :</strong> +33 4 71 79 75 24</p>
        <p><strong>Email :</strong> contact@sportify.com</p>
    </section>
</main>
