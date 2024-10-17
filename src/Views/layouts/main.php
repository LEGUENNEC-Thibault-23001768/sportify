<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestion Salle de Sport' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <!-- Votre en-tête ici -->
    </header>
    
    <main>
        <?php
        if (isset($content)) {
            echo $content;
        } else {
            echo "La variable content n'est pas définie.";
        }
        ?>
    </main>
    
    <footer>
        <!-- Votre pied de page ici -->
    </footer>
    
    <script src="/js/main.js"></script>
    
</body>
</html>