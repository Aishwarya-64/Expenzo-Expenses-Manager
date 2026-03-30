document.addEventListener("DOMContentLoaded", () => {
  // Elements
  const usernameField = document.getElementById("username");
  const emailField = document.getElementById("email");
  const currentPasswordField = document.getElementById("currentPassword");
  const newPasswordField = document.getElementById("newPassword");
  const confirmPasswordField = document.getElementById("confirmPassword");
  const updateBtn = document.getElementById("updatePasswordBtn");
  const logoutBtn = document.getElementById("logoutBtn");

  // ✅ Load user profile from server
  fetch("http://localhost/expenses_management/php/get_profile.php")
  .then(res => res.json())
  .then(data => {
      if (data.success) {
        usernameField.value = data.username;
        emailField.value = data.email;
        currentPasswordField.value = data.current_password;
        currentPasswordField.readOnly = true;
        usernameField.readOnly = true;
        emailField.readOnly = true;
      } else {
        alert("❌ Failed to load profile.");
      }
    })
    .catch(err => {
      console.error("Profile fetch error:", err);
    });

  // ✅ Update password handler
  updateBtn.addEventListener("click", () => {
    const newPass = newPasswordField.value.trim();
    const confirmPass = confirmPasswordField.value.trim();

    if (!newPass || !confirmPass) {
      alert("⚠️ Please fill in the new password fields.");
      return;
    }

    if (newPass !== confirmPass) {
      alert("⚠️ New password and confirm password do not match.");
      return;
    }

    fetch("http://localhost/expenses_management/php/update_password.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `new_password=${encodeURIComponent(newPass)}`
    })
      .then(res => res.text())
      .then(response => {
        if (response.trim() === "success") {
          alert("✅ Password updated successfully!");
          newPasswordField.value = "";
          confirmPasswordField.value = "";
        } else {
          alert("❌ Password update failed.");
        }
      })
      .catch(err => {
        console.error("Update error:", err);
        alert("❌ Error updating password.");
      });
  });

  // ✅ Logout button handler
  logoutBtn.addEventListener("click", () => {
    fetch("http://localhost/expenses_management/php/logout.php")
      .then(() => {
        window.location.href = "login.html";
      });
  });
});
