(function() {
    let tempEventId = null;
    let calendar;

    async function initCalendar() {
        const calendarData = await getCalendarData();
        mobiscroll.setOptions({
            theme: 'ios',
            themeVariant: 'dark'
        });
        calendar = mobiscroll.eventcalendar('#myCalendar', {
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
            clickToCreate: true,
            dragToCreate: false,
            dragToMove: false,
            dragToResize: false,
            eventDelete: true,
            onEventClick: function(args) {
                if (args.event.type === 'booking') return;
                showEventDetails(args.event);
            },
             onEventDeleted: async function(args) {
                try {
                    await deleteEvent(args.event.id);
                    showToast("Event deleted successfully", 'success');
                    calendar.removeEvent(args.event.id);
                } catch (error) {
                    console.error("Error deleting event:", error);
                    showToast("Error deleting event", 'error');
                }
            },
             eventTemplate: function (event) {
                    if (event.type === 'booking') {
                        return `<div class="md-booking-event-template" style="background-color: ${event.color}; color: white;">
                                <div class="md-booking-event-title">${event.title}</div>
                             </div>`;
                    } else {
                        console.log(event);
                        return `<div class="md-event-template">
                                <div class="md-event-title">${event.title}</div>
                                 <div class="md-event-time">${formatTime(event.start_time)} - ${formatTime(event.end_time)}</div>
                             </div>`;
                    }
                }
        });

        window.openCreateEventPopup = async function() {
            if (window.memberStatus === 'coach' || window.memberStatus === 'admin') {
                $('#event_name').val('');
                $('#event_date').val(formatDate(new Date()));
                $('#start_time').val(formatTime(new Date()));
                $('#end_time').val(formatTime(new Date()));
                $('#description').val('');
                $('#location').val('');
                 $('#location-times').html('');
                 $('#location').prop('selectedIndex', 0).trigger('change');
                // Show the create event popup
                createEventPopup.style.display = 'block';
                popupOverlay.style.display = 'block';

                const location = $('#location').val();
                await fetchLocationTimes(location);
            }
       }

       // Initialize datepicker for event_date
       mobiscroll.datepicker('#event_date', {
           controls: ['calendar'],
            display: 'center',
            dateFormat: 'YYYY-MM-DD',
           onSet: async function (event, inst) {
               const location = $('#location').val();
               await fetchLocationTimes(location, event.valueText);
            }
        });

        // Initialize timepickers with default constraints
        const startTimePicker = mobiscroll.datepicker('#start_time', {
           controls: ['time'],
           display: 'center',
           timeFormat: 'HH:mm',
            minTime: '08:00',
           maxTime: '20:00',
           onSet: function(event, inst) {
                // When start time is selected, update the minTime of end time picker
                endTimePicker.setOptions({
                    minTime: event.valueText
                });
            }
        });
       const endTimePicker = mobiscroll.datepicker('#end_time', {
           controls: ['time'],
           display: 'center',
            timeFormat: 'HH:mm',
           minTime: '08:00',
            maxTime: '20:00',
           onSet: function(event, inst) {
                // When start time is selected, update the maxTime of start time picker
                startTimePicker.setOptions({
                    maxTime: event.valueText
               });
            }
        });
        // Event details popup
        const eventDetailsPopup = document.getElementById('eventDetailsPopup');
        const popupOverlay = document.getElementById('popupOverlay');
        const closeDetailsButton = document.getElementById('closeDetailsButton');
        if (closeDetailsButton) {
            closeDetailsButton.addEventListener('click', closeEventDetailsPopup);
        }
    }
    async function getCalendarData() {
        try {
            const eventsData = await getCachedEvents();
            const bookingsData = (await getBookings()).bookings;
           const formattedBookings = await formatBookings(bookingsData);
           return JSON.parse(eventsData).concat(formattedBookings);
        } catch (error) {
            console.error('Error getting calendar data:', error);
           showToast("Error getting calendar data.", 'error');
           return [];
        }
    }

    async function getCachedEvents() {
        return new Promise((resolve, reject) => {
           $.ajax({
                url: '/api/events',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                   resolve(JSON.stringify(data));
                },
                error: function(error) {
                   console.error("Error fetching events data:", error);
                    showToast("Error fetching events data", 'error');
                   reject(error)
               }
           });
        });
    }

    async function getBookings() {
        return new Promise((resolve, reject) => {
           $.ajax({
               url: '/api/booking',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    resolve(data);
               },
                error: function(error) {
                    console.error("Error fetching bookings data:", error);
                   showToast("Error fetching bookings data", 'error');
                   reject(error)
               }
            });
       });
    }

    async function formatBookings(bookings) {
        return new Promise((resolve) => {
            const formattedBookings = bookings.map(booking => ({
               id: booking.reservation_id,
                title: booking.court_name + ' (Booking)',
                start: booking.reservation_date + 'T' + booking.start_time,
                end: booking.reservation_date + 'T' + booking.end_time,
                color: '#52b788',
                type: 'booking'
           }));
            resolve(formattedBookings)
        });
   }

    let eventDetailsCache = {}

   // Function to show event details
    async function showEventDetails(event) {
        const isAuthorizedToDelete = window.memberStatus === 'coach' || window.memberStatus === 'admin';
       const isEventCreator = event.created_by === window.currentUserId;
        if (event.participants === undefined) event.participants = [];
       try {
            const eventData = await fetchEvent(event.id);
           console.log(eventData);
           populateEventDetailsPopup(eventData, isAuthorizedToDelete, isEventCreator);
        } catch (error) {
           console.error("Error showing event details:", error);
            showToast("Error showing event details", 'error');
        }
   }

   async function fetchEvent(eventId) {
        return new Promise((resolve, reject) => {
            $.ajax({
               url: '/api/events/' + eventId,
                method: 'GET',
               dataType: 'json',
                success: function(eventData) {
                    resolve(eventData);
                },
                error: function(error) {
                   console.error("Error fetching event details:", error);
                   showToast("Error fetching event details", 'error');
                    reject(error);
                }
           });
        });
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
       if (canInvite && eventData.participants && eventData.participants.length > 0) {
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

        const startDate = new Date(eventData.event_date);
        const startTime = new Date(eventData.event_date + 'T' + eventData.start_time);
        const endTime = new Date(eventData.event_date + 'T' + eventData.end_time);

        const eventContent = `
           <h2>${eventData.event_name}</h2>
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

    window.closeCreateEventPopup = function() {
        createEventPopup.style.display = 'none';
       popupOverlay.style.display = 'none'; // Hide overlay
       $('#eventForm')[0].reset(); // Reset form on closing
        // Reset the time picker constraints when closing the popup
       startTimePicker.setOptions({
           maxTime: '20:00'
       });
       endTimePicker.setOptions({
            minTime: '08:00'
        });
       if (tempEventId) {
           calendar.removeEvent(tempEventId);
            tempEventId = null;
        }
    }

    $('#saveEvent').on('click', async function() {
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
       const locationTimes = $('#location-times').data('locationTimes');
        if (locationTimes) {
            const locationStartTime = new Date(eventDate + 'T' + locationTimes.start_time);
            const locationEndTime = new Date(eventDate + 'T' + locationTimes.end_time);
           if (start < locationStartTime || end > locationEndTime) {
                showToast(`Error: Event must be within location reservation times (${formatTime(locationStartTime)} - ${formatTime(locationEndTime)}).`, 'error');
                return;
            }
        }
       const eventData = {
           event_name: eventName,
            event_date: eventDate,
           start_time: startTime,
            end_time: endTime,
            description: description,
            max_participants: maxParticipants,
            location: location,
            invitations: invitations
       };

        $.ajax({
            url: '/api/events',
            method: 'POST',
            dataType: 'json',
            data: eventData,
            success: function(response) {
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
                           created_by: window.currentUserId
                        });
                   closeCreateEventPopup();
                   window.location.reload();
               } else {
                    console.error("Error: Invalid response format", response);
                }
            },
            error: function(error) {
                console.error("Error creating event:", error);
                if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.error) {
                    showToast("Error creating event: " + error.responseJSON.data.error, 'error');
                } else {
                    showToast("Error creating event: " + error, 'error');
                }
            }
        });
   });

    $('#cancelEvent').on('click', function() {
        closeCreateEventPopup();
    });

   // Function to delete an event
   async function deleteEvent(eventId) {
       return new Promise((resolve, reject) => {
           $.ajax({
                url: '/api/events/' + eventId,
               method: 'DELETE',
               success: function(response) {
                    console.log("Event deleted successfully");
                    resolve(response);
               },
               error: function(error) {
                   console.error("Error deleting event:", error.responseJSON.error);
                    showToast("Error deleting event: " + error.responseJSON.error, 'error');
                    reject(error);
                }
            });
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
            data: {
                email: email
            },
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

    window.joinEvent = async function(eventId) {
        try {
            const response = await fetch('/api/events/join/' + eventId, {
                method: 'POST'
           });
           if (!response.ok) {
                console.log(response);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.message) {
               showToast(data.message, 'success');
            }
           await calendar.removeEvent(eventId);
           await calendar.addEvent(data.event);
           closeEventDetailsPopup();
        } catch (error) {
            console.error("Error joining event:", error);
            showToast("Error joining event", 'error');
        }
    };
    window.leaveEvent = async function(eventId) {
        try {
           const response = await fetch('/api/events/leave/' + eventId, {
                method: 'POST'
            });
           if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
           if (data.message) {
               showToast(data.message, 'success');
           }
            await calendar.removeEvent(eventId);
           await calendar.addEvent(data.event);
           closeEventDetailsPopup();
       } catch (error) {
            console.error("Error leaving event:", error);
            showToast("Error leaving event", 'error');
        }
    };
   async function fetchLocationTimes(location, date = null) {
        const eventDate = date ? date : $('#event_date').val();
       if (location && eventDate) {
            try {
               const bookingsData = (await getBookings()).bookings;
                 let booking = bookingsData.find(booking => booking.court_name === location && booking.reservation_date === eventDate);
               if (booking) {
                    $('#location-times').text(`Location Available: ${booking.start_time} - ${booking.end_time}`);
                    $('#location-times').data('locationTimes', booking);
               } else {
                    $('#location-times').text("Location not booked for this date.");
                   $('#location-times').removeData('locationTimes');
                }
            } catch (error) {
                console.error("Error fetching location times:", error);
                showToast("Error fetching location times", 'error');
            }
        } else {
           $('#location-times').text("Please select a location and date");
            $('#location-times').removeData('locationTimes');
       }
    }
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
        if(date instanceof Date){
             var hours = '' + date.getHours();
           var minutes = '' + date.getMinutes();
        }
       else{
           var d = new Date(date);
            var hours = '' + d.getHours();
            var minutes = '' + d.getMinutes();
       }

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
    window.initialize = function() {
      initCalendar();
    };
})();