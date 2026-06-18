{{-- Shared company + recipient header spacing for DO / Quotation / PO previews --}}
<style>
    .content {
        padding: 0 20px 20px;
        flex: 1;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .company-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px solid #000;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }

    .company-info-left {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        text-align: left;
        width: 70%;
    }

    .company-logo-wrap {
        flex-shrink: 0;
        max-height: 75px;
        line-height: 0;
    }

    .company-logo-wrap img {
        max-height: 75px;
        width: auto;
        object-fit: contain;
        display: block;
    }

    .company-info-left h2,
    .company-info .company-info-text h2 {
        font-size: calc(1.1em + 1px);
        margin-bottom: 8px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .company-info-right {
        text-align: right;
        margin-top: 0;
        width: 28%;
        min-width: 300px;
        flex-shrink: 0;
        color: #000;
    }

    .company-info h2 {
        margin-bottom: 6px;
        color: #000;
        font-weight: bold;
        font-size: calc(1.1em + 1px);
        white-space: nowrap;
        text-transform: uppercase;
    }

    .company-info-right h2 {
        margin-bottom: 6px;
        white-space: nowrap;
        font-weight: 700;
        font-size: calc(1.0em + 1px);
        text-transform: uppercase;
        color: #000;
    }

    .company-info p {
        margin: 0;
        font-size: calc(0.78em + 1px);
    }

    .company-info-right p {
        color: #000;
        margin: 2px 0;
        font-size: calc(0.8em + 1px);
        white-space: nowrap;
    }

    .company-info-right strong {
        text-transform: uppercase;
        font-weight: bold;
    }

    .customer-info,
    .supplier-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0;
    }

    .customer-info-frame {
        border: 1px solid #000;
        padding: 4px;
        padding-left: 30px;
        width: 100%;
        font-size: 0.9em;
        height: 110px;
        overflow: hidden;
        line-height: 1.1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .customer-info-frame p {
        margin: 0;
    }

    .customer-info-frame p:first-child {
        text-indent: -26px;
    }

    .print-page--first {
        margin-top: 0;
    }

    .pages-container .print-page:first-child {
        margin-top: 20px;
    }

    @media print {
        .company-info h2,
        .company-info-right h2 {
            white-space: nowrap !important;
            font-size: 1.2em !important;
            color: #000 !important;
        }

        .company-info-right p {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
            color: #000 !important;
        }

        .company-info-right {
            min-width: 250px !important;
            width: auto !important;
            flex-shrink: 0 !important;
        }

        .pages-container .company-info {
            padding-bottom: 2px !important;
            margin-bottom: 6px !important;
        }

        .pages-container .company-logo-wrap img {
            max-height: 58px !important;
        }

        .pages-container .company-info-left h2,
        .pages-container .company-info .company-info-text h2 {
            margin-bottom: 4px !important;
        }

        .print-page--first {
            margin-top: 0 !important;
            page-break-before: auto !important;
        }

        .pages-container > .print-page:first-child {
            page-break-before: auto !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .pages-container:first-child .print-page--first {
            margin-top: 0 !important;
            padding-top: 20px !important;
        }
    }
</style>
