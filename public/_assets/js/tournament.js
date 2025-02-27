function initialize() {

    const createTournamentBtn = document.getElementById('create-tournament-btn');
    const popupTournament = document.getElementById('popup-tournament');
    const closePopupBtn = document.querySelector('.close-popup'); 

    createTournamentBtn.addEventListener('click', () => {
        showPopup('popup-tournament');
    });

    closePopupBtn.addEventListener('click', () => { 
        hidePopup('popup-tournament');
    });

    function showPopup(popupId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            popup.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }
    }

    function hidePopup(popupId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            popup.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }
    }

    document.getElementById('create-tournament-btn').addEventListener('click', () => {
        showPopup('popup-tournament');
    });
    
    // Load tournaments
    async function loadTournaments() {
        try {
            const response = await fetch('/api/tournaments');
            const data = await response.json();
            console.log(data);
            renderTournaments(data.tournaments);
        } catch (error) {
            console.log(error);
            showToast('Erreur de chargement des tournois', 'error');
        }
    }

    function showToast(message, type = 'info') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
            toastContainer = container;
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('hide'); 
            setTimeout(() => {
                toast.remove();
            }, 500); 
        }, 3000); 
    }

    function formatTournamentFormat(format) {
        switch (format) {
            case 'knockout':
                return 'Élimination directe';
            case 'roundrobin':
                return 'Poules + élimination';
            default:
                return format; 
        }
    }
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR'); // Example: French date format
    }

    
    function renderTournaments(tournaments) {
        const container = document.getElementById('tournaments-container');
        container.innerHTML = '';

        if (!tournaments || tournaments.length === 0) {
            container.innerHTML = '<div class="tournament-card empty">Aucun tournoi trouvé.</div>';
            return;
        }
        
        tournaments.forEach(tournament => {
            const card = document.createElement('div');
            console.log(card);
            card.className = 'tournament-card';
            card.innerHTML = `
                <h3>${tournament.tournament_name}</h3>
                <div class="tournament-meta">
                    <span class="sport ${tournament.sport_type}">${tournament.sport_type}</span>
                    <span class="format">${formatTournamentFormat(tournament.tournament_format)}</span> <!- Format -->
                    <span class="dates">${formatDate(tournament.start_date)} - ${formatDate(tournament.end_date)}</span>
                </div>
                <div class="tournament-actions"> <!- Container for actions -->
                    <button class="view-bracket view-bracket-btn" data-id="${tournament.tournament_id}">Voir le bracket</button>
                </div>
            `;
            container.appendChild(card);
        });
    }
    
    // Handle bracket viewing
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('view-bracket')) {
            const tournamentId = e.target.dataset.id;
            
            try {
                const response = await fetch(`/api/tournaments/${tournamentId}/bracket`);
                const data = await response.json();
                console.log(data);
                
                window.bracketsViewer.render({
                    stages: data.stage,
                    matches: data.match,
                    matchGames: data.match_game,
                    participants: data.participant
                }, {
                    selector: '#bracket-container',
                });
            } catch (error) {
                console.log(error)
                showToast('Erreur de chargement du bracket', 'error');
            }
        }
    });
    
    // Create tournament
    document.getElementById('tournament-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const tournamentData = {
            name: document.getElementById('tournament-name').value,
            sportType: document.getElementById('tournament-sport').value,
            startDate: document.getElementById('tournament-start').value,
            endDate: document.getElementById('tournament-end').value,
            format: document.getElementById('tournament-format').value,
            location: document.getElementById('tournament-sport').value
        };
    
        try {
            const response = await fetch('/api/tournaments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(tournamentData)
            });
            
            if (response.ok) {
                loadTournaments();
                hidePopup('popup-tournament');
                showToast('Tournoi créé avec succès!', 'success');
            }
        } catch (error) {
            showToast('Erreur de création du tournoi', 'error');
        }
    });
    loadTournaments();
}