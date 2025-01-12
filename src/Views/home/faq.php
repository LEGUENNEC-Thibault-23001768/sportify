<header>
    <img id="header-logo" src="https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png" alt="Logo Sportify" class="logo">
</header>    

<main class="content">
    <section class="cta-section">
        <h1>Foire aux Questions (FAQ)</h1>
        <p>Trouvez ici les réponses aux questions les plus fréquemment posées sur Sportify.</p>
    </section>

    <section class="faq-entries">
        <div class="faq-category" onclick="toggleQuestions('compte', event)">
            <h3>Mon Compte</h3>
            <span>⌄</span>
        </div>
        <div class="faq-questions" id="compte">
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment puis-je créer un compte sur Sportify ?</h4>
                <span>⌄</span>
                <p class="answer">Cliquez sur "Commencer" en haut à droite de la page d'accueil et remplissez les informations.</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment modifier mes informations personnelles ?</h4>
                <span>⌄</span>
                <p class="answer">Dans la section "Mon profil", modifiez vos informations personnelles à tout moment.</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Que faire si j'ai oublié mon mot de passe ?</h4>
                <span>⌄</span>
                <p class="answer">Cliquez sur "Mot de passe oublié" sur la page de connexion pour réinitialiser votre mot de passe.</p>
            </div>
        </div>
    
        <div class="faq-category" onclick="toggleQuestions('reservation', event)">
            <h3>Réservations</h3>
            <span>⌄</span>
        </div>
        <div class="faq-questions" id="reservation">
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment réserver un terrain ?</h4>
                <span>⌄</span>
                <p class="answer">Allez dans la section "Terrain" et sélectionnez le sport et l'heure qui vous conviennent.</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment annuler une réservation ?</h4>
                <span>⌄</span>
                <p class="answer">Dans la section "Mes réservations", sélectionnez la réservation et cliquez sur "Annuler".</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment réserver une séance avec un entraîneur ?</h4>
                <span>⌄</span>
                <p class="answer">Dans la section "Entraîneur", choisissez un coach et réservez un créneau.</p>
            </div>
        </div>
    
        <div class="faq-category" onclick="toggleQuestions('performances', event)">
            <h3>Performances</h3>
            <span>⌄</span>
        </div>
        <div class="faq-questions" id="performances">
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment suivre mes performances ?</h4>
                <span>⌄</span>
                <p class="answer">Dans la section "Suivi", vous trouverez des détails sur vos séance.</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment consulter mes historiques de séances ?</h4>
                <span>⌄</span>
                <p class="answer">Dans la section "Mes performances", consultez la liste de vos activités passées.</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Puis-je partager mes performances sur les réseaux sociaux ?</h4>
                <span>⌄</span>
                <p class="answer">Oui, partagez vos résultats directement depuis "Mes performances".</p>
            </div>
        </div>
    
        <div class="faq-category" onclick="toggleQuestions('abonnement', event)">
            <h3>Abonnements</h3>
            <span>⌄</span>
        </div>
        <div class="faq-questions" id="abonnement">
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Comment modifier mon abonnement ?</h4>
                <span>⌄</span>
                <p class="answer">Dans la section "Mon abonnement", vous pouvez gérer et modifier votre plan à tout moment.</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Est-ce que Sportify propose des offres spéciales ?</h4>
                <span>⌄</span>
                <p class="answer">Oui, consultez les offres actuelles dans la section "Offres spéciales".</p>
            </div>
            <div class="entry" onclick="toggleAnswer(this)">
                <h4>Est-ce que Sportify offre un programme de fidélité ?</h4>
                <span>⌄</span>
                <p class="answer">Oui, notre programme de fidélité récompense vos séances et réservations.</p>
            </div>
        </div>
    </section>
</main>

<script>
    function toggleQuestions(categoryId, event) {
        if (event) event.preventDefault();

        const category = document.getElementById(categoryId);
        const categoryHeader = category.previousElementSibling;

        const isExpanded = category.style.maxHeight && category.style.maxHeight !== '0px';

        if (isExpanded) {
            category.style.maxHeight = '0';
            categoryHeader.classList.remove('active');
            return;
        }

        document.querySelectorAll('.faq-questions').forEach(q => {
            q.style.maxHeight = '0';
            q.previousElementSibling.classList.remove('active');
        });

        category.style.maxHeight = category.scrollHeight + 'px';
        categoryHeader.classList.add('active');
    }

    function toggleAnswer(entry) {
        const answer = entry.querySelector('.answer');
        const isExpanded = answer.style.maxHeight && answer.style.maxHeight !== '0px';

        entry.parentElement.querySelectorAll('.answer').forEach(a => {
            a.style.maxHeight = '0';
            a.classList.remove('active');
        });

        if (!isExpanded) {
            answer.style.maxHeight = answer.scrollHeight + 'px';
            answer.classList.add('active');
        }
    }
</script>