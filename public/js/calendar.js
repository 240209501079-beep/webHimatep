// Komponen Alpine.js untuk Kalender Dinamis
document.addEventListener('alpine:init', () => {
    Alpine.data('calendarApp', () => ({
        month: new Date().getMonth(),
        year: new Date().getFullYear(),
        events: [],
        days: [],
        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        selectedEvent: null,
        modalOpen: false,

        init() {
            if (typeof dataProgram !== 'undefined') {
                this.events = dataProgram
                    .filter(p => p.agenda && p.agenda.date)
                    .map(p => ({
                        id: p.id,
                        date: p.agenda.date,
                        title: p.judul,
                        desc: p.ringkasan || '',
                        slug: p.slug,
                        gambar: p.gambar,
                        divisiColor: p.divisiColor,
                        agenda: p.agenda
                    }));
            } else {
                // Fallback statis jika dataProgram gagal dimuat
                this.events = [
                    { id: '1', date: '2026-05-10', title: 'Seminar Edutech', desc: 'Seminar nasional teknologi pendidikan.' }
                ];
            }
            this.generateCalendar();
        },

        generateCalendar() {
            this.days = [];
            let firstDay = new Date(this.year, this.month, 1).getDay();
            let daysInMonth = new Date(this.year, this.month + 1, 0).getDate();

            // Padding untuk hari sebelum tanggal 1
            for (let i = 0; i < firstDay; i++) {
                this.days.push({ empty: true });
            }

            // Isi tanggal bulan ini
            for (let i = 1; i <= daysInMonth; i++) {
                let dateStr = `${this.year}-${String(this.month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                let evt = this.events.find(e => e.date === dateStr);
                this.days.push({
                    date: i,
                    fullDate: dateStr,
                    event: evt || null,
                    empty: false
                });
            }
        },

        nextMonth() {
            if (this.month === 11) {
                this.month = 0;
                this.year++;
            } else {
                this.month++;
            }
            this.generateCalendar();
        },

        prevMonth() {
            if (this.month === 0) {
                this.month = 11;
                this.year--;
            } else {
                this.month--;
            }
            this.generateCalendar();
        },

        showEvent(evt) {
            if (evt) {
                this.selectedEvent = evt;
                this.modalOpen = true;
            }
        }
    }));
});
