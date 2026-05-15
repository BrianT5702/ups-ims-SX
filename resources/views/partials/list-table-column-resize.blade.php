{{-- Resizable columns: add class "list-col-resize-table", data-list-col-storage-key, data-list-col-variant, colgroup cols data-list-col-index, and span.list-col-resize-handle per header cell. @include once per page. --}}
@once
    <style>
        .table.list-col-resize-table {
            table-layout: fixed;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .table.list-col-resize-table thead th {
            position: sticky;
            top: 0;
            z-index: 6;
        }

        .table.list-col-resize-table thead th .list-th-label {
            display: block;
            padding-right: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .table.list-col-resize-table thead th.text-center .list-th-label {
            text-align: center;
        }

        .table.list-col-resize-table .list-col-resize-handle {
            position: absolute;
            top: 0;
            right: 0;
            width: 10px;
            height: 100%;
            cursor: col-resize;
            z-index: 7;
            user-select: none;
            touch-action: none;
        }

        .table.list-col-resize-table .list-col-resize-handle:hover {
            background: rgba(13, 110, 253, 0.12);
        }

        .table.list-col-resize-table th,
        .table.list-col-resize-table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table.list-col-resize-table td a {
            display: block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table.list-col-resize-table td .do-status,
        .table.list-col-resize-table td .do-print-flag,
        .table.list-col-resize-table td .po-print-flag,
        .table.list-col-resize-table td .quotation-print-flag {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }
    </style>
    <script>
        (function () {
            if (window.__listColResizeBound) {
                return;
            }
            window.__listColResizeBound = true;

            var STORAGE_KEY = 'listTableColWidths_v1';
            var LEGACY_DO_STORAGE_KEY = 'doListColWidths';
            var MIN_COL_PX = 48;
            var dragState = null;

            function getColsSorted(table) {
                var cols = Array.from(table.querySelectorAll('colgroup col[data-list-col-index]'));
                cols.sort(function (a, b) {
                    return parseInt(a.getAttribute('data-list-col-index'), 10) - parseInt(b.getAttribute('data-list-col-index'), 10);
                });
                return cols;
            }

            function readRoot() {
                try {
                    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                } catch (e) {
                    return {};
                }
            }

            function writeRoot(obj) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
                } catch (e) { /* ignore */ }
            }

            function compositeKey(table) {
                var sk = table.getAttribute('data-list-col-storage-key') || '';
                var v = table.getAttribute('data-list-col-variant') || 'default';
                return sk ? (sk + '|' + v) : '';
            }

            function readSavedWidths(table) {
                var key = compositeKey(table);
                if (!key) {
                    return null;
                }
                var all = readRoot();
                if (all[key] && Array.isArray(all[key])) {
                    return all[key];
                }
                if (key.indexOf('doList|') === 0) {
                    try {
                        var legacy = JSON.parse(localStorage.getItem(LEGACY_DO_STORAGE_KEY) || '{}');
                        var variant = table.getAttribute('data-list-col-variant');
                        if (variant && legacy[variant]) {
                            return legacy[variant];
                        }
                    } catch (e) { /* ignore */ }
                }
                return null;
            }

            function writeSavedWidths(table, widths) {
                var key = compositeKey(table);
                if (!key) {
                    return;
                }
                var all = readRoot();
                all[key] = widths;
                writeRoot(all);
            }

            function applySavedWidths() {
                document.querySelectorAll('table.list-col-resize-table[data-list-col-storage-key]').forEach(function (table) {
                    var saved = readSavedWidths(table);
                    if (!saved || !saved.length) {
                        return;
                    }
                    var cols = getColsSorted(table);
                    if (cols.length !== saved.length) {
                        return;
                    }
                    saved.forEach(function (w, i) {
                        if (cols[i] && typeof w === 'number' && w >= MIN_COL_PX) {
                            cols[i].style.width = Math.round(w) + 'px';
                        }
                    });
                });
            }

            document.addEventListener('mousedown', function (e) {
                var handle = e.target.closest('.list-col-resize-handle');
                if (!handle) {
                    return;
                }
                var table = handle.closest('table.list-col-resize-table');
                if (!table || !table.getAttribute('data-list-col-storage-key')) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                var idx = handle.getAttribute('data-list-col-index');
                var cols = getColsSorted(table);
                var n = cols.length;
                if (n < 2) {
                    return;
                }
                var primary = parseInt(idx, 10);
                if (isNaN(primary) || primary < 0 || primary >= n) {
                    return;
                }
                var partner = primary < n - 1 ? primary + 1 : primary - 1;
                if (partner < 0) {
                    return;
                }
                var startWidths = cols.map(function (c) {
                    return c.getBoundingClientRect().width;
                });
                dragState = {
                    table: table,
                    cols: cols,
                    primaryIndex: primary,
                    partnerIndex: partner,
                    startX: e.pageX,
                    startWidths: startWidths,
                };
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
            }, true);

            document.addEventListener('mousemove', function (e) {
                if (!dragState) {
                    return;
                }
                e.preventDefault();
                var i = dragState.primaryIndex;
                var j = dragState.partnerIndex;
                var sw = dragState.startWidths;
                var cols = dragState.cols;
                var dx = e.pageX - dragState.startX;
                var total = sw[i] + sw[j];
                var newI = sw[i] + dx;
                newI = Math.max(MIN_COL_PX, Math.min(newI, total - MIN_COL_PX));
                var newJ = total - newI;
                cols[i].style.width = Math.round(newI) + 'px';
                cols[j].style.width = Math.round(newJ) + 'px';
            });

            document.addEventListener('mouseup', function () {
                if (!dragState) {
                    return;
                }
                var table = dragState.table;
                var cols = getColsSorted(table);
                var widths = cols.map(function (c) {
                    return Math.round(c.getBoundingClientRect().width);
                });
                writeSavedWidths(table, widths);
                dragState = null;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applySavedWidths);
            } else {
                applySavedWidths();
            }
            document.addEventListener('livewire:navigated', applySavedWidths);

            document.addEventListener('livewire:init', function () {
                if (typeof Livewire === 'undefined' || !Livewire.hook) {
                    return;
                }
                Livewire.hook('morph.updated', function () {
                    applySavedWidths();
                });
            });
        })();
    </script>
@endonce