document.addEventListener("DOMContentLoaded", function () {
  // 🔒 Force-hide popup when page loads
  document.getElementById('popup').style.display = 'none';

  document.getElementById('register-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const response = await fetch(this.action, {
      method: 'POST',
      body: new FormData(this)
    });

    const result = await response.json();
    const popup = document.getElementById('popup');
    const popupMessage = document.getElementById('popup-message');

    if (result.status === 'success') {
      popupMessage.textContent = `Registration successful! User ID: ${result.user_id}`;
      popup.style.display = 'block';

      setTimeout(() => {
        window.location.href = 'http://localhost/expenses_management/templates/login.html';
      }, 1500);
    } else {
      popupMessage.textContent = result.message || "Registration failed";
      popup.style.display = 'block';
    }
  });

  // OK button to close popup
  document.getElementById('popup-ok').addEventListener('click', function () {
    document.getElementById('popup').style.display = 'none';
  });
});
