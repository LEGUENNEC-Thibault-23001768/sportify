<div class="main-content" id="manage-users-content" data-view="manage_users"> <div class="container mt-5">
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

        <h1 class="mt-5">Gestion des Utilisateurs</h1>

        <form method="GET" action="" class="search-form">
            <div class="form-group">
                <label for="search">Rechercher par nom, prénom ou email :</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Rechercher un utilisateur" value="<?= htmlspecialchars($searchTerm) ?>">
            </div>
            <button type="submit" class="btn btn-primary mt-2">Rechercher</button>
        </form>

        <?php if (!empty($searchTerm)): ?>
            <h3 class="mt-4">Résultats de la recherche pour : "<?= htmlspecialchars($searchTerm) ?>"</h3>
        <?php endif; ?>

        <table class="user-table mt-4">
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
                                <button class="btn btn-edit edit-user" data-user-id="<?= $user['member_id'] ?>">Modifier</button>
                                <a href="/dashboard/admin/users/delete?id=<?= $user['member_id'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
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
</div>

<!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Modifier l'utilisateur</h5>
            <div class="modal-tab-buttons">
                <button data-tab="user-tab" class="active">User Details</button>
                <button data-tab="subscription-tab">Subscription</button>
            </div>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="modal-body">
            <div id="user-tab" class="modal-tab-content active">
                <form id="editUserForm" class="modal-form">
                    <input type="hidden" name="user_id" id="userId">
                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="birth_date">Date de naissance</label>
                        <input type="date" id="birth_date" name="birth_date">
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <input type="text" id="address" name="address">
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status">
                            <option value="membre">Membre</option>
                            <option value="coach">Coach</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <!-- Add other fields as necessary -->
                    <div class="error-message" id="user-error"></div>
                </form>
            </div>
            <div id="subscription-tab" class="modal-tab-content">
                <h3>Subscription Details</h3>
                <div class="form-group">
                    <label for="subscription_type">Type</label>
                    <input type="text" id="subscription_type" name="subscription_type">
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="text" id="amount" name="amount">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <input type="text" id="status" name="status">
                </div>
                <button type="button" class="btn btn-primary" id="updateSubscription">Update Subscription</button>
                <button type="button" class="btn btn-danger" id="cancelSubscription">Cancel Subscription</button>
                <button type="button" class="btn btn-success" id="resumeSubscription">Resume Subscription</button>
                <div class="error-message" id="subscription-error"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closeModal">Fermer</button>
            <button type="submit" class="btn btn-primary" id="saveUser">Enregistrer</button>
        </div>
    </div>
</div>