function initialize() {
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
};