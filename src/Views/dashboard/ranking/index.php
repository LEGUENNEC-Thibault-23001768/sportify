<div data-view="ranking">
    <h1>Classement des utilisateurs par activité</h1>
    <p>Classement des utilisateurs en fonction du temps total passé par sport.</p>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>RPM (heures)</th>
                <th>Musculation (heures)</th>
                <th>Boxe (heures)</th>
                <th>Football (heures)</th>
                <th>Tennis (heures)</th>
                <th>Basketball (heures)</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
           <tr><td colspan="7">Aucun utilisateur trouvé</td></tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                     <td><?= isset($user['total_rpm_time']) ? round($user['total_rpm_time'] /3600,2) : 0 ?></td>
                    <td><?= isset($user['total_musculation_time']) ? round($user['total_musculation_time'] / 3600,2) : 0 ?></td>
                    <td><?= isset($user['total_boxe_time']) ? round($user['total_boxe_time'] / 3600,2) : 0 ?></td>
                    <td><?= isset($user['total_football_time']) ? round($user['total_football_time'] / 3600,2) : 0 ?></td>
                    <td><?= isset($user['total_tennis_time']) ? round($user['total_tennis_time'] / 3600,2) : 0 ?></td>
                    <td><?= isset($user['total_basketball_time']) ? round($user['total_basketball_time'] / 3600,2) : 0 ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th,
.table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f2f2f2;
}

.table tbody tr:hover {
    background-color: #f5f5f5;
}
</style>