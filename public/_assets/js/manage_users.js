function initialize() {
    const editButtons = $('.edit-user');
    const modal = $('#editUserModal');
    const form = $('#editUserForm');
    const closeModalButton = $('#closeModal');
    const saveUserButton = $('#saveUser');

    const userTab = $('#user-tab');
    const subscriptionTab = $('#subscription-tab');

    const updateSubscriptionButton = $('#updateSubscription');
    const cancelSubscriptionButton = $('#cancelSubscription');
    const resumeSubscriptionButton = $('#resumeSubscription');

    let currentUserId = null;

    function showModal() {
        modal.addClass('show');
        $('body').addClass('modal-open');
    }

    function closeModal() {
        modal.removeClass('show');
        $('body').removeClass('modal-open');
        $('.modal-tab-buttons button').removeClass('active');
        $('.modal-tab-content').removeClass('active').css('opacity', 0);
        $('.modal-tab-buttons button[data-tab="user-tab"]').addClass('active');
        $('#user-tab').addClass('active').css('opacity', 1);
        clearForm();
    }

    function clearForm() {
        form[0].reset();
    }

    editButtons.on('click', function () {
        const userId = $(this).data('user-id');
        currentUserId = userId;
        fetchUserData(userId);
        showModal();
    });

    $('.delete-user-btn').on('click', function() {
        const userId = $(this).data('user-id');
        deleteUser(userId);
    })

    closeModalButton.on('click', closeModal);

    saveUserButton.on('click', function () {
        const userId = $('#userId').val();
        saveUserData(userId);
    });

    function fetchUserSubscription(userId) {
        $.ajax({
            url: `/api/users/${userId}/subscription`,
            method: 'GET',
            dataType: 'json',
            success: function (subscription) {
                $('#subscription_type').val(subscription.subscription_type || '');
                $('#start_date').val(subscription.start_date || '');
                $('#end_date').val(subscription.end_date || '');
                $('#amount').val(subscription.amount || '');
                $('#status').val(subscription.status || '');
                $('#subscription-error').text('');
            },
             error: function (xhr, status, error) {
                console.error('Erreur lors de la récupération de l\'abonnement:', xhr.responseJSON);
                $('#subscription-error').text('Échec de la récupération des détails de l\'abonnement.');
            }
        });
    }

    function updateUserSubscription(userId) {
        const updatedSubscription = {
            subscription_type: $('#subscription_type').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            amount: $('#amount').val(),
        };

        $.ajax({
            url: `/api/users/${userId}/subscription`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(updatedSubscription),
             success: function (data) {
                if (data.error) {
                    $('#subscription-error').text(data.error);
                } else {
                   alert(data.message || 'Abonnement mis à jour avec succès.');
                   fetchUserSubscription(userId);
                }
            },
            error: function (xhr, status, error) {
                console.error('Erreur lors de la mise à jour de l\'abonnement:', error);
                 $('#subscription-error').text('Échec de la mise à jour de l\'abonnement.');
            }
        });
    }

    function fetchUserData(userId) {
        $.ajax({
            url: `/api/users/${userId}`,
            method: 'GET',
            dataType: 'json',
            success: function (user) {
                $('#userId').val(user.member_id);
                $('#first_name').val(user.first_name);
                $('#last_name').val(user.last_name);
                $('#email').val(user.email);
                $('#birth_date').val(user.birth_date);
                $('#address').val(user.address);
                $('#phone').val(user.phone);
                $('#status').val(user.status);
                $('#user-error').text('');

                $('#user-tab').addClass('active').css('opacity', 1);
                $('#subscription-tab').removeClass('active').css('opacity', 0);

                $('.modal-tab-buttons button[data-tab="user-tab"]').addClass('active');
                $('.modal-tab-buttons button[data-tab="subscription-tab"]').removeClass('active');
            },
              error: function (xhr, status, error) {
                console.error('Erreur lors de la récupération des données de l\'utilisateur:', error);
                $('#user-error').text('Échec de la récupération des données de l\'utilisateur.');
            }
        });
    }

    function saveUserData(userId) {
        const updatedData = {
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            email: $('#email').val(),
            birth_date: $('#birth_date').val(),
            address: $('#address').val(),
            phone: $('#phone').val(),
            status: $('#status').val()
        };

        $.ajax({
            url: `/api/users/${userId}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(updatedData),
            success: function (data) {
                if (data.error) {
                     $('#user-error').text(data.error);
                } else {
                     alert('Utilisateur mis à jour avec succès.');
                    closeModal();
                    clearForm();
                    location.reload();
                }
            },
              error: function (xhr, status, error) {
                console.error('Erreur lors de la mise à jour de l\'utilisateur:', error);
                $('#user-error').text('Échec de la mise à jour de l\'utilisateur.');
            }
        });
    }

    function cancelUserSubscription(userId) {
        if (!confirm('Êtes-vous sûr de vouloir annuler cet abonnement ?')) return;
        $.ajax({
            url: `/api/users/${userId}/subscription/cancel`,
            method: 'POST',
            dataType: 'json',
              success: function (data) {
                if (data.error) {
                    $('#subscription-error').text(data.error);
                } else {
                    alert(data.message);
                    fetchUserSubscription(userId);
                }
            },
            error: function (xhr, status, error) {
                 console.error('Erreur lors de l\'annulation de l\'abonnement:', error);
                $('#subscription-error').text('Échec de l\'annulation de l\'abonnement.');
            }
        });
    }


    function resumeUserSubscription(userId) {
        if (!confirm('Êtes-vous sûr de vouloir reprendre cet abonnement ?')) return;

        $.ajax({
            url: `/api/users/${userId}/subscription/resume`,
            method: 'POST',
            dataType: 'json',
            success: function (data) {
               if (data.error) {
                    $('#subscription-error').text(data.error);
                } else {
                   alert(data.message);
                    fetchUserSubscription(userId);
                }
            },
            error: function (xhr, status, error) {
                console.error('Erreur lors de la reprise de l\'abonnement:', error);
                $('#subscription-error').text('Échec de la reprise de l\'abonnement.');
            }
        });
    }

    $('.modal-tab-buttons button').on('click', function () {
        const tabId = $(this).data('tab');

        $('.modal-tab-buttons button').removeClass('active');
        $('.modal-tab-content').removeClass('active').css('opacity', 0);

        $(this).addClass('active');
        $(`#${tabId}`).addClass('active').css('opacity', 1);


        if (tabId === 'subscription-tab') {
            fetchUserSubscription(currentUserId);
        }
    });

    updateSubscriptionButton.on('click', function () {
        updateUserSubscription(currentUserId);
    });

    cancelSubscriptionButton.on('click', function () {
        cancelUserSubscription(currentUserId);
    });

    resumeSubscriptionButton.on('click', function () {
        resumeUserSubscription(currentUserId);
    });

    $('#search-form').submit(function (e) {
        e.preventDefault();
        const searchTerm = $('#search').val();
        searchUsers(searchTerm);
    });

    function searchUsers(searchTerm) {
        $.ajax({
            url: '/api/users',
            method: 'GET',
            data: { search: searchTerm },
            dataType: 'json',
              success: function (users) {
                updateUsersTable(users);
            },
              error: function (xhr, status, error) {
                console.error('Erreur AJAX:', status, error);
                 // Handle error appropriately, e.g., display an error message
            }
        });
    }

    function updateUsersTable(users) {
        const tableBody = $('#user-table-body');
        tableBody.empty();
        if (users.length > 0) {
            users.forEach(user => {
                const row = `
                 <tr>
                        <td>${user.last_name}</td>
                        <td>${user.first_name}</td>
                        <td>${user.email}</td>
                      <td>${user.status}</td>
                        <td>
                            <button class="btn btn-edit edit-user" data-user-id="${user.member_id}">Modifier</button>
                            <button class="btn btn-delete delete-user-btn" data-user-id="${user.member_id}">Supprimer</button>
                        </td>
                    </tr>
                `;
                tableBody.append(row);
            });
        } else {
            tableBody.append('<tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>');
        }
          $('#user-table-body').off('click', '.delete-user-btn').on('click', '.delete-user-btn', function(e) {
              e.preventDefault();
              const userId = $(this).data('user-id');
              deleteUser(userId);
          });
    }

    function deleteUser(userId) {
        if (!confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) return;
        $.ajax({
            url: `/api/users/${userId}`,
            method: 'DELETE',
           success: function (data) {
                alert(data.message || 'Utilisateur supprimé avec succès.');
                location.reload();
            },
            error: function (xhr, status, error) {
                alert('Échec de la suppression de l\'utilisateur.');
            }
        })
    }
};