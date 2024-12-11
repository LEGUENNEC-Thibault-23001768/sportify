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
            <!-- Gender -->
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
            <input type="text" name="goals" id="goals" placeholder="<?= htmlspecialchars($existingPlan['goals'] ?? 'Non défini') ?>">

            <label for="weight">Poids :</label>
            <input type="number" name="weight" id="weight" placeholder="<?= htmlspecialchars($existingPlan['weight'] ?? 'Non défini') ?>">

            <label for="height">Taille :</label>
            <input type="number" name="height" id="height" placeholder="<?= htmlspecialchars($existingPlan['height'] ?? 'Non défini') ?>">

            <label for="constraints">Contraintes :</label>
            <input type="text" name="constraints" id="constraints" placeholder="<?= htmlspecialchars($existingPlan['constraints'] ?? 'Aucune') ?>">

            <label for="preferences">Préférences :</label>
            <input type="text" name="preferences" id="preferences" placeholder="<?= htmlspecialchars($existingPlan['preferences'] ?? 'Aucune') ?>">

            <label for="equipment">Équipement :</label>
            <input type="text" name="equipment" id="equipment" placeholder="<?= htmlspecialchars($existingPlan['equipment'] ?? 'Aucun') ?>">

            <button type="submit">Enregistrer les modifications</button>
        </form>
        <a href="/dashboard/training">Annuler</a>
    </div>
</body>
</html>
