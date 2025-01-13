<div data-view="training">
        <div class="container">
            <div id="trainingContent">
                <!-- Content will be loaded here based on the user's plan status -->
            </div>
            <div class="overlay" id="popupOverlay" style="display:none;"></div>
             <div class="modal" id="createPlanPopup" style="display:none;">
                 <div class="modal-content">
                     <div class="modal-header">
                         <h5 class="modal-title">Créer votre entraînement</h5>
                         <button type="button" class="modal-close" onclick="closeCreatePlanPopup()" aria-label="Close">
                             <span aria-hidden="true">×</span>
                         </button>
                     </div>
                     <div class="modal-body">
                        <form id="trainingForm" action="" method="post">
                           <input type="hidden" name="step" id="step" value="1">

                           <div class="form-group" id="question1">
                               <label for="gender">Quel est votre sexe?</label>
                               <select name="gender" id="gender" class="form-control">
                                   <option value="Homme">Homme</option>
                                   <option value="Femme">Femme</option>
                                   <option value="Pas spécifié">Autre</option>
                               </select>
                           </div>

                           <div class="form-group" id="question2" style="display:none;">
                               <label for="level">Quel est votre niveau d'activité physique?</label>
                               <select name="level" id="level" class="form-control">
                                   <option value="Debutant">Débutant</option>
                                   <option value="intermediaire">Intermediaire</option>
                                   <option value="avancee">Avancée</option>
                               </select>
                           </div>

                           <div class="form-group" id="question3" style="display:none;">
                               <label for="goals">Quel est votre objectif d'entraînement?</label>
                               <select name="goals" id="goals" class="form-control">
                                   <option value="lose_weight">Perdre du poids</option>
                                   <option value="build_muscle">Prendre du muscle</option>
                                   <option value="improve_fitness">Corps entier</option>
                                   <option value="marathon">Courir un marathon</option>
                               </select>
                           </div>

                           <div class="form-group" id="question4" style="display:none;">
                               <label for="weight">Votre poids (en kg)</label>
                               <input type="number" name="weight" id="weight" class="form-control" min="30" max="200">
                           </div>

                           <div class="form-group" id="question5" style="display:none;">
                               <label for="height">Votre taille (en cm)</label>
                               <input type="number" name="height" id="height" class="form-control" min="140" max="210">
                           </div>

                           <div class="form-group" id="question6" style="display:none;">
                               <label for="constraints">Avez-vous des contraintes médicaux ?</label>
                               <textarea name="constraints" id="constraints" class="form-control"></textarea>
                           </div>

                           <div class="form-group" id="question7" style="display:none;">
                               <label for="preferences">Quels sont vos préferences d'entraînement?</label>
                               <select name="preferences" id="preferences" class="form-control">
                                   <option value="no_preference">Pas de préference</option>
                                   <option value="domicile">À domicile</option>
                                   <option value="Salle de sport">Salle de sport</option>
                                   <option value="Exterieur">Extérieur</option>
                               </select>
                           </div>

                           <div class="form-group" id="question8" style="display:none;">
                               <label for="equipment">Quels équipement pouvez-vous utiliser ?</label>
                               <select name="equipment" id="equipment" class="form-control">
                                   <option value="aucun">Aucun</option>
                                   <option value="halteres">Haltères</option>
                                   <option value="tapis_course">Tapis de course</option>
                                   <option value="resistance_bands">Resistance Bands</option>
                               </select>
                           </div>
                       </form>
                         <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" onclick="closeCreatePlanPopup()">Cancel</button>
                             <button type="button" id="nextButton" class="btn btn-primary">Next</button>
                       </div>
                     </div>
                 </div>
             </div>
              <!-- Edit Training Plan Modal -->
            <div class="modal" id="editPlanPopup" style="display:none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier votre Plan d'Entraînement</h5>
                        <button type="button" class="modal-close" onclick="closeEditPlanPopup()" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="/api/training/update" method="POST" id="editTrainingForm">
                            <label for="gender">Sexe :</label>
                            <select name="gender" id="gender">
                                <option value="">(Non modifié)</option>
                                <option value="Homme">Homme</option>
                                <option value="Femme">Femme</option>
                            </select>

                            <label for="level">Niveau :</label>
                            <select name="level" id="level">
                                <option value="">(Non modifié)</option>
                                <option value="Débutant">Débutant</option>
                                <option value="Intermédiaire">Intermédiaire</option>
                                <option value="Avancé">Avancé</option>
                            </select>

                            <label for="goals">Objectifs :</label>
                            <select name="goals" id="goals">
                                <option value="">(Non modifié)</option>
                                <option value="Perdre du poids">Perdre du poids</option>
                                <option value="Prendre du muscle">Construire du muscle</option>
                                <option value="Amélioration physique">Améliorer la condition physique</option>
                                <option value="Courrir un marathon">Courir un marathon</option>
                            </select>

                            <label for="weight">Poids :</label>
                            <input type="number" name="weight" id="weight" placeholder="Non défini">

                            <label for="height">Taille :</label>
                            <input type="number" name="height" id="height" placeholder="Non défini">

                            <label for="constraints">Contraintes :</label>
                            <input type="text" name="constraints" id="constraints" placeholder="Aucune">

                            <label for="preferences">Préférences :</label>
                            <select name="preferences" id="preferences">
                                <option value="">(Non modifié)</option>
                                <option value="Domicile">À domicile</option>
                                <option value="Salle de sport">En salle de sport</option>
                                <option value="Extérieur">En extérieur</option>
                            </select>

                            <label for="equipment">Équipement :</label>
                            <select name="equipment" id="equipment">
                                <option value="">(Non modifié)</option>
                                <option value="none">Aucun</option>
                                <option value="halteres">Haltères</option>
                                <option value="tapis_course">Tapis de course</option>
                                <option value="resistance_bands">Bandes de résistance</option>
                            </select>

                             <div class="modal-footer">
                                 <button type="button" class="btn btn-secondary" onclick="closeEditPlanPopup()">Cancel</button>
                                 <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="toast-container"></div>
        </div>
    </div>