function initialize() {
    const profilePictureInput = $('#profile_picture');
    const profilePicture = $('.profile-picture');

    profilePicture.on('click', function() {
        profilePictureInput.click();
    });

    profilePictureInput.on('change', function(event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePicture.attr('src', e.target.result);
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    });
}