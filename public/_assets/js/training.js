(function() {
    let currentStep = 1;
    let trainingContent = $('#trainingContent');
    let currentPlanData;
    let currentDayData = null;
    let currentExerciseIndex = 0;
    let currentSetIndex = 0;
    let restTimer = null;

    function loadTrainingData() {
        $.ajax({
            url: '/api/training',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.plan) {
                    let plan = response.plan;
                    plan = plan.replace(/^```json\\n/, '');
                    plan = plan.replace(/```$/, '');
                    plan = plan.trim();
                    try {
                        console.log("Plan data:", plan);
                        currentPlanData = JSON.parse(plan);
                    } catch (e) {
                        console.error('Error parsing JSON:', e, plan);
                        console.log("no plan : displayCreatePlanButton called");
                        displayCreatePlanButton();
                        return;
                    }
                    displayTrainingPlan(response.plan);
                } else {
                    console.log("no plan : displayCreatePlanButton called");
                    displayCreatePlanButton();
                }
            },
            error: function(error) {
                console.error("Error loading training data:", error.responseJSON);
                console.log("error : displayCreatePlanButton called", error.responseJSON);
                displayCreatePlanButton();
            }
        });
    }

    function displayCreatePlanButton() {
        console.log("displayCreatePlanButton called");
        console.log("trainingContent", trainingContent);
        trainingContent.html('<button id="createPlanButton" class="btn btn-primary">Create Training Plan</button>');
        console.log("Button HTML:", trainingContent.html());
        $('#createPlanButton').on('click', function() {
            openCreatePlanPopup();
        });
    }

    function displayTrainingPlan(plan) {
        currentPlanData = JSON.parse(plan);
        if (currentPlanData) {
            let html = '<h1>Votre entraînement personnalisé</h1><div class="training-plan">';
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
            html += '</div><button id="editPlanButton" class="btn btn-secondary">Modifier le plan</button><div id="active-training" style="display:none;"><h3 id="active-day"></h3><div id="current-exercise"></div><button id="end-set" class="hidden">End Set</button><div id="rest-timer" style="display: none;"> Resting: <span id="timer-countdown"></span> <button id="skip-timer">Skip</button> </div><button id="next-exercise" class="hidden">Next Exercise</button> <button id="finish-day" class="hidden">Finish Day</button><button id="go-back-exercises" class="hidden">Go Back</button></div>';
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
                $(this).find('.exercise-card .start-exercise').removeClass('hidden');
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
                if (!event.target.closest('.toggle-description, .star')) {
                    if (!card.classList.contains('selected-day')) {
                        showSelectedDay(card);
                    } else {
                        removeSelectedDay(card);
                    }
                }
            });
        });

        function showBackButton(card) {
            let backButton = card.querySelector('.back-button');
            if (!backButton) {
                backButton = document.createElement('button');
                backButton.textContent = 'Retour';
                backButton.classList.add('back-button');
                backButton.style.marginTop = '20px';
                backButton.addEventListener('click', () => {
                    removeSelectedDay(card);
                });
                card.appendChild(backButton);
            }
            backButton.style.display = 'block';
        }
    
        function showSelectedDay(card) {
            dayCards.forEach(otherCard => {
                if (otherCard !== card) {
                    otherCard.classList.add('hidden');
                }
            });
            card.classList.add('selected-day');
            updateDayView(card);
            toggleExercisesVisibility(card);
            showBackButton();
        }
    
        function removeSelectedDay(card) {
            dayCards.forEach(otherCard => {
                otherCard.classList.remove('hidden');
            });
            card.classList.remove('selected-day');
            removeBackButton(card);
            resetDayCards(card);
            hideBackButton();
        }
    
        function removeBackButton(card) {
            const backButton = card.querySelector('.back-button');
            if (backButton) {
                backButton.remove();
            }
        }
    
         function updateDayView(card) {
            const day = card.dataset.day;
            currentDayData = currentPlanData.days.find(d => d.day === day);
        }
    
        function resetDayCards(card) {
           dayCards.forEach(otherCard => {
                otherCard.classList.remove('hidden');
            });
            if (card) {
                toggleExercisesVisibility(card);
            }
        }

        document.querySelectorAll('.toggle-description').forEach(button => {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                const description = button.previousElementSibling;
                description.style.display = description.style.display === 'none' ? 'block' : 'none';
                button.textContent = description.style.display === 'none' ? 'Show Description' : 'Hide Description';
            });
        });
    }

     function toggleExercisesVisibility(card) {
        const exercises = card.querySelector('.exercises');
        exercises.style.display = exercises.style.display === 'none' || exercises.style.display === '' ? 'flex' : 'none';
    
        if (exercises.style.display === 'flex') {
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
         endSetButton.text('Start').removeClass('hidden');
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
                    showErrorToast("Error generating plan", 'error');
                }
            },
            error: function(error) {
                console.error("Error generating training plan:", error);
                showErrorToast("Error generating plan", 'error');
            }
        });
    }
    
    
    function openCreatePlanPopup() {
        $('#createPlanPopup').show();
        $('#popupOverlay').show();
    }
    
    window.closeCreatePlanPopup = () => {
        $('#createPlanPopup').hide();
        $('#popupOverlay').hide();
    }
    
    function openEditPlanPopup() {
        $('#editPlanPopup').show();
        $('#popupOverlay').show();
        setupEditPlanForm();
    }
    
    window.closeEditPlanPopup = () => {
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

    let currentStepModal = 0;
    const steps = [
        { title: "Sélectionnez votre sexe", type: "options", options: ["Homme", "Femme", "Autre"], key: "gender" },
        { title: "Sélectionnez votre niveau", type: "options", options: ["Débutant", "Intermédiaire", "Avancé"], key: "level" },
        { title: "Sélectionnez votre objectif", type: "options", options: ["Perdre du poids", "Prendre du muscle", "Amélioration physique", "Courrir un marathon"], key: "goals" },
        { title: "Entrez votre poids (kg)", type: "input", inputType: "number", key: "weight", placeholder: "Ex: 70" },
        { title: "Entrez votre taille (cm)", type: "input", inputType: "number", key: "height", placeholder: "Ex: 175" },
        { title: "Avez-vous des contraintes médicaux ?", type: "input", inputType: "text", key: "constraints", placeholder: "Ex: Mal de dos" },
        { title: "Emplacement préferré", type: "options", options: ["Domicile", "Salle de sport", "Extérieur"], key: "preferences" },
        { title: "Equipement disponible", type: "options", options: ["None", "Halteres", "Tapis de course", "Bande résistante"], key: "equipment" },
    ];

    let formDataModal = {};

    function clearAllButtonClass( className)  {
        $(`${className}`).removeClass('selected')
    };

    function updateSelectState (  buttonElement , formDataModalKey , newValue ) {
       const button = $(buttonElement)
        clearAllButtonClass('.square-option')
        button.addClass('selected')
        formDataModal[formDataModalKey] = newValue
    };

    function setupEditPlanForm() {
        let currentStepModal = 0;
        const modalBody = $("#editPlanPopup .modal-body")[0];
        const nextButton = document.createElement("button");
        nextButton.textContent = "Next";
        nextButton.classList.add("btn", "btn-primary");
        nextButton.style.marginTop = "20px";
        const stepTitle = document.createElement("h5");
        stepTitle.style.color = '#A2D149';
        modalBody.innerHTML = "";
        currentStepModal = 0;
        formDataModal = {};
        displayModalStep();
    
        function displayModalStep() {
            console.log("displayModalStep() appelée, étape:", currentStepModal); 
            const step = steps[currentStepModal];
            modalBody.innerHTML = "";
            stepTitle.textContent = step.title;
            modalBody.appendChild(stepTitle);
            console.log(step)
            if (step.type === "options") {
                const optionsContainer = document.createElement('div');
                optionsContainer.classList.add('options-container');

                step.options.forEach(option => {
                    const button = document.createElement("button");
                    button.textContent = option;
                    console.log("coucou", button.textContent)
                    button.classList.add("btn", "square-option");
                    button.dataset.value = option;

                    button.addEventListener("click", (event) => {
                        updateSelectState(event.target, step.key, event.target.dataset.value);
                        console.log('aaaaaaaaaaaaa', nextButton)
                        nextButton.disabled = false;
                    });
                    optionsContainer.appendChild(button);
                });
                modalBody.appendChild(optionsContainer);
            } else if (step.type === "input") {
                nextButton.disabled = false;
                const input = document.createElement("input");
                input.type = step.inputType;
                input.placeholder = step.placeholder || "";
                input.classList.add("form-control");
                input.style.marginTop = "10px";
                console.log('input', input)
                input.addEventListener("input", (event) => {
                    console.log('Ecouteur input attaché à input');
                    console.log('el', event.target.value.trim())
                    if (event.target.value.trim() !== "") {
                        formDataModal[step.key] = event.target.value;
                        nextButton.disabled = false; 
                    } else {
                        nextButton.disabled = true;  
                    }
                });
                modalBody.appendChild(input);
            }
            modalBody.appendChild(nextButton);
            nextButton.disabled = true; 
        };
    
        nextButton.addEventListener("click", () => {
            currentStepModal++;
            if (currentStepModal < steps.length) {
                displayModalStep();
            } else {
                modalBody.innerHTML = `<h5 style="color: #A2D149;">Changes finished!</h5> <pre style="color: #fff;">${JSON.stringify(formDataModal, null, 2)}</pre>`;
                nextButton.style.display = "none";
                sendUpdatedData(formDataModal);
            }
        });
    
        function sendUpdatedData(updatedData) {
            $.ajax({
                url: "/api/training/update",
                type: "POST",
                dataType: 'json',
                data: updatedData,
                success: function(response) {
                    loadTrainingData();
                    showSuccessToast("Training plan updated successfully");
                },
                error: function(xhr, status, error) {
                    console.error("Error updating training plan:", xhr);
                    showErrorToast("Error updating plan", 'error');
                }
            });
        }
    };


    window.initialize = () => {
        trainingContent = $('#trainingContent');
        loadTrainingData();

        trainingContent.on('click', '#finish-day', () => {
            $('.training-plan').show();
            $('#active-training').hide();
            $('#go-back-exercises').hide();
            $('#finish-day').addClass('hidden');
        });
        
        trainingContent.on('click', '#go-back-exercises', () => {
            $('#active-training').hide();
            $('#go-back-exercises').hide();
            $('.training-plan').show();
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
                    showErrorToast("Error updating plan", 'error');
                }
            });
        });
        
        $('#nextButton').click(function() {
            $('#nextButton').prop('disabled', false);
            let input = $(`#question${currentStep} :input`).val();
            console.log("skiii")
            console.log("Next button clicked!"); 
            console.log("Current step:", currentStep); 
            console.log("Input value:", input);

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

                        
                        if ($(`#question${response.next_step}`).find('.options-container').length) {
                            const optionsContainer = $(`#question${response.next_step}`).find('.options-container')[0];

                            $(optionsContainer).find('button').each((_, button) => {
                                $(button).on('click', () => {
                                    $(optionsContainer).find('.square-option').removeClass('selected');
                                    $(button).addClass('selected');
                                    $('#nextButton').prop('disabled', false);
                                    
                                });
                            });
                        }
                        currentStep = response.next_step;
                        $('#step').val(currentStep);
                        $('#nextButton').prop($(`#question${currentStep} select, #question${currentStep} input, #question${currentStep} textarea`).val() === "");
                    }
                },
                error: function(error) {
                    console.error("Error processing step:", error);
                    showErrorToast("Error processing step", 'error');
                }
            });
        });

        trainingContent.on('click', '#end-set', () => {
            currentSetIndex++;
            const currentExerciseContainer = $('#current-exercise');
            const numSets = parseInt(currentExerciseContainer.find('.sets').children().length);
        
        
            updateStars(numSets);
            const currentExerciseData = $(currentExerciseContainer).closest('#active-training').find('#current-exercise').find('h4').text();
            const exercise = currentDayData.exercises.find(ex => ex.name === currentExerciseData);
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
                return currentDayData.exercises[currentExerciseIndex] && currentDayData.exercises[currentExerciseIndex].name === name;
            }).first();
        
            displayCurrentExercise(exerciseCard);
        });
    }
})();