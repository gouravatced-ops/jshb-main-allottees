(function() {
    const holidays = [
        { date: "2026-01-01", title: "New Year's Day", color: "#f9d6d6" },
        { date: "2026-01-14", title: "Makar Sankranti", color: "#f7e3b6" },
        { date: "2026-01-26", title: "Republic Day", color: "#f0c8a8" },
        { date: "2026-02-14", title: "Valentine's Day", color: "#f8c8d8" },
        { date: "2026-02-19", title: "Maha Shivaratri", color: "#d4d9e6" },
        { date: "2026-03-08", title: "Women's Day", color: "#ecd4e0" },
        { date: "2026-03-17", title: "St. Patrick's Day", color: "#b8d9b0" },
        { date: "2026-04-01", title: "April Fool's Day", color: "#f5e6b0" },
        { date: "2026-04-14", title: "Baisakhi / Tamil New Year", color: "#f7d4b0" },
        { date: "2026-05-01", title: "Labour Day", color: "#e8d0c0" },
        { date: "2026-05-10", title: "Mother's Day", color: "#fadadd" },
        { date: "2026-06-21", title: "International Yoga Day", color: "#cce0cc" },
        { date: "2026-07-04", title: "US Independence Day", color: "#b8d4e8" },
        { date: "2026-08-15", title: "Independence Day (India)", color: "#f7c8a0" },
        { date: "2026-09-05", title: "Teacher's Day", color: "#d4e0f0" },
        { date: "2026-10-02", title: "Gandhi Jayanti", color: "#e8e0c0" },
        { date: "2026-10-31", title: "Halloween", color: "#f0c8a0" },
        { date: "2026-11-14", title: "Diwali (Deepavali)", color: "#fad9a0" },
        { date: "2026-12-25", title: "Christmas", color: "#d4e8d4" },
        { date: "2026-12-31", title: "New Year's Eve", color: "#d0d8f0" }
    ];

    const holidayMap = new Map();
    holidays.forEach(h => holidayMap.set(h.date, h));

    const monthYearDisplay = document.getElementById('monthYearDisplay');
    const daysGrid = document.getElementById('daysGrid');
    const prevBtn = document.getElementById('prevMonthBtn');
    const nextBtn = document.getElementById('nextMonthBtn');

    let currentYear = 2026;
    let currentMonth = 0;

    function formatDateKey(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function renderCalendar(year, month) {
        if(!daysGrid) return;
        daysGrid.innerHTML = '';
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        monthYearDisplay.textContent = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        const todayDateKey = formatDateKey(today.getFullYear(), today.getMonth(), today.getDate());
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;

        for (let i = 0; i < totalCells; i++) {
            let dayNumber, dateKey, isCurrentMonth = false;
            let cell = document.createElement('div');
            cell.className = 'day-cell';

            if (i < firstDay) {
                dayNumber = daysInPrevMonth - firstDay + i + 1;
                cell.classList.add('empty');
            } else if (i >= firstDay + daysInMonth) {
                dayNumber = i - firstDay - daysInMonth + 1;
                cell.classList.add('empty');
            } else {
                dayNumber = i - firstDay + 1;
                dateKey = formatDateKey(year, month, dayNumber);
                isCurrentMonth = true;
            }

            cell.textContent = dayNumber;

            if (isCurrentMonth) {
                if (dateKey === todayDateKey) cell.classList.add('today');
                const holiday = holidayMap.get(dateKey);
                if (holiday) {
                    cell.classList.add('holiday');
                    cell.style.backgroundColor = holiday.color || '#f0e6d0';
                    cell.setAttribute('data-holiday', holiday.title || 'Holiday');
                }
            }
            daysGrid.appendChild(cell);
        }
    }

    if(prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentMonth = currentMonth === 0 ? 11 : currentMonth - 1;
            if (currentMonth === 11) currentYear--;
            renderCalendar(currentYear, currentMonth);
        });
    }

    if(nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentMonth = currentMonth === 11 ? 0 : currentMonth + 1;
            if (currentMonth === 0) currentYear++;
            renderCalendar(currentYear, currentMonth);
        });
    }

    const initToday = new Date();
    currentYear = initToday.getFullYear();
    currentMonth = initToday.getMonth();
    // Fallback for SPA/dynamic loads
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(() => renderCalendar(currentYear, currentMonth), 100);
    } else {
        document.addEventListener('DOMContentLoaded', () => renderCalendar(currentYear, currentMonth));
    }
})();
