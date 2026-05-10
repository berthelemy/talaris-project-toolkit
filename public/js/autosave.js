(function () {
    'use strict';

    function readCookie(name) {
        const escapedName = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const match = document.cookie.match(new RegExp('(?:^|; )' + escapedName + '=([^;]*)'));

        return match ? decodeURIComponent(match[1]) : '';
    }

    function debounce(fn, delay) {
        let timer = null;

        return function debounced() {
            const context = this;
            const args = arguments;

            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }

    function setStatus(target, state, message) {
        const statusId = target.getAttribute('data-autosave-status');
        if (!statusId) {
            return;
        }

        const status = document.getElementById(statusId);
        if (!status) {
            return;
        }

        status.textContent = message;
        status.classList.remove('text-muted', 'text-success', 'text-danger', 'text-warning');

        if (state === 'saving') {
            status.classList.add('text-muted');
            return;
        }

        if (state === 'saved') {
            status.classList.add('text-success');
            return;
        }

        if (state === 'conflict') {
            status.classList.add('text-warning');
            return;
        }

        status.classList.add('text-danger');
    }

    async function autosaveField(input) {
        const url = input.getAttribute('data-autosave-url');
        const field = input.getAttribute('name');
        const csrfName = input.getAttribute('data-csrf-name');
        const csrfValue = input.getAttribute('data-csrf-value');
        const csrfCookieName = input.getAttribute('data-csrf-cookie-name');

        if (!url || !field || !csrfName || !csrfValue || !csrfCookieName) {
            return;
        }

        const currentCsrfValue = readCookie(csrfCookieName) || csrfValue;

        const payload = new URLSearchParams();
        payload.append(field, input.value);
        payload.append('last_updated_at', input.getAttribute('data-last-updated-at') || '');
        payload.append(csrfName, currentCsrfValue);

        setStatus(input, 'saving', input.getAttribute('data-status-saving') || 'Saving...');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                },
                body: payload.toString(),
            });

            const result = await response.json();

            if (response.status === 409 && result.current) {
                input.value = result.current.message || input.value;
                input.setAttribute('data-last-updated-at', result.current.updated_at || '');
                setStatus(input, 'conflict', input.getAttribute('data-status-conflict') || 'Conflict detected. Reloaded latest value.');
                return;
            }

            if (!response.ok || !result.ok) {
                setStatus(input, 'error', input.getAttribute('data-status-error') || 'Autosave failed.');
                return;
            }

            if (result.entry && result.entry.updated_at) {
                input.setAttribute('data-last-updated-at', result.entry.updated_at);
            }

            if (result.csrf_hash) {
                input.setAttribute('data-csrf-value', result.csrf_hash);
            }

            setStatus(input, 'saved', input.getAttribute('data-status-saved') || 'Saved');
        } catch (error) {
            setStatus(input, 'error', input.getAttribute('data-status-error') || 'Autosave failed.');
        }
    }

    function initializeAutosave() {
        const autosaveInputs = document.querySelectorAll('input[data-autosave="true"]');

        autosaveInputs.forEach(function (input) {
            const handler = debounce(function () {
                autosaveField(input);
            }, 500);

            input.addEventListener('input', handler);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAutosave);
    } else {
        initializeAutosave();
    }
})();
