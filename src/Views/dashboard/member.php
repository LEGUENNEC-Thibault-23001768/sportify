<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Accueil</title>
    <link rel="stylesheet" href="/public/css/home.css">
    <link rel="stylesheet" href="css/mobiscroll.javascript.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard">
        <div id="sidebar"></div>
        <main class="main-content">
            <div class="main-section">
                <div class="left-section">
                    <div class="report-controls">
                        <select name="week" id="week-select">
                            <option value="current">Semaine actuelle</option>
                            <option value="previous">Semaine précédente</option>
                        </select>
                    </div>
                    <div class="report-section">
                        <div class="report-card performance-card">
                            <div class="report-title">Dernières performances</div>
                            <div class="report-percentage">+5%</div>
                            <div class="report-value">Squat : 120kg</div>
                            <div class="report-previous">Semaine dernière : 115kg</div>
                        </div>
                        <div class="report-card game-time-card">
                            <div class="report-title">Temps de jeu</div>
                            <div class="report-percentage">+10%</div>
                            <div class="report-value">15 heures</div>
                            <div class="report-previous">Semaine dernière : 13 heures</div>
                        </div>
                        <div class="report-card calories-card">
                            <div class="report-title">Calories brûlées</div>
                            <div class="report-percentage">+20%</div>
                            <div class="report-value">650 Kcal</div>
                            <div class="report-subtitle">Objectif : 1500 Kcal</div>
                        </div>
                    </div>
                </div>
                <div class="progression-section">
                    <div class="task-completion-card">
                        <div class="progression-title">Progression</div>
                        <div class="circle-container">
                            <canvas id="taskCompletionChart"></canvas>
                            <div id="chart-center-text">71%</div>
                        </div>
                    </div>
                </div>
            </div>
            <div mbsc-page class="demo-mobile-day-view">
                <div style="height:1%">
                    <div id="demo-mobile-day-view"></div>
                </div>
            </div>
        </main>
    </div>
<div id="event-form">
    <label for="event-title">Nom</label>
    <input type="text" id="event-title" placeholder="Nom de l'événement">
    <label for="event-description">Description</label>
    <textarea id="event-description" placeholder="Description de l'événement"></textarea>
    
    <div id="color-options">
        <div class="color-option" data-color="#4981d6" style="background-color: #4981d6;"></div>
        <div class="color-option" data-color="#C1FF72" style="background-color: #C1FF72;"></div>
        <div class="color-option" data-color="#ff5e57" style="background-color: #ff5e57;"></div>
        <div class="color-option" data-color="#ffa726" style="background-color: #ffa726;"></div>
    </div>
