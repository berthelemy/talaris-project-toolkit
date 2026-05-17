/**
 * Manage widget ordering interactions in the module management table.
 *
 * Supports keyboard/button ordering and drag-and-drop row repositioning.
 * Requires data attributes:
 * - data-widget-order-list
 * - data-widget-row
 * - data-widget-order-input
 * - data-widget-position
 * - data-widget-move
 */
(function () {
    'use strict';

    var table = document.querySelector('[data-widget-order-list]');
    if (!table) {
        return;
    }

    var tbody = table.querySelector('tbody');
    if (!tbody) {
        return;
    }

    var dragSource = null;

    /**
     * Return all widget rows in current visual order.
     *
     * @returns {HTMLElement[]} Ordered row elements.
     */
    function rows() {
        return Array.prototype.slice.call(tbody.querySelectorAll('[data-widget-row]'));
    }

    /**
     * Synchronize hidden order inputs and position badges after reordering.
     *
     * @returns {void}
     */
    function refreshOrderInputs() {
        rows().forEach(function (row, index) {
            var position = index + 1;
            var input = row.querySelector('[data-widget-order-input]');
            var badge = row.querySelector('[data-widget-position]');

            if (input) {
                input.value = String(position);
            }

            if (badge) {
                badge.textContent = String(position);
            }
        });
    }

    /**
     * Move a row one step up or down.
     *
     * @param {HTMLElement|null} row Row to move.
     * @param {string|null} direction Expected values: up|down.
     * @returns {void}
     */
    function moveRow(row, direction) {
        if (!row) {
            return;
        }

        if (direction === 'up' && row.previousElementSibling) {
            tbody.insertBefore(row, row.previousElementSibling);
            refreshOrderInputs();
            return;
        }

        if (direction === 'down' && row.nextElementSibling) {
            tbody.insertBefore(row.nextElementSibling, row);
            refreshOrderInputs();
        }
    }

    tbody.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        var button = target.closest('[data-widget-move]');
        if (!button) {
            return;
        }

        var row = button.closest('[data-widget-row]');
        moveRow(row, button.getAttribute('data-widget-move'));
    });

    tbody.addEventListener('dragstart', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        var row = target.closest('[data-widget-row]');
        if (!row) {
            return;
        }

        dragSource = row;
        row.classList.add('table-active');

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', 'widget-row');
        }
    });

    tbody.addEventListener('dragend', function () {
        rows().forEach(function (row) {
            row.classList.remove('table-active');
            row.classList.remove('table-primary');
        });

        dragSource = null;
    });

    tbody.addEventListener('dragover', function (event) {
        if (!dragSource) {
            return;
        }

        event.preventDefault();

        var target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        var row = target.closest('[data-widget-row]');
        if (!row || row === dragSource) {
            return;
        }

        rows().forEach(function (item) {
            item.classList.remove('table-primary');
        });
        row.classList.add('table-primary');
    });

    tbody.addEventListener('drop', function (event) {
        if (!dragSource) {
            return;
        }

        event.preventDefault();

        var target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        var row = target.closest('[data-widget-row]');
        if (!row || row === dragSource) {
            return;
        }

        var targetRect = row.getBoundingClientRect();
        var shouldInsertBefore = event.clientY < targetRect.top + (targetRect.height / 2);

        if (shouldInsertBefore) {
            tbody.insertBefore(dragSource, row);
        } else {
            tbody.insertBefore(dragSource, row.nextElementSibling);
        }

        refreshOrderInputs();
    });

    refreshOrderInputs();
}());
