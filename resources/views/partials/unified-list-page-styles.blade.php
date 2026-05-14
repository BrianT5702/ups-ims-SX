<style>
    /* Shared with Transaction Log, DO / PO / Quotation list pages */
    .list-page-unified-density .list-page-unified-filters .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        color: #2f3b4b;
    }
    .list-page-unified-density .form-control-sm,
    .list-page-unified-density .form-select-sm {
        font-size: 0.8rem;
        min-height: calc(1.35em + 0.35rem + 2px);
        padding-top: 0.18rem;
        padding-bottom: 0.18rem;
    }
    .list-page-unified-density .list-page-unified-title {
        font-size: 1.25rem;
    }
    .list-page-unified-density .btn-sm {
        font-size: 0.78rem;
    }
    .list-page-unified-density .alert {
        font-size: 0.8rem;
    }
    .transaction-log-reset-toolbar {
        margin-top: 0.1rem;
        margin-bottom: 0.25rem;
    }
    .transaction-log-reset-toolbar .transaction-log-reset-btn {
        padding-top: 0.15rem;
        padding-bottom: 0.15rem;
        line-height: 1.2;
    }
    .transaction-log-page-header {
        background: #f7f9fc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.45rem 1rem;
    }
    .transaction-log-card-body {
        padding-top: 0.35rem !important;
    }
    .list-page-unified-density .transaction-log-card-body .list-page-unified-filters .form-label {
        font-size: 0.8rem;
    }
    .transaction-log-pagination,
    .do-list-pagination,
    .po-list-pagination,
    .quotation-list-pagination,
    .inventory-list-pagination {
        position: relative;
        width: 100%;
        margin-top: 0;
        padding-top: 0.5rem;
        border-top: 1px solid #dee2e6;
        background-color: #fff;
        z-index: 10;
        font-size: 0.82rem;
    }

    /*
     * Vertically scrollable table body with sticky header row.
     * Pair with Bootstrap .table-responsive on the same element (overrides overflow-x-only).
     */
    .table-responsive.list-sticky-table-scroll {
        max-width: 100%;
        margin-top: 0.25rem;
        max-height: min(68vh, calc(100dvh - 13rem));
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
    .table-responsive.list-sticky-table-scroll::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .table-responsive.list-sticky-table-scroll::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 5px;
    }
    .table-responsive.list-sticky-table-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 5px;
    }
    .table-responsive.list-sticky-table-scroll::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    .table-responsive.list-sticky-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 5;
    }
</style>
