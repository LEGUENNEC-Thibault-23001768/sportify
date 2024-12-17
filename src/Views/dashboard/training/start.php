<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Training Plan</title>
    <link rel="preload" href="../_assets/css/event_style.css" as="style"> <link rel="stylesheet" href="../_assets/css/event_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentStep = 1;

            $('#nextButton').click(function() {
                let input = $(`#question${currentStep} :input`).val();
                
                $.ajax({
                    url: '/api/training/process-step',
                    method: 'POST',
                    dataType: 'json',
                    data: { step: currentStep, data: input },
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
                            window.location.href = '/dashboard/training';
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
        });
    </script>
</head>
<body>
    <div class="form-container">
        <form id="trainingForm" action="" method="post">
            <input type="hidden" name="step" id="step" value="1">

            <div class="form-group" id="question1">
                <label for="gender">What's your gender?</label>
                <select name="gender" id="gender" class="form-control">
                    <option value="man">Man</option>
                    <option value="woman">Woman</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group" id="question2" style="display:none;">
                <label for="level">What's your level of physical activity?</label>
                <select name="level" id="level" class="form-control">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>

            <div class="form-group" id="question3" style="display:none;">
                <label for="goals">What are your training goals?</label>
                <select name="goals" id="goals" class="form-control">
                    <option value="lose_weight">Lose Weight</option>
                    <option value="build_muscle">Build Muscle</option>
                    <option value="improve_fitness">Improve Fitness</option>
                    <option value="marathon">Run a Marathon</option>
                </select>
            </div>

            <div class="form-group" id="question4" style="display:none;">
                <label for="weight">What's your weight? (in kg)</label>
                <input type="number" name="weight" id="weight" class="form-control" min="30" max="200">
            </div>

            <div class="form-group" id="question5" style="display:none;">
                <label for="height">What's your height? (in cm)</label>
                <input type="number" name="height" id="height" class="form-control" min="140" max="210">
            </div>

            <div class="form-group" id="question6" style="display:none;">
                <label for="constraints">Do you have any medical or physical constraints?</label>
                <textarea name="constraints" id="constraints" class="form-control"></textarea>
            </div>

            <div class="form-group" id="question7" style="display:none;">
                <label for="preferences">What are your training preferences?</label>
                <select name="preferences" id="preferences" class="form-control">
                    <option value="no_preference">No Preference</option>
                    <option value="home">Home</option>
                    <option value="gym">Gym</option>
                    <option value="outdoor">Outdoor</option>
                </select>
            </div>

            <div class="form-group" id="question8" style="display:none;">
                <label for="equipment">What equipment do you have access to?</label>
                <select name="equipment" id="equipment" class="form-control">
                    <option value="none">None</option>
                    <option value="dumbbells">Dumbbells</option>
                    <option value="treadmill">Treadmill</option>
                    <option value="resistance_bands">Resistance Bands</option>
                </select>
            </div>

            <button type="button" id="nextButton" class="btn btn-primary">Next</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Your script from index.php goes here, if needed for this view.
    </script>
</body>
</html>