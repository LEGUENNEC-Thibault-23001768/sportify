function initialize() {
    console.log("Training view initialized");
    loadTrainingData();
}

let currentStep = 1;
const trainingContent = $('#trainingContent');
let currentPlanData;
let currentDayData = null;
let currentExerciseIndex = 0;
let currentSetIndex = 0;
let restTimer = null;


function loadTrainingData() {
    $.ajax({
        url: '/dashboard/training',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.plan) {
                let plan = response.plan;
                plan = plan.replace(/^```json\\n/, '');
                plan = plan.replace(/```$/, '');
                plan = plan.trim();
                try {
                    currentPlanData = JSON.parse(plan);
                } catch (e) {
                    console.error('Error parsing JSON:', e, plan);
                    displayCreatePlanButton();
                        return;
                }
                displayTrainingPlan(response.plan);
            } else {
                 displayCreatePlanButton();
            }
        },
        error: function(error) {
            console.error("Error loading training data:", error);
            displayCreatePlanButton();
        }
    });
}

function displayCreatePlanButton() {
    trainingContent.html('<button id="createPlanButton" class="btn btn-primary">Create Training Plan</button>');
    $('#createPlanButton').on('click', function() {
        openCreatePlanPopup();
    });
}


function displayTrainingPlan(plan) {
    currentPlanData = JSON.parse(plan);
    if (currentPlanData) {
        let html = '<h1>Your Training Plan</h1><div class="training-plan">';
        currentPlanData.days.forEach(day => {
            html += `<div class="day-card" data-day="${day.day}"><h3>${day.day}</h3><div class="exercises">`;
            day.exercises.forEach(exercise => {
                html += `<div class="exercise-card" data-name="${exercise.name}" data-description="${exercise.description}"
                              data-sets="${exercise.sets}" data-reps="${exercise.reps}" data-duration="${exercise.duration}" data-intensity="${exercise.intensity}"
                              data-repos="${exercise.repos}"><h4>${exercise.name}</h4><p class="description" style="display: none;">${exercise.description}</p>
                              <div class="sets"></div><button class="toggle-description">Show Description</button><button class="start-exercise hidden">Start</button></div>`;
            });
            html += `</div></div>`;
        });
        html += '</div><button id="editPlanButton" class="btn btn-secondary">Edit Plan</button><div id="active-training" style="display:none;"><h3 id="active-day"></h3><div id="current-exercise"></div><button id="end-set" class="hidden">End Set</button><div id="rest-timer" style="display: none;"> Resting: <span id="timer-countdown"></span> <button id="skip-timer">Skip</button> </div><button id="next-exercise" class="hidden">Next Exercise</button> <button id="finish-day" class="hidden">Finish Day</button><button id="go-back-exercises" class="hidden">Go Back</button></div>'
        trainingContent.html(html);

        setupDayCardInteractions();
        setupExerciseStart();
        $('#editPlanButton').on('click', openEditPlanPopup);


    } else {
        trainingContent.html('Invalid plan format');
    }
}

function setupExerciseStart() {
    trainingContent.on('click', '.day-card', function(event) {
        if (event.target === this || event.target.closest('h3') === this.querySelector('h3')) {
            const day = $(this).data('day');
            currentDayData = currentPlanData.days.find(d => d.day === day);
            currentExerciseIndex = 0;
            currentSetIndex = 0;

            $(this).find('.exercise-card .start-exercise').removeClass('hidden')
        }
    });

    trainingContent.on('click', '.start-exercise', function() {
        const exerciseCard = $(this).closest('.exercise-card');
        $('.training-plan').hide();
        $('#active-training').show();
        $('#go-back-exercises').show();
        displayCurrentExercise(exerciseCard);
    });
}


