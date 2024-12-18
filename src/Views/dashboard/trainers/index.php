<?php
$trainers = [
    [
        'id' => 1,
        'name' => 'Mk Prime',
        'speciality' => 'Boxe',
        'image' => 'https://i.postimg.cc/CKDrZpCs/5.png',
        'description' => 'Expert en boxe anglaise, Mk Prime vous apprendra à maîtriser la technique, la puissance et l’endurance. Ancien champion régional, il a un style pédagogique qui inspire confiance et discipline.',
        'experience' => 'Plus de 7 ans d’expérience en boxe anglaise.',
        'certifications' => ['Certificat d\'entraîneur professionnel', 'Certification en préparation physique'],
        'achievements' => ['Champion régional 2018', 'Médaille d\'argent aux championnats nationaux'],
        'qualities' => ['Cardio', 'Motivation','Discipline', 'Stratégie', 'Endurance', 'Sens de l\'humour']
    ],
    [
        'id' => 2,
        'name' => 'Axelle Peak',
        'speciality' => 'Tennis',
        'image' => 'https://i.postimg.cc/G2xMQP5B/6.png',
        'description' => 'Axelle est une joueuse passionnée avec plus de 6 ans d’expérience en compétition. Elle se spécialise dans le perfectionnement des services et des coups droits. Son énergie est contagieuse !',
        'experience' => 'Plus de 6 ans d’expérience en compétition.',
        'certifications' => ['Certificat d\'entraîneur de tennis', 'Formation avancée en technique de service'],
        'achievements' => ['Vainqueur du tournoi national junior 2015', 'Finaliste du tournoi régional 2017'],
        'qualities' => ['Patience', 'Rythme', 'Pédagogie', 'Energie', 'Concentration']
    ],
    [
        'id' => 3,
        'name' => 'Sabrina Ocho',
        'speciality' => 'RPM',
        'image' => 'https://i.postimg.cc/RhPgrS94/7.png',
        'description' => 'Reine du cardio et des sessions RPM, Sabrina transforme chaque entraînement en une véritable aventure énergétique. Son coaching est idéal pour ceux qui veulent se dépasser tout en s’amusant.',
        'experience' => 'Plus de 4 ans d’expérience dans l’enseignement du RPM.',
        'certifications' => ['Certificat d\'entraîneur RPM', 'Formation avancée en coaching de groupe'],
        'achievements' => ['Championne nationale de RPM 2020', 'Formateur certifié RPM'],
        'qualities' => ['Créativité', 'Leadership', 'Energie', 'Positivité']
    ],
    [
        'id' => 4,
        'name' => 'Kai Forge',
        'speciality' => 'Basketball',
        'image' => 'https://i.postimg.cc/rygnRJ43/8.png',
        'description' => 'Avec une carrière impressionnante dans les ligues semi-professionnelles, Kai Forge est votre coach idéal pour améliorer vos tirs, vos dribbles et votre jeu collectif.',
        'experience' => 'Carrière dans les ligues semi-professionnelles pendant 5 ans.',
        'certifications' => ['Certificat d\'entraîneur de basketball', 'Formation en développement des jeunes talents'],
        'achievements' => ['MVP du championnat régional 2017', 'Vainqueur de la coupe nationale 2018'],
        'qualities' => ['Compétitif', 'Pédagogie', 'Leadership', 'Tactique', 'Esprit d\'équipe']
    ],
    [
        'id' => 5,
        'name' => 'Max Deter',
        'speciality' => 'Musculation',
        'image' => 'https://i.postimg.cc/9FnLJQJB/10.png',
        'description' => 'Max est un passionné de musculation avec une approche centrée sur la technique et la sécurité. Il crée des programmes personnalisés pour tous les niveaux et vous aidera à atteindre vos objectifs.',
        'experience' => 'Plus de 2 ans d’expérience en musculation et coaching physique.',
        'certifications' => ['Certificat d\'entraîneur de musculation', 'Diplôme en sciences du sport'],
        'achievements' => ['Champion de musculation 2019', 'Coach de l\'année 2021'],
        'qualities' => ['Mentalité', 'Rigoureux', 'Patient', 'Motivation']
    ],
    [
        'id' => 6,
        'name' => 'Inox Lafitte',
        'speciality' => 'Football',
        'image' => 'https://i.postimg.cc/Yq9X6Wf9/9.png',
        'description' => 'Inox est un ancien joueur professionnel qui combine stratégie et technique pour vous faire progresser. Sa passion pour le football est un véritable moteur pour ses élèves.',
        'experience' => 'Ancien joueur professionnel avec 3 ans de carrière.',
        'certifications' => ['Certificat d\'entraîneur de football', 'Formation en stratégie et tactique de jeu'],
        'achievements' => ['Champion national en 2015', 'Membre de l\'équipe nationale 2012-2014'],
        'qualities' => ['Endurance', 'Stratégie', 'Passion', 'Discipline', 'Leadership']
    ],
];
?>

<div data-view="trainers">
    <script>
    var trainersData = <?= json_encode($trainers) ?>;
    </script>
    <h1>Réservation d'entraîneur</h1>
    <div class="container">
        <h2 class="disponibility-label">Nos entraîneurs</h2>
        <div class="trainers-list">
            <?php foreach ($trainers as $trainer): ?>
                <div class="trainer-card" id="trainer-<?= $trainer['id']; ?>">
                    <img 
                        src="<?= $trainer['image']; ?>" 
                        alt="Image de <?= htmlspecialchars($trainer['name']); ?>" 
                        class="trainer-image" 
                        data-id="<?= $trainer['id']; ?>"
                    >
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