</div>

    <script src="js/mobiscroll.javascript.min.js"></script>
    <script>
        mobiscroll.setOptions({
            locale: mobiscroll.localeFr,
            theme: 'ios',
            themeVariant: 'dark'
        });
    
        var inst = mobiscroll.eventcalendar('#demo-mobile-day-view', {
            view: {
                schedule: {
                    type: 'week',
                    startTime: '06:00',
                    endTime: '24:00',
                    allDay: false
                }
            },
            scrollable: false,
            dragToCreate: true,
            dragToMove: true,
            dragToResize: true,
            renderEvent: function (event) {
                // Personnalisation de l'affichage des événements
                return {
                    html: `
                        <div>
                            <div class="mbsc-event-title">${event.title}</div>
                            ${event.description ? `<div class="mbsc-event-desc">${event.description}</div>` : ''}
                        </div>
                    `
                };
            },
            onEventClick: function (args) {
                var form = document.getElementById('event-form');
                var calendarBounds = document.getElementById('demo-mobile-day-view').getBoundingClientRect();
                var popupWidth = form.offsetWidth;
                var popupHeight = form.offsetHeight;
    
                let popupX = args.domEvent.clientX;
                let popupY = args.domEvent.clientY;
    
                if (popupX + popupWidth > calendarBounds.right) {
                    popupX = args.domEvent.clientX - popupWidth - 10;
                }
    
                if (popupY + popupHeight > calendarBounds.bottom) {
                    popupY = args.domEvent.clientY - popupHeight - 10;
                }
    
                popupX = Math.max(calendarBounds.left + 10, Math.min(popupX, calendarBounds.right - popupWidth - 10));
                popupY = Math.max(calendarBounds.top + 10, Math.min(popupY, calendarBounds.bottom - popupHeight - 10));
    
                form.style.display = 'block';
                form.style.left = popupX + 'px';
                form.style.top = popupY + 'px';
    
                var titleInput = document.getElementById('event-title');
                var descriptionInput = document.getElementById('event-description');
    
                titleInput.value = args.event.title || '';
                descriptionInput.value = args.event.description || '';
    
                titleInput.oninput = function () {
                    args.event.title = this.value;
                };
    
                descriptionInput.oninput = function () {
                    args.event.description = this.value;
                };
    
                // Sauvegarde et mise à jour quand on appuie sur Entrée
                titleInput.onkeydown = descriptionInput.onkeydown = function (e) {
                    if (e.key === 'Enter') {
                        args.event.title = titleInput.value;
                        args.event.description = descriptionInput.value;
                        inst.updateEvent(args.event);
                        form.style.display = 'none'; // Fermer le popup après sauvegarde
                    }
                };
    
                // Mise à jour des couleurs
                document.querySelectorAll('.color-option').forEach(option => {
                    option.onclick = function () {
                        document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                        this.classList.add('selected');
                        args.event.color = this.getAttribute('data-color');
                        inst.updateEvent(args.event);
                    };
                    option.classList.toggle('selected', option.getAttribute('data-color') === args.event.color);
                });
    
                form.onmousedown = function (e) {
                    if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && !e.target.closest('.color-option')) {
                        e.preventDefault();
                        var offsetX = e.offsetX;
                        var offsetY = e.offsetY;
    
                        function moveAt(pageX, pageY) {
                            form.style.left = Math.max(calendarBounds.left, Math.min(pageX - offsetX, calendarBounds.right - popupWidth)) + 'px';
                            form.style.top = Math.max(calendarBounds.top, Math.min(pageY - offsetY, calendarBounds.bottom - popupHeight)) + 'px';
                        }
    
                        function onMouseMove(event) {
                            moveAt(event.pageX, event.pageY);
                        }
    
                        document.addEventListener('mousemove', onMouseMove);
    
                        form.onmouseup = function () {
                            document.removeEventListener('mousemove', onMouseMove);
                            form.onmouseup = null;
                        };
                    }
                };
            },
            onEventDelete: function () {
                var form = document.getElementById('event-form');
                form.style.display = 'none'; // Cache le popup uniquement lors de la suppression de l'événement
            },
            data: [
                { color: '#4981d6', start: '2024-11-16T08:00', end: '2024-11-16T09:00', title: 'Entraînement cardio', description: 'Séance intense pour améliorer l\'endurance.' },
                { color: '#C1FF72', start: '2024-11-16T12:00', end: '2024-11-16T13:30', title: 'Séance de musculation', description: 'Focus sur les jambes et les épaules.' },
                { color: '#ff5e57', start: '2024-11-16T15:00', end: '2024-11-16T16:00', title: 'Yoga', description: 'Séance de relaxation et étirements.' },
                { color: '#ffa726', start: '2024-11-16T18:00', end: '2024-11-16T19:00', title: 'Boxe', description: 'Entraînement de boxe avec sparring.' }
            ]
        });
    
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('event-form').style.display = 'none';
            var ctx = document.getElementById('taskCompletionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Complet', 'Manqué'],
                    datasets: [
                        {
                            data: [71, 29],
                            backgroundColor: ['rgba(255, 105, 180, 0.8)', '#666'],
                            borderWidth: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    elements: {
                        arc: {
                            borderRadius: 20
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    }
                }
            });
            var chartCanvas = document.getElementById('taskCompletionChart');
            chartCanvas.style.filter = 'drop-shadow(0px 0px 15px rgba(255, 105, 180, 0.7))';
        });
    
        // Supprimer onmouseleave pour éviter que le popup disparaisse en cas de survol
        var form = document.getElementById('event-form');
        form.onmouseleave = null;
    
        document.addEventListener('click', function (e) {
            if (!form.contains(e.target) && !e.target.closest('.mbsc-schedule-event')) {
                form.style.display = 'none';
            }
        });
    
    $.ajax({
        url: 'sidebar.html',
        method: 'GET',
        success: function(data) {
            $('#sidebar').html(data);
        },
        error: function() {
            $('#sidebar').html('<p>Error loading sidebar.</p>');
        }
    });
    </script>
    
    
</body>
</html>