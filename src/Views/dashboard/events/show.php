<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212;
            color: #eee;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #222;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }
        h1, h2, h3 {
            color: #eee;
        }
        h1 {
            margin-bottom: 20px;
            border-bottom: 2px solid #555;
            padding-bottom: 10px;
        }
        .event-details {
            margin-bottom: 30px;
        }
        .event-details p {
            margin: 8px 0;
            line-height: 1.6;
        }
        .event-details strong {
            color: #999;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            border-bottom: 1px solid #555;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .list {
            list-style: none;
            padding: 0;
        }
        .list-item {
            background-color: #333;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        .actions {
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #555;
            color: #eee;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #777;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>

        <div class="event-details">
            <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
            <p><strong>Start Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?></p>
            <p><strong>End Time:</strong> <?php echo htmlspecialchars($event['end_time']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            <p><strong>Max Participants:</strong> <?php echo htmlspecialchars($event['max_participants']); ?></p>
            <p><strong>Created By:</strong> <?php echo htmlspecialchars( \Models\User::getUserById($event['created_by'])['first_name'] . ' ' . \Models\User::getUserById($event['created_by'])['last_name']); ?></p>
        </div>

        <div class="section">
            <h2 class="section-title">Participants</h2>
            <?php if (!empty($participants)) : ?>
                <ul class="list">
                    <?php foreach ($participants as $participant) : ?>
                        <li class="list-item">
                            <?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No participants yet.</p>
            <?php endif; ?>
        </div>

        <?php if ($member['status'] === 'coach' || $member['status'] === 'admin' || $currentUserId == $event['created_by']) : ?>
            <div class="section">
                <h2 class="section-title">Invited Users</h2>
                <?php if (!empty($invitations)) : ?>
                    <ul class="list">
                        <?php foreach ($invitations as $invitation) : ?>
                            <li class="list-item">
                                <?php echo htmlspecialchars($invitation['email']); ?> (Sent: <?php echo date('Y-m-d H:i:s', strtotime($invitation['created_at'])); ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No users invited yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <?php if ($member['status'] === 'coach' || $member['status'] === 'admin' || $currentUserId == $event['created_by']) : ?>
                <a href="/dashboard/events/<?php echo $event['event_id']; ?>/invite" class="btn btn-success">Invite Users</a>
            <?php endif; ?>
            <a href="/dashboard/events" class="btn">Back to Events</a>
            <?php if ($currentUserId == $event['created_by'] || $member['status'] === 'coach' || $member['status'] === 'admin') : ?>
                <form action="/dashboard/events/<?php echo $event['event_id']; ?>/delete" method="POST" style="display:inline-block;">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">Delete Event</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>