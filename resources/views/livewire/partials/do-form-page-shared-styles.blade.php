<style>
    .search-results {
        position: relative;
    }

    .do-header-fields label {
        font-size: 0.8em;
        margin-bottom: 0.1rem;
    }

    .do-header-fields .form-control,
    .do-header-fields .form-select,
    .do-header-fields textarea {
        font-size: 0.8em;
    }

    .do-header-fields p,
    .do-header-fields b {
        font-size: 1.0em;
    }

    .do-customer-detail {
        margin-top: 0.4rem;
        padding: 0.35rem 0.5rem 0.35rem 0.65rem;
        border-left: 3px solid #c5d4e8;
        background: #f8fafc;
        border-radius: 0 4px 4px 0;
        font-size: 0.78em;
        line-height: 1.35;
    }

    .do-customer-detail-title {
        font-size: 0.95em;
    }

    .do-created-by p {
        padding-top: 0.12rem;
    }

    .do-created-by-sep {
        border-color: #dee2e6 !important;
    }

    @media (min-width: 1200px) {
        .do-header-three-col .do-header-stack {
            min-height: 100%;
        }
    }

    .search-results ul {
        position: absolute;
        z-index: 100;
        background: white;
        width: 100%;
        border: 1px solid #ccc;
        max-height: 200px;
        overflow-y: auto;
    }
    .search-results ul li {
        padding: 10px;
        cursor: pointer;
    }
    .search-results ul li:hover {
        background-color: #f1f1f1;
    }
    .list-group .active {
        background-color: #0d6efd;
        color: #fff;
    }
    .list-group .active:hover {
        background-color: #0b5ed7;
        color: #fff;
    }

    .do-form-page {
        max-width: 1080px;
        margin-left: auto;
        margin-right: auto;
    }
</style>
