<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite to Event</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212; /* Dark background */
            color: #eee; /* Light text */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #222; /* Slightly lighter container background */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4); /* Subtle shadow */
        }
        .form-container {
            padding: 20px;
            border-radius: 8px;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #eee;
            border-bottom: 2px solid #555; /* Underline for heading */
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #999; /* Slightly muted label color */
        }
        input[type="email"], .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #555; /* Darker border for input */
            background-color: #333; /* Darker input background */
            color: #eee;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-primary, .btn-danger {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            color: #eee;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #007bff; /* Blue primary button */
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        .btn-danger {
            background-color: #dc3545; /* Red danger button */
        }
        .btn-danger:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #5a202c;
            border: 1px solid #721c24;
            color: #f8d7da;
        }
        .alert-success {
            background-color: #284228;
            border: 1px solid #28a745;
            color: #d4edda;
        }
        .invitations-list {
            margin-top: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #555;
        }
        .table th {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Invite to Event: <?php echo htmlspecialchars($event['event_name']); ?></h1>
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="/dashboard/events/<?php echo $event['event_id']; ?>/invite" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Send Invitation</button>
            </form>

            <?php if (!empty($invitations)) : ?>
                <div class="invitations-list">
                    <h2>Sent Invitations</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Sent At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invitations as $invitation) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($invitation['email']); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($invitation['created_at'])); ?></td>
                                    <td>
                                        <form action="/dashboard/invitations/<?php echo $invitation['invitation_id']; ?>/delete" method="POST">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this invitation?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>