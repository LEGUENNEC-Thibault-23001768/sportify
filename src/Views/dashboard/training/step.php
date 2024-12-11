<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étape <?= htmlspecialchars($step) ?> - Entraînement Personnalisé</title>
    <link rel="stylesheet" href="/_assets/css/training.css">
</head>
<body>
    <div class="training-step">
        <h1>Étape <?= htmlspecialchars($step) ?></h1>
        <form action="/dashboard/training/step/<?= htmlspecialchars($step) ?>" method="POST">
            <?php if ($step == 1): ?>
                <label for="gender">Quel est votre sexe ?</label>
                <select name="input" id="gender" required>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                </select>
            <?php elseif ($step == 2): ?>
                <label for="level">Quel est votre niveau d'entraînement ?</label>
                <select name="input" id="level" required>
                    <option value="Débutant">Débutant</option>
                    <option value="Intermédiaire">Intermédiaire</option>
                    <option value="Avancé">Avancé</option>
                </select>
            <?php elseif ($step == 3): ?>
                <label for="goals">Quel est votre objectif principal ?</label>
                <input type="text" name="input" id="goals" placeholder="Exemple : Perdre 5 kg, courir un marathon..." required>

                <?php elseif ($step == 4): ?>
                    <label for="weight">Quel est votre poids (en kg) ?</label>
                    <select name="input" id="weight" required>
                        <?php for ($i = 30; $i <= 200; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> kg</option>
                        <?php endfor; ?>
                    </select>
                    <?php elseif ($step == 5): ?>
                    <label for="height">Quelle est votre taille (en cm) ?</label>
                    <select name="input" id="height" required>
                        <?php for ($i = 140; $i <= 210; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> cm</option>
                        <?php endfor; ?>
                    </select>

                <?php elseif ($step == 6): ?>
    <label for="constraints">Avez-vous des limitations physiques ou blessures ?</label>
    <input type="text" name="input" id="constraints" placeholder="Exemple : Douleurs au genou">

<?php elseif ($step == 7): ?>
    <label>Préférez-vous des entraînements spécifiques ?</label>
    <div>
        <input type="checkbox" name="input[]" value="Domicile"> À domicile<br>
        <input type="checkbox" name="input[]" value="Salle de sport"> En salle de sport<br>
        <input type="checkbox" name="input[]" value="Extérieur"> En extérieur<br>
    </div>

<?php elseif ($step == 8): ?>
    <label for="equipment">Quel équipement avez-vous à disposition ?</label>
    <input type="text" name="input" id="equipment" placeholder="Exemple : Haltères, tapis, élastiques">
        <?php endif; ?>

            <button type="submit">Suivant</button>
        </form>
    </div>
</body>
</html>
