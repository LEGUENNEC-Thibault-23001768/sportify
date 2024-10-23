fetch('footer.html')
  .then(response => response.text())
  .then(data => {
      document.getElementById('footer-placeholder').innerHTML = data;
      configureFooterLinks();
  })
  .catch(error => console.error('Erreur lors du chargement du footer :', error));



function togglePassword(inputId, toggleIcon) {
    const passwordInput = document.getElementById(inputId);
    const isPasswordVisible = passwordInput.type === "password";
    passwordInput.type = isPasswordVisible ? "text" : "password";

    toggleIcon.textContent = isPasswordVisible ? '👁️' : '🔒'; 
}

const passwordInput = document.getElementById('new-password');
const confirmPasswordInput = document.getElementById('confirm-password');
const constraintsList = document.getElementById('password-constraints-list');
const togglePasswordIcons = document.querySelectorAll('.toggle-password');
const submitBtn = document.getElementById('submit-btn');
const form = document.querySelector('.reset-password-form');
const errorMessage = document.createElement('div');
errorMessage.id = 'error-message';
errorMessage.style.color = 'red';
errorMessage.style.marginTop = '5px';
errorMessage.style.display = 'none';
confirmPasswordInput.parentElement.insertBefore(errorMessage, confirmPasswordInput.nextSibling);

const constraintItems = {
    length: document.getElementById('length-constraint'),
    uppercase: document.getElementById('uppercase-constraint'),
    lowercase: document.getElementById('lowercase-constraint'),
    number: document.getElementById('number-constraint'),
    special: document.getElementById('special-constraint')
};

submitBtn.disabled = true;

function validateConstraints() {
    const password = passwordInput.value;

    const lengthValid = password.length >= 8;
    const uppercaseValid = /[A-Z]/.test(password);
    const lowercaseValid = /[a-z]/.test(password);
    const numberValid = /[0-9]/.test(password);
    const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    constraintItems.length.classList.toggle('valid', lengthValid);
    constraintItems.uppercase.classList.toggle('valid', uppercaseValid);
    constraintItems.lowercase.classList.toggle('valid', lowercaseValid);
    constraintItems.number.classList.toggle('valid', numberValid);
    constraintItems.special.classList.toggle('valid', specialValid);

    setConstraintColors();

    return lengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid;
}

function setConstraintColors() {
    for (const key in constraintItems) {
        const item = constraintItems[key];
        if (item.classList.contains('valid')) {
            item.style.color = '#C1FF72';
        } else {
            item.style.color = 'red';
        }
    }
}

passwordInput.addEventListener('focus', () => {
    constraintsList.classList.remove('hidden');
});

passwordInput.addEventListener('input', function () {
    const constraintsValid = validateConstraints();
    const passwordsMatch = passwordInput.value === confirmPasswordInput.value;

    if (!constraintsValid) {
        constraintsList.classList.remove('hidden');
    } else {
        constraintsList.classList.add('hidden');
    }

    if (constraintsValid && passwordsMatch) {
        submitBtn.disabled = false;
        errorMessage.style.display = 'none';
    } else {
        submitBtn.disabled = true;
    }
});

confirmPasswordInput.addEventListener('input', function () {
    errorMessage.style.display = 'none';
});

confirmPasswordInput.addEventListener('blur', function () {
    const passwordsMatch = passwordInput.value === confirmPasswordInput.value;

    if (!passwordsMatch) {
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'Les mots de passe ne correspondent pas.';
    } else {
        errorMessage.style.display = 'none';
    }
});

document.addEventListener('click', function (event) {
    if (!passwordInput.contains(event.target) && !constraintsList.contains(event.target) &&
        !event.target.classList.contains('toggle-password')) {
        const constraintsValid = validateConstraints();
        if (constraintsValid) {
            constraintsList.classList.add('hidden');
        } else {
            constraintsList.classList.remove('hidden');
        }
    }
});
