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

  const bulanSelect = document.getElementById("analisisBulan");
  const tahunSelect = document.getElementById("analisisTahun");

  function loadAhliChart(tahun, bulan = 0) {
    fetch(
      `../chart/get_ahli_data.php?persatuan_id=${window.persatuanId}&tahun=${tahun}&bulan=${bulan}`
    )
      .then((res) => res.json())
      .then((data) => {
        const jumlahLelaki = typeof data.lelaki === "number" ? data.lelaki : 0;
        const jumlahPerempuan =
          typeof data.perempuan === "number" ? data.perempuan : 0;

        Highcharts.chart("ahliChart", {
          chart: { type: "pie" },
          title: { text: "Bilangan Ahli Mengikut Jantina" },
          tooltip: {
            pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
          },
          plotOptions: {
            pie: {
              allowPointSelect: true,
              cursor: "pointer",
              dataLabels: {
                enabled: true,
                format: "<b>{point.name}</b>: {point.y}",
              },
            },
          },
          series: [
            {
              name: "Peratus",
              colorByPoint: true,
              data: [
                { name: "Lelaki", y: jumlahLelaki, color: "#3B82F6" },
                { name: "Perempuan", y: jumlahPerempuan, color: "#EC4899" },
              ],
            },
          ],
        });
      });
  }

  function loadAktivitiPenyertaanChart(tahun, bulan = 0) {
    fetch(
      `../chart/get_aktiviti_data.php?persatuan_id=${window.persatuanId}&tahun=${tahun}&bulan=${bulan}`
    )
      .then((res) => res.json())
      .then((data) => {
        Highcharts.chart("aktivitiPenyertaanChart", {
          chart: { type: "spline" },
          title: { text: "Bilangan Aktiviti Berjaya" },
          xAxis: { categories: data.labelTarikh },
          yAxis: {
            title: { text: "Bilangan Aktiviti" },
            min: 0,
            allowDecimals: false,
          },
          series: [
            {
              name: "Aktiviti",
              data: data.bilangan,
              color: "#6366F1",
            },
          ],
        });
      });
  }

  function loadSupplyDemandChart(tahun, bulan = 0) {
    fetch(
      `../chart/get_supply_demand_jenis.php?persatuan_id=${window.persatuanId}&tahun=${tahun}&bulan=${bulan}`
    )
      .then((res) => res.json())
      .then((data) => {
        Highcharts.chart("supplyDemandChart", {
          chart: { type: "line" },
          title: { text: "Supply vs Demand Aktiviti Mengikut Jenis" },
          xAxis: { categories: data.labels },
          yAxis: {
            title: { text: "Bilangan" },
            min: 0,
            allowDecimals: false,
          },
          series: [
            {
              name: "Bilangan Aktiviti (Supply)",
              data: data.supply,
              color: "#3B82F6",
            },
            {
              name: "Penyertaan Pelajar (Demand)",
              data: data.demand,
              color: "#10B981",
            },
          ],
        });
      });
  }

  function loadPrestasiDashboard(tahun, bulan = 0) {
    fetch(
      `../chart/get_prestasi.php?persatuan_id=${window.persatuanId}&tahun=${tahun}&bulan=${bulan}`
    )
      .then((res) => res.json())
      .then((data) => {
        Highcharts.chart("prestasiDonut", {
          chart: { type: "pie", backgroundColor: "transparent" },
          title: {
            text: `<span class=\"prestasi-title\">${data.prestasi}%</span>`,
            useHTML: true,
            align: "center",
            verticalAlign: "middle",
            y: 10,
          },
          tooltip: { enabled: false },
          plotOptions: {
            pie: {
              innerSize: "80%",
              dataLabels: { enabled: false },
              borderWidth: 0,
            },
          },
          series: [
            {
              data: [
                { y: data.prestasi, color: "#8b5cf6" },
                { y: 100 - data.prestasi, color: "#e5e7eb" },
              ],
            },
          ],
          credits: { enabled: false },
        });

        const indikator = [
          { id: "Ahli", value: data.ahli },
          { id: "Aktiviti", value: data.aktiviti },
          { id: "Penyertaan", value: data.penyertaan },
          { id: "MaklumBalas", value: data.feedback },
        ];

        indikator.forEach((item) => {
          document.getElementById(`peratus${item.id}`).innerText =
            item.value + "%";
          document.getElementById(`bar${item.id}`).style.width =
            item.value + "%";
        });

        document.getElementById("peratusPenalti").innerText =
          data.penalti + " batal";
        document.getElementById("barPenalti").style.width =
          Math.min(data.penalti * 5, 100) + "%";
      });
  }

  function updateSemuaGraf() {
    const tahun = tahunSelect.value;
    const bulan = bulanSelect.value;
    loadAhliChart(tahun, bulan);
    loadAktivitiPenyertaanChart(tahun, bulan);
    loadSupplyDemandChart(tahun, bulan);
    loadPrestasiDashboard(tahun, bulan);
  }

  tahunSelect.addEventListener("change", updateSemuaGraf);
  bulanSelect.addEventListener("change", updateSemuaGraf);

  updateSemuaGraf();
});
