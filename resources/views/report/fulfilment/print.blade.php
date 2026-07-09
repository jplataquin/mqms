<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request to Purchase Timeframe KPI Report</title>
    <style>
        /* General Styles for Screen and Print */
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333333;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .report-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Utility classes */
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .text-success { color: #198754 !important; }
        .text-danger { color: #dc3545 !important; }
        .fw-bold { font-weight: 700 !important; }
        .fw-semibold { font-weight: 600 !important; }
        .text-secondary { color: #6c757d !important; }
        .mb-2 { margin-bottom: 8px !important; }
        .mb-4 { margin-bottom: 24px !important; }
        .mt-4 { margin-top: 24px !important; }

        /* Action Toolbar (Screen only) */
        .no-print {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #0d6efd;
            color: white;
            border: 1px solid #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: 1px solid #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5c636a;
        }

        /* Report Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
            margin-bottom: 30px;
        }

        .header-table td {
            border: none !important;
            padding: 0 !important;
            vertical-align: middle;
        }

        .logo-img {
            max-width: 320px;
            height: auto;
        }

        .report-title {
            font-size: 24px;
            font-weight: 800;
            color: #111111;
            margin: 0;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: -0.01em;
        }

        /* KPI Metadata / Subtitle */
        .kpi-meta-section {
            border-top: 2px solid #333;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 0;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kpi-meta-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            color: #111;
        }

        .kpi-meta-subtitle {
            font-size: 12px;
            color: #6c757d;
            margin: 0;
        }

        .status-badge {
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .status-badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .status-badge-danger {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        /* Grid metrics cards */
        .metrics-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            flex: 1;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            position: relative;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            border-radius: 8px 8px 0 0;
        }

        .metric-card.primary::before { background-color: #0d6efd; }
        .metric-card.success::before { background-color: #198754; }
        .metric-card.danger::before { background-color: #dc3545; }

        .metric-card-value {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
            color: #111111;
            margin-bottom: 4px;
        }

        .metric-card-label {
            font-size: 11px;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .metric-card-subtext {
            font-size: 10px;
            color: #8c959d;
            margin-top: 4px;
            line-height: 1.3;
        }

        /* Visual Analytics Columns */
        .analytics-section {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
            align-items: center;
        }

        .gauge-column {
            flex: 0 0 220px;
            text-align: center;
        }

        .info-column {
            flex: 1;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
        }

        /* SVG Circular Progress */
        .gauge-wrapper {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }

        .gauge-bg {
            fill: none;
            stroke: #e9ecef;
            stroke-width: 12;
        }

        .gauge-progress {
            fill: none;
            stroke-width: 12;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }

        .gauge-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 100%;
        }

        .gauge-percentage {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
            color: #111111;
        }

        .gauge-label {
            font-size: 10px;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Detail List */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table td {
            border: none !important;
            padding: 8px 0;
            font-size: 13px;
        }

        .detail-table tr {
            border-bottom: 1px dashed #dee2e6;
        }

        .detail-table tr:last-child {
            border-bottom: none;
        }

        /* Signatures Section */
        .signatures-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            flex: 0 0 250px;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #333333;
            height: 40px;
            margin-bottom: 8px;
        }

        .signature-title {
            font-size: 12px;
            font-weight: 700;
            color: #111111;
        }

        .signature-subtext {
            font-size: 10px;
            color: #6c757d;
        }

        /* Print Override Styles */
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
                font-size: 11pt;
            }

            .no-print {
                display: none !important;
            }

            .report-wrapper {
                padding: 0;
                max-width: 100%;
            }

            .metric-card {
                background-color: #ffffff !important;
                border: 1px solid #999999 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-column {
                background-color: #ffffff !important;
                border: 1px solid #999999 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Enable colors when printing in browsers that support it */
            .text-success { color: #1e7e34 !important; }
            .text-danger { color: #bd2130 !important; }
            
            .gauge-bg { stroke: #e9ecef !important; }
            .gauge-progress { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            .status-badge {
                border: 1px solid #666666 !important;
                background-color: #ffffff !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .signatures-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Print / Close Toolbar (Visible on screen, hidden on print) -->
    <div class="no-print">
        <div>
            <button onclick="window.close()" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle;">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                </svg>
                Close Window
            </button>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle;">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                </svg>
                Print Document
            </button>
        </div>
    </div>

    <!-- Main Report Body -->
    <div class="report-wrapper">
        
        <!-- Header Table (Logo and Title) -->
        <table class="header-table">
            <tr>
                <td>
                    <img src="/storage/sys_images/header.png" class="logo-img" alt="Company Header Logo">
                </td>
                <td>
                    <h1 class="report-title">Fulfillment KPI</h1>
                </td>
            </tr>
        </table>

        <!-- Metadata & Status -->
        <div class="kpi-meta-section">
            <div>
                <h2 class="kpi-meta-title">Request to Purchase Timeframe KPI</h2>
                <div class="kpi-meta-subtitle">
                    Date Scope: <strong style="color: #111;">{{ Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ Carbon\Carbon::parse($to)->format('M d, Y') }}</strong>
                </div>
            </div>
            <div>
                @if($percentage >= 90)
                    <span class="status-badge status-badge-success">Target Met</span>
                @else
                    <span class="status-badge status-badge-danger">Needs Attention</span>
                @endif
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="metrics-grid">
            <!-- Total Requests -->
            <div class="metric-card primary">
                <div class="metric-card-value">{{ number_format($request_count) }}</div>
                <div class="metric-card-label">Total Requests</div>
                <div class="metric-card-subtext">Approved Material Quantity Requests (MQR).</div>
            </div>

            <!-- Target Hits -->
            <div class="metric-card success">
                <div class="metric-card-value text-success">{{ number_format($target_hit) }}</div>
                <div class="metric-card-label">Target Hits</div>
                <div class="metric-card-subtext">POs approved within 7 days of request.</div>
            </div>

            <!-- Target Misses -->
            <div class="metric-card danger">
                <div class="metric-card-value text-danger">{{ number_format($target_missed) }}</div>
                <div class="metric-card-label">Target Misses</div>
                <div class="metric-card-subtext">POs exceeding the 7-day threshold.</div>
            </div>
        </div>

        <!-- Visual Progress Ring and Details -->
        <div class="analytics-section">
            <!-- Visual Circular Progress Gauge -->
            <div class="gauge-column">
                <div class="gauge-wrapper">
                    <svg width="180" height="180" viewBox="0 0 180 180">
                        <circle class="gauge-bg" cx="90" cy="90" r="75"></circle>
                        <circle class="gauge-progress" cx="90" cy="90" r="75"
                                stroke="{{ $percentage >= 90 ? '#198754' : '#dc3545' }}"
                                stroke-dasharray="471.2"
                                stroke-dashoffset="{{ 471.2 - (471.2 * $percentage / 100) }}"></circle>
                    </svg>
                    <div class="gauge-center-text">
                        <div class="gauge-percentage">{{ $percentage }}%</div>
                        <div class="gauge-label">Fulfillment Rate</div>
                    </div>
                </div>
            </div>

            <!-- Descriptive Analytics and Policy -->
            <div class="info-column">
                <h3 class="fw-bold" style="font-size: 15px; margin-top: 0; margin-bottom: 12px; color: #111;">Performance Details</h3>
                <table class="detail-table">
                    <tr>
                        <td class="fw-semibold">Fulfillment Metric</td>
                        <td class="text-end text-secondary">MQR Approved to PO Approved ≤ 7 Days</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Target Threshold</td>
                        <td class="text-end text-secondary">90.0% Completion Rate</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Actual Performance</td>
                        <td class="text-end fw-bold {{ $percentage >= 90 ? 'text-success' : 'text-danger' }}">{{ $percentage }}%</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Evaluation Status</td>
                        <td class="text-end fw-bold {{ $percentage >= 90 ? 'text-success' : 'text-danger' }}">
                            {{ $percentage >= 90 ? 'MET (Healthy Operations)' : 'NOT MET (Requires Review)' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signatures Section -->
        <div class="signatures-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Prepared By</div>
                <div class="signature-subtext">Operations / Procurement Team</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Validated By</div>
                <div class="signature-subtext">Management / QA Auditor</div>
            </div>
        </div>

    </div>

</body>
</html>
