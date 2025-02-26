<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/_assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/_assets/css/mobiscroll.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/_assets/js/mobiscroll.min.js?v=<?= time() ?>"></script>
    <script>
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded. Please ensure it is included in your HTML.');
        }
    </script>
    <style>
        .fade-out {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        .fade-in {
            opacity: 1;
            transition: opacity 0.2s ease-in-out;
        }

        .subscription-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
            pointer-events: none;
        }

        .subscription-message {
            background: #161616;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            pointer-events: auto;
        }

        .subscription-message button {
            margin-top: 20px;
        }

        .subscription-message .logout-button {
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="https://i.postimg.cc/8k0whVFw/Sport-400-x-250-px-300-x-100-px-removebg-preview.png"
             alt="Logo Sportify">
    </div>
    <ul>
        <li><a href="/dashboard" data-target="dashboard"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="/dashboard/stats" data-target="stats"><i class="fas fa-chart-line"></i> Stats </a></li>
        <li><a href="/dashboard/booking" data-target="booking"><i class="fas fa-futbol"></i> Terrains</a></li>
        <li><a href="/dashboard/trainers" data-target="trainers"><i class="fas fa-user-friends"></i> Entraîneurs</a>
        </li>
        <li><a href="/dashboard/events" data-target="events"><i class="fas fa-trophy"></i> Événements</a></li>
        <li><a href="/dashboard/training" data-target="training"><i class="fas fa-calendar"></i> Programme</a></li>
           <li><a href="/dashboard/ranking" data-target="ranking"><i class="fas fa-list-ol"></i> Classement</a></li>
        <?php if ($user['status'] === 'admin'): ?>
            <li><a href="/dashboard/admin/users" class="management" data-target="admin/users"><i
                            class="fas fa-tasks"></i> Gestion</a></li>
        <?php endif; ?>
    </ul>
    <div class="settings-section">
        <a href="/dashboard/profile" data-target="profile" class="settings"><i class="fas fa-cog"></i> Paramètre</a>
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
        <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://i.pinimg.com/564x/7e/8c/81/7e8c8119bf240d4971880006afb7e1e6.jpg'; ?>"
             alt="Profil" id="profile-icon">
        <div class="dropdown" id="dropdown">
            <a href="/dashboard/profile" data-target="profile">Mon profil</a>
            <a href="/logout">Déconnexion</a>
        </div>
    </div>
</div>
<?php if (!isset($hasActiveSubscription) || !$hasActiveSubscription): ?>
    <div class="subscription-overlay" id="subscriptionOverlay">
        <div class="subscription-message">
            <i class="fas fa-lock" style="font-size: 5em; margin-bottom: 20px;"></i>
            <h2>Votre accès est bloqué.</h2>
            <p>Veuillez souscrire à un abonnement pour accéder à toutes les fonctionnalités.</p>
            <form action="/create-checkout-session" method="POST">
                <button type="submit" class="subscribe-button">S'abonner</button>
            </form>
        </div>
    </div>
<?php endif; ?>
<div class="dashboard-content" id="dynamic-content">
    <?php if (isset($dataView)): ?>
        <?php echo \Core\View::render($dataView, $viewData ?? []); ?>
    <?php endif; ?>
</div>
<script>
    $(document).ready(function () {
        window.currentUserId = <?php echo isset($user['member_id']) ? $user['member_id'] : 'null'; ?>;
        window.memberStatus = "<?php echo isset($user['status']) ? $user['status'] : ''; ?>";
        window.userName = "<?php echo isset($user["first_name"]) ? $user['first_name'] : "" ?>";
        const loadedScripts = {};
        const loadedCSS = {};
        const contentCache = {};
        let currentView = null;
        const dashboardCSS = "/_assets/css/dashboard.css";
        const mobiscrollCSS = "/_assets/css/mobiscroll.min.css";
        const mobiscrollJS = "/_assets/js/mobiscroll.min.js"

        function loadContent(target, href = null) {
            if (href) {
                window.history.pushState({target: target}, '', href);
            }
            const dynamicContent = $('#dynamic-content');
            dynamicContent.addClass('fade-out');

            if (contentCache[target]) {
                console.log("Content already cached for:", target);
                unloadPreviousAssets();
                showCachedContent(target);
                highlightSidebar(target);
                return;
            }

            $.ajax({
                url: '/ajax/dashboard/' + target,
                method: 'GET',
                success: function (response) {
                    contentCache[target] = response;
                    const viewContainer = $(response).filter('[data-view]');
                    if (viewContainer.length > 0) {
                        const view = viewContainer.attr('data-view');
                        console.log("View:", view);
                        unloadPreviousAssets();
                        loadAssetsForView(view, function () {
                            showContent(target, response);
                            if (typeof initialize === 'function') {
                                initialize();
                            }
                        });
                    } else {
                        console.warn("No data-view attribute found for this content.");
                        showContent(target, response);
                    }
                    highlightSidebar(target);
                },
                error: function (xhr, status, error) {
                    console.error("Error: " + status + " - " + error);
                    dynamicContent.html("<p>Error loading content.</p>");
                    dynamicContent.addClass('fade-in');
                }
            });
        }

        function loadAssetsForView(view, callback) {
            const cssPath = '/_assets/css/' + view + '.css?v=' + new Date().getTime();
            const jsPath = '/_assets/js/' + view + '.js?v=' + new Date().getTime();

            let cssLoaded = false;
            let jsLoaded = false;

            function checkComplete() {
                if (cssLoaded && jsLoaded) {
                    callback();
                }
            }

            loadCSS(cssPath, function () {
                cssLoaded = true;
                checkComplete();
            });
            loadScript(jsPath, function () {
                jsLoaded = true;
                checkComplete();
            });
        }


        function unloadPreviousAssets() {
            $('link[href^="/_assets/css/"]').each(function () {
                const href = $(this).attr('href');
                if (href !== dashboardCSS && href !== mobiscrollCSS) {
                    $(this).remove();
                    if (loadedCSS.hasOwnProperty(href)) {
                        loadedCSS[href] = false;
                    }
                }
            });
            $('script[src^="/_assets/js/"]').each(function () {
                const src = $(this).attr('src');
                if (src !== mobiscrollJS) {
                    $(this).remove();
                    if (loadedScripts.hasOwnProperty(src)) {
                        loadedScripts[src] = false;
                    }
                }
            });
        }


        function showCachedContent(target) {
            const viewContainer = $(contentCache[target]).filter('[data-view]');
            if (viewContainer.length > 0) {
                const view = viewContainer.attr('data-view');
                loadAssetsForView(view, function () {
                    $('#dynamic-content').html(contentCache[target]);
                    $('#dynamic-content').addClass('fade-in');
                    if (typeof initialize === 'function') {
                        initialize();
                    }
                });
            } else {
                $('#dynamic-content').html(contentCache[target]);
                $('#dynamic-content').addClass('fade-in');
                console.warn("No data-view attribute found for this content.");
            }
        }


        function showContent(target, response) {
            const dynamicContent = $('#dynamic-content');
            dynamicContent.html(response);
            dynamicContent.addClass('fade-in');
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
            script.onload = function () {
                loadedScripts[src] = true;
                console.log('Script loaded:', src);
                if (typeof cb === 'function') {
                    cb();
                }
            };
            script.onerror = function () {
                console.error("Error loading script:", src);
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
            link.onload = function () {
                loadedCSS[src] = true;
                console.log('CSS loaded:', src);
                if (typeof cb === 'function') {
                    cb();
                }
            };
            link.onerror = function () {
                console.error("Error loading CSS:", src);
            };
            document.head.appendChild(link);
        }

        function highlightSidebar(target) {
            $('.sidebar a').removeClass('selected');
            $('.sidebar a[data-target="' + target + '"]').addClass('selected');
            $('.dropdown a[data-target="' + target + '"]').addClass('selected');
        }

        $('.sidebar a').click(function (e) {
            e.preventDefault();
            const target = $(this).data('target');
            const href = $(this).attr('href');
            if (target) {
                loadContent(target, href);
            } else {
                window.location.href = $(this).attr('href');
            }
        });

        $(window).on('popstate', function (event) {
            const state = event.originalEvent.state;
            if (state && state.target) {
                loadContent(state.target, window.location.pathname);
            } else {
                loadContent('dashboard', '/dashboard');
            }
        });

        const initialPath = window.location.pathname;
        let initialContent = null;
        if (initialPath.startsWith('/dashboard/')) {
            initialContent = initialPath.split('/dashboard/')[1];
        } else {
            initialContent = 'dashboard';
        }
        loadContent(initialContent, initialPath);

        $('#profile-icon').click(function () {
            $('#dropdown').toggle();
        });

        $(document).click(function (event) {
            if (!$(event.target).closest('#profile-icon, #dropdown').length) {
                $('#dropdown').hide();
            }
        });

        $('#dropdown a[data-target]').click(function (e) {
            e.preventDefault();
            const target = $(this).data('target');
            const href = $(this).attr('href');
            $('#dropdown').hide();
            loadContent(target, href);
        });

        <?php if (!isset($hasActiveSubscription) || !$hasActiveSubscription): ?>
        $('#subscriptionOverlay').show();
        <?php endif; ?>
    });
</script>
</body>
</html>