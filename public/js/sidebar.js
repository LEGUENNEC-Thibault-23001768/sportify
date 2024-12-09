document.addEventListener('DOMContentLoaded', function() {
    // Charger dynamiquement le contenu de la sidebar
    fetch('sidebar.html')
        .then(response => response.text())
        .then(html => {
            document.getElementById('sidebar').innerHTML = html;

            // Code pour gérer les liens actifs une fois que la sidebar est chargée
            var links = document.querySelectorAll('.sidebar ul li a');
            
            // Vérifier si un lien actif est enregistré dans le localStorage
            var activeLink = localStorage.getItem('activeLink');
            if (activeLink) {
                links.forEach(function(link) {
                    if (link.getAttribute('href') === activeLink) {
                        link.classList.add('active');
                    }
                });
            }

            links.forEach(function(link) {
                link.addEventListener('click', function() {
                    // Supprimer la classe 'active' des autres liens
                    links.forEach(function(link) {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Enregistrer le lien actif dans le localStorage
                    localStorage.setItem('activeLink', this.getAttribute('href'));
                });
            });
        })
        .catch(error => {
            console.error('Erreur lors du chargement de la sidebar:', error);
        });
});
