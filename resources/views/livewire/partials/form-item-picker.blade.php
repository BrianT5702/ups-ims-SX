@php
    $wireFormSubmit = $wireFormSubmit ?? 'addDO';
@endphp

<div id="do-client-item-picker-modal" class="do-client-item-picker-modal" style="display: none;" wire:ignore>
    <div class="modal-backdrop fade show do-client-item-picker-backdrop" data-do-client-picker-close="1"></div>
    <div class="do-client-item-picker-center">
        <div class="modal-content modal-xl shadow">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0" id="do-client-item-picker-title">Add item</h5>
                <button type="button" class="btn-close" data-do-client-picker-close="1" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="border rounded p-3 bg-light"
                     data-do-client-picker-mode-select="1"
                     data-do-picker-active-index="0">
                    <label class="form-label small text-muted mb-2">Choose search mode first</label>
                    <div class="list-group">
                        <button type="button"
                                class="list-group-item list-group-item-action active"
                                id="do-client-item-picker-choice-code"
                                data-do-picker-choice="code"
                                data-do-client-picker-choose="code">
                            1. Search by Item Code
                        </button>
                        <button type="button"
                                class="list-group-item list-group-item-action"
                                id="do-client-item-picker-choice-name"
                                data-do-picker-choice="name"
                                data-do-client-picker-choose="name">
                            2. Search by Item Name
                        </button>
                    </div>
                    <p class="small text-muted mb-0 mt-2">
                        Use <strong>Arrow Up/Down</strong> to switch, then press <strong>Enter</strong>.
                        Or press <strong>1</strong> / <strong>2</strong> to choose directly.
                    </p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-do-client-picker-close="1">Cancel</button>
            </div>
        </div>
    </div>
</div>
<livewire:d-o-item-picker />

<script>
    (function () {
        var wireFormSubmit = @json($wireFormSubmit);
        var formSelector = 'form[wire\\:submit\\.prevent="' + wireFormSubmit + '"]';

        function openClientPickerForRow(rowIdx) {
            var clientModal = document.getElementById('do-client-item-picker-modal');
            if (!clientModal || typeof Livewire === 'undefined') return;
            if (document.querySelector('.do-item-picker-modal:not(.d-none)')) {
                Livewire.dispatch('do-item-picker-close');
            }

            clientModal.setAttribute('data-row-index', String(rowIdx));
            var titleEl = document.getElementById('do-client-item-picker-title');
            if (titleEl) titleEl.textContent = 'Add item — row ' + (rowIdx + 1);

            var modeBox = clientModal.querySelector('[data-do-client-picker-mode-select="1"]');
            if (modeBox) {
                modeBox.setAttribute('data-do-picker-active-index', '0');
                modeBox.style.display = '';
            }
            var modeBtns = clientModal.querySelectorAll('[data-do-client-picker-choose]');
            modeBtns.forEach(function (b, i) { b.classList.toggle('active', i === 0); });

            clientModal.style.display = 'block';
            clientModal.setAttribute('aria-modal', 'true');
            setTimeout(function () {
                document.getElementById('do-client-item-picker-choice-code')?.focus();
            }, 0);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'F2') return;

            var form = e.target.closest(formSelector);
            if (!form) return;
            if (form.getAttribute('data-do-dept2') === '1') return;

            e.preventDefault();

            var clientModal = document.getElementById('do-client-item-picker-modal');
            if (clientModal && clientModal.style.display !== 'none') {
                clientModal.style.display = 'none';
                clientModal.removeAttribute('data-row-index');
                Livewire.dispatch('do-item-picker-close');
                return;
            }

            if (document.querySelector('.do-item-picker-modal:not(.d-none)')) {
                Livewire.dispatch('do-item-picker-close');
                return;
            }

            var currentRow = e.target.closest('tr.item-row');
            var targetRow = currentRow;
            if (!targetRow) {
                var rows = form.querySelectorAll('tr.item-row');
                if (rows.length === 0) return;
                targetRow = rows[rows.length - 1];
            }
            if (!targetRow || !targetRow.dataset.rowIndex) return;
            var rowIdx = parseInt(targetRow.dataset.rowIndex, 10);
            if (isNaN(rowIdx)) return;

            openClientPickerForRow(rowIdx);
        });

        document.addEventListener('click', function (e) {
            var openBtn = e.target.closest('[data-do-open-item-picker]');
            if (!openBtn) return;
            var rowIdx = parseInt(openBtn.getAttribute('data-do-open-item-picker') || '', 10);
            if (isNaN(rowIdx)) return;
            e.preventDefault();
            openClientPickerForRow(rowIdx);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var clientModal = document.getElementById('do-client-item-picker-modal');
            if (clientModal && clientModal.style.display !== 'none') {
                clientModal.style.display = 'none';
                clientModal.removeAttribute('data-row-index');
                Livewire.dispatch('do-item-picker-close');
                return;
            }
            var modal = document.querySelector('.do-item-picker-modal:not(.d-none)');
            if (!modal) return;
            Livewire.dispatch('do-item-picker-close');
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-do-client-picker-close]')) {
                var cm = document.getElementById('do-client-item-picker-modal');
                if (cm && cm.style.display !== 'none') {
                    cm.style.display = 'none';
                    cm.removeAttribute('data-row-index');
                }
                Livewire.dispatch('do-item-picker-close');
                return;
            }
            var choose = e.target.closest('[data-do-client-picker-choose]');
            if (!choose) return;
            var cm = document.getElementById('do-client-item-picker-modal');
            if (!cm || cm.style.display === 'none') return;
            var rowIdx = parseInt(cm.getAttribute('data-row-index') || '', 10);
            if (isNaN(rowIdx)) return;
            var mode = choose.getAttribute('data-do-client-picker-choose');
            if (mode !== 'code' && mode !== 'name') return;

            var form = document.querySelector(formSelector);
            if (!form) return;

            cm.style.display = 'none';
            cm.removeAttribute('data-row-index');
            Livewire.dispatch('do-item-picker-open', { rowIndex: rowIdx, mode: mode });
        });
    })();
