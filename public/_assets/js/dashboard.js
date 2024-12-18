function initialize() {
    if (document.getElementById('memberStatusChart')) {
        // Initialize member status chart
        var ctxMemberStatus = document.getElementById('memberStatusChart').getContext('2d');
        new Chart(ctxMemberStatus, {
            type: 'pie',
            data: {
                labels: memberStatusData.labels,
                datasets: [{
                    data: memberStatusData.data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
        });
    }

    if (document.getElementById('reservationsByDayChart')) {
        // Initialize reservations by day chart
        var ctxReservationsByDay = document.getElementById('reservationsByDayChart').getContext('2d');
        new Chart(ctxReservationsByDay, {
            type: 'bar',
            data: {
                labels: reservationsData.labels,
                datasets: [{
                    label: 'Nombre de réservations',
                    data: reservationsData.data,
                    backgroundColor: 'rgba(153, 102, 255, 0.5)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }   
}