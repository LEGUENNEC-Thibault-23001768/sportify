document.addEventListener('DOMContentLoaded', function () {

    const personalInfoForm = document.getElementById('personal-info-form');
    
    personalInfoForm.addEventListener('submit', function (event) {
        event.preventDefault();
        
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        if (username === '' || email === '') {
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        
        if (password.length > 0 && password.length < 6) {
            alert('Le mot de passe doit contenir au moins 6 caractères.');
            return;
        }
        alert('Informations mises à jour avec succès !');
    });
    const changeSubscriptionBtn = document.querySelector('.subscription-info button:first-child');
    const cancelSubscriptionBtn = document.querySelector('.subscription-info button:last-child');

    changeSubscriptionBtn.addEventListener('click', function () {
        const currentSubscription = prompt("Choisissez votre nouvel abonnement (Standard, Premium, VIP) :");
        if (currentSubscription) {
            alert(`Votre abonnement a été changé en : ${currentSubscription}`);
        }
    });

    cancelSubscriptionBtn.addEventListener('click', function () {
        const confirmation = confirm("Êtes-vous sûr de vouloir annuler votre abonnement ?");
        if (confirmation) {
            alert('Votre abonnement a été annulé.');
        }
    });
    const paymentHistory = document.getElementById('payment-history');
    const newPayment = document.createElement('li');
    newPayment.innerHTML = '15 octobre 2024 - <strong>50 €</strong>';
    paymentHistory.appendChild(newPayment);
});
