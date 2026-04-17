import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';

function initCalendar() {
    const el = document.getElementById('calendar');
    if (!el) return;

    const isMobile = window.matchMedia('(max-width: 640px)').matches;

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, listPlugin],
        initialView: isMobile ? 'listMonth' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth',
        },
        events: '/calendar-events',
        eventDisplay: 'block',
        displayEventTime: true,
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
        height: 'auto',
        eventDidMount(info) {
            const slotsOpen = info.event.extendedProps.slotsOpen;
            const slotsTotal = info.event.extendedProps.slotsTotal;
            const location = info.event.extendedProps.location;
            const parts = [];
            if (typeof slotsOpen === 'number' && typeof slotsTotal === 'number') {
                parts.push(`${slotsOpen} of ${slotsTotal} slots open`);
            }
            if (location) parts.push(location);
            if (parts.length) info.el.title = parts.join(' • ');
        },
    });

    calendar.render();
}

document.addEventListener('DOMContentLoaded', initCalendar);
