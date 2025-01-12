<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestion Salle de Sport' ?></title>
     <?php if (isset($css)) { ?>
        <link rel="stylesheet" href="/_assets/css/<?= $css ?>.css">
        <?php }
        else { ?>
         <link rel="stylesheet" href="/_assets/css/main.css">
        <?php }
     ?>
    <link rel="stylesheet" href="/_assets/css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php if (isset($content)) {
            echo $content;
    } ?>

    <footer>
        <div class="footer-container">
            <div class="logofoot">
                <img src="https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png" alt="Logo Sportify" class="logofoot">
            </div>
            <div class="footer-links">
                <ul>
                    <li><span class="nav-link" data-page="entreprise">Entreprise</span></li>
                    <li><span class="nav-link" data-page="a-propos">À propos</span></li>
                    <li><span class="nav-link" data-page="faq">FAQ</span></li>
                    <li><span class="nav-link" data-page="blog.html">Blog</span></li>
                    <li><span class="nav-link" data-page="changelog.html">Changelog</span></li>
                    <li><span class="nav-link" data-page="contact">Nous contacter</span></li>
                    <li><span class="nav-link" data-page="partenaire.html">Partenaires</span></li>
                    <li><span class="nav-link" data-page="politique">Politique de confidentialité</span></li>
                </ul>
            </div>
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </footer>

    <script src="/_assets/js/main.js"></script>
    <script src="/_assets/js/footer.js"></script>
</body>
</html>