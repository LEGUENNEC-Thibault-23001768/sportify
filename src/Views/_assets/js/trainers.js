function initialize() {
    $('.trainers-list').on('click', '.trainer-card', function() {
        const trainerId = $(this).find('.trainer-image').data('id');

        console.log(trainersData);
        const trainer = trainersData.find(t => t.id === trainerId);

        if (trainer) {
            showTrainerDetails(trainer);
        } else {
            console.error('Trainer not found:', trainerId);
        }
    });
    
    function showTrainerDetails(trainer) {
        const container = $('.container'); // Assuming you want to replace the content within the container
        container.html(`
            <div class="cv-container">
                <div class="cv-header">
                    <h2>${trainer.name}</h2>
                    <p><strong>Spécialité:</strong> ${trainer.speciality}</p>
                    <p><strong>Années d'expérience 🕒:</strong> ${trainer.experience}</p>
                    <img src="${trainer.image}" alt="${trainer.name}" class="trainer-image-cv">
                </div>
                <div class="cv-body">
                    <div class="cv-details">
                        <p><strong>Description 📝:</strong> ${trainer.description}</p>
                        <p><strong>Certifications 🎓:</strong> ${trainer.certifications.join(', ')}</p>
                        <p><strong>Réalisations 🏆:</strong> ${trainer.achievements.join(', ')}</p>
                        <p><strong>Qualités ⭐:</strong> ${trainer.qualities.join(', ')}</p>
                    </div>
                </div>
                <div class="cv-footer">
                    <div class="cv-footer-left">
                        <div class="cv-footer-item">
                            <img src="https://gl-sport.com/wp-content/uploads/2021/09/glsport_prise-de-masse.png" alt="Icône halt" class="halt-icon">
                            <p class="prise-de-masse-label">Prise de masse</p>
                        </div>
                        <div class="cv-footer-item">
                            <img src="https://gl-sport.com/wp-content/uploads/2021/09/glsport_remise-en-forme.png" alt="Icône balance" class="balance-icon">
                            <p class="balance-label">Remise en forme</p>
                        </div>
                        <div class="cv-footer-item">
                            <img src="https://gl-sport.com/wp-content/uploads/2021/09/glsport_remise-en-forme.png" alt="Icône remise en forme" class="remise-icon">
                            <p class="remise-en-forme-label">Perte de poids</p>
                        </div>
                    </div>
                    <div class="cv-footer-buttons">
                        <button class="cv-back-btn">Retour</button>
                        <button class="cv-reserve-btn" id="reserve-btn-${trainer.id}">Réserver</button>
                    </div>
                </div>
            </div>
        `);

        // Handle "Back" button click
        $('.cv-back-btn').on('click', function() {
            location.reload(); // Reloads the trainers list
        });

        // Handle "Reserve" button click
        $(`#reserve-btn-${trainer.id}`).on('click', function() {
            // Hide the CV
            $('.cv-container').hide();
            // Implement your reservation logic here
            console.log("Reservation for:", trainer.name);
        });
    }
}