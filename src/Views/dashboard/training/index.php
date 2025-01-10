<div data-view="training">
        <div class="container">
            <div id="trainingContent">
                <!-- Content will be loaded here based on the user's plan status -->
            </div>
            <div class="overlay" id="popupOverlay" style="display:none;"></div>
             <div class="modal" id="createPlanPopup" style="display:none;">
                 <div class="modal-content">
                     <div class="modal-header">
                         <h5 class="modal-title">Create Training Plan</h5>
                         <button type="button" class="modal-close" onclick="closeCreatePlanPopup()" aria-label="Close">
                             <span aria-hidden="true">×</span>
                         </button>
                     </div>
                     <div class="modal-body">
                        <form id="trainingForm" action="" method="post">
                           <input type="hidden" name="step" id="step" value="1">

                           <div class="form-group" id="question1">
                               <label for="gender">What's your gender?</label>
                               <select name="gender" id="gender" class="form-control">
                                   <option value="man">Man</option>
                                   <option value="woman">Woman</option>
                                   <option value="other">Other</option>
                               </select>
                           </div>

                           <div class="form-group" id="question2" style="display:none;">
                               <label for="level">What's your level of physical activity?</label>
                               <select name="level" id="level" class="form-control">
                                   <option value="beginner">Beginner</option>
                                   <option value="intermediate">Intermediate</option>
                                   <option value="advanced">Advanced</option>
                               </select>
                           </div>

                           <div class="form-group" id="question3" style="display:none;">
                               <label for="goals">What are your training goals?</label>
                               <select name="goals" id="goals" class="form-control">
                                   <option value="lose_weight">Lose Weight</option>
                                   <option value="build_muscle">Build Muscle</option>
                                   <option value="improve_fitness">Improve Fitness</option>
                                   <option value="marathon">Run a Marathon</option>
                               </select>
                           </div>

                           <div class="form-group" id="question4" style="display:none;">
                               <label for="weight">What's your weight? (in kg)</label>
                               <input type="number" name="weight" id="weight" class="form-control" min="30" max="200">
                           </div>

                           <div class="form-group" id="question5" style="display:none;">
                               <label for="height">What's your height? (in cm)</label>
                               <input type="number" name="height" id="height" class="form-control" min="140" max="210">
                           </div>

                           <div class="form-group" id="question6" style="display:none;">
                               <label for="constraints">Do you have any medical or physical constraints?</label>
                               <textarea name="constraints" id="constraints" class="form-control"></textarea>
                           </div>

                           <div class="form-group" id="question7" style="display:none;">
                               <label for="preferences">What are your training preferences?</label>
                               <select name="preferences" id="preferences" class="form-control">
                                   <option value="no_preference">No Preference</option>
                                   <option value="home">Home</option>
                                   <option value="gym">Gym</option>
                                   <option value="outdoor">Outdoor</option>
                               </select>
                           </div>

                           <div class="form-group" id="question8" style="display:none;">
                               <label for="equipment">What equipment do you have access to?</label>
                               <select name="equipment" id="equipment" class="form-control">
                                   <option value="none">None</option>
                                   <option value="dumbbells">Dumbbells</option>
                                   <option value="treadmill">Treadmill</option>
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
                                <option value="Lose Weight">Perdre du poids</option>
                                <option value="Build Muscle">Construire du muscle</option>
                                <option value="Improve Fitness">Améliorer la condition physique</option>
                                <option value="Run a Marathon">Courir un marathon</option>
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
                                <option value="dumbbells">Haltères</option>
                                <option value="treadmill">Tapis de course</option>
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