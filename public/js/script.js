let lastScrollTop = 0;
const header = document.querySelector("header");
let isScrollingDown = false;

window.addEventListener("scroll", () => {
  let scrollTop = window.scrollY;

  if (scrollTop > lastScrollTop && !isScrollingDown) {
    header.style.opacity = "0";
    header.style.pointerEvents = "none";
    isScrollingDown = true;
  } else if (scrollTop < lastScrollTop && isScrollingDown) {
    header.style.opacity = "1";
    header.style.pointerEvents = "all"; 
    isScrollingDown = false;
  }

  lastScrollTop = scrollTop;
});


function toggleTeamManagement() {
  const teamManagement = document.querySelector('.team-management-card');
  if (teamManagement.style.display === "none" || teamManagement.style.display === "") {
    teamManagement.style.display = "block";
  } else {
    teamManagement.style.display = "none";
  }
}


const showMoreBtn = document.getElementById('show-more-btn');
const testimonialsOnTablet = window.matchMedia("(max-width: 1024px)");
const testimonialsOnPhone = window.matchMedia("(max-width: 768px)");

let currentVisibleCount = 3; 
const testimonials = document.querySelectorAll('.testimonial-card');

function showMoreTestimonials() {
  const totalTestimonials = testimonials.length;

  for (let i = currentVisibleCount; i < currentVisibleCount + 3 && i < totalTestimonials; i++) {
    testimonials[i].style.display = 'block';
  }

  currentVisibleCount += 3;

  if (currentVisibleCount >= totalTestimonials) {
    showMoreBtn.style.display = 'none';
  }
}

showMoreBtn.addEventListener('click', showMoreTestimonials);

testimonials.forEach((testimonial, index) => {
  if (index >= currentVisibleCount) {
    testimonial.style.display = 'none';
  }
});

const pricingCards = document.querySelectorAll('.pricing-card');

function activatePlan(plan) {
  pricingCards.forEach(card => card.classList.remove('active'));

  plan.classList.add('active');
}

pricingCards.forEach(card => {
  card.addEventListener('click', () => {
    activatePlan(card);
  });
});

document.querySelector('.pro-plan').classList.add('active');

/* Charger le footer */
fetch('footer.html')
  .then(response => response.text())
  .then(data => {
    document.getElementById('footer-placeholder').innerHTML = data;
  });


function loadSportStats(sport) {
  document.getElementById('selected-sport').textContent = sport.charAt(0).toUpperCase() + sport.slice(1);

  let stats = {
    football: {
      stats: ['Temps total : 10h 20m', 'Meilleur score : 5 buts', 'Records personnels : 12 matchs gagnés'],
      comparison: ['Objectif de matchs gagnés : 20 / 30', 'Objectif de buts marqués : 18 / 25', 'Temps total visé : 15h / 20h'],
      history: ['20 septembre 2024 : Match gagné 3-1', '15 septembre 2024 : Match perdu 1-2', '10 septembre 2024 : Match gagné 2-0']
    },
    natation: {
      stats: ['Temps total : 5h 30m', 'Meilleur score : 500m en 8min', 'Records personnels : 3 compétitions gagnées'],
      comparison: ['Objectif de compétitions gagnées : 2 / 5', 'Temps total visé : 8h / 10h'],
      history: ['18 septembre 2024 : 2ème place au 200m', '10 septembre 2024 : 1ère place au 100m']
    },
    course: {
      stats: ['Temps total : 8h 15m', 'Meilleure distance : 10km en 45min', 'Records personnels : 5 courses terminées'],
      comparison: ['Objectif de courses terminées : 4 / 6', 'Objectif de distance : 40km / 50km'],
      history: ['22 septembre 2024 : Course de 5km en 25min', '15 septembre 2024 : Course de 10km en 45min']
    },
    basket: {
      stats: ['Temps total : 12h 45m', 'Meilleur score : 35 points', 'Records personnels : 15 matchs gagnés'],
      comparison: ['Objectif de matchs gagnés : 12 / 20', 'Points marqués : 100 / 150'],
      history: ['25 septembre 2024 : Match gagné 80-75', '18 septembre 2024 : Match perdu 70-65']
    }
  };

  let selectedStats = stats[sport];
  document.getElementById('sport-stats').innerHTML = selectedStats.stats.map(stat => `<li>${stat}</li>`).join('');
  document.getElementById('performance-comparison').innerHTML = selectedStats.comparison.map(comp => `<li>${comp}</li>`).join('');
  document.getElementById('activity-history').innerHTML = selectedStats.history.map(history => `<li>${history}</li>`).join('');

  // Mise à jour du graphique
  updateChart(sport);
}

// Fonction pour mettre à jour le graphique
function updateChart(sport) {
  const ctx = document.getElementById('progressChart').getContext('2d');
  const chartData = {
    football: [10, 15, 20, 25, 30],
    natation: [5, 6, 7, 8, 9],
    course: [8, 9, 10, 11, 12],
    basket: [12, 14, 16, 18, 20]
  };

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4', 'Semaine 5'],
      datasets: [{
        label: 'Progression',
        data: chartData[sport],
        borderColor: '#C1FF72',
        fill: false
      }]
    }
  });
}

// Charger le graphique par défaut pour le football
window.onload = function() {
  updateChart('football');
};
