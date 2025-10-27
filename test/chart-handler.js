document.addEventListener("DOMContentLoaded", function () {
  const bulanLabels = [
    "Jan",
    "Feb",
    "Mac",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Ogos",
    "Sep",
    "Okt",
    "Nov",
    "Dis",
  ];
  const hariLabels = Array.from({ length: 31 }, (_, i) => `Hari ${i + 1}`);

  const bulanSelect = document.getElementById("perbandinganBulan");
  const tahunSelect = document.getElementById("perbandinganTahun");

  const ahliCtx = document.getElementById("ahliChart")?.getContext("2d");
  const aktivitiCtx = document
    .getElementById("aktivitiChart")
    ?.getContext("2d");
  const penyertaanCtx = document
    .getElementById("penyertaanChart")
    ?.getContext("2d");

  const ahliChart = new Chart(ahliCtx, {
    type: "bar",
    data: {
      labels: bulanLabels,
      datasets: [
        {
          label: "Jumlah Ahli",
          backgroundColor: "#60A5FA",
          data: [],
        },
      ],
    },
    options: {
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 5 } },
      },
    },
  });

  const aktivitiChart = new Chart(aktivitiCtx, {
    type: "line",
    data: {
      labels: bulanLabels,
      datasets: [
        {
          label: "Bilangan Aktiviti",
          data: [],
          borderColor: "#9333EA",
          backgroundColor: "#E9D5FF",
          fill: false,
          tension: 0.3,
        },
      ],
    },
    options: {
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
      },
    },
  });

  const penyertaanChart = new Chart(penyertaanCtx, {
    type: "line",
    data: {
      labels: bulanLabels,
      datasets: [
        {
          label: "Penyertaan Pelajar",
          data: [],
          borderColor: "#10B981",
          backgroundColor: "#D1FAE5",
          fill: false,
          tension: 0.3,
        },
      ],
    },
    options: {
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 5 } },
      },
    },
  });

  function loadAllCharts(tahun, bulan) {
    console.log("Fetching for tahun:", tahun, "bulan:", bulan);

    fetch(
      `../../Chart/get_aktiviti_data.php?persatuan_id=${window.persatuanId}&tahun=${tahun}&bulan=${bulan}`
    )
      .then((res) => res.json())
      .then((data) => {
        const isMonthly = bulan !== "all";
        const labels = isMonthly ? hariLabels : bulanLabels;

        ahliChart.data.labels = labels;
        aktivitiChart.data.labels = labels;
        penyertaanChart.data.labels = labels;

        ahliChart.data.datasets[0].data = data.ahli;
        aktivitiChart.data.datasets[0].data = data.aktiviti;
        penyertaanChart.data.datasets[0].data = data.penyertaan;

        ahliChart.update();
        aktivitiChart.update();
        penyertaanChart.update();
      })
      .catch((error) => {
        console.error("Fetch error:", error);
      });
  }

  bulanSelect.addEventListener("change", () => {
    loadAllCharts(tahunSelect.value, bulanSelect.value);
  });

  tahunSelect.addEventListener("change", () => {
    loadAllCharts(tahunSelect.value, bulanSelect.value);
  });

  loadAllCharts(tahunSelect.value, bulanSelect.value);
});
