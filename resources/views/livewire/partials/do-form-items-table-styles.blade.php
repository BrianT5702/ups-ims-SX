<style>
    :root {
        --do-grid-border: #d6deea;
        --do-grid-border-strong: #bcc8d9;
        --do-row-divider: #aebcd0;
        --do-row-alt: #fbfcfe;
        --do-row-focus: #f1f6ff;
        --do-row-focus-accent: #0d6efd;
    }

    /* Item picker modal: clear grid lines */
    .do-item-picker-table-wrap .do-item-picker-table th,
    .do-item-picker-table-wrap .do-item-picker-table td {
        border: 1px solid #c5cdd6 !important;
        vertical-align: middle;
    }
    .do-item-picker-table-wrap .do-item-picker-table thead th {
        border-bottom: 2px solid #aeb8c4 !important;
    }
    .do-item-picker-modal.do-item-picker-modal-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .do-item-picker-table-wrap tr.do-item-picker-row.table-active > td {
        --bs-table-bg-state: var(--bs-table-active-bg);
        background-color: var(--bs-table-active-bg) !important;
        box-shadow: inset 3px 0 0 var(--bs-primary);
    }

    /* Fixed 24-row table layout */
    .do-table-shell {
        border: 1px solid #c8d3e2;
        border-radius: 8px;
        overflow: auto;
        background: #fff;
    }

    .do-fixed-table {
        table-layout: fixed;
        width: 100%;
        border: 0;
        border-collapse: collapse;
        background: #fff;
        margin-bottom: 0;
    }

    .do-fixed-table th, .do-fixed-table td {
        padding: 4px 6px;
        vertical-align: middle;
        word-wrap: break-word;
        border-left: 1px solid #e7ecf4;
        border-right: 1px solid #e7ecf4;
        border-top: 0;
        border-bottom: 0;
        font-size: 0.82em;
    }

    .do-fixed-table th {
        font-size: 0.85em;
        font-weight: bold;
        text-transform: uppercase;
        border-bottom: 1px solid var(--do-grid-border-strong);
        background: #f3f7fc;
        position: sticky;
        top: 0;
        z-index: 2;
        box-shadow: inset 0 -1px 0 var(--do-grid-border-strong);
        letter-spacing: 0.02em;
    }

    .do-fixed-table tbody tr {
        min-height: 24px;
        border-bottom: 1.6px solid var(--do-row-divider);
    }

    .do-fixed-table td:nth-child(2),
    .do-fixed-table td:nth-child(3),
    .do-fixed-table td:nth-child(5),
    .do-fixed-table td:nth-child(6) {
        background: #fcfdff;
    }

    /* QTY header and values align right toward UNIT (Dept 1 grid) */
    .do-fixed-table:not(.do-dept2-grid) th:nth-child(2) {
        text-align: right;
    }

    /* Dept 2: column 2 is Code — left-align header and values */
    .do-fixed-table.do-dept2-grid th.do-dept2-col-code,
    .do-fixed-table.do-dept2-grid td:nth-child(2) {
        text-align: left;
        padding-left: 0.45rem;
    }

    .do-fixed-table td.do-qty-cell {
        text-align: right;
        vertical-align: top;
    }

    .do-fixed-table td:nth-child(5),
    .do-fixed-table td:nth-child(6) {
        text-align: center;
    }

    .do-fixed-table td:nth-child(6) {
        text-align: right;
        padding-right: 8px;
    }

    /* Tighten Description column so input aligns closer to cell edges */
    .do-fixed-table td:nth-child(4) {
        padding-left: 3px;
        padding-right: 3px;
    }

    .do-fixed-table td:nth-child(4) .form-control,
    .do-fixed-table td:nth-child(4) .form-control-sm {
        margin-left: 0;
        margin-right: 0;
    }

    .do-fixed-table .remark-row {
        background-color: #f8f9fa;
    }

    .do-fixed-table tbody .item-row:nth-child(even) {
        background-color: var(--do-row-alt);
    }

    .do-fixed-table .item-row:hover {
        background-color: #f7faff;
    }

    .do-fixed-table tbody tr:last-child {
        border-bottom: 0;
    }

    /* Strong focus cue to match "operator-first" row tracking */
    .do-fixed-table .item-row:focus-within {
        background-color: var(--do-row-focus) !important;
        box-shadow: inset 3px 0 0 var(--do-row-focus-accent);
    }

    /* Subtle section separators for easier scanning */
    .do-fixed-table th:nth-child(1),
    .do-fixed-table td:nth-child(1),
    .do-fixed-table th:nth-child(2),
    .do-fixed-table td:nth-child(2),
    .do-fixed-table th:nth-child(3),
    .do-fixed-table td:nth-child(3),
    .do-fixed-table th:nth-child(5),
    .do-fixed-table td:nth-child(5),
    .do-fixed-table th:nth-child(6),
    .do-fixed-table td:nth-child(6) {
        border-right: 1px solid #d2dcea;
    }

    .do-fixed-table th:nth-child(4),
    .do-fixed-table td:nth-child(4) {
        border-right: 1px solid #b8c6da;
    }

    /* Input fields in table */
    .do-fixed-table input[type="text"],
    .do-fixed-table input[type="number"],
    .do-fixed-table textarea {
        width: 100%;
        padding: 0.12rem 0.22rem;
        font-size: 0.8em;
        border: 1px solid transparent;
        border-radius: 0.2rem;
        background: transparent;
    }

    /*
     * QTY values: browsers treat input width:auto like "fill the cell", so margin-left:auto
     * does not pull the field right. Use a fixed width + inline-block inside text-align:right td.
     */
    .do-fixed-table td.do-qty-cell input[data-do-role="qty"] {
        display: inline-block !important;
        width: 4.5rem !important;
        max-width: 72px !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        text-align: right !important;
        box-sizing: border-box;
        vertical-align: top;
    }

    .do-fixed-table td.do-qty-cell input[type="number"][data-do-role="qty"]::-webkit-outer-spin-button,
    .do-fixed-table td.do-qty-cell input[type="number"][data-do-role="qty"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .do-fixed-table td.do-qty-cell input[type="number"][data-do-role="qty"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .do-fixed-table input[type="text"]:hover,
    .do-fixed-table input[type="number"]:hover,
    .do-fixed-table textarea:hover,
    .do-fixed-table select:hover {
        border-color: #d6deea;
        background: #fff;
    }

    .do-fixed-table .form-control-sm,
    .do-fixed-table .form-select-sm {
        min-height: calc(1.2em + 0.24rem + 2px);
        padding-top: 0.12rem;
        padding-bottom: 0.12rem;
        line-height: 1.1;
    }

    .do-fixed-table .form-select,
    .do-fixed-table .form-select-sm,
    .do-fixed-table .form-control-sm,
    .do-fixed-table .btn-sm {
        font-size: 0.8em;
    }

    .do-item-name-text {
        font-size: 0.84em;
        line-height: 1.2;
    }

    .do-row-number-cell {
        font-weight: 600;
        color: #73829a !important;
        background: #f7f9fc;
    }

    .do-amount-cell {
        font-variant-numeric: tabular-nums;
        font-family: "Segoe UI", Tahoma, sans-serif;
    }

    .do-qty-row {
        flex-wrap: nowrap;
    }

    /* Keep row height tight; show move controls only when needed */
    .do-move-actions {
        display: none !important;
    }

    .item-row:hover .do-move-actions,
    .item-row:focus-within .do-move-actions {
        display: flex !important;
    }

    .do-price-row {
        margin-top: 2px !important;
        line-height: 1.05;
        flex-wrap: nowrap !important;
        white-space: nowrap;
        overflow-x: auto;
    }

    .do-price-tier-select {
        padding-right: 1.15rem !important;
        background-position: right 0.3rem center;
        text-overflow: ellipsis;
    }

    [data-do-add-item-button] {
        border: 1px solid #cfd8e6 !important;
        background: #ffffff !important;
        color: #5a6f8f !important;
        padding: 1px 4px !important;
    }

    [data-do-add-item-button]:hover {
        border-color: #b8c9de !important;
        background: #f5f9ff !important;
        color: #3f5f87 !important;
    }

    .do-fixed-table td:nth-child(4) .btn.btn-sm {
        border-radius: 4px;
    }

    .do-fixed-table input[type="text"]:focus,
    .do-fixed-table input[type="number"]:focus,
    .do-fixed-table textarea:focus,
    .do-fixed-table select:focus {
        outline: none;
        border-color: #3d7be0;
        box-shadow: 0 0 0 0.12rem rgba(13, 110, 253, 0.18);
        background: #fff;
    }

    /* Search dropdown */
    .do-fixed-table .list-group {
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .do-fixed-table .list-group-item {
        padding: 6px 10px;
        font-size: 0.85em;
        cursor: pointer;
    }

    .do-fixed-table .list-group-item:hover,
    .do-fixed-table .list-group-item.active {
        background-color: #0d6efd;
        color: #fff;
    }

    [x-cloak] { display: none !important; }

    .do-client-item-picker-modal {
        position: fixed;
        inset: 0;
        z-index: 20070;
    }
    .do-client-item-picker-backdrop {
        position: fixed;
        inset: 0;
        z-index: 20071;
        background: rgba(0, 0, 0, 0.45);
    }
    .do-client-item-picker-center {
        position: fixed;
        inset: 0;
        z-index: 20072;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        pointer-events: none;
    }
    .do-client-item-picker-center .modal-content {
        pointer-events: auto;
        width: 100%;
        max-width: 720px;
    }

    @media (max-width: 1200px) {
        .do-fixed-table th,
        .do-fixed-table td {
            padding: 2px 5px;
        }
    }
</style>
