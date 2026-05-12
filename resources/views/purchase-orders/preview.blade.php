<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Purchase Order Preview</title>

    <style>
        /* Force consistent rendering across all browsers and settings */
        html {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            text-size-adjust: 100%;
            zoom: 1;
            font-size: 16px; /* Base font size - not affected by browser settings */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        /*
         * Minimum visual height per printed sheet (paginatePO uses row count; this keeps page boxes tall enough).
         * Letter minus @page top/bottom margins (0.5cm each), then × factor.
         */
        :root {
            --po-page-height-factor: 0.99;
            --po-page-printable-height: calc((11in - (0.5cm * 2)) * var(--po-page-height-factor));
        }

        body {
            font-family: Tahoma, Arial, sans-serif; /* Use Tahoma - thicker text, similar size to Arial, better letter "I" rendering */
            color: #000;
            background-color: #fff;
            font-size: 14px; /* Match print font-size (14px instead of 16px) */
            line-height: 1.3; /* Match print line-height (1.3 instead of 1.5) */
            zoom: 1; /* Force 1:1 zoom */
            transform: scale(1); /* Additional normalization */
            transform-origin: top left;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1000px; /* Wider for letter size */
            margin: 20px auto;
            border: 1px solid #000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            min-height: 100vh; /* Full viewport height for screen view */
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .content {
            padding: 24px 20px 20px;
            flex: 0 0 auto; /* Don't grow */
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .company-info-left {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-align: left;
            width: 70%;  /* Give more space to company info */
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
        
        .company-info-left h2 {
            font-size: calc(1.1em + 1px); /* Match DO font size */
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .company-info-right {
            text-align: right;
            margin-top: 0;
            width: 28%;  /* Fixed width for right side */
            min-width: 200px; /* Ensure minimum width */
        }

        .company-info-right h2 {
            margin-bottom: 6px;
            white-space: nowrap;
            font-size: calc(1.0em + 1px); /* Match DO font size */
            text-transform: uppercase;
        }

        .company-info-right p {
            margin: 2px 0;
            font-size: calc(0.78em + 1px); /* Match DO font size */
            line-height: 1.3; /* Explicitly set to match print */
        }

        .company-info h2 {
            margin-bottom: 6px;
            color: #000; /* Changed from #333 to black */
            font-weight: bold;
            font-size: calc(1.1em + 1px); /* Match DO font size */
            white-space: nowrap;
            text-transform: uppercase;
        }

        .company-info p {
            margin: 1px 0;
            font-size: calc(0.78em + 1px); /* Match DO font size */
            line-height: 1.3; /* Explicitly set to match print */
        }

        /* Removed duplicate print media query - using the one at line 549 instead */

        /* Ensure PO top-right info section is fully black */
        .company-info-right { color: #000; }
        .company-info-right h2 { color: #000; }
        .company-info-right p { color: #000; }
        .company-info-right strong { text-transform: uppercase; }
        @media print {
            .company-info-right h2 { color: #000 !important; }
            .company-info-right p { color: #000 !important; }
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .purchase-order-info {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            margin-bottom: 20px;
        }

        .supplier-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .supplier-info-frame {
            border: 1px solid #000;
            padding: 6px;
            padding-left: 40px;
            width: 100%;
            font-size: 1.1em;
            line-height: 1.3; /* Already matches print */
        }

        .supplier-info-frame p {
            margin: 0;
        }

        .supplier-info-frame p:first-child {
            text-indent: -40px; /* Pull 'To:' line back to margin */
        }

        .supplier-info-date {
            text-align: right;
            width: 30%;
            font-size: 0.9em;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            font-size: 0.85em;
        }

        .items-table th {
            padding: 6px 8px 4px 8px;
            text-align: left;
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8em;
            line-height: 1.3;
        }

        .items-table td {
            padding: 3px 7px;
            text-align: left;
            vertical-align: top;
            border-bottom: none;
            font-size: 1.0 em;
            line-height: 1.2;
        }

        /* Fixed column widths (favoring readability and totals) */
        .items-table th:nth-child(1), .items-table td:nth-child(1) { width: 5%; text-align: center; }   /* No. */
        .items-table th:nth-child(2), .items-table td:nth-child(2) { width: 60%; }   /* Description */
        .items-table th:nth-child(3), .items-table td:nth-child(3) { width: 8%; text-align: right; white-space: nowrap; }   /* QTY */
        .items-table th:nth-child(4), .items-table td:nth-child(4) { width: 12%; text-align: right; white-space: nowrap; }   /* Unit Price */
        .items-table th:nth-child(5), .items-table td:nth-child(5) { width: 15%; text-align: right; white-space: nowrap; }   /* Amount */


        .items-table tbody tr:last-child td { border-bottom: none; }

        /* Continuation rows: one table row per description line (grouped via data-po-line-group) */
        .items-table tr.items-table__desc-row td:nth-child(1) {
            border-bottom: none;
        }
        .items-table tr.items-table__desc-row td:nth-child(2) {
            padding-left: 15px;
            font-size: 1.0em;
            color: #000;
        }

        /* Totals Section Styles - only TOTAL, no subtotal/tax */
        .totals-section {
            border-top: 1px dotted #000;
            padding-top: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-weight: bold;
            font-size: 1.05em;
            text-transform: uppercase;
        }

        .total-label {
            text-align: left;
            width: 75%;
        }

        .total-value {
            text-align: right;
            width: 25%;
            white-space: nowrap;
        }

        .signature-section {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
            border-top: 1px solid #000 !important;
            padding: 10px 0 8px;
            margin-top: auto;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: avoid;
            page-break-before: avoid;
            font-size: 0.75em;
            line-height: 1.3;
            flex: 0 0 auto;
        }
        
        .signature-section p, .signature-section strong {
            text-transform: uppercase;
        }

        .signature-left {
            width: 45% !important;
        }

        .signature-right {
            width: 45% !important;
            text-align: center !important;
        }

        .signature-line {
            border-bottom: 1px solid #000 !important;
            margin-top: 30px !important;
            margin-bottom: 3px !important;
        }

        .signature-label {
            font-size: 0.75em !important;
            color: #000 !important;
            text-transform: uppercase;
            font-weight: bold !important;
            text-align: center !important;
            line-height: 1.3;
            }
            

        .signature-left {
            width: 45%;
        }

        .signature-right {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 80px;
            margin-bottom: 5px;
        }

        .signature-lines {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .signature-line-item {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            margin-top: 80px;
        }

        .signature-label {
            font-size: 0.9em;
            color: #666;
        }

        .button-container {
            text-align: right;
            padding: 20px;
            position: relative;
        }

        .print-button {
            position: fixed;
            top: 0;
            right: 0;
            padding: 10px 20px;
            font-size: 17px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000;
        }

        .back-button {
            position: fixed;
            top: 0;
            left: 0;
            padding: 10px 20px;
            font-size: 17px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000; 
        }

        .print-button:hover, .back-button:hover {
            background-color: #0056b3;
        }

        /* On-screen reminder for correct print formatting (match DO preview size) */
        .print-reminder {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 4px 8px;
            font-size: 10px;
            line-height: 1.2;
            color: #856404;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            z-index: 1000;
            max-width: 150px;
        }

        /* Force standard zoom detection warning */
        #zoom-warning {
            position: fixed;
            top: 110px;
            right: 20px;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: bold;
            color: #721c24;
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            border-radius: 4px;
            z-index: 10000;
            display: none;
        }

        .pages-container {
            display: none;
            flex-direction: column;
            gap: 28px;
            width: 100%;
        }

        @media print {
            .pages-container {
                gap: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .pages-container .print-page {
                margin: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .pages-container .print-page:not(:last-child) {
                margin-bottom: 0 !important;
            }
        }

        .page-counter {
            position: fixed;
            top: 120px;
            right: 20px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            color: #0d6efd;
            background: #e7f3ff;
            border: 2px solid #0d6efd;
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }

        .page-counter.show {
            display: block;
        }

        .pages-container .print-page {
            background-color: #fff;
            border: 1px solid #000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto 28px;
            max-width: 1000px;
            padding: 20px;
            box-sizing: border-box;
            }

        .pages-container .print-page:last-child {
            margin-bottom: 0;
        }

        .pages-container .print-page-footer .signature-section {
            padding: 10px 0 8px !important;
        }

        .pages-container[data-measuring="true"] .print-page {
            min-height: auto !important;
            height: auto !important;
        }
        
        /* During measurement, ensure flexbox calculates correctly */
        .pages-container[data-measuring="true"] .print-page-body {
            min-height: 0 !important;
        }
        
        .pages-container[data-measuring="true"] .print-page-footer {
            min-height: 0 !important;
        }

        .print-page {
            display: flex;
            flex-direction: column;
            position: relative;
            min-height: var(--po-page-printable-height);
            page-break-after: always;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .print-page--last {
            page-break-after: auto;
        }

        .print-page::after {
            content: 'Page ' attr(data-page-number) ' of ' attr(data-total-pages);
            position: absolute;
            bottom: 0.5cm;
            right: 0.75cm;
            font-size: 0.7em;
            font-family: Tahoma, Arial, sans-serif;
            color: #000;
        }

        @media print {
            .print-page::after {
                display: block;
                bottom: 0.12cm !important;
                right: 0.5cm !important;
            }
        }

        .print-page--first {
            margin-top: 20px;
        }

        @media print {
            .print-page--first {
                margin-top: 0 !important;
                page-break-before: auto !important;
            }
            
            .pages-container > .print-page:first-child {
                page-break-before: auto !important;
                margin-top: 0 !important;
            }
            
            .print-page-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .print-page-footer .signature-section {
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .print-page--last .print-page-body {
                page-break-after: avoid !important;
            }
        }

        .print-page-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1 1 auto;
        }

        /* Last page only: pin remark + total to bottom of body, directly above signature footer */
        .print-page-extras-above-signature {
            margin-top: auto;
            flex: 0 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .print-page-extras-above-signature [data-page-remark] {
            margin-top: 0;
            margin-bottom: 0;
        }
        
        /* Reduce spacing around remark when not in bottom bundle (should not occur on paginated output) */
        .print-page-body > [data-page-remark],
        .pages-container .print-page-body > [data-page-remark] {
            margin-top: -8px !important;
            margin-bottom: -8px !important;
        }

        .print-page-footer {
            margin-top: auto;
            padding-top: 10px;
            flex: 0 0 auto;
        }

        #signature-template {
            display: none !important;
        }

        /* @page only affects print/PDF — not the on-screen paginated preview */
        @page {
            margin: 0.5cm;
            size: letter;
        }

        @media print {
            /* Match screen root (16px) so em/rem match preview; body stays 14px */
            html {
                zoom: 1 !important;
                font-size: 16px !important;
            }

            body {
                font-family: Tahoma, Arial, sans-serif !important;
                background-color: #fff;
                counter-reset: page;
                zoom: 1 !important;
                transform: scale(1) !important;
                font-size: 14px !important;
                line-height: 1.3 !important;
            }
            
            /* Match on-screen preview typography (avoid print-only 1.2em jump) */
            .company-info-left h2,
            .company-info .company-info-text h2 {
                white-space: nowrap !important;
                font-size: calc(1.1em + 1px) !important;
                color: #000 !important;
                line-height: 1.2 !important;
            }
            .company-info-right h2 {
                white-space: nowrap !important;
                font-size: calc(1.0em + 1px) !important;
                color: #000 !important;
                line-height: 1.2 !important;
            }
            
            .company-info p, .company-info-right p {
                font-size: calc(0.78em + 1px) !important;
                line-height: 1.3 !important;
            }
            
            /* Compact vertical rhythm so one logical preview page fits one physical sheet */
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

            .supplier-info {
                margin-bottom: 10px !important;
            }
            
            /*
             * Match preview: left padding + negative first-line indent so “To:” sits left
             * while address / contact lines align under the company name (inside the border).
             */
            .supplier-info-frame {
                padding: 4px 8px 4px 40px !important;
                box-sizing: border-box !important;
                font-size: 1.05em !important;
                line-height: 1.22 !important;
            }
            .supplier-info-frame p:first-child {
                text-indent: -40px !important;
            }

            .print-button, .back-button {
                display: none !important; 
            }

            html, body {
                height: auto;
                margin: 0;
                padding: 0;
            }
            
            @page {
                margin: 0.5cm;
                size: letter;
            }
            
            .container {
                width: 100% !important;
                max-width: none !important;
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                min-height: auto !important;
                display: block !important;
                position: relative;
            }
            
            .content {
                padding: 0 !important;
                margin: 0 !important;
                flex: none !important;
            }
            
            .pages-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /*
             * Fit the printable area (100% of @page body). A fixed 1000px is wider than
             * letter minus margins and caused horizontal clip → missing price/amount columns.
             */
            .pages-container,
            .pages-container .print-page,
            .pages-container .print-page-body {
                overflow: visible !important;
            }
            /*
             * Pin signature at the bottom of each printed sheet: min-height matches Letter
             * minus @page top+bottom margins (0.5cm + 0.5cm = 1cm). Body grows; footer uses
             * margin-top:auto. page-break-inside:avoid keeps one logical .print-page on one sheet.
             */
            .pages-container .print-page {
                width: 100% !important;
                max-width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding: 8px 10px !important;
                box-sizing: border-box !important;
                min-height: calc(11in - 1cm) !important;
                height: auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .signature-section {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-end !important;
                border-top: 1px solid #000 !important;
                padding: 6px 0 6px !important;
                margin: 0 !important;
                margin-top: 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                width: 100%;
                box-sizing: border-box;
                flex: 0 0 auto;
                position: relative !important;
                background: white;
            }
            
            .items-table thead { 
                display: table-header-group; 
            }
            
            .items-table tr {
                page-break-inside: avoid;
            }
            
            .items-table tfoot {
                display: table-row-group;
            }
            
            .pages-container [data-page-remark],
            .pages-container [data-page-total] {
                position: relative !important;
            }
            
            /* In print, keep totals with remark - prevent page break between them */
            .pages-container [data-page-remark] {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            
            /* Only add page-break rules for totals - use existing CSS classes for styling */
            .pages-container [data-page-total] {
                page-break-before: avoid !important;
                break-before: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            /* If remark exists on a page, ensure totals stay with it */
            .pages-container .print-page:has([data-page-remark]) [data-page-total] {
                page-break-before: avoid !important;
                break-before: avoid !important;
            }
            
            .items-table { 
                table-layout: fixed !important;
            }
            /* Print-only column mix: narrower QTY, wider description (preview table stays 5/60/8/12/15) */
            .items-table th:nth-child(1), .items-table td:nth-child(1) {
                width: 4% !important;
            }
            .items-table th:nth-child(2), .items-table td:nth-child(2) {
                width: 70% !important;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .items-table th:nth-child(3), .items-table td:nth-child(3) {
                width: 4% !important;
                white-space: nowrap !important;
            }
            .items-table th:nth-child(4), .items-table td:nth-child(4) {
                width: 14% !important;
                white-space: nowrap !important;
            }
            .items-table th:nth-child(5), .items-table td:nth-child(5) {
                width: 10% !important;
                white-space: nowrap !important;
            }
            .items-table {
                margin-bottom: 0px !important;
            }

            /*
             * Print: keep rows slightly tighter than on-screen paginated preview so the same
             * row count (e.g. 26) fits one physical Letter sheet — otherwise the browser splits
             * the table and row 26 drops to sheet 2 even though the on-screen .print-page looks fine.
             */
            .pages-container .items-table th {
                font-size: 0.9em !important;
                padding: 4px 6px 3px 6px !important;
                line-height: 1.3 !important;
                white-space: nowrap !important;
                overflow: visible !important;
                text-overflow: unset !important;
            }

            .pages-container .items-table td {
                padding: 2px 3px !important;
                font-size: 1.1em !important;
                line-height: 1.25 !important;
                vertical-align: top !important;
            }

            .pages-container .totals-section {
                padding-top: -2px !important;
            }

            .print-reminder {
                display: none !important;
            }
            #zoom-warning {
                display: none !important;
            }
            .page-counter {
                display: none !important;
            }
            
            #print-source {
                display: none !important;
            }
            
            .pages-container {
                display: flex !important;
            }
            
            .pages-container .print-page {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding: 8px 10px !important;
                position: relative !important;
            }
            
            .pages-container .print-page-body,
            .pages-container .print-page-footer {
                position: relative !important;
            }
            
            /* Ensure flex layout works in print mode to match preview spacing */
            .pages-container .print-page {
                display: flex !important;
                flex-direction: column !important;
            }
            
            .pages-container .print-page-body {
                display: flex !important;
                flex-direction: column !important;
                flex: 1 1 auto !important;
                min-height: 0 !important;
                gap: 4px !important;
            }
            
            .pages-container .print-page-extras-above-signature {
                margin-top: auto !important;
                gap: 0 !important;
            }

            .pages-container .print-page-extras-above-signature [data-page-remark] {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }

            /* Reduce spacing around remark when direct child of body (legacy) */
            .pages-container .print-page-body > [data-page-remark] {
                margin-top: -6px !important;
                margin-bottom: -6px !important;
            }
            
            .pages-container .print-page-footer {
                margin-top: auto !important;
                flex: 0 0 auto !important;
                padding-top: 6px !important;
            }

            .pages-container .print-page-footer .signature-section {
                padding: 8px 0 6px !important;
                margin-top: 0 !important;
            }

            .pages-container .print-page-footer .signature-line {
                margin-top: 28px !important;
                margin-bottom: 2px !important;
            }

            .pages-container .supplier-info {
                margin-bottom: 6px !important;
            }

            .pages-container .total-row {
                padding: 1px 0 !important;
                font-size: 1em !important;
            }
        }
        
        .items-table {
            font-size: 0.85em;
        }
        .items-table th {
            font-size: 0.8em;
            line-height: 1.3;
            white-space: nowrap;
            overflow: visible;
            text-overflow: unset;
        }
        .items-table td {
            font-size: 1.1em;
            line-height: 1.1;
        }

    </style>
</head>
<body>
    <div class="print-reminder">✓ Optimized for Letter Size (8.5" × 11") paper</div>
    <div id="zoom-warning">⚠️ Browser zoom is not 100%! Press Ctrl+0 (Cmd+0 on Mac) to reset zoom for accurate printing.</div>
    <div id="page-counter" class="page-counter">Calculating pages...</div>
    <div class="container">
        <button onclick="history.back()" class="back-button">Back</button>
        <div class="content">
            <div id="print-source">
                <div class="page-header">
            <!-- Company Information Section -->
            <div class="company-info">
                <div class="company-info-left">
                    <div class="company-logo-wrap">
                        <img src="{{ asset('images/company-logo-1.png') }}" alt="{{ $companyProfile->company_name ?? 'Company' }}" />
                    </div>
                    <div class="company-info-text">
                    <h2>{{ $companyProfile->company_name }}</h2>
                    <p>{{ $companyProfile->company_no }} | GST No: {{ $companyProfile->gst_no }}</p>
                    <p>{{ $companyProfile->address_line1 }}</p>
                    @if($companyProfile->address_line2)
                        <p>{{ $companyProfile->address_line2 }}</p>
                    @endif
                    @if($companyProfile->address_line3)
                        <p>{{ $companyProfile->address_line3 }}</p>
                    @endif
                    @if($companyProfile->address_line4)
                        <p>{{ $companyProfile->address_line4 }}</p>
                    @endif
                    <p>Contact Number: {{ $companyProfile->phone_num1 }} 
                        @if($companyProfile->phone_num2)
                            | {{ $companyProfile->phone_num2 }}
                        @endif
                    </p>
                    <p>Fax: {{ $companyProfile->fax_num }} | Email: {{ $companyProfile->email }}</p>
                    </div>
                </div>
                <div class="company-info-right">
                    <h2>Purchase Order</h2>
                    <p><strong>PO No:</strong> <strong>{{ $purchaseOrder->po_num }}</strong></p>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->date)->format('d/m/Y') }}</p>
                    <p><strong>Reference No:</strong> {{ $purchaseOrder->ref_num ?? '-' }}</p>
                </div>
            </div>

            <!-- Supplier Information Section -->
            <div class="supplier-info">
                <div class="supplier-info-frame">
                    <p><strong>To: </strong><strong>{{ $purchaseOrder->supplierSnapshot->sup_name ?? 'N/A' }}</strong></p>

                    @if($purchaseOrder->supplierSnapshot->address_line1)
                    <p>{{ $purchaseOrder->supplierSnapshot->address_line1 }}</p>
                    @endif
                    @if($purchaseOrder->supplierSnapshot->address_line2)
                        <p>{{ $purchaseOrder->supplierSnapshot->address_line2 }}</p>
                    @endif
                    @if($purchaseOrder->supplierSnapshot->address_line3)
                        <p>{{ $purchaseOrder->supplierSnapshot->address_line3 }}</p>
                    @endif
                    @if($purchaseOrder->supplierSnapshot->address_line4)
                        <p>{{ $purchaseOrder->supplierSnapshot->address_line4 }}</p>
                    @endif
                    <p>Contact Number: {{ $purchaseOrder->supplierSnapshot->phone_num }}</p>
                    @if($purchaseOrder->supplierSnapshot->email)
                    <p>Email: {{ $purchaseOrder->supplierSnapshot->email }}</p>
                    @endif
                </div>
                </div>
            </div>

            <!-- Purchase Order Details -->
            <div class="table-area" id="items-table-source">
            <table class="items-table" id="items-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Description</th>
                        <th>QTY</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $index => $item)
                    @php
                        $poDescLines = [];
                        if ($item->item && !empty($item->item->details)) {
                            foreach (explode("\n", $item->item->details) as $line) {
                                if (trim($line) !== '') {
                                    $poDescLines[] = trim($line);
                                }
                            }
                        }
                        if (!empty($item->more_description)) {
                            foreach (explode("\n", $item->more_description) as $line) {
                                if (trim($line) !== '') {
                                    $poDescLines[] = trim($line);
                                }
                            }
                        }
                    @endphp
                    <tr data-po-line-group="{{ $index }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->custom_item_name ?? ($item->item->item_name ?? 'N/A') }}</td>
                        <td>
                            @php
                                $q = floatval($item->quantity);
                                $qtyFmt = (round($q) == $q) ? number_format($q, 0) : ((round($q * 100) == round($q * 10) * 10) ? number_format($q, 1) : number_format($q, 2));
                            @endphp
                            {{ $qtyFmt }}
                        </td>
                        <td>{{ $item->total_price_line_item > 0 ? number_format($item->unit_price, 2) : '' }}</td>
                        <td>{{ $item->total_price_line_item > 0 ? number_format($item->total_price_line_item, 2) : '' }}</td>
                    </tr>
                    @foreach ($poDescLines as $descLine)
                    <tr data-po-line-group="{{ $index }}" class="items-table__desc-row">
                        <td></td>
                        <td>• {{ $descLine }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            </div>

            @if(!empty($purchaseOrder->remark))
                <div id="remark-source" style="margin: 0 0 0 5%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; position: relative; z-index: 5; background: white;">
                    <div style="font-size: 0.95em; font-family: Tahoma, Arial, sans-serif; line-height: 1.3; color: #000; display: flex;">
                        <span style="font-weight: bold; min-width: 60px; text-transform: uppercase;">Remark:&nbsp;&nbsp;&nbsp;</span>
                        <div style="flex: 1;">{!! nl2br(e($purchaseOrder->remark)) !!}</div>
                    </div>
                </div>
            @endif

            <!-- Totals Section - only TOTAL, no tax -->
                @php($poCurrency = $purchaseOrder->supplierSnapshot->currency ?? 'MYR')
            <div id="totals-source" class="totals-section" style="border-top: 1px dotted #000; padding-top: 8px;">
                <div class="total-row" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-weight: bold; font-size: 1.05em; text-transform: uppercase;">
                    <span class="total-label" style="text-align: left; width: 75%;">Total</span>
                    <span class="total-value" style="text-align: right; width: 25%; white-space: nowrap;">{{ $poCurrency }} {{ number_format($purchaseOrder->final_total_price ?? 0, 2) }}</span>
                </div>
                </div>
            </div>

            <div id="pages-container" class="pages-container"></div>
        </div>
        
        <div id="signature-template" class="signature-section" style="display: none;">
            <div class="signature-left">
                <div class="signature-line"></div>
                <p class="signature-label">Company Chop & Signature</p>
            </div>
            <div class="signature-right">
                <p><strong>{{ $companyProfile->company_name }}</strong></p>
                <br><br><br>
                <div class="signature-line"></div>
                <p class="signature-label">(Authorized Signature)</p>
            </div>
        </div>
    </div>

    <button type="button" onclick="triggerPrint()" class="print-button">Print</button>

    <script>
        // Force standard zoom level detection and warning
        (function() {
            function checkZoom() {
                // Multiple methods to detect zoom level
                var zoom = Math.round(window.devicePixelRatio * 100);
                var browserZoom = Math.round((window.outerWidth / window.innerWidth) * 100);
                
                // Detect zoom level (accounting for browser differences)
                var detectedZoom = zoom;
                if (browserZoom > 0 && browserZoom !== Infinity) {
                    detectedZoom = browserZoom;
                }
                
                // Show warning if not 100%
                var warning = document.getElementById('zoom-warning');
                if (warning) {
                    if (Math.abs(detectedZoom - 100) > 5) { // 5% tolerance
                        warning.style.display = 'block';
                        warning.textContent = '⚠️ Browser zoom is ' + detectedZoom + '%! Press Ctrl+0 (Cmd+0 on Mac) to reset to 100% for accurate printing.';
                    } else {
                        warning.style.display = 'none';
                    }
                }
            }
            
            // Check zoom on load and resize
            checkZoom();
            window.addEventListener('resize', checkZoom);
            window.addEventListener('load', checkZoom);
            
            // Set body zoom to 100% programmatically
            document.body.style.zoom = "100%";
        })();

        function triggerPrint() {
            try { paginatePO(true); } catch (e) {}
            // Mark as printed before opening print dialog
            fetch('{{ route('purchase-orders.mark-printed', $purchaseOrder->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function() {
                setTimeout(function(){ 
                    try { paginatePO(true); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            }).catch(function(error) {
                console.error('Failed to mark as printed:', error);
                setTimeout(function(){ 
                    try { paginatePO(true); } catch (e) {}
                    setTimeout(function(){ window.print(); }, 150);
                }, 50);
            });
        }
    </script>
    <script>
        (function () {
            /** Max tbody <tr> rows per page (main line + each description line each count as one). Keep in sync with PO line-item cap in POForm. */
            var MAX_ROWS_PER_PAGE = 26;

            var scheduled = false;
            var building = false;

            function removeIds(node) {
                if (!node) {
                    return;
                }
                if (node.removeAttribute) {
                    node.removeAttribute('id');
                }
                node.querySelectorAll('[id]').forEach(function (el) {
                    el.removeAttribute('id');
                });
            }

            function createPage(pagesContainer, headerTemplate, theadTemplate, signatureTemplate, isFirstPage) {
                var page = document.createElement('div');
                page.className = 'print-page';
                if (isFirstPage) {
                    page.classList.add('print-page--first');
                }

                var body = document.createElement('div');
                body.className = 'print-page-body';
                page.appendChild(body);

                if (headerTemplate) {
                    var headerClone = headerTemplate.cloneNode(true);
                    removeIds(headerClone);
                    body.appendChild(headerClone);
                }

                var tableWrapper = document.createElement('div');
                var table = document.createElement('table');
                table.className = 'items-table';
                tableWrapper.appendChild(table);
                body.appendChild(tableWrapper);

                if (theadTemplate) {
                    var theadClone = theadTemplate.cloneNode(true);
                    removeIds(theadClone);
                    table.appendChild(theadClone);
                }

                var tbody = document.createElement('tbody');
                table.appendChild(tbody);

                var footerWrapper = document.createElement('div');
                footerWrapper.className = 'print-page-footer';
                var signature = signatureTemplate.cloneNode(true);
                signature.style.display = '';
                removeIds(signature);
                footerWrapper.appendChild(signature);
                page.appendChild(footerWrapper);

                pagesContainer.appendChild(page);

                return {
                    page: page,
                    body: body,
                    table: table,
                    tbody: tbody,
                    footer: footerWrapper
                };
        }

            function paginatePO(force) {
                if (building) {
                    return;
                }
                building = true;

                try {
                    var source = document.getElementById('print-source');
                    var pagesContainer = document.getElementById('pages-container');
                    var signatureTemplate = document.getElementById('signature-template');
                    var itemsTable = document.getElementById('items-table');

                    if (!source || !pagesContainer || !signatureTemplate || !itemsTable) {
                        return;
                    }

                    var headerTemplate = source.querySelector('.page-header');
                    var theadTemplate = itemsTable.querySelector('thead');
                    if (!theadTemplate) {
                        return;
                    }

                    var rows = Array.from(itemsTable.querySelectorAll('tbody tr'));

                    function buildPoRowGroups(tbodyRows) {
                        var groups = [];
                        var i = 0;
                        while (i < tbodyRows.length) {
                            var row = tbodyRows[i];
                            var key = row.getAttribute('data-po-line-group');
                            if (key === null || key === '') {
                                groups.push([row]);
                                i += 1;
                                continue;
                            }
                            var g = [row];
                            i += 1;
                            while (i < tbodyRows.length && tbodyRows[i].getAttribute('data-po-line-group') === key) {
                                g.push(tbodyRows[i]);
                                i += 1;
                            }
                            groups.push(g);
                        }
                        return groups;
                    }

                    var rowGroups = buildPoRowGroups(rows);
                    var remarkSource = document.getElementById('remark-source');
                    var totalsSource = document.getElementById('totals-source');

                    var isFirstPage = true;
                    var activePage = null;
                    var lastPageWithContent = null;

                    pagesContainer.innerHTML = '';
                    pagesContainer.style.display = 'flex';
                    pagesContainer.removeAttribute('data-measuring');
                    pagesContainer.style.opacity = '';
                    pagesContainer.style.position = '';
                    pagesContainer.style.top = '';
                    pagesContainer.style.left = '';
                    pagesContainer.style.width = '';
                    pagesContainer.style.height = '';
                    pagesContainer.style.pointerEvents = '';
                    pagesContainer.style.zIndex = '';
                    pagesContainer.style.overflow = '';

                    function ensurePage() {
                        if (!activePage) {
                            activePage = createPage(pagesContainer, headerTemplate, theadTemplate, signatureTemplate, isFirstPage);
                            isFirstPage = false;
                        }
                    }

                    function tbodyRowCount(tbody) {
                        return tbody ? tbody.children.length : 0;
                    }

                    function appendExtraBlock(targetBody, sourceNode, attr) {
                        if (!sourceNode || !targetBody) {
                            return;
                        }
                        var clone = sourceNode.cloneNode(true);
                        removeIds(clone);
                        if (attr) {
                            clone.setAttribute(attr, '');
                        }
                        targetBody.appendChild(clone);
                    }

                    rowGroups.forEach(function (groupRows) {
                        var clones = groupRows.map(function (r) {
                            var c = r.cloneNode(true);
                            removeIds(c);
                            return c;
                        });
                        // Keep a PO line + all its continuation description rows together
                        // when possible. Only split if the group itself exceeds the per-page max.
                        if (clones.length <= MAX_ROWS_PER_PAGE) {
                            ensurePage();
                            var space = MAX_ROWS_PER_PAGE - tbodyRowCount(activePage.tbody);
                            if (space <= 0 || clones.length > space) {
                                activePage = null;
                                ensurePage();
                            }
                            clones.forEach(function (c) {
                                activePage.tbody.appendChild(c);
                            });
                            lastPageWithContent = activePage;
                            if (tbodyRowCount(activePage.tbody) >= MAX_ROWS_PER_PAGE) {
                                activePage = null;
                            }
                        } else {
                            // LEGACY split fallback: a single PO line (with very long description) can
                            // be longer than MAX_ROWS_PER_PAGE, so we split it across pages.
                            var gi = 0;
                            while (gi < clones.length) {
                                ensurePage();
                                var space = MAX_ROWS_PER_PAGE - tbodyRowCount(activePage.tbody);
                                if (space <= 0) {
                                    activePage = null;
                                    ensurePage();
                                    space = MAX_ROWS_PER_PAGE;
                                }
                                var take = Math.min(space, clones.length - gi);
                                for (var t = 0; t < take; t++) {
                                    activePage.tbody.appendChild(clones[gi + t]);
                                }
                                gi += take;
                                lastPageWithContent = activePage;
                                if (tbodyRowCount(activePage.tbody) >= MAX_ROWS_PER_PAGE) {
                                    activePage = null;
                                }
                            }
                        }
                    });

                    if (rows.length === 0) {
                        ensurePage();
                        lastPageWithContent = activePage;
                    }

                    if (!lastPageWithContent) {
                        ensurePage();
                        lastPageWithContent = activePage;
                    }

                    // Extras page placement rules:
                    // - Totals WITHOUT remark: totals can stay on the last item page even if it already has MAX_ROWS_PER_PAGE.
                    // - With remark: estimate "remark + total" space as (remarkLines + 2).
                    //   If (itemRowsOnLastPage + (remarkLines + 2)) > MAX_ROWS_PER_PAGE, move remark+total to a new page.
                    var extrasTarget = lastPageWithContent;

                    var hasRemark = !!remarkSource;
                    var remarkLineCount = 0;
                    if (hasRemark) {
                        // remark uses nl2br() so each newline becomes <br>. Each segment => one visual "row".
                        // Example: 1 remark line => brCount=0 => remarkLineCount=1
                        var brCount = remarkSource.querySelectorAll('br').length;
                        remarkLineCount = brCount + 1;
                    }

                    var extrasRowsNeeded = hasRemark ? (remarkLineCount + 2) : 0;
                    var lastRowsUsed = tbodyRowCount(extrasTarget.tbody);
                    if (extrasRowsNeeded > 0 && (lastRowsUsed + extrasRowsNeeded) > MAX_ROWS_PER_PAGE) {
                        activePage = null;
                        ensurePage();
                        extrasTarget = activePage;
                    }

                    var extrasWrap = document.createElement('div');
                    extrasWrap.className = 'print-page-extras-above-signature';
                    appendExtraBlock(extrasWrap, remarkSource, 'data-page-remark');
                    appendExtraBlock(extrasWrap, totalsSource, 'data-page-total');
                    extrasTarget.body.appendChild(extrasWrap);

                    var renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    renderedPages.forEach(function (page) {
                        var tbody = page.querySelector('tbody');
                        var hasRows = tbody && tbody.children.length > 0;
                        var hasExtras = page.querySelector('[data-page-remark], [data-page-total]');
                        if (!hasRows && !hasExtras) {
                            page.parentNode.removeChild(page);
                        }
                    });

                    renderedPages = Array.from(pagesContainer.querySelectorAll('.print-page'));
                    if (renderedPages.length > 0) {
                        var totalPages = renderedPages.length;
                        renderedPages.forEach(function (page, index) {
                            page.classList.remove('print-page--last');
                            page.setAttribute('data-page-number', index + 1);
                            page.setAttribute('data-total-pages', totalPages);
                        });
                        renderedPages[renderedPages.length - 1].classList.add('print-page--last');
                    }

                    source.style.display = 'none';

                    var pageCounter = document.getElementById('page-counter');
                    if (pageCounter) {
                        var pageCount = renderedPages.length;
                        if (pageCount > 0) {
                            pageCounter.textContent = 'Total Pages: ' + pageCount + (pageCount === 1 ? ' page' : ' pages');
                            pageCounter.classList.add('show');
                        } else {
                            pageCounter.classList.remove('show');
                        }
                    }
                } finally {
                    building = false;
                }
            }

            function schedulePaginate() {
                if (scheduled) {
                    return;
                }
                scheduled = true;
                window.requestAnimationFrame(function () {
                    scheduled = false;
                    paginatePO(false);
                });
            }

            window.paginatePO = paginatePO;

            window.addEventListener('load', function () {
                paginatePO(true);
            });

            window.addEventListener('resize', schedulePaginate);

            if (window.matchMedia) {
                // Don't repaginate when entering print mode - keep same layout as preview
                // This ensures print matches preview exactly
                // The CSS page-break rules will handle any print-specific layout needs
                var mq = window.matchMedia('print');
                if (mq.addEventListener) {
                    mq.addEventListener('change', function (e) {
                        // Removed repagination on print - keep preview layout
                        // if (e.matches) {
                        //     pageHeightCache = null;
                        //     paginatePO(true);
                        // }
                    });
                } else if (mq.addListener) {
                    mq.addListener(function (e) {
                        // Removed repagination on print - keep preview layout
                        // if (e.matches) {
                        //     pageHeightCache = null;
                        //     paginatePO(true);
                        // }
                    });
                }
            }

            window.addEventListener('beforeprint', function () {
                setTimeout(function () {
                    paginatePO(true);
                }, 10);
            });

            window.addEventListener('afterprint', function () {
                setTimeout(function () {
                    paginatePO(true);
                }, 10);
            });
        })();
    </script>
</body>
</html>