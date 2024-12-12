<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan d'Entraînement Personnalisé</title>
    <style>
        .card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }
        .card h2 {
            margin: 0 0 12px;
            font-size: 18px;
            color: #333;
        }
        .card p {
            margin: 0;
            white-space: pre-wrap; /* Conserve les espaces et les sauts de ligne */
            line-height: 1.5;
        }
        .card .start-button {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .card .start-button:hover {
            background-color: #0056b3;
        }
        .edit-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }
        .edit-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div>
        <h1>Votre Plan d'Entraînement</h1>
        
        <!-- Bouton Modifier le Plan -->
        <a href="/dashboard/training/edit" class="edit-button">Modifier le plan</a>

        <?php
// Supposons que $planContent contient le plan d'entraînement généré
$planContent = $plan ?? ''; // Remplacez par le contenu réel du plan

// Si $planContent est un tableau, le convertir en chaîne
if (is_array($planContent)) {
    $planContent = implode("\n", $planContent); // Concatène les éléments du tableau avec des sauts de ligne
}

if ($planContent) {
    preg_match_all('/\*\*Jour (\d+) :\*\*\n(.*?)(?=\n\*\*Jour |\Z)/s', $planContent, $matches, PREG_SET_ORDER);

    if ($matches) {
        foreach ($matches as $match) {
            $dayTitle = "Jour " . $match[1];
            $dayContent = trim($match[2]);

            // Génération d'une carte pour chaque jour
            echo '<div class="card">';
            echo '<h2>' . htmlspecialchars($dayTitle, ENT_QUOTES, 'UTF-8') . '</h2>';
            echo '<p>' . nl2br(htmlspecialchars($dayContent, ENT_QUOTES, 'UTF-8')) . '</p>';

            // Ajouter un bouton d'action pour le premier jour, sinon afficher seulement les informations
            if ($match[1] == 1) {
                $encodedContent = urlencode($dayContent);
                echo '<a href="/dashboard/training/train?day=' . $match[1] . '" class="start-button">Commencer</a>';
            }
            

            echo '</div>';
        }
    } else {
        echo '<p>Aucun jour trouvé dans le plan d\'entraînement.</p>';
    }
} else {
    echo '<p>Votre plan d\'entraînement n\'est pas disponible. Veuillez le générer ou contacter l\'administrateur.</p>';
}
?>

    </div>
</body>
</html>
