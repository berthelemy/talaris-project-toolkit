/**
 * Autosave integration for input fields marked with data-autosave="true".
 *
 * DOM contract:
 * - data-autosave-url
 * - data-csrf-name
 * - data-csrf-value
 * - data-csrf-cookie-name
 * - data-autosave-status (status element id)
 */
(function () {
    'use strict';

    /**
     * Read a cookie value by name.
     *
     * @param {string} name Cookie key.
     * @returns {string} Decoded cookie value or an empty string.
     */
    function readCookie(name) {
        const escapedName = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const match = document.cookie.match(new RegExp('(?:^|; )' + escapedName + '=([^;]*)'));

        return match ? decodeURIComponent(match[1]) : '';
    }

    /**
     * Debounce callback execution to reduce write frequency.
     *
     * @param {Function} fn Callback to debounce.
     * @param {number} delay Delay in milliseconds.
     * @returns {Function} Debounced callback.
     */
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

    /**
     * Update the status indicator linked to the edited field.
     *
     * @param {HTMLInputElement} target Autosave input field.
     * @param {string} state Status state key.
     * @param {string} message Status text to display.
     * @returns {void}
     */
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

        if (state === 'locked') {
            status.classList.add('text-warning');
            return;
        }

        status.classList.add('text-danger');
    }

    /**
     * Persist the latest field value to the backend autosave endpoint.
     *
     * @param {HTMLInputElement} input Autosave-enabled input element.
     * @returns {Promise<void>}
     */
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

            if (response.status === 423) {
                input.readOnly = true;
                input.setAttribute('data-autosave', 'false');
                setStatus(input, 'locked', result.message || input.getAttribute('data-status-locked') || 'Editing is locked by another user.');
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

    /**
     * Bind debounced autosave handlers to all autosave-enabled inputs.
     *
     * @returns {void}
     */
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
