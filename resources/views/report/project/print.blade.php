<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Project Report - Print</title>
        <style>
            * {
                box-sizing: border-box;
            }
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                color: #111;
                margin: 20px;
                background: #fff;
            }
            h1, h2, h3, h4, h5 {
                margin-top: 0;
                margin-bottom: 8px;
            }
            .header-info {
                margin-bottom: 20px;
            }
            .record-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .record-table th, .record-table td {
                border: 1px solid #ccc;
                padding: 6px 10px;
                text-align: left;
            }
            .record-table th {
                background-color: #f1f5f9;
                width: 200px;
            }
            .report-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .report-table th, .report-table td {
                border: 1px solid #ccc;
                padding: 6px 8px;
            }
            .report-table th {
                background-color: #e2e8f0;
                font-size: 11px;
                text-align: left;
            }
            .text-center { text-align: center; }
            .text-end { text-align: right; }
            .fw-bold { font-weight: bold; }
            .contract-item-row td {
                background-color: #cbd5e1;
                font-weight: bold;
                font-size: 12px;
            }
            .component-row td {
                background-color: #e2e8f0;
                font-weight: bold;
                padding-left: 15px;
            }
            .component-item-row td {
                background-color: #f8fafc;
                font-weight: 600;
                padding-left: 25px;
            }
            .material-row td {
                padding-left: 35px;
            }
            .badge {
                display: inline-block;
                padding: 2px 6px;
                font-size: 10px;
                border-radius: 4px;
                background-color: #e2e8f0;
            }
            .summary-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .summary-table th, .summary-table td {
                border: 1px solid #94a3b8;
                padding: 6px 8px;
            }
            .summary-table th {
                background-color: #334155;
                color: #fff;
                font-size: 10.5px;
                text-transform: uppercase;
            }
            .summary-table tfoot td {
                background-color: #f1f5f9;
                font-weight: bold;
            }
            @media print {
                body { margin: 10mm; }
                .no-print { display: none; }
                .page-break { page-break-before: always; }
                tr { page-break-inside: avoid; }
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="margin-bottom: 15px; text-align: right;">
            <button onclick="window.print()" style="padding: 6px 14px; font-weight: bold; cursor: pointer;">Print</button>
        </div>

        <div class="header-info">
            <h2>Project Report</h2>
            <table class="record-table">
                <tbody>
                    <tr>
                        <th>Project</th>
                        <td>{{$project_name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section_name}}</td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>{{$contract_item_name}}</td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>{{$component_name}}</td>
                    </tr>
                    <tr>
                        <th>As of</th>
                        <td>{{$as_of_display}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary of Materials Quantity -->
        <h3>Summary of Materials Quantity</h3>
        <p style="color: #64748b; margin-top: -4px; margin-bottom: 8px;">Materials quantity count grouped by material and unit.</p>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">#</th>
                    <th>Material Item</th>
                    <th style="width: 90px;" class="text-center">Unit</th>
                    <th style="width: 80px;" class="text-center">Item Count</th>
                    <th style="width: 100px;" class="text-end">Budget Qty</th>
                    <th style="width: 100px;" class="text-end">Requested Qty</th>
                    <th style="width: 100px;" class="text-end">PO Qty</th>
                    <th style="width: 110px;" class="text-end">PO Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($material_summary as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item['material_name'] }}</strong>
                    </td>
                    <td class="text-center">{{ $item['unit'] }}</td>
                    <td class="text-center">{{ number_format($item['count']) }}</td>
                    <td class="text-end">{{ number_format($item['total_budget_quantity'], 2) }}</td>
                    <td class="text-end">{{ number_format($item['total_request_quantity'], 2) }}</td>
                    <td class="text-end">{{ number_format($item['total_po_quantity'], 2) }}</td>
                    <td class="text-end">P {{ number_format($item['total_po_amount'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No materials found.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($material_summary) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end">Total Items Count:</td>
                    <td class="text-center">{{ number_format(array_sum(array_column($material_summary, 'count'))) }}</td>
                    <td colspan="3" class="text-end">Total PO Amount:</td>
                    <td class="text-end">P {{ number_format(array_sum(array_column($material_summary, 'total_po_amount')), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

        <!-- Detailed Breakdown -->
        <h3 style="margin-top: 25px;">Detailed Breakdown</h3>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="width: 120px;" class="text-end">Budget Qty</th>
                    <th style="width: 120px;" class="text-end">Requested Qty</th>
                    <th style="width: 120px;" class="text-end">PO Qty</th>
                    <th style="width: 130px;" class="text-end">PO Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report as $contract_item_id => $contract_item)
                    <tr class="contract-item-row">
                        <td colspan="5">
                            {{ $contract_item_arr[$contract_item_id]->item_code }} {{ $contract_item_arr[$contract_item_id]->description }}
                        </td>
                    </tr>
                    @foreach($contract_item as $component_id => $component)
                        <tr class="component-row">
                            <td colspan="5">
                                {{ $component_arr[$component_id]->name }}
                            </td>
                        </tr>
                        @foreach($component as $component_item_id => $component_item)
                            <tr class="component-item-row">
                                <td colspan="5">
                                    {{ $component_item_arr[$component_item_id]->name }}
                                </td>
                            </tr>
                            @foreach($component_item as $material_quantity_id => $result)
                                @php 
                                    $mat_item = $material_item_arr[$material_quantity_arr[$material_quantity_id]->material_item_id];
                                @endphp
                                <tr class="material-row">
                                    <td>{{ $mat_item->formatted_name }}</td>
                                    <td class="text-end">{{ number_format($result['budget_quantity'], 2) }}</td>
                                    <td class="text-end">{{ number_format($result['request_quantity'], 2) }}</td>
                                    <td class="text-end">{{ number_format($result['po_quantity'], 2) }}</td>
                                    <td class="text-end">P {{ number_format($result['po_amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </body>
</html>
