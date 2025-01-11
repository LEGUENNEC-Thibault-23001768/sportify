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
 };
   const addStatsBtn = document.getElementById("add-stats-btn");
  const popupContainer = document.getElementById("popup-container");
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

function initializePopups() {
  document.querySelectorAll(".category-card").forEach(card => {
       card.removeEventListener("click", handleSportSelection);
       card.addEventListener("click", handleSportSelection);
   });

   document.querySelectorAll(".close-popup").forEach(closeBtn => {
      closeBtn.removeEventListener("click", handleClosePopup);
       closeBtn.addEventListener("click", handleClosePopup);
   });

   document.getElementById("close-congrats-btn").addEventListener("click", () => {
       closeCongratsPopup();
  });

   const confirmYesBtn = document.getElementById("confirm-yes-btn");
  const confirmNoBtn = document.getElementById("confirm-no-btn");

   confirmYesBtn?.removeEventListener("click", handleConfirmYes);
  confirmYesBtn?.addEventListener("click", handleConfirmYes);

   confirmNoBtn?.removeEventListener("click", handleConfirmNo);
   confirmNoBtn?.addEventListener("click", handleConfirmNo);
}


const submitButton = document.getElementById("stat-submit-btn");
const statInput = document.getElementById("stat-input");

statInput.addEventListener("input", () => {
  if (statInput.value.length > 4) {
       statInput.value = statInput.value.slice(0, 4);
   }
   statInput.value = statInput.value.replace(/[^0-9]/g, "");
});

const prevButton = document.createElement("button");
prevButton.id = "stat-prev-btn";
prevButton.textContent = "Précédent";
const buttonsContainer = document.querySelector('.buttons-container');
buttonsContainer.insertBefore(prevButton, submitButton);

prevButton.addEventListener("click", handlePrev);
submitButton.addEventListener("click", handleNext);

document.addEventListener("keydown", (e) => {
   if (e.key === "Enter") {
       if (!document.getElementById("popup-stats").classList.contains("hidden")) {
          handleNext();
       }
   }
});

function handleSportSelection() {
   resetPopups();
   selectedSport = this.dataset.sport;
  currentStatIndex = 0;
  statsValues = new Array(sportsStats[selectedSport].length).fill("");
   document.getElementById("popup-sports").classList.add("hidden");
   document.getElementById("popup-stats").classList.remove("hidden");
   updateStatPopup();
  isCongratsPopupActive = false;
}

function handleClosePopup() {
   const currentPopup = this.closest(".popup");
  if (currentPopup) {
       currentPopup.classList.add("hidden");
       document.getElementById("popup-confirmation").classList.remove("hidden");
      lastActivePopup = currentPopup;
   }
}

function handleConfirmYes() {
   document.getElementById("popup-confirmation").classList.add("hidden");
  initializePopups();
}

function handleConfirmNo() {
  document.getElementById("popup-confirmation").classList.add("hidden");
   if (lastActivePopup) {
      lastActivePopup.classList.remove("hidden");
       lastActivePopup = null;
  }
}

function closeCongratsPopup() {
   document.getElementById("popup-congrats").classList.add("hidden");
  isCongratsPopupActive = false;
}

function handlePrev() {
   clearError();
   if (currentStatIndex > 0) {
       currentStatIndex--;
       updateStatPopup();
   } else {
       document.getElementById("popup-stats").classList.add("hidden");
       document.getElementById("popup-sports").classList.remove("hidden");
   }
}

function handleNext() {
  const value = statInput.value;
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
       document.getElementById("popup-stats").classList.add("hidden");
       document.getElementById("popup-congrats").classList.remove("hidden");
      isCongratsPopupActive = true;
   }
}

function updateStatPopup() {
  document.getElementById("stat-sport-title").textContent = `Statistiques - ${selectedSport.charAt(0).toUpperCase() + selectedSport.slice(1)}`;
  document.getElementById("stat-question").textContent = sportsStats[selectedSport][currentStatIndex];
  statInput.value = statsValues[currentStatIndex] || "";
   submitButton.textContent = currentStatIndex < sportsStats[selectedSport].length - 1 ? "Suivant" : "Soumettre";
   prevButton.style.display = "inline-block";
}

function resetPopups() {
  document.querySelectorAll(".popup").forEach(popup => popup.classList.add("hidden"));
   clearError();
  statInput.value = "";
}

function showError(message) {
  const errorDiv = document.getElementById("error-message");
   errorDiv.textContent = message;
   errorDiv.style.color = "red";
   statInput.classList.add("input-error");

   setTimeout(() => {
      statInput.classList.remove("input-error");
   }, 500);
}

function clearError() {
  const errorDiv = document.getElementById("error-message");
   errorDiv.textContent = "";
}
   
function showToast(message, type = 'success') {
       // Create toast element
      var toast = $(`<div class="toast-message toast-${type}">` + message + '<span class="close-toast">×</span></div>');

       // Append to container
      $('#toast-container').append(toast);

       // Show the toast
       toast.fadeIn(400).delay(3000).fadeOut(400, function() {
          $(this).remove();
       });
       // Close button functionality
       toast.find('.close-toast').click(function() {
          toast.remove();
      });
}