</script>

<script>
    (function () {
        function getModeButtons(container) {
            if (!container) return [];
            return Array.from(container.querySelectorAll('[data-do-picker-choice]'));
        }

        function setActiveChoice(container, index) {
            var buttons = getModeButtons(container);
            if (buttons.length === 0) return;

            var clamped = Math.max(0, Math.min(index, buttons.length - 1));
            container.setAttribute('data-do-picker-active-index', String(clamped));

            buttons.forEach(function (btn, i) {
                if (i === clamped) {
                    btn.classList.add('active');
                    btn.focus();
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            var t = e.target;
            var tag = t && t.tagName ? t.tagName.toLowerCase() : '';
            if (tag === 'input' || tag === 'textarea' || tag === 'select' || (t && t.isContentEditable)) {
                return;
            }

            var clientModal = document.getElementById('do-client-item-picker-modal');
            var clientContainer = null;
            if (clientModal && window.getComputedStyle(clientModal).display !== 'none') {
                clientContainer = clientModal.querySelector('[data-do-client-picker-mode-select="1"]');
            }
            var serverContainer = document.querySelector('.do-item-picker-modal:not(.d-none) [data-do-picker-mode-select="1"]');
            var container = clientContainer || serverContainer;
            if (!container) return;

            var buttons = getModeButtons(container);
            if (buttons.length === 0) return;

            var activeIndex = parseInt(container.getAttribute('data-do-picker-active-index') || '0', 10);
            if (isNaN(activeIndex)) activeIndex = 0;

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActiveChoice(container, activeIndex - 1);
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActiveChoice(container, activeIndex + 1);
                return;
            }
            if (e.key === '1') {
                e.preventDefault();
                buttons[0].click();
                return;
            }
            if (e.key === '2') {
                e.preventDefault();
                if (buttons[1]) buttons[1].click();
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                var chosen = buttons[activeIndex] || buttons[0];
                if (chosen) chosen.click();
            }
        });

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-do-picker-choice]');
            if (!btn) return;
            var container = btn.closest('[data-do-picker-mode-select="1"]')
                || btn.closest('[data-do-client-picker-mode-select="1"]');
            if (!container) return;
            var buttons = getModeButtons(container);
            var idx = buttons.indexOf(btn);
            if (idx >= 0) container.setAttribute('data-do-picker-active-index', String(idx));
        });
    })();
</script>

