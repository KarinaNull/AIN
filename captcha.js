var form = document.getElementById('contact-form');
form.addEventListener("submit", validateForm);

function validateForm(event) {
    event.preventDefault();
    document.getElementById('captchaModal').style.display = 'flex';
    return false;
}

function submitForm() {
    const captchaResponse = grecaptcha.getResponse();
    if (captchaResponse.length === 0) {
        alert("Пожалуйста, пройдите капчу.");
        return;
    }
    document.getElementById('g-recaptcha-response').value = captchaResponse;
    document.querySelector('.contact-form').submit();
    closeModal();
}

function closeModal() {
    document.getElementById('captchaModal').style.display = 'none';
}
