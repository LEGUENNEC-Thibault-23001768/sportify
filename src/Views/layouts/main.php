<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestion Salle de Sport' ?></title>
    <?php if (isset($css)) { echo '<link rel=stylesheet" href="_asssets/css/'.$css.'">'; }
        else { echo '<link rel="stylesheet" href="_assets/css/main.css">';}
    ?> 
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php if (isset($content)) {
            echo $content;
    } ?>

    <?php /*<header>
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
    */ ?>

</body>
</html>