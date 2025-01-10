<div data-view="stats">
    <div class="container">
        <div class="top-row">
            <div class="stats-wrapper">
                <div class="card-grid">
                    <div class="report-card performance-card">
                        <select id="sport-select" onchange="updateSportDisplay()">
                            <option value="football" selected>Football</option>
                            <option value="musculation">Musculation</option>
                            <option value="rpm">RPM</option>
                            <option value="boxe">Boxe</option>
                            <option value="tennis">Tennis</option>
                            <option value="basketball">Basketball</option>
                        </select>
                    </div>
                    <div class="report-card game-time-card" id="stat-1">
                        <div class="report-title">Temps total joué</div>
                        <div class="report-value">120 min</div>
                    </div>
                    <div class="report-card calories-card" id="stat-2">
                        <div class="report-title">Buts marqués</div>
                        <div class="report-value">5</div>
                    </div>
                    <div class="report-card game-time-card" id="stat-3">
                        <div class="report-title">Passes réussies</div>
                        <div class="report-value">25</div>
                    </div>
                </div>

                <div class="task-completion-wrapper">
                    <div class="task-completion-card">
                        <div class="progression-title">Progression</div>
                        <div class="circle-container">
                            <canvas id="taskCompletionChart"></canvas>
                           <div id="chart-center-text">71%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-row">
            <div class="chart-container">
                <div class="chart-title">Temps passé par jour</div>
                <canvas id="barChart"></canvas>
            </div>
             <button id="add-stats-btn" class="add-stats-btn">Ajoutez vos statistiques</button>
        </div>

        <div id="popup-container" class="popup hidden"></div>

    
        <div id="popup-sports" class="popup hidden">
            <div class="popup-content">
                <span class="close-popup">×</span>
                <h2>Choisissez un Sport</h2>
                <p>Sélectionnez un sport pour commencer à enregistrer vos statistiques.</p>
                <div class="categories-container">
                    <!-- Tennis -->
                   <div class="category-card tennis" data-sport="tennis">
                        <div class="emoji-wrapper tennis">
                           <img src="https://emojigraph.org/media/apple/tennis_1f3be.png" alt="Tennis" class="emoji-svg">
                       </div>
                        <h3>Tennis</h3>
                    </div>

                   <!-- Football -->
                    <div class="category-card football" data-sport="football">
                        <div class="emoji-wrapper football">
                           <img src="https://emojigraph.org/media/apple/soccer-ball_26bd.png" alt="Football" class="emoji-svg">
                        </div>
                        <h3>Football</h3>
                    </div>

                    <!-- Basketball -->
                   <div class="category-card basketball" data-sport="basketball">
                        <div class="emoji-wrapper basketball">
                            <img src="https://emojigraph.org/media/apple/basketball_1f3c0.png" alt="Basketball" class="emoji-svg">
                        </div>
                        <h3>Basketball</h3>
                    </div>

                    <!-- RPM -->
                   <div class="category-card rpm" data-sport="rpm">
                        <div class="emoji-wrapper rpm">
                            <img src="https://emojigraph.org/media/apple/person-biking_1f6b4.png" alt="RPM" class="emoji-svg">
                       </div>
                        <h3>RPM</h3>
                   </div>

                   <!-- Musculation -->
                   <div class="category-card musc" data-sport="musculation">
                        <div class="emoji-wrapper musc">
                           <img src="https://emojigraph.org/media/apple/flexed-biceps_1f4aa.png" alt="Musculation" class="emoji-svg">
                        </div>
                        <h3>Musculation</h3>
                   </div>

                   <!-- Boxe -->
                   <div class="category-card boxing" data-sport="boxe">
                        <div class="emoji-wrapper boxing">
                            <img src="https://em-content.zobj.net/source/apple/118/boxing-glove_1f94a.png" alt="Boxe" class="emoji-svg">
                       </div>
                       <h3>Boxe</h3>
                    </div>
               </div>
           </div>
       </div>

       <!-- Popup Statistiques -->
       <div id="popup-stats" class="popup hidden">
            <div class="popup-content">
               <span class="close-popup">×</span>
               <h2 id="stat-sport-title"></h2>
               <p id="stat-question">Entrez votre première statistique :</p>
                <input type="number" id="stat-input" placeholder="Entrez une valeur" min="0">
                <p id="error-message" class="error-message"></p>
               <div class="buttons-container">
                    <button id="stat-submit-btn">Soumettre</button>
               </div>
           </div>
        </div>
   

        <!-- Popup Félicitations -->
        <div id="popup-congrats" class="popup hidden no-close">
            <div class="popup-content">
                <span class="close-popup">×</span>
               <img src="https://emojigraph.org/media/apple/check-mark-button_2705.png" alt="Succès" class="emoji-success">
               <h2 id="congrats-title">Félicitations !</h2>
                <p class="congrats-message">Vous avez enregistré toutes vos statistiques avec succès.<br> Continuez comme ça !</p>
               <button id="close-congrats-btn">Fermer</button>
           </div>
        </div>

        <div id="popup-confirmation" class="popup hidden no-close">
            <div class="popup-content">
                <span class="close-popup">×</span>
                <h2 id="confirmation-title">Êtes-vous sûr de vouloir quitter ?</h2>
               <p id="confirmation-message">
                    Vos modifications pourraient être perdues.
                </p>
                <div class="buttons-container">
                   <button id="confirm-yes-btn">Oui</button>
                   <button id="confirm-no-btn">Non</button>
               </div>
            </div>
        </div>
    </div>
</div>
<script>
    const currentUserId = <?php echo $user['member_id']; ?>;
   const memberStatus = "<?php echo $user['status']; ?>";
</script>