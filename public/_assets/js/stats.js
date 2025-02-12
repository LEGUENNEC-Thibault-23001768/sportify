(function() {
    const sportsStats = {
        tennis: ["Temps total joué (en minutes)", "Distance parcourue (km)", "Calories brûlées (kcal)"],
        football: ["Temps total joué (en minutes)", "Distance parcourue (km)", "Calories brûlées (kcal)"],
        basketball: ["Temps total joué (en minutes)", "Distance parcourue (km)", "Calories brûlées (kcal)"],
        rpm: ["Temps total (en minutes)", "Calories brûlées (en kcal)", "Distance parcourue (en kilomètres)"],
        musculation: ["Temps total (en minutes)", "Poids maximum soulevé (en kg)", "Nombre de répétitions"],
        boxe: ["Temps de combat (en minutes)", "Coups réussis", "Coups encaissés"],
    };

    let selectedSport = "";
    let currentStatIndex = 0;
    let statsValues = [];
    let isCongratsPopupActive = false;
    let lastActivePopup = null;
    let prevButton;

    // Function to load stats data from API
    async function loadStatsData() {
        try {
            const statsData = await getStatsData();
            console.log("Stats data: ", statsData);
            updateUI(statsData);
        } catch (error) {
            console.error("Error loading stats data:", error);
            showToast("Error loading stats data", 'error');
        }
    }

    // Function to fetch stats data from API
    async function getStatsData() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/stats',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    resolve(data);
                },
                error: function(error) {
                    console.error("Error fetching stats data:", error);
                    showToast("Error fetching stats data", 'error');
                    reject(error)
                }
            });
        });
    }

    function updateUI(statsData) {
        let chartData;
    
        if (window.memberStatus === 'admin') {
            chartData = updateAdminUI(statsData);
        } else {
            chartData = updateMemberUI(statsData);
        }
    
        renderBarChart(chartData);
        renderTaskCompletionChart(71);
    }
    

    function updateAdminUI(statsData) {
        const statTitles = [
            document.querySelector("#stat-1 .report-title"),
            document.querySelector("#stat-2 .report-title"),
            document.querySelector("#stat-3 .report-title")
        ];

        const statValues = [
            document.getElementById("stat-1-value"),
            document.getElementById("stat-2-value"),
            document.getElementById("stat-3-value")
        ];

       if (statsData && statsData.averageRpmStats && statsData.averageRpmStats.length > 0) {
            const rpmStats = statsData.averageRpmStats[0];

            statTitles[0].textContent = "Temps moyen (min)";
            statTitles[1].textContent = "Calories moyennes (kcal)";
            statTitles[2].textContent = "Distance moyenne (km)";

            statValues[0].textContent = rpmStats.avg_time ? rpmStats.avg_time.toFixed(1) : "0";
            statValues[1].textContent = rpmStats.avg_calories ? rpmStats.avg_calories.toFixed(1) : "0";
            statValues[2].textContent = rpmStats.avg_distance ? rpmStats.avg_distance.toFixed(1) : "0";

        } else {
           statTitles[0].textContent = "Temps total joué";
           statTitles[1].textContent = "Buts marqués";
           statTitles[2].textContent = "Passes réussies";
            statValues[0].textContent = "0";
            statValues[1].textContent = "0";
            statValues[2].textContent = "0";
       }
         return [0.5, 1, 1.5, 2, 0.5, 1.5, 0.25];
    }

    function updateMemberUI(statsData) {
        const sportSelect = document.getElementById("sport-select");
        const statTitles = [
            document.querySelector("#stat-1 .report-title"),
            document.querySelector("#stat-2 .report-title"),
            document.querySelector("#stat-3 .report-title"),
        ];
    
        const statValues = [
            document.getElementById("stat-1-value"),
            document.getElementById("stat-2-value"),
            document.getElementById("stat-3-value"),
        ];
    
        if (statsData.averageRpmStats) {
            const rpmStats = statsData.averageRpmStats;
    
            statTitles[0].textContent = "Temps moyen (minutes)";
            statTitles[1].textContent = "Calories moyennes (kcal)";
            statTitles[2].textContent = "Distance moyenne (km)";
    
            statValues[0].textContent = rpmStats.avg_time ? rpmStats.avg_time.toFixed(1) : "0";
            statValues[1].textContent = rpmStats.avg_calories ? rpmStats.avg_calories.toFixed(1) : "0";
            statValues[2].textContent = rpmStats.avg_distance ? rpmStats.avg_distance.toFixed(1) : "0";
        } else {
            statTitles[0].textContent = "Temps total joué";
            statTitles[1].textContent = "Buts marqués";
            statTitles[2].textContent = "Passes réussies";
            statValues[0].textContent = "0";
            statValues[1].textContent = "0";
            statValues[2].textContent = "0";
        }
    
        if (statsData.performanceDataRpm) {
            return [
                statsData.performanceDataRpm.monday || 0,
                statsData.performanceDataRpm.tuesday || 0,
                statsData.performanceDataRpm.wednesday || 0,
                statsData.performanceDataRpm.thursday || 0,
                statsData.performanceDataRpm.friday || 0,
                statsData.performanceDataRpm.saturday || 0,
                statsData.performanceDataRpm.sunday || 0,
            ];
        } else {
            return [0, 0, 0, 0, 0, 0, 0];
        }
    }
    
    // Function to render the bar chart
    function renderBarChart(chartData) {
        const barCtx = document.getElementById("barChart").getContext("2d");
        new Chart(barCtx, {
            type: "bar",
            data: {
                labels: ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"],
                datasets: [
                    {
                        label: "",
                        data: chartData,
                        backgroundColor: "rgba(75, 192, 192, 0.2)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1
                    }
                ]
            },
            options: {
                maintainAspectRatio: true,
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 0.5,
                            callback: function(value) {
                                const hours = Math.floor(value);
                                const minutes = (value % 1) * 60;
                                return `${hours}h${minutes === 0 ? "00" : "30"}`;
                            },
                            color: "#FFFFFF",
                            font: {
                                size: 14,
                                weight: "bold"
                            }
                        },
                        grid: {
                            color: "rgba(255, 255, 255, 0.1)"
                        }
                    },
                    x: {
                        ticks: {
                            color: "#FFFFFF",
                            font: {
                                size: 14,
                                weight: "bold"
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function renderTaskCompletionChart(completionPercentage) {
        const taskCtx = document.getElementById("taskCompletionChart").getContext("2d");
        new Chart(taskCtx, {
            type: "doughnut",
            data: {
                labels: ["Complet", "Manqué"],
                datasets: [
                    {
                        data: [completionPercentage, 100 - completionPercentage],
                        backgroundColor: ["rgba(255, 105, 180, 0.8)", "#666"],
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                cutout: "70%",
                plugins: {
                    legend: {
                        display: false
                    }
                },
                elements: {
                    arc: {
                        borderRadius: 20
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });

        const chartCanvas = document.getElementById("taskCompletionChart");
        chartCanvas.style.filter = "drop-shadow(0px 0px 15px rgba(255, 105, 180, 0.7))";
    }

    // Function to initialize popup event listeners
    function initializePopups() {
        document.querySelectorAll(".category-card").forEach(card => {
            card.removeEventListener("click", handleSportSelection);
            card.addEventListener("click", handleSportSelection);
        });

        document.querySelectorAll(".popup").forEach(popup => {
            popup.addEventListener('transitionend', function() {
                if (this.classList.contains('hidden')) {
                    this.style.display = 'none';
                }
            });
        });
        document.querySelectorAll(".close-popup").forEach(closeBtn => {
            closeBtn.removeEventListener("click", handleClosePopup);
            closeBtn.addEventListener("click", handleClosePopup);
        });

        document.getElementById("close-congrats-btn")?.addEventListener("click", () => {
            closeCongratsPopup();
        });

        const confirmYesBtn = document.getElementById("confirm-yes-btn");
        const confirmNoBtn = document.getElementById("confirm-no-btn");

        confirmYesBtn?.removeEventListener("click", handleConfirmYes);
        confirmYesBtn?.addEventListener("click", handleConfirmYes);

        confirmNoBtn?.removeEventListener("click", handleConfirmNo);
        confirmNoBtn?.addEventListener("click", handleConfirmNo);

        const statInput = document.getElementById("stat-input");
        statInput.addEventListener("input", () => {
            if (statInput.value.length > 4) {
                statInput.value = statInput.value.slice(0, 4);
            }
            statInput.value = statInput.value.replace(/[^0-9]/g, "");
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                if (!document.getElementById("popup-stats").classList.contains("hidden")) {
                    handleNext();
                }
                 if (!document.getElementById("popup-rpm").classList.contains("hidden")) {
                  handleRpmSubmit();
                }
            }
        });
    }

    // Function to setup the "Add Stats" button
    function setupAddStatsButton() {
        const addStatsButton = document.getElementById("add-stats-btn");
        addStatsButton.addEventListener("click", () => {
            resetPopups();
            showPopup("popup-sports");
        });
    }

    // Function to handle sport selection
   function handleSportSelection() {
        resetPopups();
        selectedSport = this.dataset.sport;
	currentStatIndex = 0;
	statsValues = new Array(sportsStats[selectedSport].length).fill("");
        hidePopup("popup-sports");
        showPopup("popup-stats");
        updateStatPopup();
        isCongratsPopupActive = false;
    }

    // Function to handle closing a popup
    function handleClosePopup() {
        const currentPopup = this.closest(".popup");
        if (currentPopup) {
            hidePopup(currentPopup.id);
            showPopup("popup-confirmation");
            lastActivePopup = currentPopup;
        }
    }

    // Function to handle confirmation of leaving the popup
    function handleConfirmYes() {
        hidePopup("popup-confirmation");
        initializePopups();
    }

    // Function to handle cancelling leaving the popup
    function handleConfirmNo() {
        hidePopup("popup-confirmation");
        if (lastActivePopup) {
            showPopup(lastActivePopup.id);
            lastActivePopup = null;
        }
    }

    // Function to close the congrats popup
    function closeCongratsPopup() {
        hidePopup("popup-congrats");
        isCongratsPopupActive = false;
    }
    const submitButton = document.getElementById("stat-submit-btn");


    // Function to handle going to the previous stat input
    function handlePrev() {
        clearError();
        if (currentStatIndex > 0) {
            currentStatIndex--;
            updateStatPopup();
        } else {
            hidePopup("popup-stats");
            showPopup("popup-sports");
        }
    }

    // Function to handle going to the next stat input or submitting
    async function handleNext() {
        const value = document.getElementById("stat-input").value;
        clearError();
        if (value === "") {
            showError("Oups ! Tu dois entrer une valeur avant de continuer. 😅");
            return;
        } else if (value < 0) {
            showError("Hé, on veut des stats positives ici, pas des valeurs de déprime ! 😜");
            return;
        }
        statsValues[currentStatIndex] = value;

        if (currentStatIndex < sportsStats[selectedSport].length - 1) {
            currentStatIndex++;
            updateStatPopup();
        } else {
            try {
                await saveStatsData(); // Save to API
                hidePopup("popup-stats");
                showPopup("popup-congrats");
                isCongratsPopupActive = true;
            } catch (err) {
                console.error("Error saving stats", err)
                showToast("Error saving stats!", 'error')
            }
        }
    }
    
    async function handleRpmSubmit() {
        const rpmTime = document.getElementById("rpm-time-input").value;
        const rpmCalories = document.getElementById("rpm-calories-input").value;
        const rpmDistance = document.getElementById("rpm-distance-input").value;
      clearError();
        if (rpmTime === "") {
            showError("Oups ! Tu dois entrer une valeur avant de continuer. 😅");
            return;
        }
         if (rpmTime < 0) {
            showError("Hé, on veut des stats positives ici, pas des valeurs de déprime ! 😜");
            return;
        }
      try {
            await saveStatsData(null, { playTime: rpmTime, calories: rpmCalories, distance: rpmDistance }); // Save to API
              hidePopup("popup-rpm");
              showPopup("popup-congrats");
                isCongratsPopupActive = true;
        } catch (err) {
            console.error("Error saving RPM stats", err)
          showToast("Error saving RPM stats!", 'error')
        }
    }


    async function saveStatsData(stats = null, rpmData = null) {
        return new Promise((resolve, reject) => {
            let data = {};
          if (rpmData) {
                data = {
                    rpm: true,
                    playTime: rpmData.playTime,
                    calories: rpmData.calories,
                    distance: rpmData.distance,
                    member_id: window.currentUserId
                }
            } else {
             data = {
                    sport: selectedSport,
                    stats: statsValues,
                    member_id: window.currentUserId,
                }
            }

            $.ajax({
                url: '/api/stats',
                method: 'POST',
                dataType: 'json',
                data: data,
                success: function(data) {
                    console.log("Stats saved successfully:", data);
                    showToast("Stats saved successfully!", 'success')
                    resolve(data)
                    loadStatsData();

                },
                error: function(error) {
                    console.error("Error saving stats data:", error);
                    showToast("Error saving stats", 'error');
                   reject(error)
                }
            });
        });
    }


    function updateStatPopup() {
        document.getElementById("stat-sport-title").textContent = `Statistiques - ${selectedSport.charAt(0).toUpperCase() + selectedSport.slice(1)}`;
        document.getElementById("stat-question").textContent = sportsStats[selectedSport][currentStatIndex];
        document.getElementById("stat-input").value = statsValues[currentStatIndex] || "";
        document.getElementById("stat-submit-btn").textContent = currentStatIndex < sportsStats[selectedSport].length - 1 ? "Suivant" : "Soumettre";
        prevButton.style.display = "inline-block";
    }

    function resetPopups() {
        document.querySelectorAll(".popup").forEach(popup => {
            hidePopup(popup.id);
        });
        clearError();
       document.querySelectorAll('.form-input').forEach(input => {
           input.value = '';
       });
       document.getElementById("stat-input").value = "";
    }


    // Function to display error messages
   function showError(message) {
        const errorDiv = document.getElementById("error-message");
        errorDiv.textContent = message;
        errorDiv.style.color = "red";
        if(document.getElementById("stat-input")) {
             document.getElementById("stat-input").classList.add("input-error");
             setTimeout(() => {
                  document.getElementById("stat-input").classList.remove("input-error");
             }, 500);
        }
        if(document.getElementById("rpm-time-input")) {
              document.getElementById("rpm-time-input").classList.add("input-error");
              setTimeout(() => {
                   document.getElementById("rpm-time-input").classList.remove("input-error");
             }, 500);
        }

    }

    function clearError() {
        const errorDiv = document.getElementById("error-message");
        errorDiv.textContent = "";
    }

    function showToast(message, type = 'success') {
        var toast = $(`<div class="toast-message toast-${type}">` + message + `<span class="close-toast">×</span></div>`);

        $('#toast-container').append(toast);

        toast.fadeIn(400).delay(3000).fadeOut(400, function() {
            $(this).remove();
        });
        toast.find('.close-toast').click(function() {
            toast.remove();
        });
    }
    function showPopup(popupId) {
        const popup = document.getElementById(popupId);
        if(popup) {
            popup.style.display = 'block';
            popup.classList.remove('hidden');
        }
    }

    function hidePopup(popupId) {
        const popup = document.getElementById(popupId);
        if(popup) {
            popup.classList.add('hidden');
        }
    }
    window.initialize = () => {

        // Create previous button here
        prevButton = document.createElement("button");
        prevButton.id = "stat-prev-btn";
        prevButton.textContent = "Précédent";
        const submitButton = document.getElementById("stat-submit-btn");
        const buttonsContainer = document.querySelector('.buttons-container');
        buttonsContainer.insertBefore(prevButton, submitButton);

        prevButton.addEventListener("click", handlePrev);
        // Add click event listener to submit button
        submitButton.addEventListener("click", handleNext);
         loadStatsData();
        initializePopups();
        setupAddStatsButton();
    };
     window.addEventListener('DOMContentLoaded', () => {
            window.initialize();
        })
})();
