function calendarComponent() {
  return {
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
    days: [],
    eventDates: [],
    eventDetails: {},
    selected: [],

    async fetchCalendar() {
      const res = await fetch(
        `get_calendar_data.php?bulan=${this.bulan}&tahun=${this.tahun}`
      );
      const data = await res.json();
      this.eventDates = data.dates;
      this.eventDetails = data.details;
      this.renderDays();
    },

    renderDays() {
      const daysInMonth = new Date(this.tahun, this.bulan, 0).getDate();
      const jsDay = new Date(this.tahun, this.bulan - 1, 1).getDay();
      const firstDay = (jsDay + 6) % 7;
      const today = new Date();
      const todayStr = `${today.getFullYear()}-${String(
        today.getMonth() + 1
      ).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;

      this.days = [];
      this.selected = [];

      for (let i = 0; i < firstDay; i++) {
        this.days.push({ label: "", dot: false, details: [], isToday: false });
      }

      for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${this.tahun}-${String(this.bulan).padStart(
          2,
          "0"
        )}-${String(d).padStart(2, "0")}`;
        const detailArr = this.eventDetails[dateStr]
          ? this.eventDetails[dateStr]
          : [];
        this.days.push({
          label: d,
          dot: this.eventDates.includes(dateStr),
          details: detailArr,
          isToday: dateStr === todayStr,
        });
      }
    },

    prevMonth() {
      if (--this.bulan < 1) {
        this.bulan = 12;
        this.tahun--;
      }
      this.fetchCalendar();
    },

    nextMonth() {
      if (++this.bulan > 12) {
        this.bulan = 1;
        this.tahun++;
      }
      this.fetchCalendar();
    },

    async init() {
      await this.fetchCalendar();
    },
  };
}
