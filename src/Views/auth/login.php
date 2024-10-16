<link rel="stylesheet" href="../assets/login.css">

<?php
session_start(); 

if (isset($_SESSION['error_message'])) {
    echo '<p class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']);
}

if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/signup" method="post" id="signup-form">
    <h2>Sign Up</h2>
    
    <section class="eleForm">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </section>
    
    <section class="eleForm">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </section>
    
    <section class="eleForm">
        <label for="confirm_password">Confirm password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </section>
    
    <button type="submit">Sign Up</button>

    <section class="footer-text">
        <p>Already have an account? <a href="#" class="login-link">Login</a></p>
    </section>
</form>

<form action="/login" method="post" id="login-form" style="display: none;">
    <h2>Log In</h2>
    
    <section class="eleForm">
        <label for="login-email">Email:</label>
        <input type="email" id="login-email" name="email" required>
    </section>
    
    <section class="eleForm">
        <label for="login-password">Password:</label>
        <input type="password" id="login-password" name="password" required>
    </section>
    <section class="eleForm">
        <label for="confirm_password">Confirm password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </section>

    <button type="submit">Log In</button>

    <section class="footer-text">
        <p>Don't have an account? <a href="#" class="sign-up-link">Sign Up</a></p>
    </section>
    
    <section class="forgot-password-wrapper">
        <a href="#" class="forgot-password">Forgot password?</a>
    </section>
</form>

<script>
    const showSignup = document.querySelector('.sign-up-link');
    const showLogin = document.querySelector('.login-link');
    const signupForm = document.getElementById('signup-form');
    const loginForm = document.getElementById('login-form');

    showSignup.addEventListener('click', function(e) {
        e.preventDefault();
        loginForm.style.display = 'none';
        signupForm.style.display = 'block';
    });

    showLogin.addEventListener('click', function(e) {
        e.preventDefault();
        signupForm.style.display = 'none';
        loginForm.style.display = 'block';
    });
</script>

