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
                    <button class="edit-tournament-btn" data-id="${tournament.tournament_id}">Modifier</button>
                    <button class="delete-tournament-btn" data-id="${tournament.tournament_id}">Supprimer</button>
                    <button class="generate-bracket-btn" data-id="${tournament.tournament_id}">Générer le bracket</button>
                </div>
            `;
            container.appendChild(card);
        });
    }

    // Handle bracket viewing
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('view-bracket')) {
            const tournamentId = e.target.dataset.id;
            document.querySelector('#bracket-container').dataset.tournamentId = tournamentId;
            try {
                const response = await fetch(`/api/tournaments/${tournamentId}/bracket`);
                const data = await response.json();
                console.log(data);
                renderBracket(data, tournamentId);
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
            location: document.getElementById('tournament-location').value,
            maxTeams: document.getElementById('tournament-teams').value,
            teams: getTeamsFromForm(),
            //teamList: document.getElementById('tournament-teams-list').value.split('\n').filter(team => team.trim() !== '')
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
            } else {
                const errorData = await response.json();
                showToast(`Erreur: ${errorData.error}`, 'error');
            }
        } catch (error) {
            showToast('Erreur de création du tournoi', 'error');
        }
    });

    // Edit tournament
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('edit-tournament-btn')) {
            const tournamentId = e.target.dataset.id;
            showAddTeamsPopup(tournamentId);
        }
    });

    function getTeamsFromForm() {
        const teamListText = document.getElementById('tournament-teams-list').value;
        const teamLines = teamListText.split('\n').filter(line => line.trim() !== '');
        const teams = [];

        teamLines.forEach(line => {
            const parts = line.split(':');
            const teamName = parts[0].trim();
            const memberNames = parts[1] ? parts[1].split(',').map(name => name.trim()) : []; // Split member names

            teams.push({
                name: teamName,
                memberNames: memberNames
            });
        });

        return teams;
    }

    async function showAddTeamsPopup(tournamentId) {
        // Fetch tournament data
        try {
            const response = await fetch(`/api/tournaments/${tournamentId}/showteams`);
            const data = await response.json();

            // Populate edit form
            const editPopup = document.createElement('div');
            editPopup.id = 'edit-tournament-popup';
            editPopup.className = 'popup';
            editPopup.innerHTML = `
                <div class="popup-content">
                    <span class="close-popup">×</span>
                    <h2>Modifier le tournoi</h2>
                    <form id="edit-tournament-form">
                        <input type="hidden" id="edit-tournament-id" value="${data.tournament.tournament_id}">
                         <div class="form-group">
                            <label>Équipes existantes:</label>
                            <ul id="existing-teams-list">
                                ${data.teams.map(team => `<li>${team.team_name}</li>`).join('')}
                            </ul>
                        </div>
                        <div class="form-group">
                        <label>Équipes à ajouter (Nom: Membre1, Membre2):</label>
                        <textarea id="edit-tournament-teams-list" rows="5"></textarea>
                    </div>
                        <div class="buttons-container">
                            <button type="submit">Modifier le tournoi</button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(editPopup);
            showPopup('edit-tournament-popup');

            // Add event listener for the edit form
            document.getElementById('edit-tournament-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const tournamentId = document.getElementById('edit-tournament-id').value;
                const teamListText = document.getElementById('edit-tournament-teams-list').value;

                // Function to parse the team list
                function parseTeamList(teamListText) {
                    const teams = [];
                    const teamLines = teamListText.split('\n').filter(line => line.trim() !== '');

                    teamLines.forEach(line => {
                        const parts = line.split(':');
                        const teamName = parts[0].trim();
                        const memberNames = parts[1] ? parts[1].split(',').map(name => name.trim()) : [];
                        teams.push({
                            name: teamName,
                            memberNames: memberNames
                        });
                    });
                    return teams;
                }

                const teams = parseTeamList(teamListText);

                try {
                    const response = await fetch(`/api/tournaments/${tournamentId}/addTeams`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({teams})
                    });

                    if (response.ok) {
                        const successData = await response.json();
                        loadTournaments();
                        hidePopup('edit-tournament-popup');
                        editPopup.remove();
                        showToast(successData.message, 'success');
                    } else {
                        const errorData = await response.json();

                        showToast(`Erreur de modification du tournoi: ${errorData.error}`, 'error');
                    }
                } catch (error) {
                    console.log(error);
                    showToast('Erreur de modification du tournoi', 'error');
                }
            });

            // Add event listener to close popup
            editPopup.querySelector('.close-popup').addEventListener('click', () => {
                hidePopup('edit-tournament-popup');
                editPopup.remove();
            });

        } catch (error) {
            showToast('Erreur de chargement des données du tournoi', 'error');
        }
    }

    // Delete tournament
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('delete-tournament-btn')) {
            const tournamentId = e.target.dataset.id;
            if (confirm('Êtes-vous sûr de vouloir supprimer ce tournoi?')) {
                try {
                    const response = await fetch(`/api/tournaments/${tournamentId}`, {
                        method: 'DELETE'
                    });

                    if (response.ok) {
                        loadTournaments();
                        showToast('Tournoi supprimé avec succès!', 'success');
                    } else {
                        const errorData = await response.json();
                        showToast(`Erreur de suppression du tournoi: ${errorData.error}`, 'error');
                    }
                } catch (error) {
                    showToast('Erreur de suppression du tournoi', 'error');
                }
            }
        }
    });

    // Generate bracket
    document.addEventListener('click', async (e) => {
        if (e.target.classList.contains('generate-bracket-btn')) {
            const tournamentId = e.target.dataset.id;
            try {
                const response = await fetch(`/api/tournaments/${tournamentId}/generate-bracket`, {
                    method: 'POST'
                });

                if (response.ok) {
                    loadTournaments();
                    showToast('Bracket généré avec succès!', 'success');
                } else {
                    const errorData = await response.json();
                    showToast(`Erreur de génération du bracket: ${errorData.error}`, 'error');
                }
            } catch (error) {
                showToast('Erreur de génération du bracket', 'error');
            }
        }
    });

    // Input Mask Constants
    const INPUT_MASK = 'input-mask';
    const INPUT_SUBMIT = 'input-submit';
    const TEAM1_SCORE = 'team1-score';
    const TEAM2_SCORE = 'team2-score';
    const WINNER_ID = 'winner-id';
    const CLOSE_POPUP = 'close-popup';

    document.getElementById('bracket-container').addEventListener('click', async (e) => {
        if (e.target.classList.contains('participant')) {
            const matchElement = e.target.closest('.match');
            const matchId = matchElement.dataset.matchId;
            const tournamentId = document.querySelector('#bracket-container').dataset.tournamentId;
            const participantId = e.target.dataset.participantId;

            const userStatus = await getUserStatus();
            if (userStatus == 'admin') {
                showMatchEditPopup(tournamentId, matchId, e.target);
            } else {
                showTeamMembersPopup(tournamentId, participantId); // Participant ID is the team ID
            }
        }
    });

    async function getUserStatus() {
        try {
            const response = await fetch('/api/profile/status');
            const data = await response.json();
            return data.status; 
        } catch (error) {
            console.error('Failed to get user status:', error);
            return 'non-admin'; // Default to non-admin in case of an error
        }
    }

    async function getTeamMembers(tournamentId, teamId) {
        try {
            const response = await fetch(`/api/tournaments/${tournamentId}/showteams`);
            const data = await response.json();
            const team = data.teams.find(team => team.tournament_team_id == teamId);

            if (!team) {
                showToast('Team not found', 'error');
                return [];
            }

            return team.members;
        } catch (error) {
            console.error('Error fetching team members:', error);
            showToast('Error fetching team members', 'error');
            return [];
        }
    }

    async function showTeamMembersPopup(tournamentId, teamId) {
        try {
            const teamMembers = await getTeamMembers(tournamentId, teamId);
    
            const membersPopup = document.createElement('div');
            membersPopup.id = 'team-members-popup';
            membersPopup.className = 'popup';
            membersPopup.innerHTML = `
                <div class="popup-content">
                    <span class="close-popup">×</span>
                    <h2>Membres de l'équipe:</h2>
                    <ul>
                        ${teamMembers.map(member => `<li>${member}</li>`).join('')}
                    </ul>
                </div>
            `;
    
            document.body.appendChild(membersPopup);
            showPopup('team-members-popup');
    
            membersPopup.querySelector('.close-popup').addEventListener('click', () => {
                hidePopup('team-members-popup');
                membersPopup.remove();
            });
    
        } catch (error) {
            console.error('Error fetching team members:', error);
            showToast('Error fetching team members', 'error');
        }
    }


    async function showMatchEditPopup(tournamentId, matchId, clickedElement) {
        const inputMask = document.getElementById(INPUT_MASK);
        inputMask.style.display = 'flex';

        const team1Element = clickedElement.closest('.opponents').querySelector('.participant:nth-child(2) .name');
        const team2Element = clickedElement.closest('.opponents').querySelector('.participant:last-child .name');
        console.log("els:",team1Element, team2Element);

        const team1Name = team1Element ? team1Element.textContent : 'Équipe 1';
        const team2Name = team2Element ? team2Element.textContent : 'Équipe 2';
        const matchTitle = clickedElement.querySelector('.name').textContent;

        inputMask.querySelector('h3').textContent = `Modifier le match pour ${matchTitle}`;

        // Fetch team data
        const team1Id = clickedElement.closest('.match').querySelectorAll('.opponents .participant')[0].dataset.participantId;
        const team2Id = clickedElement.closest('.match').querySelectorAll('.opponents .participant')[1].dataset.participantId;

        const team1Members = await getTeamMembers(tournamentId, team1Id);
        const team2Members = await getTeamMembers(tournamentId, team2Id);

        // Display team members
        document.getElementById('opponent1-label').textContent = `${team1Name} ( ${team1Members.join(', ')})`;
        document.getElementById('opponent2-label').textContent = `${team2Name} ( ${team2Members.join(', ')})`;

        const saveMatchButton = document.getElementById(INPUT_SUBMIT);
        saveMatchButton.onclick = async () => {
            const team1Score = document.getElementById(TEAM1_SCORE).value;
            const team2Score = document.getElementById(TEAM2_SCORE).value;

            try {
                const response = await fetch(`/api/tournaments/${tournamentId}/matches/${matchId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        team1_score: team1Score,
                        team2_score: team2Score
                    })
                });

                if (response.ok) {
                    loadTournaments(); // Reload tournaments to refresh the bracket
                    inputMask.style.display = 'none'; // Hide the popup
                    showToast('Match updated!', 'success');
                } else {
                    showToast('Error updating match', 'error');
                }
            } catch (error) {
                showToast('Error updating match', 'error');
            }
        };

        // Close Button
        console.log(inputMask);
        inputMask.querySelector('.' + CLOSE_POPUP).addEventListener('click', () => {
            console.log("we are trying to close");
            inputMask.style.display = 'none';
        });
    }

    function renderBracket(data, tournamentId) {
        document.getElementById("bracket-container").innerHTML = '';
        window.bracketsViewer.render({
            stages: data.stage,
            matches: data.match,
            matchGames: data.match_game,
            participants: data.participant,
        }, {
            selector: '#bracket-container',
            participantOriginPlacement: 'before',
            separatedChildCountLabel: true,
            showSlotsOrigin: true,
            showLowerBracketSlotsOrigin: true,
            highlightParticipantOnHover: true,
            matchTemplate: function (match) {
                function getParticipantName(participantId) {
                    if (!participantId) return '';
                    const participant = data.participants.find(p => p.id === participantId);
                    return participant ? participant.name : 'TBD'; // TBD = To Be Determined
                }

                const participant1Name = getParticipantName(match.opponent1.id);
                const participant2Name = getParticipantName(match.opponent2.id);

                return `<div class="match" data-match-id="${match["data-match-id"]}" data-match-status="${match.status}">
                            <div class="opponents">
                                ${match.opponent1 ? `<div class="participant" data-participant-id="${match.opponent1.id}" title="${participant1Name}"><div class="name">${participant1Name}</div><div class="result">${match.opponent1.score ? match.opponent1.score : '-'}</div></div>` : `<div class="participant"><div class="name"></div><div class="result">-</div></div>`}
                                ${match.opponent2 ? `<div class="participant" data-participant-id="${match.opponent2.id}" title="${participant2Name}"><div class="name">${participant2Name}</div><div class="result">${match.opponent2.score ? match.opponent2.score : '-'}</div></div>` : `<div class="participant"><div class="name"></div><div class="result">-</div></div>`}
                            </div>
                        </div>`;
            }
        });
        document.querySelector('#bracket-container').dataset.tournamentId = tournamentId;
    }

    loadTournaments();
    
}