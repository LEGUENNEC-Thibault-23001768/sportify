$(document).ready(async function () {
    const endTimePicker = mobiscroll.datepicker('#end_time', {
        controls: ['time'],
        display: 'center',
        timeFormat: 'HH:mm',
        minTime: '08:00',
        maxTime: '20:00',
        onSet: function (event, inst) {
            // When end time is selected, update the maxTime of start time picker
            startTimePicker.setOptions({maxTime: event.valueText});
        }
    });
    mobiscroll.setOptions({
        theme: 'ios',
        themeVariant: 'dark'
    });

    async function getCalendarData() {
        var eventsData = JSON.parse(await getCachedEvents());
        var bookingsData = JSON.parse(await getBookings());
        return eventsData.concat(bookingsData);
    }

    const bookingsData = await getBookings();
    const calendarData = await getCalendarData();

    const calendar = mobiscroll.eventcalendar('#myCalendar', {
        view: {
            schedule: {
                type: 'week',
                startTime: '08:00',
                endTime: '20:00',
                startDay: 1,
                endDay: 0
            }
        },
        data: calendarData,
        clickToCreate: false,
        dragToCreate: false,
        dragToMove: false,
        dragToResize: false,
        eventDelete: true,
        onEventClick: function (args) {
            if (args.event.type === 'booking') return;
            showEventDetails(args.event);
        },
        // onEventCreated: onEventCreated,
        onEventDeleted: function (args) {
            // Handle event deletion via AJAX
            $.ajax({
                url: '/api/events/' + args.event.id,
                method: 'DELETE',
                success: function (response) {
                    console.log("Event deleted successfully");
                },
                error: function (error) {
                    console.error("Error deleting event:", error);
                    showToast("Error deleting event", 'error');
                }
            });
        }
    });

    window.openCreateEventPopup = function () {
        // Check if the user is authorized to create events
        if (memberStatus === 'coach' || memberStatus === 'admin') {
            // Pre-fill the form fields with event data
            $('#event_name').val('');
            $('#event_date').val(formatDate(new Date()));
            $('#start_time').val(formatTime(new Date().getTime()));
            $('#end_time').val(formatTime(new Date().getTime()));
            $('#description').val('');
            $('#location').val('');

            // Show the create event popup
            createEventPopup.style.display = 'block';
            popupOverlay.style.display = 'block';
        }
    }

    // Initialize datepicker for event_date
    mobiscroll.datepicker('#event_date', {
        controls: ['calendar'],
        display: 'center',
        dateFormat: 'YYYY-MM-DD',
    });

    // Initialize timepickers with default constraints
    const startTimePicker = mobiscroll.datepicker('#start_time', {
        controls: ['time'],
        display: 'center',
        timeFormat: 'HH:mm',
        minTime: '08:00',
        maxTime: '20:00',
        onSet: function (event, inst) {
            // When start time is selected, update the minTime of end time picker
            endTimePicker.setOptions({minTime: event.valueText});
        }
    });


    // Event details popup
    const eventDetailsPopup = document.getElementById('eventDetailsPopup');
    const popupOverlay = document.getElementById('popupOverlay');
    const closeDetailsButton = document.getElementById('closeDetailsButton');

    if (closeDetailsButton) {
        closeDetailsButton.addEventListener('click', closeEventDetailsPopup);
    }

    // caching events

    async function getCachedEvents() {
        let eventsData;
        await $.ajax({
            url: '/api/events',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                // console.log(data)
                eventsData = JSON.stringify(data);
                // localStorage.setItem('eventsData', JSON.stringify(data));
                // calendar.setEvents(data);
            },
            error: function (error) {
                console.error("Error fetching events data:", error);
                showToast("Error fetching events data", 'error');
            }
        });
        // return eventsData || localStorage.getItem('eventsData');
        // console.log(eventsData)
        return eventsData;
    }

    async function getBookings() {
        let bookingsData;
        await $.ajax({
            url: '/api/booking',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                bookingsData = JSON.stringify(data);
                // localStorage.setItem('bookingsData', JSON.stringify(data));
                // calendar.setEvents(data);
            },
            error: function (error) {
                console.error("Error fetching bookings data:", error);
                showToast("Error fetching bookings data", 'error');
            }
        });
        // return bookingsData || localStorage.getItem('bookingsData');
        return bookingsData;
    }


    let eventDetailsCache = {}

    // Function to show event details
    function showEventDetails(event) {
        const isAuthorizedToDelete = memberStatus === 'coach' || memberStatus === 'admin';
        const isEventCreator = event.created_by === currentUserId;

        if (event.participants === undefined) event.participants = [];

        populateEventDetailsPopup(event, isAuthorizedToDelete, isEventCreator);

        // if (eventDetailsCache[event.id]) {
        //     populateEventDetailsPopup(eventDetailsCache[event.id], isAuthorizedToDelete, isEventCreator);
        // } else {
        //     $.ajax({
        //         url: '/api/events/' + event.id,
        //         method: 'GET',
        //         dataType: 'json',
        //         success: function (eventData) {
        //             // Cache the fetched event details
        //             eventDetailsCache[event.id] = eventData;
        //             populateEventDetailsPopup(eventData, isAuthorizedToDelete, isEventCreator);
        //         },
        //         error: function (error) {
        //             console.error("Error fetching event details:", error);
        //             showToast("Error fetching event details", 'error');
        //         }
        //     });
        // }
    }


    function populateEventDetailsPopup(eventData, isAuthorizedToDelete, isEventCreator) {
        let deleteButton = isAuthorizedToDelete ? `<button class="btn btn-danger" onclick="deleteEvent(${eventData.id})">Delete</button>` : '';
        const canInvite = isAuthorizedToDelete || isEventCreator;
        let joinLeaveButton = '';
        if (eventData.is_registered) {
            joinLeaveButton = `<button class="btn btn-secondary" onclick="leaveEvent(${eventData.id})">Leave Event</button>`;
        } else if (!isEventCreator) {
            joinLeaveButton = `<button class="btn btn-success" onclick="joinEvent(${eventData.id})">Join Event</button>`;
        }

        let participantsList = '<p>No participants yet.</p>';
        if (canInvite && eventData.participants.length > 0) {
            participantsList = '<ul>';
            for (let participant of eventData.participants) {
                participantsList += `<li>${participant.first_name} ${participant.last_name}</li>`;
            }
            participantsList += '</ul>';
        } else if (!canInvite) {
            participantsList = '<p>Participants list is private.</p>';
        }

        let inviteForm = canInvite ? `
            <div class="form-group">
                <label for="invite_email">Invite by Email</label>
                <input type="email" id="invite_email" name="invite_email" class="form-control" required>
                <button type="button" class="btn btn-primary" onclick="sendInvite(${eventData.id})">Send Invite</button>
            </div>
        ` : '';

        const startDate = new Date(eventData.start);
        const startTime = new Date(eventData.start).getTime();
        const endTime = new Date(eventData.end).getTime();

        const eventContent = `
            <h2>${eventData.title}</h2>
            <p><strong>Date:</strong> ${formatDate(startDate)}</p>
            <p><strong>Start Time:</strong> ${formatTime(startTime)}</p>
            <p><strong>End Time:</strong> ${formatTime(endTime)}</p>
            <p><strong>Location:</strong> ${eventData.location}</p>
            <p><strong>Description:</strong> ${eventData.description}</p>
            <p><strong>Max Participants:</strong> ${eventData.max_participants}</p>
            ${canInvite ? '<p><strong>Participants:</strong></p>' : ''}
            ${canInvite ? participantsList : ''}
            ${inviteForm}
            ${joinLeaveButton}
            ${deleteButton}
        `;

        $('#eventDetailsContent').html(eventContent);
        eventDetailsPopup.style.display = 'block';
        popupOverlay.style.display = 'block';
    }

    // Function to close event details popup
    function closeEventDetailsPopup() {
        eventDetailsPopup.style.display = 'none';
        popupOverlay.style.display = 'none'; // Hide overlay
    }

    // Event create popup
    const createEventPopup = document.getElementById('createEventPopup');

    window.closeCreateEventPopup = function () {
        createEventPopup.style.display = 'none';
        popupOverlay.style.display = 'none'; // Hide overlay
        $('#eventForm')[0].reset(); // Reset form on closing
        // Remove the temporary event if it exists
        // if (tempEventId) {
        //     calendar.removeEvent(tempEventId);
        //     tempEventId = null;
        // }
        // Reset the time picker constraints when closing the popup
        startTimePicker.setOptions({maxTime: '20:00'});
        endTimePicker.setOptions({minTime: '08:00'});
    }

    $('#saveEvent').on('click', function () {
        var eventName = $('#event_name').val();
        var eventDate = $('#event_date').val();
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        var description = $('#description').val();
        var maxParticipants = $('#max_participants').val();
        var location = $('#location').val();
        var invitations = $('#invitations').val();

        // Validate the duration of the event (max 2 hours)
        var start = new Date(eventDate + 'T' + startTime);
        if (start < new Date()) {
            showToast('Error: Event start time cannot be in the past.', 'error');
            return;
        }
        var end = new Date(eventDate + 'T' + endTime);
        var durationInHours = (end - start) / 1000 / 60 / 60; // Duration in hours

        if (durationInHours > 2) {
            showToast('Error: Event duration cannot exceed 2 hours.', 'error');
            return;
        }

        // Validate max_participants
        if (maxParticipants < 5) {
            showToast('Error: Maximum participants must be at least 5.', 'error');
            return;
        }

        $.ajax({
            url: '/api/events',
            method: 'POST',
            dataType: 'json',
            data: {
                event_name: eventName,
                event_date: eventDate,
                start_time: startTime,
                end_time: endTime,
                description: description,
                max_participants: maxParticipants,
                location: location,
                invitations: invitations
            },
            success: function (response) {
                if (response && response.id) {
                    showToast("Event created successfully", 'success');
                    calendar.addEvent({
                        id: response.id,
                        title: response.title,
                        start: response.start,
                        end: response.end,
                        description: response.description,
                        max_participants: response.max_participants,
                        location: response.location,
                        created_by: currentUserId
                    });

                    closeCreateEventPopup();
                    window.location.reload();
                } else {
                    console.error("Error: Invalid response format", response);
                }
            },
            error: function (error) {
                console.error("Error creating event:", error.responseJSON.error);
                showToast("Error creating event: " + error.responseJSON.error, 'error');
            }
        });
    });

    $('#cancelEvent').on('click', function () {
        closeCreateEventPopup();
        // if (tempEventId != null) {
        //     calendar.removeEvent(tempEventId);
        //     tempEventId = null;
        // }
    });

    // Function to delete an event
    window.deleteEvent = function (eventId) {
        if (!confirm('Are you sure you want to delete this event?')) {
            return;
        }

        $.ajax({
            url: '/api/events/' + eventId,
            method: 'DELETE',
            success: function (response) {
                console.log("Event deleted successfully");
                calendar.removeEvent(eventId);
                closeEventDetailsPopup();
                window.location.reload();
            },
            error: function (error) {
                console.error("Error deleting event:", error.responseJSON.error);
                showToast("Error deleting event: " + error.responseJSON.error, 'error');
            }
        });
    }

    // Function to send an invitation
    window.sendInvite = function (eventId) {
        var email = $('#invite_email').val();
        if (email) {
            $.ajax({
                url: '/api/events/' + eventId + '/invite',
                method: 'POST',
                dataType: 'json',
                data: {email: email},
                success: function (response) {
                    console.log("Invitation sent successfully");
                    $('#invite_email').val('');
                    showToast("Invitation sent successfully", 'success');
                    closeEventDetailsPopup();
                },
                error: function (error) {
                    console.error("Error sending invitation:", error);
                    showToast("Error sending invitation: " + error.responseJSON.error, 'error');
                }
            });
        } else {
            showToast("Please enter an email address.", 'error');
        }
    }

    window.joinEvent = function (eventId) {
        $.ajax({
            url: '/api/events/join/' + eventId,
            method: 'POST',
            success: async function (response) {
                console.log("Joined event successfully");
                console.log(response.event);
                console.log(eventId);
                await calendar.removeEvent(eventId);
                await calendar.addEvent(response.event);
                closeEventDetailsPopup();
            },
            error: function (error) {
                console.error("Error joining event:", error);
                showToast("Error joining event", 'error');
            }
        });
    };

    window.leaveEvent = function (eventId) {
        $.ajax({
            url: '/api/events/leave/' + eventId,
            method: 'POST',
            success: function (response) {
                console.log("Left event successfully");
                calendar.removeEvent(eventId);
                calendar.addEvent(response.event);
                closeEventDetailsPopup();
            },
            error: function (error) {
                console.error("Error leaving event:", error);
                showToast("Error leaving event", 'error');
            }
        });
    };

    // Format date to YYYY-MM-DD
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [year, month, day].join('-');
    }

    // Format time to HH:mm
    function formatTime(date) {
        var d = new Date(date),
            hours = '' + d.getHours(),
            minutes = '' + d.getMinutes();

        if (hours.length < 2)
            hours = '0' + hours;
        if (minutes.length < 2)
            minutes = '0' + minutes;

        return [hours, minutes].join(':');
    }

    // Function to show a toast notification
    function showToast(message, type = 'success') {
        // Create toast element
        var toast = $(`<div class="toast-message toast-${type}">` + message + '<span class="close-toast">×</span></div>');

        // Append to container
        $('#toast-container').append(toast);

        // Show the toast
        toast.fadeIn(400).delay(3000).fadeOut(400, function () {
            $(this).remove();
        });

        // Close button functionality
        toast.find('.close-toast').click(function () {
            toast.remove();
        });
    }
})