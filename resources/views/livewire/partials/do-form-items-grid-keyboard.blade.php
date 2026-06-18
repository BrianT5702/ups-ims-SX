@php
    $wireFormSubmit = $wireFormSubmit ?? 'addDO';
@endphp
<script>
    (function() {
        var formSelector = 'form[wire\\:submit\\.prevent="{{ $wireFormSubmit }}"]';

        // Enter handling inside the form:
        // - Never submits the form
        // - From QTY field: jump to same-row UOM field
        // - Otherwise from item-row field: jump into the NEXT row's description text field (if any)
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            if (e.defaultPrevented) return;

            var form = e.target.closest(formSelector);
            if (!form) return;

            e.preventDefault();

            var currentRow = e.target.closest('tr.item-row');
            if (!currentRow) return;

            if (form.getAttribute('data-do-dept2') === '1' && e.target.matches('[data-do-role="code"]')) {
                return;
            }

            if (e.target.matches('[data-do-role="qty"]')) {
                var unitInput = currentRow.querySelector('[data-do-role="uom"]:not([disabled])');
                if (unitInput) {
                    unitInput.focus();
                    if (typeof unitInput.select === 'function') unitInput.select();
                    return;
                }
            }

            if (e.target.matches('[data-do-role="uom"]')) {
                var sameRowDesc = currentRow.querySelector('[data-do-role="desc"]:not([disabled])');
                if (sameRowDesc) {
                    sameRowDesc.focus();
                    if (typeof sameRowDesc.select === 'function') sameRowDesc.select();
                    return;
                }
            }

            if (e.target.matches('[data-do-role="desc"]')) {
                var sameRowPrice = currentRow.querySelector('[data-do-role="price"]:not([disabled])');
                if (sameRowPrice) {
                    sameRowPrice.focus();
                    if (typeof sameRowPrice.select === 'function') sameRowPrice.select();
                    return;
                }
            }

            var nextRow = currentRow.nextElementSibling;
            while (nextRow) {
                if (nextRow.classList.contains('item-row')) {
                    var descInput = nextRow.querySelector('[data-do-role="desc"]');
                    if (descInput && !descInput.disabled) {
                        descInput.focus();
                        if (typeof descInput.select === 'function') descInput.select();
                        return;
                    }
                }
                nextRow = nextRow.nextElementSibling;
            }
        });
    })();
</script>
<script>
    (function() {
        var formSelector = 'form[wire\\:submit\\.prevent="{{ $wireFormSubmit }}"]';
        var roles = ['qty', 'uom', 'desc', 'price'];

        function focusField(row, role) {
            if (!row) return false;
            var selector = '[data-do-role="' + role + '"]:not([disabled])';
            var target = row.querySelector(selector);
            if (!target) return false;
            target.focus();
            if (typeof target.select === 'function') target.select();
            return true;
        }

        document.addEventListener('keydown', function (e) {
            if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) return;
            if (e.defaultPrevented) return;

            var source = e.target;
            if (!source || !source.matches('[data-do-role]')) return;

            var form = source.closest(formSelector);
            if (!form) return;

            var currentRow = source.closest('tr.item-row');
            if (!currentRow) return;

            var currentRole = source.getAttribute('data-do-role');
            var roleIdx = roles.indexOf(currentRole);
            if (roleIdx === -1) return;

            var rows = Array.from(form.querySelectorAll('tr.item-row'));
            var rowIdx = rows.indexOf(currentRow);
            if (rowIdx === -1) return;

            var moved = false;

            if (e.key === 'ArrowLeft') {
                for (var leftIdx = roleIdx - 1; leftIdx >= 0; leftIdx--) {
                    if (focusField(currentRow, roles[leftIdx])) {
                        moved = true;
                        break;
                    }
                }
            } else if (e.key === 'ArrowRight') {
                for (var rightIdx = roleIdx + 1; rightIdx < roles.length; rightIdx++) {
                    if (focusField(currentRow, roles[rightIdx])) {
                        moved = true;
                        break;
                    }
                }
            } else if (e.key === 'ArrowUp') {
                for (var upRowIdx = rowIdx - 1; upRowIdx >= 0; upRowIdx--) {
                    if (focusField(rows[upRowIdx], currentRole)) {
                        moved = true;
                        break;
                    }
                }
            } else if (e.key === 'ArrowDown') {
                for (var downRowIdx = rowIdx + 1; downRowIdx < rows.length; downRowIdx++) {
                    if (focusField(rows[downRowIdx], currentRole)) {
                        moved = true;
                        break;
                    }
                }
            }

            if (moved) e.preventDefault();
        });
    })();
</script>
