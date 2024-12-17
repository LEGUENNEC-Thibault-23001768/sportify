$(document).ready(function() {
    mobiscroll.setOptions({
        theme: 'ios',
        themeVariant: 'dark'
    });

    var eventsData = JSON.parse(getCachedEvents());
    var calendar = mobiscroll.eventcalendar('#myCalendar', {
        view: {
            schedule: { 
                type: 'week',
                startTime: '08:00',
                endTime: '20:00',
             }
        },
        data: eventsData,
        clickToCreate: 'double',
        dragToCreate: true,
        dragToMove: true,
        dragToResize: true,
        eventDelete: true,
        onEventClick: function (args) {
            showEventDetails(args.event);
        },
        onEventCreated: function (args) {
            var newEvent = args.event;
            // Check if the event is in the past
            if (new Date(newEvent.end) < new Date()) {
                calendar.removeEvent(newEvent);
                showToast('Cannot create events in the past.', 'error');
                return;
            }
            
            tempEventId = newEvent.id; // Store the ID of the newly created event
            // Check if the user is authorized to create events
            if (memberStatus === 'coach' || memberStatus === 'admin') {
                // Pre-fill the form fields with event data
                $('#event_name').val(newEvent.title || '');
                $('#event_date').val(formatDate(newEvent.start));
                $('#start_time').val(formatTime(newEvent.start));
                $('#end_time').val(formatTime(newEvent.end));
                $('#description').val(newEvent.description || '');
                $('#location').val(newEvent.location || '');

                // Show the create event popup
                createEventPopup.style.display = 'block';
                popupOverlay.style.display = 'block';
            }
        },
        onEventDeleted: function (args) {
            // Handle event deletion via AJAX
            $.ajax({
                url: '/api/events/' + args.event.id,
                method: 'DELETE',
                success: function(response) {
                    console.log("Event deleted successfully");
                },
                error: function(error) {
                    console.error("Error deleting event:", error);
                    showToast("Error deleting event", 'error');
                }
            });
        }
    });
    // Initialize datepicker for event_date
    mobiscroll.datepicker('#event_date', {
        controls: ['calendar'],
        display: 'center',
        dateFormat: 'YYYY-MM-DD',
    });

    // Initialize timepickers with default constraints
    var startTimePicker = mobiscroll.datepicker('#start_time', {
        controls: ['time'],
        display: 'center',
        timeFormat: 'HH:mm',
        minTime: '08:00',
        maxTime: '20:00',
        onSet: function(event, inst) {
            // When start time is selected, update the minTime of end time picker
            endTimePicker.setOptions({ minTime: event.valueText });
        }
    });

    var endTimePicker = mobiscroll.datepicker('#end_time', {
        controls: ['time'],
        display: 'center',
        timeFormat: 'HH:mm',
        minTime: '08:00',
        maxTime: '20:00',
        onSet: function(event, inst) {
            // When end time is selected, update the maxTime of start time picker
            startTimePicker.setOptions({ maxTime: event.valueText });
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

    function getCachedEvents() {
        $.ajax({
            url: '/api/events',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                localStorage.setItem('eventsData', JSON.stringify(data));
                calendar.setEvents(data);
            },
            error: function(error) {
                console.error("Error fetching events data:", error);
                showToast("Error fetching events data", 'error');
            }
        });
        return localStorage.getItem('eventsData');
    }


    let eventDetailsCache = {}
    eventsData = JSON.parse(getCachedEvents());

    // Function to show event details
    function showEventDetails(event) {
        const isAuthorizedToDelete = memberStatus === 'coach' || memberStatus === 'admin';
        const isEventCreator = event.created_by === currentUserId;

        if (eventDetailsCache[event.id]) {
            populateEventDetailsPopup(eventDetailsCache[event.id], isAuthorizedToDelete, isEventCreator);
        } else {
            $.ajax({
                url: '/api/events/' + event.id,
                method: 'GET',
                dataType: 'json',
                success: function(eventData) {
                    // Cache the fetched event details
                    eventDetailsCache[event.id] = eventData;
                    populateEventDetailsPopup(eventData, isAuthorizedToDelete, isEventCreator);
                },
                error: function(error) {
                    console.error("Error fetching event details:", error);
                    showToast("Error fetching event details", 'error');
                }
            });
        }
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

        const eventContent = `
            <h2>${eventData.event_name}</h2>
            <p><strong>Date:</strong> ${eventData.event_date}</p>
            <p><strong>Start Time:</strong> ${eventData.start_time}</p>
            <p><strong>End Time:</strong> ${eventData.end_time}</p>
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

    function closeCreateEventPopup() {
        createEventPopup.style.display = 'none';
        popupOverlay.style.display = 'none'; // Hide overlay
        $('#eventForm')[0].reset(); // Reset form on closing
        // Remove the temporary event if it exists
        if (tempEventId) {
            calendar.removeEvent(tempEventId);
            tempEventId = null;
        }
        // Reset the time picker constraints when closing the popup
        startTimePicker.setOptions({ maxTime: '20:00' });
        endTimePicker.setOptions({ minTime: '08:00' });
    }

    $('#saveEvent').on('click', function() {
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
            success: function(response) {
                if (response && response.id) {
                    calendar.addEvent({
                        id: response.id,
                        title: response.title,
                        start: response.start,
                        end: response.end,
                        description: response.description,
                        location: response.location
                    });
                    
                    closeCreateEventPopup();
                    location.reload();
                } else {
                    console.error("Error: Invalid response format", response);
                }
            },
            error: function(error) {
                console.error("Error creating event:", error);
                showToast("Error creating event: " + error.responseJSON.error, 'error');
            }
        });
    });

    $('#cancelEvent').on('click', function() {
        closeCreateEventPopup();
        if (tempEventId) {
            calendar.removeEvent(tempEventId);
            tempEventId = null;
        }
    });

    // Function to delete an event
    window.deleteEvent = function(eventId) {
        if (!confirm('Are you sure you want to delete this event?')) {
            return;
        }

        $.ajax({
            url: '/api/events/' + eventId,
            method: 'DELETE',
            success: function(response) {
                console.log("Event deleted successfully");
                calendar.removeEvent(eventId);
                closeEventDetailsPopup();
                location.reload();
            },
            error: function(error) {
                console.error("Error deleting event:", error);
                showToast("Error deleting event: " + error.responseJSON.error, 'error');
            }
        });
    }

    // Function to send an invitation
    window.sendInvite = function(eventId) {
        var email = $('#invite_email').val();
        if (email) {
            $.ajax({
                url: '/api/events/' + eventId + '/invite',
                method: 'POST',
                dataType: 'json',
                data: { email: email },
                success: function(response) {
                    console.log("Invitation sent successfully");
                    $('#invite_email').val('');
                    showToast("Invitation sent successfully", 'success');
                    closeEventDetailsPopup();
                },
                error: function(error) {
                    console.error("Error sending invitation:", error);
                    showToast("Error sending invitation: " + error.responseJSON.error, 'error');
                }
            });
        } else {
            showToast("Please enter an email address.", 'error');
        }
    }

    window.joinEvent = function(eventId) {
        $.ajax({
            url: '/api/events/join/' + eventId,
            method: 'POST',
            success: function(response) {
                console.log("Joined event successfully");
                calendar.removeEvent(eventId);
                calendar.addEvent(response.event);
                closeEventDetailsPopup();
            },
            error: function(error) {
                console.error("Error joining event:", error);
                showToast("Error joining event", 'error');
            }
        });
    };

    window.leaveEvent = function(eventId) {
        $.ajax({
            url: '/api/events/leave/' + eventId,
            method: 'POST',
            success: function(response) {
                console.log("Left event successfully");
                calendar.removeEvent(eventId);
                calendar.addEvent(response.event);
                closeEventDetailsPopup();
            },
            error: function(error) {
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
        toast.fadeIn(400).delay(3000).fadeOut(400, function() {
            $(this).remove();
        });

        // Close button functionality
        toast.find('.close-toast').click(function() {
            toast.remove();
        });
    }
})