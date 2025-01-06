<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <style>
body {
    font-family: 'League Spartan', sans-serif;
    background-color: #000000;
    color: #eeeeee;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 30px;
    background-color: #000000;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
}

h1, h3 {
    color: #C1FF72;
}

.search-form {
    margin-bottom: 20px;
}

.search-form label {
    display: block;
    margin-bottom: 5px;
    color: #aaaaaa;
}

.search-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-container .form-control {
    width: 300px;
    padding: 10px;
    border: none;
    border-radius: 5px;
    background-color: #222222;
    color: #eeeeee;
}

.search-container .form-control:focus {
    outline: none;
}

.search-container .btn-primary {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    background-color: #C1FF72;
    color: #000000;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.search-container .btn-primary:hover {
    background-color: #eeeeee;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
}

.user-table {
    width: 100%;
    max-width: 1200px;
    margin: 20px auto;
    border-collapse: collapse;
}

.user-table th, .user-table td {
    padding: 20px 25px;
    text-align: left;
    border-bottom: 1px solid #333333;
    color: #eeeeee;
}

.user-table th {
    background: linear-gradient(to bottom, rgb(25, 25, 25), rgb(51, 51, 51));
    color: #C1FF72;
    font-weight: bold;
    font-size: 16px;
    padding: 15px 10px;
    text-align: left;
}

.user-table th, .user-table td {
    white-space: nowrap;
}

.user-table {
    table-layout: auto;
}

.user-table tr:hover {
    background-color: #222222;
}

/* Action Buttons */
.user-table .btn {
    display: inline-block;
    padding: 10px 15px;
    margin-right: 5px;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    font-weight: bold;
    text-align: center;
    transition: all 0.3s ease;
    text-decoration: none;
}

.user-table .btn-edit {
    background-color: #ffaa33;
    color:rgb(255, 255, 255);
    border: none;
}

.user-table .btn-edit:hover {
    background-color: #ffcc66;
    box-shadow: 0 0 10px rgba(255, 204, 102, 0.8);
}

.user-table .btn-delete {
    background-color: #ff3d3d;
    color: #ffffff;
    border: none;
}

.user-table .btn-delete:hover {
    background-color: #ff6666;
    box-shadow: 0 0 10px rgba(255, 102, 102, 0.8);
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: 5px;
    font-size: 14px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #008000;
    color: #ffffff;
}

.alert-danger {
    background-color: #ff0000;
    color: #ffffff;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 1000;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #000000;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.5);
    width: 90%;
    max-width: 600px;
}

.modal-header {
    border-bottom: 1px solid #333333;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.modal-title {
    color: #C1FF72;
    text-align: center;
    font-size: 24px;
}

.modal-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 20px;
    color: #aaaaaa;
    cursor: pointer;
}

/* Modal Form */
.modal-form label {
    color: #aaaaaa;
    display: block;
    margin-bottom: 5px;
}

.modal-form input, .modal-form select {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    background-color: #222222;
    color: #eeeeee;
    margin-bottom: 15px;
}

.modal-form input:focus, .modal-form select:focus {
    outline: none;
    box-shadow: 0 0 10px rgba(193, 255, 114, 0.5);
}

.modal-footer {
    text-align: right;
}

.modal-footer .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.modal-footer .btn-primary {
    background-color: #C1FF72;
    color: #000000;
}

.modal-footer .btn-primary:hover {
    background-color: #eeeeee;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

.modal-footer .btn-secondary {
    background-color: #444444;
    color: #eeeeee;
}

.modal-footer .btn-secondary:hover {
    background-color: #666666;
}

.sort-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-left: -40px;
}

.dropdown {
    display: inline-block;
    position: relative;
    margin-left: 5px;
}

.dropdown-btn {
    background: none;
    color: #eeeeee;
    border: none;
    font-size: 14px;
    cursor: pointer;
    padding: 5px 10px;
    font-weight: bold;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.dropdown-btn:hover {
    background-color: #333333;
    color: #C1FF72;
}

.arrow-down {
    font-size: 10px;
    margin-left: 5px;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    background-color: #222222;
    border: 1px solid #444444;
    border-radius: 5px;
    list-style: none;
    padding: 0;
    margin: 5px 0 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    z-index: 1000;
    min-width: 150px;
}

.dropdown-menu li {
    padding: 10px 15px;
    cursor: pointer;
    color: #eeeeee;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.dropdown-menu li:hover {
    background-color: #333333;
    color: #C1FF72;
}

.dropdown.open .dropdown-menu {
    display: block;
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
    <label for="search">Rechercher par nom, prénom ou email :</label>
    <div class="search-container">
        <input type="text" name="search" id="search" class="form-control" placeholder="Rechercher un utilisateur" value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </div>
</form>


        <?php if (!empty($searchTerm)): ?>
            <h3 class="mt-4">Résultats de la recherche pour : "<?= htmlspecialchars($searchTerm) ?>"</h3>
        <?php endif; ?>

        <table class="user-table mt-4">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
            <th class="sort-header">
                <div class="dropdown">
                    <button class="dropdown-btn">Trier <span class="arrow-down">▼</span></button>
                    <ul class="dropdown-menu">
                        <li data-sort="last_name">Nom</li>
                        <li data-sort="first_name">Prénom</li>
                        <li data-sort="email">Email</li>
                        <li data-sort="status">Status</li>
                    </ul>
                </div>
            </th>
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
        document.getElementById('user-tab').style.opacity = 1;
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
                    fetchUserSubscription(userId);
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
    
                    document.getElementById('user-tab').classList.add('active');
                    document.getElementById('subscription-tab').classList.remove('active');
    
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
                    fetchUserSubscription(userId); 
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
                    fetchUserSubscription(userId);
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
                tabButtons.forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.modal-tab-content').forEach(tab => {
                    tab.classList.remove('active');
                    tab.style.opacity = 0;
                });
    
                this.classList.add('active');
                const tabId = this.dataset.tab;
                const activeTab = document.getElementById(tabId);
                activeTab.classList.add('active');
    
                activeTab.offsetWidth;
    
                activeTab.style.opacity = 1;
    
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

    document.addEventListener('DOMContentLoaded', function () {
        const dropdown = document.querySelector('.dropdown');
        const dropdownBtn = dropdown.querySelector('.dropdown-btn');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
        const tableBody = document.querySelector('.user-table tbody');

        dropdownBtn.addEventListener('click', function () {
            dropdown.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        dropdownMenu.addEventListener('click', function (e) {
            if (e.target.tagName === 'LI') {
                const sortKey = e.target.dataset.sort;
                const rows = Array.from(tableBody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const aText = a.querySelector(`td:nth-child(${getColumnIndex(sortKey) + 1})`).textContent.trim();
                    const bText = b.querySelector(`td:nth-child(${getColumnIndex(sortKey) + 1})`).textContent.trim();
                    return aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' });
                });

                tableBody.innerHTML = '';
                rows.forEach(row => tableBody.appendChild(row));
                dropdown.classList.remove('open');
            }
        });

        function getColumnIndex(column) {
            const columns = ['last_name', 'first_name', 'email', 'status'];
            return columns.indexOf(column);
        }
    });

</script>

</body>
</html>