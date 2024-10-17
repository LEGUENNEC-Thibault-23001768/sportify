<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events List</title>
    <link rel="stylesheet" href="/path/to/bootstrap.css">
    <style>
        .event-table {
            width: 100%;
            margin-bottom: 30px;
            border: 1px solid #ddd;
        }
        .event-table th, .event-table td {
            padding: 10px;
            text-align: left;
        }
        .event-table th {
            background-color: #f7f7f7;
        }
        .event-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .event-actions a {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="event-actions">
            <h1>Events List</h1>
            
            <?php if ($member['status'] === 'coach' || $member['status'] === 'admin'): ?>
                <a href="/dashboard/events/create" class="btn btn-primary">Create New Event</a>
            <?php endif; ?>
        </div>

        <!-- Vérifier s'il y a des événements -->
        <?php if (!empty($events)): ?>
            <table class="table table-striped event-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Location</th>
                        <th>Max Participants</th>
                        <th>Participants Registered</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td><?php echo htmlspecialchars($event['max_participants']); ?></td>
                            <td><?php echo htmlspecialchars($event['participants_count']); ?></td>
                            <td><?php echo htmlspecialchars($event['created_by_name']); ?></td>
                            <td>
                                <a href="/dashboard/events/<?php echo $event['event_id']; ?>" class="btn btn-info">View</a>
                                <?php if ((int)$_SESSION['user_id'] == (int)$event['created_by'] || $member['status'] === 'coach' || $member['status'] === 'admin'): ?>
                                    <form action="/dashboard/events/<?php echo $event['event_id']; ?>/delete" method="POST" style="display:inline-block;">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <!-- Message si aucun événement n'est trouvé -->
            <p>No events found.</p>
        <?php endif; ?>
    </div>

    <script src="/path/to/bootstrap.js"></script>
</body>
</html>
