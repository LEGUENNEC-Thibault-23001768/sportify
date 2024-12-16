<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <style>
        /* General Styling */
        body {
            font-family: sans-serif;
            background-color: #181818;
            color: #eee;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #282828;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        h1, h3 {
            color: #eee;
        }

        /* Search Form */
        .search-form {
            margin-bottom: 20px;
        }

        .search-form label {
            display: block;
            margin-bottom: 5px;
        }

        .search-form input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #333;
            color: #eee;
        }

        .search-form button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            background-color: #0077cc;
            color: #fff;
            cursor: pointer;
        }

        /* Table */
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th,
        .user-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #555;
        }

        .user-table th {
            background-color: #333;
        }

        /* Action Buttons */
        .user-table .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-right: 5px;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .user-table .btn-edit {
            background-color: #ffaa00;
            color: #fff;
            border: none;
        }

        .user-table .btn-delete {
            background-color: #cc0000;
            color: #fff;
            border: none;
        }

        /* Alerts */
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #008800;
            color: #fff;
        }

        .alert-danger {
            background-color: #880000;
            color: #fff;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #282828;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }

        .modal-header {
            border-bottom: 1px solid #555;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative; /* Added for absolute positioning of close button */
        }

        .modal-title {
            color: #eee;
            margin: 0;
            text-align: center;
            width: 100%;
            font-size: 24px; /* Increased title size */
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            color: #aaa;
            cursor: pointer;
        }

        .modal-form label {
            display: block;
            margin-bottom: 5px;
        }

        .modal-form input,
        .modal-form select {
            width: 95%;
            padding: 8px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #333;
            color: #eee;
            margin-bottom: 10px;
        }

        .modal-form .form-group {
            margin-bottom: 15px;
        }

        .modal-footer {
            text-align: right;
            margin-top: 20px;
        }

        .modal-footer .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
        }

        .modal-footer .btn-secondary {
            background-color: #555;
            margin-right: 10px;
        }

        .modal-footer .btn-primary {
            background-color: #0077cc;
        }

        /* Modal Form */
        .modal-form label {
            display: block;
            margin-bottom: 5px;
        }

        .modal-form input,
        .modal-form select {
            width: 95%;
            padding: 8px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #333;
            color: #eee;
            margin-bottom: 10px;
        }

        .modal-form .form-group {
            margin-bottom: 15px;
        }

        /* Modal Animation */
        .modal.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Blurred Background */
        body.modal-open {
            overflow: hidden;
        }

        body.modal-open .main-content {
            filter: blur(4px);
            pointer-events: none;
            user-select: none;
        }

        .main-content {
            transition: filter 0.3s ease-out;
        }

        .modal-tab-buttons {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            margin-bottom: 10px; /* Reduced margin */
        }

        .modal-tab-buttons button {
            background: none;
            border: none;
            padding: 0;
            font-size: 16px;
            color: #aaa;
            cursor: pointer;
            margin: 0 10px;
            border-bottom: 2px solid transparent;
            transition: border-bottom-color 0.3s ease, color 0.3s ease;
        }

        .modal-tab-buttons button.active {
            color: #eee;
            border-bottom-color: #0077cc;
        }

        .modal-tab-content {
            display: none;
            opacity: 0; /* Initially hidden for transition */
            transition: opacity 0.3s ease; /* Added transition */
        }

        .modal-tab-content.active {
            display: block;
            opacity: 1; /* Make visible when active */
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
        .modal-form button, .modal-footer button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-form button:hover, .modal-footer button:hover {
            opacity: 0.8;
        }

        .modal-form button.btn-primary, .modal-footer button.btn-primary {
            background-color: #0077cc;
        }

        .modal-form button.btn-secondary, .modal-footer button.btn-secondary {
            background-color: #555;
        }

        .modal-form button.btn-danger, .modal-footer button.btn-danger {
            background-color: #cc0000;
        }

        .modal-form button.btn-success, .modal-footer button.btn-success {
            background-color: #008800;
        }

        .modal-form input, .modal-form select {
            width: 95%;
            padding: 8px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #333;
            color: #eee;
            margin-bottom: 10px;
            transition: border-color 0.3s ease;
        }

        .modal-form input:focus, .modal-form select:focus {
            border-color: #0077cc;
            outline: none;
        }

        #subscription-tab .form-group input {
            width: 95%;
            padding: 8px;
            border: 1px solid #555;
            border-radius: 4px;
            background-color: #333;
            color: #eee;
            margin-bottom: 10px;
            transition: border-color 0.3s ease;
        }

        #subscription-tab .form-group input:focus {
            border-color: #0077cc;
            outline: none;
        }

        #subscription-tab .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px; /* Added some space above buttons */
        }

        #subscription-tab .btn:hover {
            opacity: 0.8;
        }

        #subscription-tab .btn-primary {
            background-color: #0077cc;
        }

        #subscription-tab .btn-danger {
            background-color: #cc0000;
        }

        #subscription-tab .btn-success {
            background-color: #008800;
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="container mt-5">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-user');
    const modal = document.getElementById('editUserModal');
    const form = document.getElementById('editUserForm');
    const closeModalButton = document.getElementById('closeModal');
    const saveUserButton = document.getElementById('saveUser');
    const mainContent = document.querySelector('.main-content');

    const userTab = document.getElementById('user-tab');
    const subscriptionTab = document.getElementById('subscription-tab');

    const updateSubscriptionButton = document.getElementById('updateSubscription');
    const cancelSubscriptionButton = document.getElementById('cancelSubscription');
    const resumeSubscriptionButton = document.getElementById('resumeSubscription');

    function showModal() {
        modal.classList.add('show');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');

        document.querySelectorAll('.modal-tab-buttons button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.modal-tab-content').forEach(tab => {
            tab.classList.remove('active');
            tab.style.opacity = 0;
        });
        document.querySelector('.modal-tab-buttons button[data-tab="user-tab"]').classList.add('active');
        document.getElementById('user-tab').classList.add('active');
        document.getElementById('user-tab').style.opacity = 1; // Make sure it's visible
    }

    function clearForm() {
        form.reset();
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            fetchUserData(userId);
            showModal();
        });
    });

    closeModalButton.addEventListener('click', closeModal);

    saveUserButton.addEventListener('click', function() {
        const userId = document.getElementById('userId').value;
        saveUserData(userId);
    });

    let currentUserId = null;

    function fetchUserSubscription(userId) {
            fetch(`/api/users/${userId}/subscription`)
                .then(response => response.json())
                .then(subscription => {
                    document.getElementById('subscription_type').value = subscription.subscription_type || '';
                    document.getElementById('start_date').value = subscription.start_date || '';
                    document.getElementById('end_date').value = subscription.end_date || '';
                    document.getElementById('amount').value = subscription.amount || '';
                    document.getElementById('status').value = subscription.status || '';
                })
                .catch(error => {
                    console.error('Error fetching subscription:', error);
                    document.getElementById('subscription-error').textContent = 'Failed to fetch subscription details.';
                });
        }
        function updateUserSubscription(userId) {
            const updatedSubscription = {
                subscription_type: document.getElementById('subscription_type').value,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                amount: document.getElementById('amount').value,
            };
    
            fetch(`/api/users/${userId}/subscription`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updatedSubscription)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('subscription-error').textContent = data.error;
                } else {
                    alert(data.message || 'Subscription updated successfully.');
                    fetchUserSubscription(userId); // Refresh the subscription data
                }
            })
            .catch(error => {
                console.error('Error updating subscription:', error);
                document.getElementById('subscription-error').textContent = 'Failed to update subscription.';
            });
        }
    
        function fetchUserData(userId) {
            currentUserId = userId;
            fetch(`/api/users/${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(user => {
                    document.getElementById('userId').value = user.member_id;
                    document.getElementById('first_name').value = user.first_name;
                    document.getElementById('last_name').value = user.last_name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('birth_date').value = user.birth_date;
                    document.getElementById('address').value = user.address;
                    document.getElementById('phone').value = user.phone;
                    document.getElementById('status').value = user.status;
    
                    // Show user tab by default
                    document.getElementById('user-tab').classList.add('active');
                    document.getElementById('subscription-tab').classList.remove('active');
    
                    // Highlight the User Details tab button
                    document.querySelector('.modal-tab-buttons button[data-tab="user-tab"]').classList.add('active');
                    document.querySelector('.modal-tab-buttons button[data-tab="subscription-tab"]').classList.remove('active');
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    document.getElementById('user-error').textContent = 'Failed to fetch user data.';
                });
        }
    
        function saveUserData(userId) {
            const updatedData = {
                first_name: document.getElementById('first_name').value,
                last_name: document.getElementById('last_name').value,
                email: document.getElementById('email').value,
                birth_date: document.getElementById('birth_date').value,
                address: document.getElementById('address').value,
                phone: document.getElementById('phone').value,
                status: document.getElementById('status').value
            };
    
            fetch(`/api/users/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updatedData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    document.getElementById('user-error').textContent = data.error;
                } else {
                    alert('User updated successfully.');
                    closeModal();
                    clearForm();
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error updating user:', error);
                document.getElementById('user-error').textContent = 'Failed to update user.';
            });
        }
    
        function cancelUserSubscription(userId) {
            if (!confirm('Are you sure you want to cancel this subscription?')) return;
    
            fetch(`/api/users/${userId}/subscription/cancel`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('subscription-error').textContent = data.error;
                } else {
                    alert(data.message);
                    fetchUserSubscription(userId); // Refresh the subscription data
                }
            })
            .catch(error => {
                console.error('Error cancelling subscription:', error);
                document.getElementById('subscription-error').textContent = 'Failed to cancel subscription.';
            });
        }
    
        function resumeUserSubscription(userId) {
            if (!confirm('Are you sure you want to resume this subscription?')) return;
    
            fetch(`/api/users/${userId}/subscription/resume`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('subscription-error').textContent = data.error;
                } else {
                    alert(data.message);
                    fetchUserSubscription(userId); // Refresh the subscription data
                }
            })
            .catch(error => {
                console.error('Error resuming subscription:', error);
                document.getElementById('subscription-error').textContent = 'Failed to resume subscription.';
            });
        }
    
        const tabButtons = document.querySelectorAll('.modal-tab-buttons button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and hide all tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.modal-tab-content').forEach(tab => {
                    tab.classList.remove('active');
                    tab.style.opacity = 0; // Reset opacity for the transition
                });
    
                // Add active class to the clicked button and show the corresponding tab
                this.classList.add('active');
                const tabId = this.dataset.tab;
                const activeTab = document.getElementById(tabId);
                activeTab.classList.add('active');
    
                // Force a reflow to ensure the opacity reset takes effect before adding the new opacity
                activeTab.offsetWidth;
    
                // Fade in the active tab
                activeTab.style.opacity = 1;
    
                // If the subscription tab is active, fetch the subscription data
                if (tabId === 'subscription-tab') {
                    fetchUserSubscription(currentUserId);
                }
            });
        });
    
        updateSubscriptionButton.addEventListener('click', function() {
            updateUserSubscription(currentUserId);
        });
    
        cancelSubscriptionButton.addEventListener('click', function() {
            cancelUserSubscription(currentUserId);
        });
    
        resumeSubscriptionButton.addEventListener('click', function() {
            resumeUserSubscription(currentUserId);
        });
});

</script>

</body>
</html>