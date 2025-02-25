<div data-view="booking">
    <h2>Carte de la Salle de Sport</h2>
    <div class="gym-map">
        <img src="../_assets/img/Map_sportify.png" alt="Carte de la salle de sport" class="gym-image">
        <div class="room tennis" data-room="Tennis" data-court-id="6" data-max-capacity="4" onclick="openReservationForm(this)">Tennis</div>
        <div class="room foot" data-room="Foot" data-court-id="1"  data-max-capacity="22" onclick="openReservationForm(this)">Football</div>
        <div class="room rpm" data-room="RPM" data-court-id="3" data-max-capacity="8" onclick="openReservationForm(this)">RPM</div>
       <div class="room musculation" data-room="Musculation" data-court-id="4" data-max-capacity="12" onclick="openReservationForm(this)">Musculation</div>
        <div class="room basketball" data-room="Basketball" data-court-id="2" data-max-capacity="10" onclick="openReservationForm(this)">Basketball</div>
        <div class="room boxe" data-room="Boxe" data-court-id="5" data-max-capacity="12" onclick="openReservationForm(this)">Boxe</div>
    </div>

     <div id="reservation-container" style="display: none;">
         <h3 id="reservation-title">Réserver une salle</h3>
        <button type="button" class="close-button" onclick="closeReservationForm()">×</button>
         <div class="form-container">
                <button class="tab-button active" data-tab="reservation-form">Formulaire</button>
                <button class="tab-button" data-tab="reservations">Historique</button>
            </div>
        <div class="tab-content active" id="reservation-form">
             <form id="reservation-form">
                 <input type="hidden" id="court_id" name="court_id">
                 <input type="hidden" name="member_id" id="member_id" value="">
                <input type="hidden" id="max_capacity" name="max_capacity">
                
                 <div class="reservation-type">
                    <label><input type="radio" name="reservation_type" value="individual" checked> Individuelle</label>
                    <label><input type="radio" name="reservation_type" value="team"> En équipe</label>
                </div>

                <div id="team-section" style="display: none;">
                    <label for="team_name">Nom de l'équipe:</label>
                    <input type="text" id="team_name" name="team_name">

                    <div class="member-selection">
                        <label>Membres :</label>
                        <div id="selected-member-list">
                        </div>
                        <button type="button" onclick="showMemberSearch()">Ajouter un membre</button>
                    </div>
                </div>

                 <label for="member_name">Votre Nom:</label>
                 <input type="text" id="member_name" name="member_name" value="" readonly>

                  <label for="date">Date:</label>
                 <input type="date" id="date" name="reservation_date" required>

                 <label>Veuillez sélectionner vos horaires :</label> 
                 <div id="available-hours">
                 </div>
                 <input type="hidden" id="selected-time" name="start_time">
                  <input type="hidden" id="duration" name="duration">

                 <button type="button" id="reserve-button">Réserver</button>
                 <button type="button" onclick="closeReservationForm()">Annuler</button>
             </form>
         </div>
       
         <div id="member-search-modal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="closeMemberSearch()">×</span>
                <label for="member-search">Rechercher un membre :</label>
                <form id="member-search-form">
                    <input type="text" id="member-search" placeholder="Nom, prénom, email">
                    <button type="submit">Rechercher</button> 
                </form>
                <div id="search-results"></div>
            </div>
        </div>

        <div class="tab-content" id="reservations" style="margin-top:10px; display:none;">
             <h3>Historique des Réservations</h3>
             <ul id="reservation-list">
            </ul>
        </div>
    </div>

    <div id="edit-reservation-container" style="display: none;">
        <h2>Modifier la Réservation</h2>
        <button type="button" class="close-button" onclick="closeEditReservation()">×</button>
        <form id="edit-reservation-form">
            <input type="hidden" id="edit_reservation_id" name="reservation_id" value="">
            <label for="edit_member_name">Votre Nom:</label>
            <input type="text" id="edit_member_name" name="member_name" value="" readonly>

            <label for="edit_date">Date:</label>
            <input type="date" id="edit_date" name="reservation_date" required>
                <label>Veuillez sélectionner vos horaires :</label> 
                <div id="edit-available-hours">
                </div>
                <input type="hidden" id="edit_start-time" name="start_time">
                <input type="hidden" id="edit_end-time" name="end_time">

            <button type="button" id="update-button">Mettre à jour</button>
            <button type="button" onclick="cancelEditReservation()">Annuler</button>
        </form>
   </div>
  <div id="toast-container"></div>
</div>
 <script>
    window.currentUserId = <?php echo isset($user['member_id']) ? $user['member_id'] : 'null'; ?>;
    window.memberStatus = "<?php echo isset($user['status']) ? $user['status'] : ''; ?>";
    window.userName = "<?php echo isset($user["first_name"]) ? $user['first_name'] : "" ?>";
</script>