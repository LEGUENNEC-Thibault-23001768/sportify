<?php use Models\Booking; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events List</title>

    <link rel="preconnect" href="https://code.jquery.com">
    <link rel="dns-prefetch" href="https://code.jquery.com">
    <link rel="stylesheet" href="../_assets/css/mobiscroll.min.css">
    <link rel="preload" href="../_assets/css/event_style.css" as="style">
    <link rel="stylesheet" href="../_assets/css/event_style.css">
    <script src="../_assets/js/mobiscroll.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script>
        const currentUserId = <?php echo $member['member_id']; ?>;
        const memberStatus = "<?php echo $member['status']; ?>";
    </script>
    <script src="/_assets/js/event_script.js" defer></script>
</head>
<body>
<div class="container">
    <div id="myCalendar"></div>

    <div>
        <button id="createEventButton" class="btn btn-primary" onclick="openCreateEventPopup()">Create Event</button>
    </div>

    <div id="eventDetailsPopup" class="custom-popup">
        <div class="popup-header">
            <h2>Event Details</h2>
            <span class="close-button" id="closeDetailsButton">×</span>
        </div>
        <div class="popup-content" id="eventDetailsContent"></div>
    </div>

    <div id="createEventPopup" class="custom-popup">
        <div class="popup-header">
            <h2>Create Event</h2>
            <span class="close-button" onclick="closeCreateEventPopup()">×</span>
        </div>
        <div class="popup-content">
            <form id="eventForm">
                <div class="form-group">
                    <label for="event_name">Event Name</label>
                    <input type="text" id="event_name" name="event_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="text" id="event_date" name="event_date" class="form-control" autocomplete="off"
                           required>
                </div>
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="text" id="start_time" name="start_time" class="form-control" autocomplete="off"
                           required>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="text" id="end_time" name="end_time" class="form-control" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="max_participants">Max Participants</label>
                    <input type="number" id="max_participants" name="max_participants" class="form-control" min="5"
                           value="5"
                           required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <!--                    <input type="text" id="location" name="location" class="form-control" required>-->
                    <select id="location" name="location">
                        <?php
                        $reservations = Booking::getAllReservations();
                        foreach ($reservations as $reservation) {
                            if ($reservation['event_id'] == null) {
                                if ($reservation['member_id'] === $_SESSION['user_id'] || $member['status'] === 'admin') {
                                    ?>
                                    <option value='<?= $reservation['court_name'] ?>'>
                                        <?= $reservation['reservation_date'] ?> - <?= $reservation['start_time'] ?>
                                        à <?= $reservation['end_time'] ?> - <?= $reservation['court_name'] ?>
                                    </option>;
                                <?php }
                            }
                        } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="invitations">Invite by Email (comma-separated)</label>
                    <textarea id="invitations" name="invitations" class="form-control"
                              placeholder="Enter email addresses separated by commas"></textarea>
                </div>
                <button id="saveEvent" type="button" class="btn btn-primary">Create Event</button>
                <button id="cancelEvent" type="button" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <div class="overlay" id="popupOverlay"></div>
    <div id="toast-container"></div>
</div>


</body>
</html>