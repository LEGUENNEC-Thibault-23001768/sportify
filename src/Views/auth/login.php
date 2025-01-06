<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion / Inscription</title>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="_assets/css/login.css">
</head>
<body>
<?php
  $errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : $error ?? "";
  $successMessage = isset($message) ? $message : '';
?>
  <div class="login-register-container">
    <div class="form-container">
      <?php if ($errorMessage): ?>
        <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
      <?php endif; ?>

      <?php if ($successMessage): ?>
        <p class="success-message"><?= htmlspecialchars($successMessage) ?></p>
      <?php endif; ?>
      <h1 id="form-title">Rejoignez <span>Sportify</span></h1>
      <form id="login-form" action="/login" method="POST">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
        </div>
        
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
        </div>

        <div class="forgot-password">
          <a href="/forgot-password">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="login-btn">Connexion</button>

        <div class="divider">
          <span>OU</span>
        </div>

        <button class="google-btn">
          <img src="https://img.icons8.com/color/16/000000/google-logo.png" alt="Google Logo">
          Se connecter avec Google
        </button>
        
        <p id="toggle-form">Vous n'avez pas de compte ? <a onclick="toggleForm()">Inscrivez-vous</a></p>
      </form>

      <form id="register-form" action="/register" method="POST" style="display:none;">
        <div class="form-group">
          <label for="register-email">Email</label>
          <input type="email" id="register-email" name="email" placeholder="Entrez votre email" required>
        </div>
        
        <div class="form-group">
          <label for="register-password">Mot de passe</label>
          <input type="password" id="register-password" name="password" placeholder="Entrez votre mot de passe" required>
        </div>

        <div class="form-group">
          <label for="confirm-password">Confirmez le mot de passe</label>
          <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
        </div>

        <button type="submit" class="login-btn">S'inscrire</button>

        <div class="divider">
          <span>OU</span>
        </div>

        <button class="google-btn">
          <img src="https://img.icons8.com/color/16/000000/google-logo.png" alt="Google Logo">
          S'inscrire avec Google
        </button>
        
        <p id="toggle-form-back">Vous avez déjà un compte ? <a onclick="toggleForm()">Connectez-vous</a></p>
      </form>
    </div>

    <div class="image-container">
      <img src="https://i.postimg.cc/yN23RqfV/pixelcut-export-1.png" alt="Image de fond" class="background-img">
    </div>
  </div>

  <script src="_assets/js/main.js"></script>
</body>
</html>
