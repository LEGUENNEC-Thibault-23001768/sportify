<?php
if (!isset($_SESSION['user_id']) || $membre['status'] !== 'admin') {
    //echo $_SESSION['user_id'];
    header('Location: /dashboard');
    exit;
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$users = isset($users) ? $users : [];

?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success_message'] ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error_message'] ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>


<div class="container">
    <h1 class="mt-5">Gestion des Utilisateurs</h1>

    <form method="GET" action="">
        <div class="form-group">
            <label for="search">Rechercher par nom, prénom ou email :</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Rechercher un utilisateur" value="<?= htmlspecialchars($searchTerm) ?>">
        </div>
        <button type="submit" class="btn btn-primary mt-2">Rechercher</button>
    </form>

    <?php if (!empty($searchTerm)): ?>
        <h3 class="mt-4">Résultats de la recherche pour : "<?= htmlspecialchars($searchTerm) ?>"</h3>
    <?php endif; ?>

    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['first_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['status']) ?></td>
                        <td>
                            <a href="/dashboard/admin/users/edit?id=<?= $user['member_id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <a href="/dashboard/admin/users/delete?id=<?= $user['member_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Aucun utilisateur trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

