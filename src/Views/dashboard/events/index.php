<?php use Models\Booking; ?>
<div data-view="events_dash">
    <div class="container">
        <div id="myCalendar"></div>

        <div>
            <button id="createEventButton" class="btn btn-primary" onclick="openCreateEventPopup()">Create Event</button>
        </div>

        <!-- Event Details Modal -->
        <div class="modal" id="eventDetailsPopup">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="modal-close" id="closeDetailsButton" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="eventDetailsContent">
                    </div>
            </div>
        </div>

        <!-- Create Event Modal -->
        <div class="modal" id="createEventPopup">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Event</h5>
                    <button type="button" class="modal-close" onclick="closeCreateEventPopup()" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <div class="form-group">
                            <label for="event_name">Event Name</label>
                            <input type="text" id="event_name" name="event_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="event_date">Event Date</label>
                            <input type="text" id="event_date" name="event_date" class="form-control" autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="text" id="start_time" name="start_time" class="form-control" autocomplete="off" required>
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
                            <input type="number" id="max_participants" name="max_participants" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <select id="location" name="location">
                            <?php
                            $reservations = Booking::getAllReservations();
                            foreach ($reservations as $reservation) {
                                if ($reservation['event_id'] == null && $reservation['reservation_date'] >= date('Y-m-d')) {
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
                            <textarea id="invitations" name="invitations" class="form-control" placeholder="Enter email addresses separated by commas"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateEventPopup()">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEvent">Create Event</button>
                </div>
            </div>
        </div>

        <div class="overlay" id="popupOverlay"></div>
        <div id="toast-container"></div>
    </div>
</div>
<script>
    window.currentUserId = <?php echo isset($user['member_id']) ? $user['member_id'] : 'null'; ?>;
    window.memberStatus = "<?php echo isset($user['status']) ? $user['status'] : ''; ?>";
</script>