<div data-view="tournament">
    <div class="container">
        <div class="top-row">
            <!-- Add Tournament Section -->
            <div class="tournaments-wrapper">
                <div class="section-header">
                    <h2>Tournois</h2>
                    <button id="create-tournament-btn" class="add-stats-btn">Créer un tournoi</button>
                </div>

                <div class="tournaments-list" id="tournaments-container">
                    <!-- Tournament cards will be loaded here -->
                    <div class="tournament-card loading">
                        Chargement des tournois...
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-row">
            <div class="chart-container">
                <div class="chart-title">Arbre du tournoi</div>
                <div id="bracket-container" class="brackets-viewer" data-tournament-id=""></div>
            </div>
        </div>
    </div>


    <div id="input-mask">
    <div>
        <span class="close-popup">×</span>
        <h3></h3>
        <label id="opponent1-label" for="team1-score"></label><input type="number" id="team1-score"><br>
        <label id="opponent2-label" for="team2-score"></label><input type="number" id="team2-score"><br>
        <button id="input-submit">Enregistrer</button>
    </div>
    </div>
    <!-- Add Tournament Creation Popup -->
    <div id="popup-tournament" class="popup hidden">
    <div class="popup-content">
        <span class="close-popup">×</span>
        <h2>Créer un nouveau tournoi</h2>
        <form id="tournament-form">
            <div class="form-group">
                <label>Nom du tournoi:</label>
                <input type="text" id="tournament-name" required>
            </div>
            <div class="form-group">
                <label>Sport:</label>
                <select id="tournament-sport" required>
                    <option value="tennis">Tennis</option>
                    <option value="football">Football</option>
                    <option value="basketball">Basketball</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date de début:</label>
                <input type="date" id="tournament-start" required>
            </div>
            <div class="form-group">
                <label>Date de fin:</label>
                <input type="date" id="tournament-end" required>
            </div>
            <div class="form-group">
                <label>Format:</label>
                <select id="tournament-format">
                    <option value="knockout">Élimination directe</option>
                    <option value="roundrobin">Poules + élimination</option>
                </select>
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" id="tournament-location">
            </div>
            <div class="form-group">
                <label>Nombre maximal d'équipes:</label>
                <input type="number" id="tournament-teams" required>
            </div>
            <div class="form-group">
                <label>Équipes initiales (Nom: Membre1, Membre2):</label>
                <textarea id="tournament-teams-list" rows="5"></textarea>
            </div>
            <?php if (isset($user) && ($user["status"] === "admin")): ?>
                <div class="buttons-container">
                    <button type="submit">Créer le tournoi et générer le bracket</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
</div>