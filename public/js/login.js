function toggleForm() {
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');
    var title = document.getElementById('form-title');
    var subtitle = document.getElementById('form-subtitle');
    
    if (loginForm.style.display === "none") {
      loginForm.style.display = "block";
      registerForm.style.display = "none";
      title.innerText = "Rejoignez Sportify";
      subtitle.innerText = "Inscrivez-vous dès aujourd'hui et accédez à un monde de fitness, bien-être et plus encore.";
    } else {
      loginForm.style.display = "none";
      registerForm.style.display = "block";
      title.innerText = "Créer un compte Sportify";
      subtitle.innerText = "Créez un compte et accédez à toutes nos fonctionnalités.";
    }
  }
  