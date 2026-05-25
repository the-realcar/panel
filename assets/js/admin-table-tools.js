(function() {
    function parseCellValue(cell, type) {
        const raw = (cell.textContent || '').trim();
        if (type === 'number') {
            const normalized = raw.replace(/\s+/g, '').replace(',', '.');
            const value = Number(normalized);
            return Number.isNaN(value) ? Number.NEGATIVE_INFINITY : value;
        }

        return raw.toLowerCase();
    }

    function sortTable(table, columnIndex, direction) {
        const tbody = table.tBodies[0];
        if (!tbody) {
            return;
        }

        const header = table.tHead && table.tHead.rows[0] ? table.tHead.rows[0].cells[columnIndex] : null;
        const type = header && header.dataset.sortType ? header.dataset.sortType : 'text';
        const rows = Array.from(tbody.rows);

        rows.sort(function(a, b) {
            const aValue = parseCellValue(a.cells[columnIndex], type);
            const bValue = parseCellValue(b.cells[columnIndex], type);

            if (aValue < bValue) {
                return direction === 'asc' ? -1 : 1;
            }
            if (aValue > bValue) {
                return direction === 'asc' ? 1 : -1;
            }
            return 0;
        });

        rows.forEach(function(row) {
            tbody.appendChild(row);
        });

        Array.from(table.tHead.rows[0].cells).forEach(function(cell, index) {
            cell.removeAttribute('data-sort-dir');
            if (index === columnIndex) {
                cell.setAttribute('data-sort-dir', direction);
            }
        });
    }

    function initTable(table) {
        if (!table.tHead || !table.tHead.rows.length) {
            return;
        }

        Array.from(table.tHead.rows[0].cells).forEach(function(cell, index) {
            if (cell.dataset.noSort === 'true') {
                return;
            }

            cell.style.cursor = 'pointer';
            cell.title = 'Kliknij, aby sortować';
            cell.addEventListener('click', function() {
                const nextDirection = cell.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
                sortTable(table, index, nextDirection);
            });
        });

        const defaultSort = table.dataset.defaultSort || '';
        const parts = defaultSort.split(':');
        if (parts.length === 2) {
            const columnIndex = Number(parts[0]);
            const direction = parts[1] === 'desc' ? 'desc' : 'asc';
            if (!Number.isNaN(columnIndex)) {
                sortTable(table, columnIndex, direction);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-sortable-table]').forEach(initTable);
    });
})();