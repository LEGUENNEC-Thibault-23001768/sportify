<?php
    $user_id = $_SESSION['user_id'] ?? null;
?>

<?php
use Core\Database;
$pdo = Database::getConnection();
$sql = "SELECT * FROM COACH";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);


$image_urls = [
    'Boxe' => "https://i.postimg.cc/CKDrZpCs/5.png",
    'Tennis' => "https://i.postimg.cc/G2xMQP5B/6.png",
    'RPM' => "https://i.postimg.cc/RhPgrS94/7.png",
    'Basketball' => "https://i.postimg.cc/rygnRJ43/8.png",
    'Musculation' => "https://i.postimg.cc/9FnLJQJB/10.png",
    'Football' => "https://i.postimg.cc/Yq9X6Wf9/9.png"
];
?>

<div data-view="trainers">
    <h1>Réservation d'entraîneur</h1>
    <div class="container">
        <h2 class="disponibility-label">Nos entraîneurs</h2>
        <div class="trainers-list">
            <?php foreach ($trainers as $trainer): ?>
                <div class="trainer-card" data-id="<?= $trainer['coach_id']; ?>">
                    <img 
                        src="<?= $image_urls[$trainer['specialty']] ?? 'https://via.placeholder.com/150'; ?>" 
                        alt="Image du coach" 
                        class="trainer-image"
                    >
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    window.currentUserId = <?php echo isset($user['member_id']) ? $user['member_id'] : 'null'; ?>;
</script>