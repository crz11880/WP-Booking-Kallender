(function () {
    'use strict';

    function shiftMonth(month, offset) {
        var parts = month.split('-');
        var year = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10) - 1;

        var d = new Date(year, m + offset, 1);
        var y = d.getFullYear();
        var newMonth = (d.getMonth() + 1).toString().padStart(2, '0');

        return y + '-' + newMonth;
    }

    function initCalendar(container) {
        var calendarId = parseInt(container.dataset.calendarId, 10);
        if (!calendarId) {
            return;
        }

        var month = container.dataset.month;
        var navButtons = container.querySelectorAll('.fewo-nav-btn');
        var monthLabel = container.querySelector('.fewo-month-label');
        var gridWrap = container.querySelector('.fewo-grid-wrap');

        function loadMonth(targetMonth) {
            var body = new URLSearchParams();
            body.append('action', 'fewo_kalender_get_month');
            body.append('nonce', fewoKalenderFrontend.nonce);
            body.append('calendar_id', String(calendarId));
            body.append('month', targetMonth);

            gridWrap.classList.add('fewo-loading');

            fetch(fewoKalenderFrontend.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString()
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (result) {
                    if (!result || !result.success || !result.data) {
                        return;
                    }

                    gridWrap.innerHTML = result.data.html;
                    monthLabel.textContent = result.data.monthLabel;
                    month = targetMonth;
                    container.dataset.month = targetMonth;
                })
                .catch(function () {
                    // Fehler wird absichtlich still behandelt, um Frontend-Ausgabe robust zu halten.
                })
                .finally(function () {
                    gridWrap.classList.remove('fewo-loading');
                });
        }

        navButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var direction = button.dataset.direction;
                var next = shiftMonth(month, direction === 'prev' ? -1 : 1);
                loadMonth(next);
            });
        });
    }

    document.querySelectorAll('.fewo-frontend-calendar').forEach(initCalendar);
})();
