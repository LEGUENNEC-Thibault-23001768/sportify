<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <link rel="stylesheet" href="/path/to/bootstrap.css"> 
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            margin-top: 50px;
        }
        .form-container h1 {
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Create New Event</h1>

            <form action="/dashboard/events/store" method="POST">
                <div class="form-group">
                    <label for="event_name">Event Name</label>
                    <input type="text" name="event_name" id="event_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" name="event_date" id="event_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label for="max_participants">Max Participants</label>
                    <input type="number" name="max_participants" id="max_participants" class="form-control" required>
                </div>

                <!-- Nouveau champ pour le lieu de l'événement -->
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Event</button>
            </form>

        </div>
    </div>

    <script src="/path/to/bootstrap.js"></script>
</body>
</html>
