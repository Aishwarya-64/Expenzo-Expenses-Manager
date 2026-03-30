document.getElementById("loginForm").addEventListener("submit", async function (event) {
    event.preventDefault();
    
    // Get form values
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
        const response = await fetch("http://localhost/expenses_management/php/login.php", {
            method: "POST",
            credentials: 'include', // Important for sessions/cookies
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const result = await response.json();

        if (response.ok && result.status === "success") {
            showPopup("Login successful!", true);
        } else {
            // Show specific error message from backend
            showPopup(result.message || "Login failed", false);
        }
    } catch (error) {
        console.error("Login request failed:", error);
        showPopup("Network error. Please try again.", false);
    }
});

// Rest of your popup functions remain the same
let shouldRedirect = false;

function showPopup(message, redirect = false) {
    document.getElementById("popup-message").textContent = message;
    document.getElementById("popup").style.display = "flex";
    shouldRedirect = redirect;
}

function closePopup() {
    document.getElementById("popup").style.display = "none";
    if (shouldRedirect) {
        window.location.href = "http://localhost/expenses_management/templates/dashboard.html";
    }
}