<script>
    (function () {
        var pickerListActiveIndex = -1;

        function getPickerLivewire() {
            var modal = document.querySelector('.do-item-picker-modal:not(.d-none)');
            if (!modal || typeof Livewire === 'undefined') return null;
            var root = modal.closest('[wire\\:id]');
            if (!root) return null;
            return Livewire.find(root.getAttribute('wire:id'));
        }

        function getPickerDataRows() {
            var wrap = document.querySelector('.do-item-picker-modal:not(.d-none) .do-item-picker-table-wrap');
            if (!wrap) return [];
            return Array.from(wrap.querySelectorAll('tbody tr.do-item-picker-row[data-item-id]'));
        }

        function clearPickerHighlight() {
            document.querySelectorAll('.do-item-picker-modal:not(.d-none) tr.do-item-picker-row').forEach(function (row) {
                row.classList.remove('table-active');
            });
        }

        function applyPickerHighlight(index) {
            var rows = getPickerDataRows();
            clearPickerHighlight();
            if (index < 0 || index >= rows.length) return;
            rows[index].classList.add('table-active');
            rows[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        function syncPickerIndexAfterDomChange() {
            var rows = getPickerDataRows();
            if (pickerListActiveIndex >= rows.length) {
                pickerListActiveIndex = -1;
                clearPickerHighlight();
            }
        }

        document.addEventListener('input', function (e) {
            if (e.target && e.target.id === 'do-item-picker-search') {
                pickerListActiveIndex = -1;
                clearPickerHighlight();
            }
        });

        document.addEventListener('click', function (e) {
            var row = e.target.closest('.do-item-picker-modal:not(.d-none) tr.do-item-picker-row[data-item-id]');
            if (!row) return;
            var rows = getPickerDataRows();
            var idx = rows.indexOf(row);
            if (idx >= 0) pickerListActiveIndex = idx;
        });

        document.addEventListener('keydown', function (e) {
            if (!document.querySelector('.do-item-picker-modal:not(.d-none)')) return;
            var search = document.getElementById('do-item-picker-search');
            if (!search) return;

            var rows = getPickerDataRows();
            if (rows.length === 0) return;

            var t = e.target;
            var inSearch = t === search;
            var inPickerRow = t.closest && t.closest('.do-item-picker-modal:not(.d-none) tr.do-item-picker-row');
            if (!inSearch && !inPickerRow) return;

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                syncPickerIndexAfterDomChange();
                rows = getPickerDataRows();
                if (rows.length === 0) return;

                if (e.key === 'ArrowDown') {
                    if (pickerListActiveIndex < rows.length - 1) {
                        pickerListActiveIndex++;
                    } else {
                        pickerListActiveIndex = rows.length - 1;
                    }
                    if (pickerListActiveIndex < 0) pickerListActiveIndex = 0;
                    applyPickerHighlight(pickerListActiveIndex);
                    return;
                }
                if (pickerListActiveIndex > 0) {
                    pickerListActiveIndex--;
                    applyPickerHighlight(pickerListActiveIndex);
                } else {
                    pickerListActiveIndex = -1;
                    clearPickerHighlight();
                }
                return;
            }

            if (e.key !== 'Enter') return;

            syncPickerIndexAfterDomChange();
            rows = getPickerDataRows();
            if (rows.length === 0) return;

            var idx = pickerListActiveIndex >= 0 ? pickerListActiveIndex : 0;
            var row = rows[idx];
            if (!row) return;

            var id = row.getAttribute('data-item-id');
            if (!id) return;

            e.preventDefault();
            e.stopPropagation();
            var pickerComp = getPickerLivewire();
            if (pickerComp) pickerComp.call('selectItem', parseInt(id, 10));
        }, true);
    })();
</script>

<script>
    (function() {
        var registered = false;
        function registerFocusQtyAfterAdd() {
            if (typeof Livewire === 'undefined' || registered) return;
            registered = true;
            Livewire.on('focus-qty-row', (event) => {
                var payload = event && event[0];
                var rowIndex = payload && payload.rowIndex;
                if (rowIndex === null || rowIndex === undefined) return;

                setTimeout(function() {
                    var row = document.querySelector('tr.item-row[data-row-index="' + rowIndex + '"]');
                    if (!row) return;
                    var qtyInput = row.querySelector('[data-do-role="qty"]:not([disabled])');
                    if (!qtyInput) return;
                    qtyInput.focus();
                    if (typeof qtyInput.select === 'function') qtyInput.select();
                }, 0);
            });
        }
        document.addEventListener('livewire:init', registerFocusQtyAfterAdd);
        if (document.readyState !== 'loading' && typeof Livewire !== 'undefined') registerFocusQtyAfterAdd();
    })();
</script>
