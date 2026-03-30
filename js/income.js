// Get elements
const categories = document.querySelectorAll('.category');
const amountBox = document.getElementById('amountBox');
const amountInput = document.getElementById('amountInput');
const saveButton = document.getElementById('saveAmount');
const categoryTitle = document.getElementById('selectedCategoryTitle');

let selectedCategoryName = "";
let incomeData = {}; // Optional: local JS object

// Handle category selection
categories.forEach(category => {
    category.addEventListener('click', () => {
        categories.forEach(cat => cat.classList.remove('selected'));
        category.classList.add('selected');

        selectedCategoryName = category.querySelector('span').innerText.trim();
        categoryTitle.innerText = `Enter Amount for ${selectedCategoryName}`;
        amountBox.style.display = 'block';
        amountInput.value = incomeData[selectedCategoryName] || "";
        amountInput.focus();
    });
});

// Save income entry
saveButton.addEventListener('click', async () => {
    const amount = parseFloat(amountInput.value.trim());

    if (!selectedCategoryName) {
        alert("Please select a category.");
        return;
    }

    if (isNaN(amount) || amount <= 0) {
        alert("Please enter a valid amount.");
        return;
    }

    // Save to local object (optional)
    incomeData[selectedCategoryName] = amount;

    // Prepare data to send to PHP
    const formData = new FormData();
    formData.append("category", selectedCategoryName);
    formData.append("amount", amount);

    try {
        const response = await fetch("http://localhost/expenses_management/php/income.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.status === "success") {
            showSuccessModal();
        } else {
            alert(result.message || "Failed to save income.");
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Something went wrong. Please try again.");
    }
});

// Success modal
function showSuccessModal() {
    const modal = document.getElementById("successModal");
    if (modal) {
        modal.style.display = "flex";

        const okBtn = document.getElementById("okBtn");
        if (okBtn) {
            okBtn.onclick = () => {
                modal.style.display = "none";
                amountInput.value = "";
                amountBox.style.display = "none";
                categories.forEach(cat => cat.classList.remove('selected'));
            };
        }
    } else {
        alert("Income saved successfully!"); // Fallback
    }
}
