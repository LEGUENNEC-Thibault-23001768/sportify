<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entraînement du Jour</title>
    <style>
        .exercise {
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .exercise h2 {
            font-size: 18px;
        }
        .btn {
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-stop {
            background-color: #dc3545;
        }
        .btn-stop:hover {
            background-color: #c82333;
        }
        .timer {
            font-weight: bold;
            font-size: 20px;
            color: #d9534f;
            margin-top: 10px;
        }
    </style>
    <script>
        let timerInterval;
        let remainingTime = 0;

        function startTimer(minutes) {
            clearInterval(timerInterval);
            remainingTime = minutes * 60;

            updateTimerDisplay();

            timerInterval = setInterval(function () {
                remainingTime--;

                updateTimerDisplay();

                if (remainingTime <= 0) {
                    clearInterval(timerInterval);
                    alert("Temps écoulé pour cet exercice !");
                }
            }, 1000);
        }

        function stopTimer() {
            clearInterval(timerInterval);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            document.getElementById('timer').innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
    </script>
</head>
<body>

    <h1>Entraînement du Jour</h1>

    <?php 
    $dayContent = $dayContent ?? '';

    if (!empty($dayContent)) {
        // Découper chaque exercice pour affichage
        $exercises = array_filter(array_map('trim', explode("\n", $dayContent)));

        foreach ($exercises as $index => $exercise) {
            preg_match('/(\d+) minutes/', $exercise, $matches);
            $exerciseDuration = $matches[1] ?? 0;

            echo "<div class='exercise'>
                    <h2>Exercice " . ($index + 1) . "</h2>
                    <p>" . htmlspecialchars($exercise, ENT_QUOTES) . "</p>";

            // Ajouter un timer uniquement si une durée est spécifiée
            if ($exerciseDuration > 0 && $index === 0) {
                echo "<div class='timer' id='timer'></div>
                      <button onclick='startTimer($exerciseDuration)' class='btn'>Reprendre</button>
                      <button onclick='stopTimer()' class='btn btn-stop'>Arrêter</button>";
            }

            echo "</div>";
        }
    } else {
        echo "<p>Aucun exercice programmé pour aujourd'hui.</p>";
    }
    ?>

    <script>
        const initialDuration = <?php echo !empty($exercises) && isset($matches[1]) ? (int)$matches[1] : 0; ?>;
        if (initialDuration > 0) {
            startTimer(initialDuration);
        }
    </script>

</body>
</html>
