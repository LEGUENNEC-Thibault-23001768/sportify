<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/_assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Check if jQuery is loaded before using it
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            // You can try loading it here or display an error message to the user
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="https://i.postimg.cc/wTWZmp2r/Sport-400-x-250-px-300-x-100-px-2.png" alt="Logo Sportify">
        </div>
        <ul>
            <!-- Updated data-target attributes -->
            <li><a href="#" data-target="dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="#" data-target="suivi"><i class="fas fa-chart-line"></i> Suivi </a></li>
            <li><a href="#" data-target="booking"><i class="fas fa-futbol"></i> Terrains</a></li>
            <li><a href="#" data-target="trainers"><i class="fas fa-user-friends"></i> Entraîneurs</a></li>
            <li><a href="#" data-target="events"><i class="fas fa-trophy"></i> Événements</a></li>
            <li><a href="#" data-target="training"><i class="fas fa-calendar"></i> Programme</a></li>
            <li><a href="#" class="management" data-target="admin/users"><i class="fas fa-tasks"></i> Gestion</a></li>
        </ul>
        <div class="settings-section">
            <a href="/dashboard/profile" data-target="profile" class="settings"> Paramètres</a>
            <a href="/logout" class="logout"> Se déconnecter</a>
        </div>
    </div>
    <div class="navbar">
        <div class="logo"></div>
        <p class="profile-name">Jack OWO</p>
        <div class="profile-icon">
            <img src="uploads/profile_pictures/675bf6cc8c469_index.jpeg" alt="Profil" id="profile-icon">
            <div class="dropdown" id="dropdown">
                <a href="/dashboard/profile">Mon profil</a>
                <a href="/logout">Déconnexion</a>
            </div>
        </div>
    </div>

    <div class="dashboard-content" id="dynamic-content">
        <!-- Dynamic content will be loaded here -->
    </div>

    <script>
        $(document).ready(function() {
            $('.sidebar a').click(function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                if (target) {
                    loadContent(target);
                }
            });

            const loadedScripts = {};
            const loadedCSS = {};

            function loadContent(content) {
                $.ajax({
                    url: '/dashboard/content/' + content,
                    method: 'GET',
                    success: function(response) {
                        $('#dynamic-content').html(response);
            
                        const viewContainer = $('#dynamic-content').find('[data-view]');
            
                        if (viewContainer.length > 0) {
                            const view = viewContainer.attr('data-view'); 
                            console.log("View:", view);
            
                            // Load the script and CSS if the view is defined
                            loadScript('/_assets/js/' + view + '.js', function() {
                                if (view == "events_dash") {
                                    loadCSS('/_assets/css/mobiscroll.min.css');
                                    loadScript('/_assets/js/mobiscroll.min.js', () => {
                                        loadScript('/_assets/js/events_dash.js', function() {
                                            if (typeof initialize === 'function') {
                                                initialize();
                                            }
                                        });
                                    });
                                } else if (view == "trainers") {
                                    loadCSS('/_assets/css/mobiscroll.min.css');
                                    loadScript('/_assets/js/mobiscroll.min.js', () => {
                                        loadScript('/_assets/js/trainers.js', function() {
                                            if (typeof initialize === 'function') {
                                                initialize();
                                            }
                                        });
                                    });
                                } else {
                                    
                                    if (typeof initialize === 'function') {
                                        initialize();
                                    }
                                } 
                            });
                            loadCSS('/_assets/css/' + view + '.css');
                        } else {
                            console.warn("No data-view attribute found for this content.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error: " + status + " - " + error);
                        $('#dynamic-content').html("<p>Error loading content.</p>");
                    }
                });
            }

            function loadScript(src, cb) {
                if (loadedScripts[src]) {
                    console.log('Script already loaded:', src);
                    if (typeof cb === 'function') {
                        cb();
                    }
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;

                script.onload = function() {
                    loadedScripts[src] = true;
                    console.log('Script loaded:', src);

                    if (typeof cb === 'function') {
                        cb();
                    }
                };

                script.onerror = function() {
                    console.error("Error loading script:", src);
                    delete loadedScripts[src];
                };

                document.body.appendChild(script);
            }

            function loadCSS(src, cb) {
                if (loadedCSS[src]) {
                    console.log('CSS already loaded:', src);
                    if (typeof cb === 'function') {
                        cb();
                    }
                    return;
                }

                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = src;

                link.onload = function() {
                    loadedCSS[src] = true;
                    console.log('CSS loaded:', src);

                    if (typeof cb === 'function') {
                        cb();
                    }
                };

                link.onerror = function() {
                    console.error("Error loading CSS:", src);
                    delete loadedCSS[src];
                };

                document.head.appendChild(link);
            }

            

            loadContent('dashboard');
        });
    </script>
</body>
</html>