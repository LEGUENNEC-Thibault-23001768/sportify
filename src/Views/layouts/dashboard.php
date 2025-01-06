<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/_assets/css/dashboard.css">
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
            <li><a href="/dashboard" data-target="dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="/dashboard/suivi" data-target="suivi"><i class="fas fa-chart-line"></i> Suivi </a></li>
            <li><a href="/dashboard/booking" data-target="booking"><i class="fas fa-futbol"></i> Terrains</a></li>
            <li><a href="/dashboard/coaches" data-target="trainers"><i class="fas fa-user-friends"></i> Entraîneurs</a></li>
            <li><a href="/dashboard/events" data-target="events"><i class="fas fa-trophy"></i> Événements</a></li>
            <li><a href="/dashboard/program" data-target="training"><i class="fas fa-calendar"></i> Programme</a></li>
            <li><a href="/dashboard/admin/users" class="management" data-target="admin/users"><i class="fas fa-tasks"></i> Gestion</a></li>
        </ul>
        <div class="settings-section">
            <a href="/dashboard/profile" data-target="profile" class="settings"> Paramètres</a>
            <a href="/logout" class="logout"> Se déconnecter</a>
        </div>
    </div>
    <div class="navbar">
        <div class="logo"></div>
        <div class="profile-info">
            <p class="profile-name"><?= htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']) ?></p>
            <div class="subscription-status">
                <?php if (!isset($hasActiveSubscription) || !$hasActiveSubscription): ?>
                    <form action="/create-checkout-session" method="POST">
                        <button type="submit" class="subscribe-button">S'abonner</button>
                    </form>
                <?php else: ?>
                    <p class="active-subscription">Abonnement actif</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-icon">
            <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://i.pinimg.com/564x/7e/8c/81/7e8c8119bf240d4971880006afb7e1e6.jpg'; ?>" alt="Profil" id="profile-icon">
            <div class="dropdown" id="dropdown">
                <!-- Make "Mon profil" link behave like "Paramètres" -->
                <a href="/dashboard/profile" data-target="profile">Mon profil</a>
                <a href="/logout">Déconnexion</a>
            </div>
        </div>
    </div>
    <div class="dashboard-content" id="dynamic-content">
        <!-- Dynamic content will be loaded here -->
    </div>

    <script>
    $(document).ready(function() {
    const loadedScripts = {};
    const loadedCSS = {};
    const contentCache = {}; // Cache HTML content

    function loadContent(target, href = null) {
        if (href) {
            window.history.pushState({ target: target }, '', href);
        }

        // Check if content is in cache
        if (contentCache[target]) {
            console.log("Content already cached for:", target);
            showContent(target);
            return;
        }

        $.ajax({
            url: '/dashboard/content/' + target,
            method: 'GET',
            success: function(response) {
                // Cache the HTML content
                contentCache[target] = response;

                $('#dynamic-content').html(response);
                const viewContainer = $('#dynamic-content').find('[data-view]');

                if (viewContainer.length > 0) {
                    const view = viewContainer.attr('data-view');
                    console.log("View:", view);

                    // Load CSS (non-blocking)
                    loadCSS('/_assets/css/' + view + '.css');

                    // Load and execute scripts
                    loadScript('/_assets/js/' + view + '.js', function() {
                        if (view === "events_dash") {
                            loadCSS('/_assets/css/mobiscroll.min.css');
                            loadScript('/_assets/js/mobiscroll.min.js', () => {
                                loadScript('/_assets/js/events_dash.js', function() {
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

    function showContent(target) {
        $('#dynamic-content').html(contentCache[target]);
        initialize(); // Re-run initialization for the target view
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
        };
        document.body.appendChild(script);
    }

    function loadCSS(src) {
        if (loadedCSS[src]) {
            console.log('CSS already loaded:', src);
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = src;
        link.onload = function() {
            loadedCSS[src] = true;
            console.log('CSS loaded:', src);
        };
        link.onerror = function() {
            console.error("Error loading CSS:", src);
        };
        document.head.appendChild(link);
    }

    // Sidebar navigation
    $('.sidebar a').click(function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        const href = $(this).attr('href');
        if (target) {
            loadContent(target, href);
        }
    });

    // Handle back/forward navigation
    $(window).on('popstate', function(event) {
        const state = event.originalEvent.state;
        if (state && state.target) {
            loadContent(state.target, window.location.pathname);
        } else {
            loadContent('dashboard', '/dashboard');
        }
    });

    // Initial content load
    const initialPath = window.location.pathname;
    if (initialPath.startsWith('/dashboard/')) {
        const initialContent = initialPath.split('/dashboard/')[1];
        loadContent(initialContent, initialPath);
    } else {
        loadContent('dashboard', '/dashboard');
    }

    // Dropdown profile
    $('#profile-icon').click(function() {
        $('#dropdown').toggle();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest('#profile-icon, #dropdown').length) {
            $('#dropdown').hide();
        }
    });

    // Handle dropdown "Mon profil" link click
    $('#dropdown a[data-target]').click(function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        const href = $(this).attr('href');
        $('#dropdown').hide();
        loadContent(target, href);
    });
});

    </script>
</body>
</html>