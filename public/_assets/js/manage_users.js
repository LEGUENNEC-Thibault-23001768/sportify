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
    }
    function clearForm() {
        form[0].reset();
    }

    let currentUserId = null;
     // Edit user button click event
    editButtons.on('click', function() {
        const userId = $(this).data('user-id');
        currentUserId = userId; // Store current user ID
        fetchUserData(userId);
        showModal();
     });


    closeModalButton.on('click', closeModal);

    saveUserButton.on('click', function() {
        const userId = $('#userId').val();
        saveUserData(userId);
    });

    function fetchUserSubscription(userId) {
        $.ajax({
            url: `/api/users/${userId}/subscription`,
            method: 'GET',
            dataType: 'json',
            success: function(subscription) {
                $('#subscription_type').val(subscription.subscription_type || '');
                $('#start_date').val(subscription.start_date || '');
                $('#end_date').val(subscription.end_date || '');
                $('#amount').val(subscription.amount || '');
                $('#status').val(subscription.status || '');
                $('#subscription-error').text(''); // Clear any previous errors
            },
            error: function(xhr, status, error) {
                console.error('Error fetching subscription:', error);
                 $('#subscription-error').text('Failed to fetch subscription details.');
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
                success: function(data) {
                    if (data.error) {
                        $('#subscription-error').text(data.error);
                    } else {
                         alert(data.message || 'Subscription updated successfully.');
                        fetchUserSubscription(userId);
                    }
                },
                error: function(xhr, status, error) {
                  console.error('Error updating subscription:', error);
                   $('#subscription-error').text('Failed to update subscription.');
                }
            });
        }

    function fetchUserData(userId) {
        $.ajax({
          url: `/api/users/${userId}`,
          method: 'GET',
          dataType: 'json',
          success: function(user) {
            $('#userId').val(user.member_id);
            $('#first_name').val(user.first_name);
            $('#last_name').val(user.last_name);
            $('#email').val(user.email);
            $('#birth_date').val(user.birth_date);
            $('#address').val(user.address);
            $('#phone').val(user.phone);
             $('#status').val(user.status);
             $('#user-error').text(''); // Clear any previous errors
           
           $('#user-tab').addClass('active').css('opacity', 1);
           $('#subscription-tab').removeClass('active').css('opacity', 0);

           $('.modal-tab-buttons button[data-tab="user-tab"]').addClass('active');
           $('.modal-tab-buttons button[data-tab="subscription-tab"]').removeClass('active');
        },
            error: function(xhr, status, error) {
                console.error('Error fetching user data:', error);
              $('#user-error').text('Failed to fetch user data.');
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
            success: function(data) {
                if (data.error) {
                    $('#user-error').text(data.error);
                } else {
                     alert('User updated successfully.');
                    closeModal();
                    clearForm();
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
              console.error('Error updating user:', error);
               $('#user-error').text('Failed to update user.');
            }
          });
      }

    function cancelUserSubscription(userId) {
       if (!confirm('Are you sure you want to cancel this subscription?')) return;
       $.ajax({
           url: `/api/users/${userId}/subscription/cancel`,
           method: 'POST',
           dataType: 'json',
           success: function(data) {
              if (data.error) {
                   $('#subscription-error').text(data.error);
                } else {
                    alert(data.message);
                    fetchUserSubscription(userId);
               }
           },
           error: function(xhr, status, error) {
              console.error('Error cancelling subscription:', error);
               $('#subscription-error').text('Failed to cancel subscription.');
           }
       });
    }


  function resumeUserSubscription(userId) {
        if (!confirm('Are you sure you want to resume this subscription?')) return;

       $.ajax({
           url: `/api/users/${userId}/subscription/resume`,
           method: 'POST',
           dataType: 'json',
           success: function(data) {
                if (data.error) {
                  $('#subscription-error').text(data.error);
                } else {
                    alert(data.message);
                    fetchUserSubscription(userId);
               }
           },
            error: function(xhr, status, error) {
              console.error('Error resuming subscription:', error);
               $('#subscription-error').text('Failed to resume subscription.');
            }
       });
  }

  $('.modal-tab-buttons button').on('click', function() {
    const tabId = $(this).data('tab');

    $('.modal-tab-buttons button').removeClass('active');
    $('.modal-tab-content').removeClass('active').css('opacity', 0);

    $(this).addClass('active');
    $(`#${tabId}`).addClass('active').css('opacity', 1);


        if (tabId === 'subscription-tab' ) {
            fetchUserSubscription(currentUserId);
        }
   });

    updateSubscriptionButton.on('click', function() {
        updateUserSubscription(currentUserId);
    });

    cancelSubscriptionButton.on('click', function() {
        cancelUserSubscription(currentUserId);
    });

    resumeSubscriptionButton.on('click', function() {
        resumeUserSubscription(currentUserId);
    });

     $('#search-form').submit(function(e) {
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
             success: function(users) {
                    console.log(users);
                    updateUsersTable(users);
               },
              error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
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
                          <a href="/dashboard/admin/users/delete?id=${user.member_id}" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                        </td>
                    </tr>
                `;
                tableBody.append(row);
            });
       } else {
          tableBody.append('<tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>');
       }
    }
};