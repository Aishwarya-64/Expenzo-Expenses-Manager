let notifications = [];

// 🔔 Add Notification to bell panel
function addNotification(message) {
  const now = new Date();
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

  notifications.unshift({ message, time });
  updateNotificationUI();
}

// 🔄 Update Notification UI panel under bell icon
function updateNotificationUI() {
  const countEl = document.getElementById("notifCount");
  const list = document.getElementById("notificationList");
  if (!countEl || !list) return;

  countEl.textContent = notifications.length;
  list.innerHTML = "";

  notifications.forEach(n => {
    const item = document.createElement("div");
    item.innerHTML = `
      <strong>${n.message}</strong><br>
      <small>${n.time}</small>
      <hr>`;
    list.appendChild(item);
  });
}

// ✅ Toast Notification with fade animation
function showToast(message) {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.classList.remove("hidden");
  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
    toast.classList.add("hidden");
  }, 3000);
}

// ✅ Handle DOM load
document.addEventListener("DOMContentLoaded", () => {
  const bell = document.getElementById("notificationBell");
  const form = document.getElementById("budgetForm");

  // 🔁 Fix "To Date cannot be earlier than From Date"
  const startInput = document.getElementById("startDate");
  const endInput = document.getElementById("endDate");

  startInput.addEventListener("change", () => {
    const startDate = startInput.value;
    endInput.min = startDate;

    if (endInput.value && endInput.value < startDate) {
      endInput.value = startDate;
    }
  });

  // Toggle bell panel
  if (bell) {
    bell.addEventListener("click", () => {
      document.getElementById("notificationList")?.classList.toggle("hidden");
    });
  }

  // Handle form submit
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const category = document.getElementById("category").value;
    const period = document.getElementById("period").value;
    const startDate = startInput.value;
    const endDate = endInput.value;
    const amount = parseFloat(document.getElementById("amount").value);

    if (!category || !period || !startDate || !endDate || isNaN(amount)) {
      showToast("⚠️ Please fill in all fields!");
      return;
    }

    const formData = new URLSearchParams();
    formData.append("category", category);
    formData.append("period", period);
    formData.append("startDate", startDate);
    formData.append("endDate", endDate);
    formData.append("amount", amount);

    fetch("http://localhost/expenses_management/php/budget.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData.toString()
    })
    .then(response => response.text())
    .then(result => {
      if (result.trim() === "success" || result.includes("success")) {
        const saveMsg = `🆕 Budget added for ${category} - ₹${amount}`;
        showToast(saveMsg);
        addNotification(saveMsg);

        fetch("http://localhost/expenses_management/php/notifications.php")
          .then(res => res.json())
          .then(data => {
            let delay = 0;
            (data.notifications || []).forEach(n => {
              setTimeout(() => {
                showToast(n.message);
                addNotification(n.message);
              }, delay);
              delay += 3500;
            });
          });

        // Reset form
        form.reset();
        document.getElementById("category").selectedIndex = 0;
        document.getElementById("period").selectedIndex = 0;
      } else {
        showToast("❌ Failed to save budget!");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showToast("⚠️ Error saving budget!");
    });
  });
});
