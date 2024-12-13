<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Plan</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212; /* Dark background */
            color: #eee; /* Light text */
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            color: #A2D149; /* Green accent color */
            text-align: center;
            margin-bottom: 30px;
        }

        /* Training Plan Styles */
        .training-plan {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }

        .day-card {
            background-color: #1E1E1E; /* Darker card background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7); /* More pronounced shadow */
            padding: 20px;
            width: 30%;
            min-width: 300px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .day-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.9); /* Stronger shadow on hover */
        }

        .day-card h3 {
            color: #A2D149;
            margin-top: 0;
            border-bottom: 2px solid #A2D149; /* Green separator */
            padding-bottom: 10px;
        }

        .exercises {
            display: none; /* Initially hidden */
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }

        .exercise-card {
            background-color: #282828;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .exercise-card h4 {
            color: #A2D149;
            margin: 0 0 5px 0;
        }

        .exercise-card .description {
            color: #ccc;
            font-size: 0.9em;
            line-height: 1.4;
            margin-bottom: 10px;
        }

        .exercise-card .sets {
            margin-top: 10px;
        }

        .exercise-card .star {
            color: #A2D149;
            font-size: 1.2em;
            margin-right: 4px;
            /* Non-interactive */
        }

        .exercise-card .star.completed {
            color: yellow;
        }

        /* Button Styles */
        .toggle-description,
        #start-training,
        #end-set,
        #next-exercise,
        #finish-day {
            background-color: #4A6572;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .toggle-description:hover,
        #start-training:hover,
        #end-set:hover,
        #next-exercise:hover,
        #finish-day:hover {
            background-color: #344955;
        }

        /* Active Training Section Styles */
        #active-training {
            background-color: #1E1E1E;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
            padding: 20px;
            margin-top: 30px;
        }

        #active-training h3 {
            color: #A2D149;
            margin-top: 0;
        }

        #current-exercise {
            margin-bottom: 20px;
        }

        #rest-timer {
            font-size: 1.2em;
            color: #A2D149;
            margin-top: 10px;
        }

        /* Utility Classes */
        .hidden {
            display: none;
        }

        .text-center {
            text-align: center;
        }

        #skip-timer {
            margin-left: 10px;
            background-color: #4CAF50;
            /* Green color for a positive action */
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #skip-timer:hover {
            background-color: #367c39;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Training Plan</h1>

        <div class="training-plan">
            <?php if (isset($plan)): ?>
                <?php $planData = json_decode($plan, true); ?>
                <?php if (isset($planData['days']) && is_array($planData['days'])): ?>
                    <?php foreach ($planData['days'] as $day): ?>
                        <div class="day-card" data-day="<?= $day['day'] ?>">
                            <h3><?= $day['day'] ?></h3>
                            <div class="exercises">
                                <?php foreach ($day['exercises'] as $exercise): ?>
                                    <div class="exercise-card" data-name="<?= htmlspecialchars($exercise['name']) ?>"
                                        data-description="<?= htmlspecialchars($exercise['description']) ?>"
                                        data-sets="<?= $exercise['sets'] ?? '' ?>" data-reps="<?= $exercise['reps'] ?? '' ?>"
                                        data-duration="<?= $exercise['duration'] ?? '' ?>"
                                        data-intensity="<?= $exercise['intensity'] ?? '' ?>"
                                        data-repos="<?= $exercise['repos'] ?? '' ?>">
                                        <h4><?= $exercise['name'] ?></h4>
                                        <p class="description" style="display: none;"><?= $exercise['description'] ?></p>
                                        <div class="sets">
                                            <!-- Stars will be generated here -->
                                        </div>
                                        <button class="toggle-description">Show Description</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">Invalid plan format.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No training plan found.</p>
            <?php endif; ?>
        </div>

        <!-- Active Training Section (Initially Hidden) -->
        <div id="active-training" style="display: none;">
            <h3 id="active-day"></h3>
            <button id="start-training" class="hidden">Start Training</button>
            <div id="current-exercise"></div>
            <button id="end-set" class="hidden">End Set</button>
            <div id="rest-timer" style="display: none;">
                Resting: <span id="timer-countdown"></span>
                <button id="skip-timer">Skip</button>
            </div>
            <button id="next-exercise" class="hidden">Next Exercise</button>
            <button id="finish-day" class="hidden">Finish Day</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setupDayCardInteractions();
            setupActiveTraining();
        });

        function setupDayCardInteractions() {
            const dayCards = document.querySelectorAll('.day-card');

            dayCards.forEach(card => {
                card.addEventListener('click', function (event) {
                    // Prevent triggering active training if clicking on interactive elements
                    if (!event.target.closest('.toggle-description, .star')) {
                        toggleExercisesVisibility(card);
                    }
                });
            });

            document.querySelectorAll('.toggle-description').forEach(button => {
                button.addEventListener('click', function (event) {
                    event.stopPropagation(); // Prevent event from bubbling up to the card
                    const description = button.previousElementSibling;
                    description.style.display = description.style.display === 'none' ? 'block' : 'none';
                    button.textContent = description.style.display === 'none' ? 'Show Description' : 'Hide Description';
                });
            });
        }

        function toggleExercisesVisibility(card) {
            const exercises = card.querySelector('.exercises');
            exercises.style.display = exercises.style.display === 'none' || exercises.style.display === '' ? 'block' : 'none';

            if (exercises.style.display === 'block') {
                const exerciseCards = exercises.querySelectorAll('.exercise-card');
                exerciseCards.forEach(exerciseCard => {
                    const setsContainer = exerciseCard.querySelector('.sets');
                    const sets = exerciseCard.dataset.sets;

                    setsContainer.innerHTML = ''; // Clear existing stars

                    // Use the sets value directly (no need to split)
                    const numSets = parseInt(sets);
                    for (let i = 0; i < numSets; i++) {
                        const star = document.createElement('span');
                        star.classList.add('star');
                        star.textContent = '★';
                        setsContainer.appendChild(star);
                    }
                });
            }
        }

        function setupActiveTraining() {
            const dayCards = document.querySelectorAll('.day-card');
            const activeTrainingSection = document.getElementById('active-training');
            const trainingPlanSection = document.querySelector('.training-plan');
            const activeDayTitle = document.getElementById('active-day');
            const startTrainingButton = document.getElementById('start-training');
            const currentExerciseContainer = document.getElementById('current-exercise');
            const endSetButton = document.getElementById('end-set');
            const restTimerDisplay = document.getElementById('rest-timer');
            const timerCountdown = document.getElementById('timer-countdown');
            const nextExerciseButton = document.getElementById('next-exercise');
            const finishDayButton = document.getElementById('finish-day');
            let currentDayData = null;
            let currentExerciseIndex = 0;
            let currentSetIndex = 0;
            let restTimer = null;

            dayCards.forEach(card => {
                card.addEventListener('click', (event) => {
                    // Only trigger active training if clicking on the card itself or its h3
                    if (event.target === card || event.target.closest('h3') === card.querySelector('h3')) {
                        const day = card.dataset.day;
                        currentDayData = <?= json_encode($planData['days']) ?>.find(d => d.day === day);
                        currentExerciseIndex = 0;
                        currentSetIndex = 0;

                        trainingPlanSection.style.display = 'none';
                        activeTrainingSection.style.display = 'block';
                        activeDayTitle.textContent = day;

                        // Show the Start Training button
                        startTrainingButton.classList.remove('hidden');
                    }
                });
            });

            startTrainingButton.addEventListener('click', () => {
                startTrainingButton.classList.add('hidden'); // Hide the Start Training button
                displayCurrentExercise();
            });

            function displayCurrentExercise() {
                if (!currentDayData || currentExerciseIndex >= currentDayData.exercises.length) {
                    finishDay();
                    return;
                }

                const exercise = currentDayData.exercises[currentExerciseIndex];
                const numSets = parseInt(exercise.sets);

                currentExerciseContainer.innerHTML = `
                    <h4>${exercise.name}</h4>
                    <p>${exercise.description}</p>
                    <div class="sets">
                        ${generateSetsStars(numSets)}
                    </div>
                `;

                currentSetIndex = 0; // Reset set index for each new exercise
                updateStars(numSets);

                endSetButton.classList.remove('hidden');
                nextExerciseButton.classList.add('hidden');
                finishDayButton.classList.add('hidden');
                restTimerDisplay.style.display = 'none';
            }

            function generateSetsStars(numSets) {
                let starsHtml = '';
                for (let i = 0; i < numSets; i++) {
                    starsHtml += '<span class="star">★</span>';
                }
                return starsHtml;
            }

            function updateStars(numSets) {
                const stars = currentExerciseContainer.querySelectorAll('.star');
                stars.forEach((star, index) => {
                    if (index < currentSetIndex) {
                        star.classList.add('completed');
                    } else {
                        star.classList.remove('completed');
                    }
                });

                if (currentSetIndex === numSets) {
                    endSetButton.classList.add('hidden');
                    nextExerciseButton.classList.remove('hidden');
                }
            }

            endSetButton.addEventListener('click', () => {
                currentSetIndex++;
                const exercise = currentDayData.exercises[currentExerciseIndex];
                const numSets = parseInt(exercise.sets);

                updateStars(numSets);

                const restTime = exercise.repos ? parseInt(exercise.repos) : 60; // Default to 60 seconds if not specified
                startRestTimer(restTime);

                if (currentSetIndex === numSets) {
                    endSetButton.classList.add('hidden'); 
                }
            });

            nextExerciseButton.addEventListener('click', () => {
                currentExerciseIndex++;
                nextExerciseButton.classList.add('hidden'); 
                displayCurrentExercise();
            });

            function startRestTimer(seconds) {
                restTimerDisplay.style.display = 'block';
                let timeLeft = seconds;
                timerCountdown.textContent = timeLeft;

                clearInterval(restTimer);
                restTimer = setInterval(() => {
                    timeLeft--;
                    timerCountdown.textContent = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(restTimer);
                        restTimerDisplay.style.display = 'none';
                    }
                }, 1000);

                // Skip Timer Button
                const skipTimerButton = document.getElementById('skip-timer');
                skipTimerButton.onclick = () => {
                    clearInterval(restTimer);
                    restTimerDisplay.style.display = 'none';
                };
            }

            function finishDay() {
                currentExerciseContainer.innerHTML = '<p>All exercises completed for the day!</p>';
                endSetButton.classList.add('hidden');
                nextExerciseButton.classList.add('hidden');
                finishDayButton.classList.remove('hidden');
            }

            finishDayButton.addEventListener('click', () => {
                trainingPlanSection.style.display = 'flex';
                activeTrainingSection.style.display = 'none';
                finishDayButton.classList.add('hidden');
            });
        }
    </script>
</body>

</html>