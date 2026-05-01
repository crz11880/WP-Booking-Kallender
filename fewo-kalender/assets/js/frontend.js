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
        var requestForm = container.querySelector('.fewo-request-form');
        var fromDateInput = requestForm ? requestForm.querySelector('.fewo-from-date') : null;
        var toDateInput = requestForm ? requestForm.querySelector('.fewo-to-date') : null;
        var fromDisplayInput = requestForm ? requestForm.querySelector('.fewo-from-display') : null;
        var toDisplayInput = requestForm ? requestForm.querySelector('.fewo-to-display') : null;

        // Persistente Auswahl-Variablen (über Monatswechsel hinweg)
        var persistSelectedFrom = fromDateInput ? fromDateInput.value : '';
        var persistSelectedTo = toDateInput ? toDateInput.value : '';
        // phase: 0=keine Auswahl, 1=erster Tag gewählt, 2=Range fertig
        var selectPhase = (persistSelectedFrom && persistSelectedTo) ? 2 : 0;

        function syncDateInputs(fromDate, toDate) {
            if (!fromDateInput || !toDateInput || !fromDisplayInput || !toDisplayInput) {
                return;
            }

            fromDateInput.value = fromDate || '';
            toDateInput.value = toDate || '';
            fromDisplayInput.value = fromDate || '';
            toDisplayInput.value = toDate || '';
        }

        function renderSelection(fromDate, toDate) {
            // Markiere ALLE Tage im gesamten Kalender (auch andere Monate, falls in DOM)
            var allDayNodes = document.querySelectorAll('.fewo-day[data-date]');

            allDayNodes.forEach(function (node) {
                var date = node.dataset.date;
                var selected = fromDate && toDate && date >= fromDate && date <= toDate;
                node.classList.toggle('fewo-selected-day', !!selected);
            });
        }

        function isFreeNode(node) {
            return !!(node && node.classList && node.classList.contains('fewo-status-free'));
        }

        function isRangeFree(fromDate, toDate, dayNodes) {
            if (!fromDate || !toDate) {
                return false;
            }

            var free = true;
            dayNodes.forEach(function (node) {
                var date = node.dataset.date;
                if (date >= fromDate && date <= toDate && !isFreeNode(node)) {
                    free = false;
                }
            });

            return free;
        }

        function initDateSelection() {
            if (!requestForm) {
                return;
            }

            var dayNodes = gridWrap.querySelectorAll('.fewo-day[data-date]');

            // Zeige aktuelle Auswahl an
            renderSelection(persistSelectedFrom, persistSelectedTo);

            dayNodes.forEach(function (node) {
                if (!isFreeNode(node)) {
                    node.classList.add('fewo-unselectable-day');
                }

                node.addEventListener('click', function () {
                    var clickedDate = node.dataset.date;
                    if (!clickedDate) {
                        return;
                    }

                    if (!isFreeNode(node)) {
                        window.alert(fewoKalenderFrontend.labels.freeOnlyHint || 'Es koennen nur freie Tage markiert werden.');
                        return;
                    }

                    if (selectPhase === 0 || selectPhase === 2) {
                        // Starte neue Auswahl
                        persistSelectedFrom = clickedDate;
                        persistSelectedTo = '';
                        selectPhase = 1;
                    } else {
                        // Zweiter Klick: Range abschliessen
                        persistSelectedTo = clickedDate;

                        if (persistSelectedFrom > persistSelectedTo) {
                            var swap = persistSelectedFrom;
                            persistSelectedFrom = persistSelectedTo;
                            persistSelectedTo = swap;
                        }

                        var allDayNodes = gridWrap.querySelectorAll('.fewo-day[data-date]');
                        if (!isRangeFree(persistSelectedFrom, persistSelectedTo, allDayNodes)) {
                            window.alert(fewoKalenderFrontend.labels.rangeBlockedHint || 'Der gewaehlte Zeitraum enthaelt nicht freie Tage.');
                            persistSelectedFrom = clickedDate;
                            persistSelectedTo = '';
                            selectPhase = 1;
                        } else {
                            selectPhase = 2;
                        }
                    }

                    syncDateInputs(persistSelectedFrom, persistSelectedTo);
                    renderSelection(persistSelectedFrom, persistSelectedTo || persistSelectedFrom);
                });
            });

        }

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
                    // Behalte die Auswahl bei - rufe renderSelection mit den aktuellen Werten auf
                    renderSelection(persistSelectedFrom, persistSelectedTo || persistSelectedFrom);
                    initDateSelection();
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

        if (requestForm && requestForm.dataset.fewoBound !== '1') {
            var feedbackDiv = requestForm.querySelector('.fewo-form-feedback');

            function clearFieldErrors() {
                requestForm.querySelectorAll('.fewo-field-error').forEach(function (el) {
                    el.textContent = '';
                    el.style.display = 'none';
                });
                requestForm.querySelectorAll('input, textarea').forEach(function (el) {
                    el.classList.remove('fewo-input-error');
                });
                if (feedbackDiv) {
                    feedbackDiv.style.display = 'none';
                    feedbackDiv.textContent = '';
                    feedbackDiv.className = 'fewo-form-feedback';
                }
            }

            function showFieldErrors(errors) {
                Object.keys(errors).forEach(function (field) {
                    var errEl = requestForm.querySelector('.fewo-field-error[data-field="' + field + '"]');
                    if (errEl) {
                        errEl.textContent = errors[field];
                        errEl.style.display = 'block';
                    }
                    var inputEl = requestForm.querySelector('[name="' + field + '_display"], [name="' + field + '"]');
                    if (inputEl) {
                        inputEl.classList.add('fewo-input-error');
                    }
                });
            }

            function showFeedback(msg, isSuccess) {
                if (!feedbackDiv) { return; }
                feedbackDiv.textContent = msg;
                feedbackDiv.className = 'fewo-form-feedback fewo-request-feedback ' + (isSuccess ? 'is-success' : 'is-error');
                feedbackDiv.style.display = 'block';
                feedbackDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            requestForm.addEventListener('submit', function (evt) {
                evt.preventDefault();
                clearFieldErrors();

                if (!fromDateInput || !toDateInput || !fromDateInput.value || !toDateInput.value) {
                    showFieldErrors({
                        from_date: 'Bitte Anreisedatum im Kalender auswaehlen.',
                        to_date: 'Bitte Abreisedatum im Kalender auswaehlen.'
                    });
                    return;
                }

                var submitBtn = requestForm.querySelector('.fewo-request-submit');
                if (submitBtn) { submitBtn.disabled = true; }

                var body = new URLSearchParams();
                body.append('action', fewoKalenderFrontend.labels.bookingAjaxAction || 'fewo_kalender_booking_ajax');
                new FormData(requestForm).forEach(function (val, key) {
                    if (key !== 'action') { body.append(key, val); }
                });

                fetch(fewoKalenderFrontend.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: body.toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (result) {
                    if (result && result.success) {
                        showFeedback(result.data.message, true);
                        requestForm.reset();
                        persistSelectedFrom = '';
                        persistSelectedTo = '';
                        selectPhase = 0;
                        syncDateInputs('', '');
                        renderSelection('', '');
                    } else {
                        var data = result && result.data ? result.data : {};
                        if (data.errors) {
                            showFieldErrors(data.errors);
                        } else {
                            showFeedback(data.message || 'Fehler beim Senden.', false);
                        }
                    }
                })
                .catch(function () {
                    showFeedback('Verbindungsfehler. Bitte erneut versuchen.', false);
                })
                .finally(function () {
                    if (submitBtn) { submitBtn.disabled = false; }
                });
            });
            requestForm.dataset.fewoBound = '1';
        }

        initDateSelection();
    }

    document.querySelectorAll('.fewo-frontend-calendar').forEach(initCalendar);
})();
