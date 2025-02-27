const login_btn = document.querySelector('.login-btn-acc');

const redirect = (url) => {
  window.location.href = window.location.origin + url;
}

login_btn.addEventListener('click', () => {
  redirect('/login')
})

function toggleForm() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const title = document.getElementById('form-title');
    const subtitle = document.getElementById('form-subtitle');
    
    if (loginForm.style.display === "none") {
      loginForm.style.display = "block";
      registerForm.style.display = "none";
      title.innerText = "Rejoignez ";
      title.innerHTML += "<span>Sportify</span>";
      subtitle.innerText = "Inscrivez-vous dès aujourd'hui et accédez à un monde de fitness, bien-être et plus encore.";
    } else {
      loginForm.style.display = "none";
      registerForm.style.display = "block";
      title.innerText = "Créer un compte ";
      title.innerHTML += "<span>Sportify</span>"
      subtitle.innerText = "Créez un compte et accédez à toutes nos fonctionnalités.";
    }
}


const google_btn = document.querySelector(".google-btn")
console.log(google_btn)
if (google_btn) {
  google_btn.addEventListener("click", (e) => {
    e.preventDefault();
    redirect("/google");
  })
}
window.addEventListener("load", (e) => {
})

