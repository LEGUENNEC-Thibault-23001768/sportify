function initialize() {
    console.log("Booking view initialized");
    loadReservations();
    // Other initialization code for booking view, if any
    
}
let currentUserId = null;
let userStatus = null;
let userName = null;
let memberId = null;

function setUserData(userData) {
    currentUserId = userData.member_id;
    userName = userData.first_name;
    userStatus = userData.status;
    memberId = userData.member_id;
}

function openReservationForm(roomElement) {
    const courtId = roomElement.getAttribute('data-court-id');
    document.getElementById('court_id').value = courtId;
    document.getElementById('member_name').value = userName;
    document.getElementById('member_id').value = memberId;
    document.getElementById('reservation-container').style.display = 'block';

    $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
}

function closeReservationForm() {
    document.getElementById('reservation-container').style.display = 'none';
    document.getElementById('reservation-form').reset();

    $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
}

$(document).ready(function() {
    $('#reserve-button').click(function() {
        const formData = {
            court_id: $('#court_id').val(),
            member_id: memberId,
            reservation_date: $('#date').val(),
            start_time: $('#start-time').val(),
            end_time: $('#end-time').val()
        };

        $.ajax({
            url: '/api/booking',
            type: 'POST',
            data: formData,
            success: function(response) {
                showSuccessToast('Réservation ajoutée avec succès!');
                closeReservationForm();
                loadReservations();
            },
            error: function(xhr, status, error) {
                showErrorToast("Erreur lors de l'ajout de la réservation.");
                console.error("Error: " + status + " - " + error);
            }
        });
    });

    $('#update-button').click(function() {
        const reservationId = $('#edit_reservation_id').val();
        const formData = {
            reservation_date: $('#edit_date').val(),
            start_time: $('#edit_start-time').val(),
            end_time: $('#edit_end-time').val()
        };
   
        $.ajax({
            url: '/api/booking/' + reservationId,
            type: 'PUT',
            contentType: 'application/json', 
            data: JSON.stringify(formData), 
            success: function(response) {
              if(response.data && response.data.message){
                  showSuccessToast(response.data.message);
                }else{
                    showSuccessToast('Réservation mise à jour avec succès!');
                }
                hideEditForm();
                loadReservations();
            },
            error: function(xhr, status, error) {
              let errorMessage = "Erreur lors de la mise à jour de la réservation.";
              if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.error) {
                  errorMessage = xhr.responseJSON.data.error;
              }
              showErrorToast(errorMessage);
                console.error("Error: " + status + " - " + error);
            }
        });
    });
});

function loadReservations() {
    $.ajax({
        url: '/api/booking',
        type: 'GET',
        success: function(response) {
            console.log("Reservations data:", response);
            setUserData(response.user);
            displayReservations(response.bookings);
        },
        error: function(xhr, status, error) {
            console.error("Error: " + status + " - " + error);
        }
    });
}

function displayReservations(bookings) {
    const list = $('#reservation-list');
    list.empty();

    if (bookings.length === 0) {
        list.append('<li>Aucune réservation trouvée.</li>');
        return;
    }

    bookings.forEach(booking => {
        let listItem = $('<li></li>');
        listItem.text(`${booking.reservation_date} - ${booking.start_time} à ${booking.end_time} - ${booking.court_name} (Réservé par ${booking.member_name})`);

        if (booking.member_id == currentUserId || userStatus === 'admin') {
            let editButton = $(`<button class="edit-button" data-id="${booking.reservation_id}">Modifier</button>`);
            editButton.click(function() {
                editReservation(booking.reservation_id);
            });

            let deleteButton = $(`<button class="delete-button" data-id="${booking.reservation_id}">Supprimer</button>`);
            deleteButton.click(function() {
                deleteReservation(booking.reservation_id);
            });

            listItem.append(editButton);
            listItem.append(deleteButton);
        }

        list.append(listItem);
    });
}

function editReservation(reservationId) {
    $.ajax({
        url: '/api/booking/' + reservationId,
        type: 'GET',
        success: function(response) {
            console.log(response);
            showEditForm(response.reservation);
        },
        error: function(xhr, status, error) {
            showErrorToast("Erreur lors de la récupération des détails de la réservation.");
            console.error("Error: " + status + " - " + error);
        }
    });
}

function deleteReservation(reservationId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette réservation?')) {
        return;
    }

    $.ajax({
        url: '/api/booking/' + reservationId,
        type: 'DELETE',
        success: function(response) {
            console.log(response)
            showSuccessToast('Réservation supprimée avec succès!');
            loadReservations(); // Refresh the list after deletion
        },
        error: function(xhr, status, error) {
            showErrorToast("Erreur lors de la suppression de la réservation.");
            console.error("Error: " + status + " - " + error);
        }
    });
}

function showEditForm(reservation) {
    $('#edit_reservation_id').val(reservation.reservation_id);
    $('#edit_member_name').val(userName);
    $('#edit_date').val(reservation.reservation_date);
    $('#edit_start-time').val(reservation.start_time);
    $('#edit_end-time').val(reservation.end_time);

    $('#edit-reservation-container').show();

    $('.gym-map, #reservations, .dashboard-content > h2').addClass('modal-open');
}

function hideEditForm() {
    $('#edit-reservation-container').hide();
    $('.gym-map, #reservations, .dashboard-content > h2').removeClass('modal-open');
}

function cancelEditReservation() {
    hideEditForm();
}

function showSuccessToast(message) {
    const toastContainer = $('#toast-container');
    const toast = $(`<div class="toast success">${message}</div>`);
    toastContainer.append(toast);
    setTimeout(() => toast.remove(), 3000);
}

function showErrorToast(message) {
    const toastContainer = $('#toast-container');
    const toast = $(`<div class="toast error">${message}</div>`);
    toastContainer.append(toast);
    setTimeout(() => toast.remove(), 3000);
}