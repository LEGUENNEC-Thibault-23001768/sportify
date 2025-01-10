function initialize() {
    loadStatsData();
    console.log('Stats view initialized');
}

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


async function getStatsData() {
 return new Promise((resolve, reject) => {
       $.ajax({
          url: '/api/stats',
          method: 'GET',
           dataType: 'json',
           success: function (data) {
               resolve(data);
           },
           error: function (error) {
              console.error("Error fetching stats data:", error);
               showToast("Error fetching stats data", 'error');
               reject(error)
           }
     });
 });
}
function updateUI(statsData) {

   const sportsStats = {
       tennis: ["Temps total joué (en minutes)", "Nombre de sets gagnés", "Nombre d'aces"],
       football: ["Temps total joué (en minutes)", "Buts marqués", "Nombre de passes"],
       basketball: ["Temps total joué (en minutes)", "Points marqués", "Tirs cadrés"],
      rpm: ["Temps total (en minutes)", "Calories brûlées (en kcal)", "Distance parcourue (en kilomètres)"],
       musculation: ["Temps total (en minutes)", "Poids maximum soulevé (en kg)", "Nombre de répétitions"],
       boxe: ["Temps de combat (en minutes)", "Coups réussis", "Coups encaissés"]
   };
   const sportSelect = document.getElementById("sport-select");
   const statTitles = [
       document.querySelector("#stat-1 .report-title"),
       document.querySelector("#stat-2 .report-title"),
      document.querySelector("#stat-3 .report-title")
   ];
       const updateStats = () => {
       const selectedSport = sportSelect.value;
       const stats = sportsStats[selectedSport] || ["--", "--", "--"];
       statTitles.forEach((title, index) => {
           title.textContent = stats[index] || "--";
       });
   };

   sportSelect.addEventListener("change", updateStats);
  updateStats();

   const barCtx = document.getElementById("barChart").getContext("2d");
   new Chart(barCtx, {
       type: "bar",
      data: {
           labels: ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"],
           datasets: [
               {
                   label: "",
                   data: [0.5, 1, 1.5, 2, 0.5, 1.5, 0.25],
                  backgroundColor: "rgba(75, 192, 192, 0.2)",
                   borderColor: "rgba(75, 192, 192, 1)",
                   borderWidth: 1
               }
           ]
       },
       options: {
           maintainAspectRatio: true, // Garde un bon ratio hauteur/largeur
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

   const taskCtx = document.getElementById("taskCompletionChart").getContext("2d");
   new Chart(taskCtx, {
      type: "doughnut",
       data: {
           labels: ["Complet", "Manqué"],
          datasets: [
               {
                   data: [71, 29],
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
   // Gestion du popup
  const addStatsBtn = document.getElementById("add-stats-btn");
   const popupContainer = document.getElementById("popup-container");

   const loadPopup = async () => {
       try {
           const response = await fetch("/src/Views/dashboard/statutil.html");
           if (!response.ok) {
              throw new Error(`Erreur lors du chargement du fichier HTML : ${response.statusText}`);
           }
           const popupHTML = await response.text();
           popupContainer.innerHTML = popupHTML;
           popupContainer.classList.remove("hidden");

           // Charger le CSS et JS dynamiquement
           const statutilCSS = document.createElement("link");
          statutilCSS.rel = "stylesheet";
           statutilCSS.href = "/public/_assets/css/statutil.css";
           document.head.appendChild(statutilCSS);

           const statutilJS = document.createElement("script");
          statutilJS.src = "/public/_assets/js/statutil.js";
           document.body.appendChild(statutilJS);
       } catch (error) {
          console.error("Erreur lors du chargement du popup :", error);
           popupContainer.innerHTML = "<p style='color:red;'>Erreur : Impossible de charger le popup.</p>";
       }
   };

   addStatsBtn.addEventListener("click", () => {
       if (!document.getElementById("popup-sports")) {
          loadPopup();
       } else {
           const popupSports = document.getElementById("popup-sports");
           if (popupSports) {
               popupSports.classList.remove("hidden");
           }
       }
   });

  popupContainer.addEventListener("click", (event) => {
       if (event.target.classList.contains("close-popup")) {
           popupContainer.classList.add("hidden");
       }
   });

   const popupSports = document.getElementById("popup-sports");
    const popupConfirmation = document.getElementById("popup-confirmation");
  const pageWrapper = document.querySelector(".page-wrapper");
   const closeButtons = document.querySelectorAll(".close-popup, #close-congrats-btn, #confirm-yes-btn, #confirm-no-btn");

   // Fonction pour afficher un popup
  const showPopup = (popup) => {
       popup.classList.remove("hidden");
       pageWrapper.classList.add("blur"); // Floute l'arrière-plan
   };

  // Fonction pour fermer un popup
   const closePopup = (popup) => {
       popup.classList.add("hidden");
       pageWrapper.classList.remove("blur"); // Supprime le flou
   };

   // Gestion du clic sur "Ajoutez vos statistiques"
  addStatsBtn.addEventListener("click", () => {
       showPopup(popupSports);
   });

   // Gestion des clics sur les boutons de fermeture
  closeButtons.forEach((button) => {
       button.addEventListener("click", (event) => {
           const parentPopup = event.target.closest(".popup");
           if (parentPopup) {
               closePopup(parentPopup);
          }
       });
  });

   // Gestion du clic sur le popup de confirmation
   document.getElementById("confirm-yes-btn").addEventListener("click", () => {
       closePopup(popupConfirmation);
   });

  // Gestion de la touche "Échap" pour fermer les popups
   document.addEventListener("keydown", (event) => {
       if (event.key === "Escape") {
          const activePopup = document.querySelector(".popup:not(.hidden)");
           if (activePopup) {
               closePopup(activePopup);
           }
       }
   });

   // Gestion du clic en dehors des popups
   document.querySelectorAll(".popup").forEach((popup) => {
      popup.addEventListener("click", (event) => {
           if (event.target === popup) {
               closePopup(popup);
          }
       });
   });
};