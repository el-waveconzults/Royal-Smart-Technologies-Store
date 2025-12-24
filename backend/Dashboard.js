// User Statistics Chart
const userStatsCtx = document.getElementById("userStats").getContext("2d");
new Chart(userStatsCtx, {
  type: "line",
  data: {
    labels: ["Jan", "Feb", "April", "June", "Aug", "Sep", "Oct", "Dec"],
    datasets: [
      {
        label: "Weekly Users",
        data: [50, 100, 150, 100, 200, 450, 200, 100],
        borderColor: "#ff3366",
        tension: 0.4,
        fill: false,
      },
      {
        label: "Monthly Users",
        data: [75, 125, 100, 150, 175, 200, 150, 125],
        borderColor: "#ffc107",
        tension: 0.4,
        fill: false,
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: "bottom",
      },
    },
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  },
});

// Traffic Types Pie Chart
const trafficCtx = document.getElementById("trafficPie").getContext("2d");
new Chart(trafficCtx, {
  type: "pie",
  data: {
    labels: ["Organic", "Referral", "Other"],
    datasets: [
      {
        data: [44.46, 5.54, 50],
        backgroundColor: ["#2196f3", "#00e676", "#ffc107"],
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: "bottom",
      },
    },
  },
});

// Advertising & Promotion Chart
const adStatsCtx = document.getElementById("adStats").getContext("2d");
new Chart(adStatsCtx, {
  type: "bar",
  data: {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    datasets: [
      {
        label: "Ad Spend",
        data: [12000, 19000, 15000, 25000, 22000, 30000],
        backgroundColor: "#ff3366",
      },
      {
        label: "ROI",
        data: [15000, 21000, 18000, 28000, 26000, 35000],
        backgroundColor: "#2196f3",
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: "bottom",
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value) {
            return "$" + value.toLocaleString();
          },
        },
      },
    },
  },
});

// Update stats periodically (simulate real-time updates)
function updateStats() {
  const visits = document.querySelector(".stat-card.red h2");
  const bounceRate = document.querySelector(".stat-card.yellow h2");
  const pageviews = document.querySelector(".stat-card.green h2");
  const growthRate = document.querySelector(".stat-card.blue h2");

  // Simulate random fluctuations
  setInterval(() => {
    visits.textContent = (
      914001 + Math.floor(Math.random() * 1000)
    ).toLocaleString();
    bounceRate.textContent = (46.41 + Math.random()).toFixed(2) + "%";
    pageviews.textContent = (
      4054876 + Math.floor(Math.random() * 1000)
    ).toLocaleString();
    growthRate.textContent = (46.43 + Math.random()).toFixed(2) + "%";
  }, 5000);
}

updateStats();

// Update social media stats
function updateSocialStats() {
  const socialStats = {
    facebook: document.querySelector(".social-card.facebook h4"),
    x: document.querySelector(".social-card.x h4"),
    instagram: document.querySelector(".social-card.instagram h4"),
    linkedin: document.querySelector(".social-card.linkedin h4")
  };

  setInterval(() => {
    socialStats.facebook.textContent = (52869 + Math.floor(Math.random() * 100)).toLocaleString();
    socialStats.x.textContent = (42405 + Math.floor(Math.random() * 100)).toLocaleString();
    socialStats.instagram.textContent = (35320 + Math.floor(Math.random() * 100)).toLocaleString();
    socialStats.linkedin.textContent = (28458 + Math.floor(Math.random() * 100)).toLocaleString();
  }, 5000);
}

updateSocialStats();
