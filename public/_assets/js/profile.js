function initialize() {
    const profilePictureInput = $('#profile_picture');
    const profilePicture = $('.profile-picture');

    profilePicture.on('click', function () {
        profilePictureInput.click();
    });

    profilePictureInput.on('change', function (event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profilePicture.attr('src', e.target.result);
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    });

    const profileForm = document.getElementById('profileForm');
    const profileMessageDiv = document.getElementById('profile-message');

    console.log(profileForm);

    profileForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(profileForm);

        if (formData.get('current_password') === '') {
            formData.delete('current_password');
        }
        if (formData.get('new_password') === '') {
            formData.delete('new_password');
        }
        if (formData.get('confirm_password') === '') {
            formData.delete('confirm_password');
        }

        console.log(formData);
        fetch('/api/profile', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                return response.json()
            })
            .then(data => {
                console.log(data);
                if (data.message) {
                    profileMessageDiv.textContent = data.message;
                    profileMessageDiv.className = 'success';
                } else if (data.error) {
                    profileMessageDiv.textContent = data.error;
                    profileMessageDiv.className = 'error';
                }
                setTimeout(() => {
                    profileMessageDiv.textContent = '';
                    profileMessageDiv.className = '';
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error);
                profileMessageDiv.textContent = 'Une erreur est survenue.';
                profileMessageDiv.className = 'error';
            });
    });
}