function setupDayCardInteractions() {
    const dayCards = document.querySelectorAll('.day-card');

    dayCards.forEach(card => {
        card.addEventListener('click', function(event) {
            // Prevent triggering active training if clicking on interactive elements
            if (!event.target.closest('.toggle-description, .star')) {
                toggleExercisesVisibility(card);
            }
        });
    });

    document.querySelectorAll('.toggle-description').forEach(button => {
        button.addEventListener('click', function(event) {
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

            setsContainer.innerHTML = '';

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


function displayCurrentExercise(exerciseCard) {
    const activeTrainingSection = $('#active-training');
    const currentExerciseContainer = $('#current-exercise');
    const endSetButton = $('#end-set');
    const nextExerciseButton = $('#next-exercise');
    const finishDayButton = $('#finish-day');


    if (!currentDayData || currentExerciseIndex >= currentDayData.exercises.length) {
        finishDay();
        return;
    }

    const exerciseData = exerciseCard.data();
    const numSets = parseInt(exerciseData.sets);

    currentExerciseContainer.html(`
                <h4>${exerciseData.name}</h4>
                <p>${exerciseData.description}</p>
                <div class="sets">
                    ${generateSetsStars(numSets)}
               </div>
             `);

    currentSetIndex = 0;
    updateStars(numSets);


    endSetButton.removeClass('hidden');
    nextExerciseButton.addClass('hidden');
    finishDayButton.addClass('hidden');
    activeTrainingSection.find('#active-day').text(currentDayData.day);

}

function generateSetsStars(numSets) {
    let starsHtml = '';
    for (let i = 0; i < numSets; i++) {
        starsHtml += '<span class="star">★</span>';
    }
    return starsHtml;
}

function updateStars(numSets) {
    const currentExerciseContainer = $('#current-exercise');
    const stars = currentExerciseContainer.find('.star');
    stars.each((index, star) => {
        if (index < currentSetIndex) {
            $(star).addClass('completed');
        } else {
            $(star).removeClass('completed');
        }
    });

    if (currentSetIndex === numSets) {
        $('#end-set').addClass('hidden');
        $('#next-exercise').removeClass('hidden');
    }
}
trainingContent.on('click', '#end-set', () => {
    currentSetIndex++;
    const currentExerciseContainer = $('#current-exercise');
    const numSets = parseInt(currentExerciseContainer.find('.sets').children().length);


    updateStars(numSets);
    const currentExerciseData = $(currentExerciseContainer).closest('#active-training').find('#current-exercise').find('h4').text();
    const exercise = currentDayData.exercises.find(ex => ex.name === currentExerciseData)
    const restTime = exercise.repos ? parseInt(exercise.repos) : 60;
    startRestTimer(restTime);
    if (currentSetIndex === numSets) {
        $('#end-set').addClass('hidden');
    }
});

trainingContent.on('click', '#next-exercise', () => {
    currentExerciseIndex++;
    $('#next-exercise').addClass('hidden');
    const exerciseCard = $('.exercise-card').filter((index, element) => {
        const name = $(element).find('h4').text();
        return currentDayData.exercises[currentExerciseIndex] && currentDayData.exercises[currentExerciseIndex].name === name
    }).first()

    displayCurrentExercise(exerciseCard);
});



function startRestTimer(seconds) {
    const restTimerDisplay = $('#rest-timer');
    const timerCountdown = $('#timer-countdown');

    restTimerDisplay.show();
    let timeLeft = seconds;
    timerCountdown.text(timeLeft);

    clearInterval(restTimer);
    restTimer = setInterval(() => {
        timeLeft--;
        timerCountdown.text(timeLeft);
        if (timeLeft <= 0) {
            clearInterval(restTimer);
            restTimerDisplay.hide();
        }
    }, 1000);

    // Skip Timer Button
    const skipTimerButton = $('#skip-timer');
    skipTimerButton.off('click').on('click', () => {
        clearInterval(restTimer);
        restTimerDisplay.hide();
    });
}

function finishDay() {
    const activeTrainingSection = $('#active-training');
    const currentExerciseContainer = $('#current-exercise');
    const endSetButton = $('#end-set');
    const nextExerciseButton = $('#next-exercise');
    const finishDayButton = $('#finish-day');

    currentExerciseContainer.html('<p>All exercises completed for the day!</p>');
    endSetButton.addClass('hidden');
    nextExerciseButton.addClass('hidden');
    finishDayButton.removeClass('hidden');
}

trainingContent.on('click', '#finish-day', () => {
    $('.training-plan').show();
    $('#active-training').hide();
    $('#go-back-exercises').hide();
    $('#finish-day').addClass('hidden');
});
trainingContent.on('click', '#go-back-exercises', () => {
    $('.training-plan').show();
    $('#active-training').hide();
    $('#go-back-exercises').hide();
});



$('#editTrainingForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        success: function(response) {
            loadTrainingData();
            closeEditPlanPopup();
            showSuccessToast("Training plan updated successfully", 'success');
        },
        error: function(xhr, status, error) {
            console.error("Error updating training plan:", error);
            showToast("Error updating plan", 'error');
        }
    });
});

$('#nextButton').click(function() {
    let input = $(`#question${currentStep} :input`).val();

    $.ajax({
        url: '/api/training/process-step',
        method: 'POST',
        dataType: 'json',
        data: {
            step: currentStep,
            data: input
        },
        success: function(response) {
            if (response.next_step === 'generate') {
                generateTrainingPlan();
            } else {
                $(`#question${currentStep}`).hide();
                $(`#question${response.next_step}`).show();
                currentStep = response.next_step;
                $('#step').val(currentStep);
            }
        },
        error: function(error) {
            console.error("Error processing step:", error);
            showToast("Error processing step", 'error');
        }
    });
});


function generateTrainingPlan() {
    $.ajax({
        url: '/api/training/generate',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.plan) {
                loadTrainingData();
                closeCreatePlanPopup();
            } else {
                console.error("Error: Invalid response format", response);
                showToast("Error generating plan", 'error');
            }
        },
        error: function(error) {
            console.error("Error generating training plan:", error);
            showToast("Error generating plan", 'error');
        }
    });
}

loadTrainingData();

function openCreatePlanPopup() {
    $('#createPlanPopup').show();
    $('#popupOverlay').show();
}

function closeCreatePlanPopup() {
    $('#createPlanPopup').hide();
    $('#popupOverlay').hide();
}

function openEditPlanPopup() {
    $('#editPlanPopup').show();
    $('#popupOverlay').show();
}

function closeEditPlanPopup() {
    $('#editPlanPopup').hide();
    $('#popupOverlay').hide();
}

function showSuccessToast(message) {
    const toastContainer = $('#toast-container');
    const toast = $(`<div class="toast success">${message}</div>`);
    toastContainer.append(toast);
    setTimeout(() => toast.remove(), 3000);
}

function showErrorToast(message) {
    const toastContainer = $('#toast-container');
    const toast = $(`<div class="toast error">${message}</div>`);
    toastContainer.append(toast);
    setTimeout(() => toast.remove(), 3000);
}