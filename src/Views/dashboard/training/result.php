<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan d'Entraînement Personnalisé</title>
    <link rel="stylesheet" href="/_assets/css/training.css">
</head>
<body>
    <div class="navbar">
        <div class="menu">
            <a href="/dashboard">Tableau de bord</a>
            <a href="/dashboard/training/edit" class="edit-plan">Modifier le plan</a>
        </div>
    </div>

    <div class="training-result">
    <h1>Votre Plan d'Entraînement</h1>
    <?php if (isset($plan) && !empty($plan)): ?>
    <pre>
        <?php if (is_array($plan)): ?>
            <?php foreach ($plan as $content): ?>
                <?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . "\n" ?>
            <?php endforeach; ?>
        <?php else: ?>
            <?= htmlspecialchars($plan, ENT_QUOTES, 'UTF-8') ?>
        <?php endif; ?>
        </pre>
    <?php else: ?>
        Aucun plan d'entraînement trouvé.
    <?php endif; ?>


    <a href="/dashboard" class="back-to-dashboard">Retour au tableau de bord</a>
</div>

</body>
</html>
