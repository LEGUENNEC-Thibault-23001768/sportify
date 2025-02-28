(function() {
    let currentSortField = 'total_rpm_time'; 
    let currentSortOrder = 'desc'; 

    function loadRanking(sortBy = currentSortField, sortOrder = currentSortOrder, sport = 'all') {
        $.ajax({
            url: `/api/ranking?sort_by=${sortBy}&sort_order=${sortOrder}&sport=${sport}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Ranking data:", response);
                displayRanking(response.users);
                currentSortField = sortBy;
                currentSortOrder = sortOrder;
            },
            error: function(xhr, status, error) {
                console.error("Error: " + status + " - " + error);
                alert("Erreur lors du chargement du classement.");
            }
        });
    }
    
    function formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = seconds % 60;

        const formattedHours = String(hours).padStart(2, '0');
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(remainingSeconds).padStart(2, '0');

        return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
    }


    function displayRanking(users) {
        const tableBody = $('#ranking-table-body');
        tableBody.empty();
    
        if (users.length === 0) {
            tableBody.append('<tr><td colspan="5">Aucun utilisateur trouvé</td></tr>');
            return;
        }
    
        users.forEach((user, index) => {
            let row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${user.first_name} ${user.last_name}</td>
                    <td>${Number(user.total_score).toFixed(2)}</td>
                    <td>${Number(user.total_calories).toFixed(2)}</td>
                    <td>${formatTime(user.total_play_time_seconds)}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }

    $(document).ready(function() {
        console.log("Ranking view initialized");
        loadRanking();

        $('#apply-filter').on('click', function() {
            const sport = $('#sport').val();
            const sortBy = $('#sort-column').val();
            const sortOrder = $('input[name="sort-order"]:checked').val();
            loadRanking(sortBy, sortOrder, sport);
        }); 
    });
})();