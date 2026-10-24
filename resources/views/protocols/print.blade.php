<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $result['protocol'] ?? 'Protocol Diagnostic' }} - {{ $project->name }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 15mm 15mm 15mm 15mm;
            line-height: 1.35;
        }
        h1, h2, h3, h4, p {
            margin: 0 0 6px 0;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Print Controls */
        .no-print {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn-print {
            background: #000;
            color: #fff;
            border: 1px solid #000;
            padding: 5px 14px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-close {
            background: #fff;
            color: #000;
            border: 1px solid #000;
            padding: 5px 14px;
            font-size: 11px;
            cursor: pointer;
        }

        /* Black and white tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 5px 7px;
            vertical-align: top;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        thead th {
            border-bottom: 2px solid #000;
        }

        /* Status box */
        .status-box {
            border: 2px solid #000;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-weight: bold;
        }
        .status-pass {
            background: #fff;
        }
        .status-fail {
            background: #f0f0f0;
        }

        .section-title {
            font-size: 11.5px;
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        .sign-off {
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .sign-off table {
            border: none;
        }
        .sign-off td {
            border: none;
            padding: 10px 15px 0 0;
        }
        .sign-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 10px;
        }

        @media print {
            body { margin: 8mm; }
            .no-print { display: none; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print Report</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <!-- Header Table -->
    <table>
        <tbody>
            <tr>
                <td style="width: 20%;" class="fw-bold">PROTOCOL</td>
                <td style="width: 45%;" class="fw-bold">{{ $result['protocol'] ?? $protocol }}</td>
                <td style="width: 15%;" class="fw-bold">PRINTED</td>
                <td style="width: 20%;">{{ $printedAt }}</td>
            </tr>
            <tr>
                <td class="fw-bold">PROJECT</td>
                <td>#{{ $project->id }} - {{ $project->name }}</td>
                <td class="fw-bold">STATUS</td>
                <td class="fw-bold uppercase">{{ !empty($result['passed']) ? 'CONFORMING (PASS)' : 'NON-CONFORMING (FAIL)' }}</td>
            </tr>
            <tr>
                <td class="fw-bold">SUMMARY</td>
                <td colspan="3">{{ $result['summary'] ?? 'Diagnostic evaluation complete.' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Protocol Specific: Level 1 Budget Diagnostic -->
    @if (($result['slug'] ?? '') === 'level-1-budget-diagnostic')
        <!-- Metric Summary -->
        <table>
            <thead>
                <tr>
                    <th class="text-center">Total Items Checked</th>
                    <th class="text-center">Conforming Items</th>
                    <th class="text-center">Violations Count</th>
                    <th class="text-center">Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $result['total_items'] ?? 0 }}</td>
                    <td class="text-center">{{ max(0, ($result['total_items'] ?? 0) - ($result['violations_count'] ?? 0)) }}</td>
                    <td class="text-center fw-bold">{{ $result['violations_count'] ?? 0 }}</td>
                    <td class="text-center fw-bold uppercase">{{ !empty($result['passed']) ? 'PASS' : 'FAIL' }}</td>
                </tr>
            </tbody>
        </table>

        @if (!empty($result['violations']) && count($result['violations']) > 0)
            <div class="section-title">Non-Conforming Items (Exceeding Budget)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 24%;">Component Item</th>
                        <th style="width: 22%;">Component</th>
                        <th style="width: 20%;">Contract Item</th>
                        <th style="width: 10%;" class="text-end">Budget</th>
                        <th style="width: 10%;" class="text-end">Requested</th>
                        <th style="width: 10%;" class="text-end">Excess</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['violations'] as $idx => $v)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $v['component_item_name'] ?? 'N/A' }}</td>
                            <td>{{ $v['component_name'] ?? 'N/A' }}</td>
                            <td>{{ $v['contract_item_name'] ?? ($v['contract_item_code'] ?? 'N/A') }}</td>
                            <td class="text-end">{{ number_format($v['allowed_budget'] ?? 0, 2) }} {{ $v['unit_text'] ?? '' }}</td>
                            <td class="text-end">{{ number_format($v['total_requested'] ?? 0, 2) }} {{ $v['unit_text'] ?? '' }}</td>
                            <td class="text-end fw-bold">+{{ number_format($v['over_budget_quantity'] ?? 0, 2) }} {{ $v['unit_text'] ?? '' }}</td>
                        </tr>
                        @if (!empty($v['breakdown']) && count($v['breakdown']) > 0)
                            <tr>
                                <td></td>
                                <td colspan="6" style="padding: 4px 6px;">
                                    <table style="margin-bottom: 0; background: #fff;">
                                        <thead>
                                            <tr>
                                                <th style="width: 12%;">MQR #</th>
                                                <th style="width: 16%;">Date Needed</th>
                                                <th style="width: 40%;">Material Item</th>
                                                <th style="width: 16%;" class="text-end">MQR Qty</th>
                                                <th style="width: 16%;" class="text-end">Actual Scaled</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($v['breakdown'] as $b)
                                                <tr>
                                                    <td>#{{ $b['material_quantity_request_id'] }}</td>
                                                    <td>{{ $b['request_date_needed'] ?? 'N/A' }}</td>
                                                    <td>{{ $b['material_item_name'] ?? 'N/A' }}</td>
                                                    <td class="text-end">{{ number_format($b['requested_quantity'] ?? 0, 2) }}</td>
                                                    <td class="text-end fw-bold">{{ number_format($b['actual_quantity'] ?? 0, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="status-box status-pass text-center">
                ALL EVALUATED COMPONENT ITEMS CONFORM STRICTLY TO AUTHORIZED BUDGETS.
            </div>
        @endif

    <!-- Protocol Specific: Level 2 Budget Diagnostic -->
    @elseif (($result['slug'] ?? '') === 'level-2-budget-diagnostic')
        <!-- Metric Summary -->
        <table>
            <thead>
                <tr>
                    <th class="text-center">MQR Items Checked</th>
                    <th class="text-center">MQR Qty Overages</th>
                    <th class="text-center">Contract Items</th>
                    <th class="text-center">Contract Amount Overages</th>
                    <th class="text-center">Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $result['total_mqr_items_checked'] ?? 0 }}</td>
                    <td class="text-center fw-bold">{{ $result['quantity_violations_count'] ?? 0 }}</td>
                    <td class="text-center">{{ $result['total_contract_items'] ?? 0 }}</td>
                    <td class="text-center fw-bold">{{ $result['amount_violations_count'] ?? 0 }}</td>
                    <td class="text-center fw-bold uppercase">{{ !empty($result['passed']) ? 'PASS' : 'FAIL' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Part 1: Quantity Violations -->
        <div class="section-title">Part 1: Purchase Order Quantities vs MQR Requisitions</div>
        @if (!empty($result['quantity_violations']) && count($result['quantity_violations']) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 10%;">MQR #</th>
                        <th style="width: 32%;">Material Item</th>
                        <th style="width: 24%;">Component</th>
                        <th style="width: 10%;" class="text-end">MQR Qty</th>
                        <th style="width: 10%;" class="text-end">PO Qty</th>
                        <th style="width: 10%;" class="text-end">Over Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['quantity_violations'] as $idx => $qv)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>#{{ $qv['material_quantity_request_id'] ?? 'N/A' }}</td>
                            <td>{{ $qv['material_name'] ?? 'N/A' }}</td>
                            <td>{{ $qv['component_name'] ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($qv['allowed_mqr_quantity'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($qv['total_po_quantity'] ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">+{{ number_format($qv['over_ordered_quantity'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table>
                <tbody>
                    <tr>
                        <td class="text-center fw-bold">No quantity overages. All approved purchase order quantities are within MQR limits.</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <!-- Part 2: Contract Item Financial Summary -->
        <div class="section-title">Part 2: Contract Item Financial Utilization</div>
        @if (!empty($result['contract_items_summary']) && count($result['contract_items_summary']) > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Code</th>
                        <th style="width: 34%;">Contract Item</th>
                        <th style="width: 15%;" class="text-end">Contract Amount</th>
                        <th style="width: 15%;" class="text-end">Approved PO Total</th>
                        <th style="width: 12%;" class="text-end">% Used</th>
                        <th style="width: 14%;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['contract_items_summary'] as $ci)
                        <tr>
                            <td>{{ $ci['contract_item_code'] ?? 'N/A' }}</td>
                            <td>{{ $ci['contract_item_name'] ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($ci['contract_amount'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($ci['total_po_amount'] ?? 0, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($ci['percentage_used'] ?? 0, 2) }}%</td>
                            <td class="text-center fw-bold uppercase">
                                {{ !empty($ci['is_over_budget']) ? 'OVER BUDGET' : 'OK' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    <!-- Generic Fallback for Other Protocols -->
    @else
        @if (!empty($result['violations']) && count($result['violations']) > 0)
            <div class="section-title">Diagnostic Violations</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 95%;">Violation Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['violations'] as $idx => $v)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>
                                @if (is_array($v))
                                    {{ json_encode($v) }}
                                @else
                                    {{ $v }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="status-box status-pass text-center">
                DIAGNOSTIC PASSED WITHOUT NON-CONFORMING VIOLATIONS.
            </div>
        @endif
    @endif

    <!-- Sign-off Block -->
    <div class="sign-off">
        <table>
            <tbody>
                <tr>
                    <td style="width: 33%;">
                        <div class="sign-line">Prepared By / Inspector</div>
                    </td>
                    <td style="width: 33%;">
                        <div class="sign-line">Reviewed By / Project Engineer</div>
                    </td>
                    <td style="width: 33%;">
                        <div class="sign-line">Approved By / Operations Manager</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
