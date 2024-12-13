<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Plan d'Entraînement</title>
    <link rel="stylesheet" href="/_assets/css/training.css">
</head>
<body>
    <div class="edit-plan-form">
        <h1>Modifier votre Plan d'Entraînement</h1>
        <form action="/dashboard/training/edit" method="POST">
            <label for="gender">Sexe :</label>
            <select name="gender" id="gender">
                <option value="">(Non modifié)</option>
                <option value="Homme" <?= isset($existingPlan['gender']) && $existingPlan['gender'] === 'Homme' ? 'selected' : '' ?>>Homme</option>
                <option value="Femme" <?= isset($existingPlan['gender']) && $existingPlan['gender'] === 'Femme' ? 'selected' : '' ?>>Femme</option>
            </select>

            <label for="level">Niveau :</label>
            <select name="level" id="level">
                <option value="">(Non modifié)</option>
                <option value="Débutant" <?= isset($existingPlan['level']) && $existingPlan['level'] === 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                <option value="Intermédiaire" <?= isset($existingPlan['level']) && $existingPlan['level'] === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                <option value="Avancé" <?= isset($existingPlan['level']) && $existingPlan['level'] === 'Avancé' ? 'selected' : '' ?>>Avancé</option>
            </select>

            <label for="goals">Objectifs :</label>
            <select name="goals" id="goals">
                <option value="">(Non modifié)</option>
                <option value="Lose Weight" <?= isset($existingPlan['goals']) && $existingPlan['goals'] === 'Lose Weight' ? 'selected' : '' ?>>Perdre du poids</option>
                <option value="Build Muscle" <?= isset($existingPlan['goals']) && $existingPlan['goals'] === 'Build Muscle' ? 'selected' : '' ?>>Construire du muscle</option>
                <option value="Improve Fitness" <?= isset($existingPlan['goals']) && $existingPlan['goals'] === 'Improve Fitness' ? 'selected' : '' ?>>Améliorer la condition physique</option>
                <option value="Run a Marathon" <?= isset($existingPlan['goals']) && $existingPlan['goals'] === 'Run a Marathon' ? 'selected' : '' ?>>Courir un marathon</option>
            </select>

            <label for="weight">Poids :</label>
            <input type="number" name="weight" id="weight" placeholder="<?= htmlspecialchars($existingPlan['weight'] ?? 'Non défini') ?>">

            <label for="height">Taille :</label>
            <input type="number" name="height" id="height" placeholder="<?= htmlspecialchars($existingPlan['height'] ?? 'Non défini') ?>">

            <label for="constraints">Contraintes :</label>
            <input type="text" name="constraints" id="constraints" placeholder="<?= htmlspecialchars($existingPlan['constraints'] ?? 'Aucune') ?>">

            <label for="preferences">Préférences :</label>
            <select name="preferences" id="preferences">
                <option value="">(Non modifié)</option>
                <option value="Domicile" <?= isset($existingPlan['preferences']) && $existingPlan['preferences'] === 'Domicile' ? 'selected' : '' ?>>À domicile</option>
                <option value="Salle de sport" <?= isset($existingPlan['preferences']) && $existingPlan['preferences'] === 'Salle de sport' ? 'selected' : '' ?>>En salle de sport</option>
                <option value="Extérieur" <?= isset($existingPlan['preferences']) && $existingPlan['preferences'] === 'Extérieur' ? 'selected' : '' ?>>En extérieur</option>
            </select>

            <label for="equipment">Équipement :</label>
            <select name="equipment" id="equipment">
                <option value="">(Non modifié)</option>
                <option value="none" <?= isset($existingPlan['equipment']) && $existingPlan['equipment'] === 'none' ? 'selected' : '' ?>>Aucun</option>
                <option value="dumbbells" <?= isset($existingPlan['equipment']) && $existingPlan['equipment'] === 'dumbbells' ? 'selected' : '' ?>>Haltères</option>
                <option value="treadmill" <?= isset($existingPlan['equipment']) && $existingPlan['equipment'] === 'treadmill' ? 'selected' : '' ?>>Tapis de course</option>
                <option value="resistance_bands" <?= isset($existingPlan['equipment']) && $existingPlan['equipment'] === 'resistance_bands' ? 'selected' : '' ?>>Bandes de résistance</option>
            </select>

            <button type="submit">Enregistrer les modifications</button>
        </form>
        <a href="/dashboard/training">Annuler</a>
    </div>
</body>
</html>