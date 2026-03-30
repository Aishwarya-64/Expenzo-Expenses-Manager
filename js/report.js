document.addEventListener("DOMContentLoaded", () => {
  fetch("http://localhost/expenses_management/php/report.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        alert(data.error);
        return;
      }

      const income = parseFloat(data.income) || 0;
      const expenses = parseFloat(data.expenses) || 0;
      const balance = parseFloat(data.balance ?? (income - expenses));

      updateText("totalIncome", `₹${income.toFixed(2)}`);
      updateText("totalExpenses", `₹${expenses.toFixed(2)}`);
      updateText("balance", `₹${balance.toFixed(2)}`);

      const categoryList = document.getElementById("categoryList");
      categoryList.innerHTML = "";

      const categories = Array.isArray(data.categories) ? data.categories : [];
      const labels = [];
      const values = [];

      categories.forEach((cat) => {
        const categoryName = cat.category || "Unknown";
        const total = parseFloat(cat.total) || 0;

        labels.push(categoryName);
        values.push(total);

        const div = document.createElement("div");
        div.classList.add("category-item");
        div.innerHTML = `<strong>${categoryName}</strong>: ₹${total.toFixed(2)}`;
        categoryList.appendChild(div);
      });

      renderChart("pie", labels, values);

      const pieBtn = document.getElementById("generatePieChart");
      const barBtn = document.getElementById("generateBarChart");

      if (pieBtn && barBtn) {
        pieBtn.onclick = () => toggleChart("pie", labels, values);
        barBtn.onclick = () => toggleChart("bar", labels, values);
      }
    })
    .catch((err) => {
      console.error("Error fetching data:", err);
      alert("Failed to fetch report data.");
    });
});

function updateText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

let pieChart, barChart;

function toggleChart(type, labels, data) {
  const pieChartEl = document.getElementById("pieChart");
  const barChartEl = document.getElementById("barChart");

  if (!pieChartEl || !barChartEl) return;

  pieChartEl.style.display = type === "pie" ? "block" : "none";
  barChartEl.style.display = type === "bar" ? "block" : "none";

  renderChart(type, labels, data);
}

function renderChart(type, labels, data) {
  const ctxPie = document.getElementById("pieChart")?.getContext("2d");
  const ctxBar = document.getElementById("barChart")?.getContext("2d");

  if (type === "pie" && ctxPie) {
    if (pieChart) pieChart.destroy();

    const pieColors = [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0",
      "#9966FF", "#FF9F40", "#8BC34A", "#F06292",
      "#E57373", "#81C784", "#FFD54F", "#64B5F6"
    ];

    pieChart = new Chart(ctxPie, {
      type: "doughnut",
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor: data.map((_, i) => pieColors[i % pieColors.length])
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: "60%",
        plugins: {
          legend: {
            position: "top"
          },
          tooltip: {
            callbacks: {
              label: (tooltipItem) =>
                `${tooltipItem.label}: ₹${tooltipItem.raw.toFixed(2)}`
            }
          }
        }
      }
    });
  }

  if (type === "bar" && ctxBar) {
    const barColors = [
      "#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0",
      "#9966FF", "#FF9F40", "#8BC34A", "#F06292",
      "#E57373", "#81C784", "#FFD54F", "#64B5F6"
    ];
  
    if (barChart) barChart.destroy();
  
    barChart = new Chart(ctxBar, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [{
          label: "Expense Amount (₹)",
          data: data,
          backgroundColor: labels.map((_, i) => barColors[i % barColors.length]),
          borderColor: labels.map((_, i) => barColors[i % barColors.length]),
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => `₹${value}`
            },
            grid: {
              color: "rgba(255,255,255,0.1)"
            }
          },
          x: {
            grid: {
              color: "rgba(255,255,255,0.1)"
            }
          }
        },
        plugins: {
          legend: {
            position: "top",
            labels: {
              font: {
                size: 14,
                weight: "bold"
              }
            }
          },
          tooltip: {
            callbacks: {
              label: (tooltipItem) => `₹${tooltipItem.raw.toFixed(2)}`
            }
          }
        }
      }
    });
  }
}  