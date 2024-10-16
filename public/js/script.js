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
