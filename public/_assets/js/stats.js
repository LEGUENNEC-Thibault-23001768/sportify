(function() {
    const sportsStats = {
        tennis: ["Temps total joué (en minutes)", "Nombre de sets gagnés", "Nombre d'aces"],
        football: ["Temps total joué (en minutes)", "Buts marqués", "Nombre de passes"],
        basketball: ["Temps total joué (en minutes)", "Points marqués", "Tirs cadrés"],
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

    // Function to update the user interface with the fetched data
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
        if (statsData) {
            // Handle admin UI updates if needed
        }
        return [0.5, 1, 1.5, 2, 0.5, 1.5, 0.25];
    }


    function updateMemberUI(statsData) {
        const sportSelect = document.getElementById("sport-select");
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

        const updateStatsValues = (selectedSport, data) => {
            if (data && data.topActivities) {
                const activityData = data.topActivities.find((activity) => activity.sport_name === selectedSport);

                if (activityData) {
                    statValues[0].textContent = activityData.total_time;
                    statValues[1].textContent = activityData.score1;
                    statValues[2].textContent = activityData.score2;
                } else {
                    statValues[0].textContent = "0";
                    statValues[1].textContent = "0";
                    statValues[2].textContent = "0";
                }
            } else {
                statValues[0].textContent = "0";
                statValues[1].textContent = "0";
                statValues[2].textContent = "0";
            }
        };


        const updateStats = () => {
            const selectedSport = sportSelect.value;
            const stats = sportsStats[selectedSport] || ["--", "--", "--"];
            statTitles.forEach((title, index) => {
                title.textContent = stats[index] || "--";
            });
            updateStatsValues(selectedSport, statsData)
        };

        sportSelect.addEventListener("change", updateStats);
        updateStats();
        if(statsData.performanceDataFootball) {
            return [statsData.performanceDataFootball.monday || 0, statsData.performanceDataFootball.tuesday || 0, statsData.performanceDataFootball.wednesday || 0,
                statsData.performanceDataFootball.thursday || 0, statsData.performanceDataFootball.friday || 0, statsData.performanceDataFootball.saturday || 0, statsData.performanceDataFootball.sunday || 0]
        }  else if(statsData.performanceDataBasketball) {
            return [statsData.performanceDataBasketball.monday || 0, statsData.performanceDataBasketball.tuesday || 0, statsData.performanceDataBasketball.wednesday || 0,
                statsData.performanceDataBasketball.thursday || 0, statsData.performanceDataBasketball.friday || 0, statsData.performanceDataBasketball.saturday || 0, statsData.performanceDataBasketball.sunday || 0]
        }
        else if(statsData.performanceDataMusculation) {
            return [statsData.performanceDataMusculation.monday || 0, statsData.performanceDataMusculation.tuesday || 0, statsData.performanceDataMusculation.wednesday || 0,
                statsData.performanceDataMusculation.thursday || 0, statsData.performanceDataMusculation.friday || 0, statsData.performanceDataMusculation.saturday || 0, statsData.performanceDataMusculation.sunday || 0]
        } else {
            return [0, 0, 0, 0, 0, 0, 0]
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
   async function saveStatsData() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/api/stats',
                method: 'POST',
                dataType: 'json',
                data: {
                    sport: selectedSport,
                    stats: statsValues,
                    member_id: window.currentUserId,
                },
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
        document.getElementById("stat-input").value = "";
    }


    // Function to display error messages
    function showError(message) {
        const errorDiv = document.getElementById("error-message");
        errorDiv.textContent = message;
        errorDiv.style.color = "red";
        document.getElementById("stat-input").classList.add("input-error");

        setTimeout(() => {
            document.getElementById("stat-input").classList.remove("input-error");
        }, 500);
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
})();