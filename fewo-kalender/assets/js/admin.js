(function () {
    'use strict';

    var statusOrder = ['free', 'booked', 'changeover', 'halfday', 'halfday_reverse'];
    var form = document.getElementById('fewo-status-form');

    if (!form) {
        return;
    }

    var dayButtons = form.querySelectorAll('.fewo-day[data-date]');
    var hiddenInput = document.getElementById('fewo-day-statuses');

    function updateButtonStyle(button, status) {
        button.dataset.status = status;

        button.classList.remove('fewo-status-free', 'fewo-status-booked', 'fewo-status-changeover', 'fewo-status-halfday', 'fewo-status-halfday_reverse', 'fewo-status-halfday-reverse');
        button.classList.add('fewo-status-' + status);

        var label = button.querySelector('.fewo-day-status-label');
        if (label) {
            if (status === 'free') {
                label.textContent = 'frei';
            } else if (status === 'booked') {
                label.textContent = 'belegt';
            } else if (status === 'halfday') {
                label.textContent = 'halber tag (belegt/frei)';
            } else if (status === 'halfday_reverse') {
                label.textContent = 'halber tag (frei/belegt)';
            } else {
                label.textContent = 'wechseltag';
            }
        }
    }

    dayButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var current = button.dataset.status || 'free';
            var idx = statusOrder.indexOf(current);
            var next = statusOrder[(idx + 1) % statusOrder.length];
            updateButtonStyle(button, next);
        });
    });

    form.addEventListener('submit', function () {
        var data = {};

        dayButtons.forEach(function (button) {
            var date = button.dataset.date;
            var status = button.dataset.status || 'free';

            if (status !== 'free') {
                data[date] = status;
            }
        });

        hiddenInput.value = JSON.stringify(data);
    });

    function initDesignPreviews() {
        var selects = document.querySelectorAll('.fewo-design-select');

        selects.forEach(function (select) {
            var previewWrap = select.parentElement.querySelector('.fewo-design-previews');
            if (!previewWrap) {
                return;
            }

            var chips = previewWrap.querySelectorAll('.fewo-design-chip');

            function sync() {
                var selectedValue = select.value || 'modern';

                chips.forEach(function (chip) {
                    var isMatch = chip.classList.contains('fewo-design-' + selectedValue);
                    chip.classList.toggle('is-active', isMatch);
                });
            }

            select.addEventListener('change', sync);
            sync();
        });
    }

    initDesignPreviews();
})();
