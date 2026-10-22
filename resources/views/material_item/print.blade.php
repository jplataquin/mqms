<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Items List - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000000;
            background-color: #ffffff;
            margin: 20px;
        }

        .header-container {
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta-info {
            font-size: 11px;
            color: #333333;
            margin-bottom: 4px;
        }

        .no-print {
            margin-bottom: 15px;
            text-align: right;
        }

        .no-print button {
            padding: 6px 14px;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            background-color: #ffffff;
            border: 1px solid #000000;
        }

        .group-section {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .group-header {
            background-color: #f1f5f9;
            border: 1px solid #000000;
            border-bottom: none;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .group-title {
            font-weight: bold;
            font-size: 13px;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .group-count {
            font-weight: normal;
            font-size: 11px;
            color: #475569;
            margin-left: 6px;
        }

        table.group-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            color: #000000;
        }

        table.group-table, 
        table.group-table tr, 
        table.group-table td, 
        table.group-table th {
            border: solid 1px #000000;
            font-size: 11px;
            color: #000000;
        }

        table.group-table th {
            text-align: center;
            background-color: #f8fafc;
            padding: 6px;
            font-weight: bold;
        }

        table.group-table td {
            padding: 5px;
            font-size: 11px;
        }

        .text-center {
            text-align: center !important;
        }

        .text-start {
            text-align: left !important;
        }

        .text-end {
            text-align: right !important;
        }

        @media print {
            body {
                margin: 10px;
            }

            .no-print {
                display: none !important;
            }

            td, th {
                font-size: 10px;
            }

            .group-section {
                page-break-inside: avoid;
            }

            .group-header {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.group-table th {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button id="printBtn" onclick="window.print()">Print</button>
    </div>

    <div class="header-container">
        <div class="header-title">Master Data: Material Items</div>
        <div class="meta-info">Date Generated: {{ $current_datetime }}</div>
        @if(!empty($materialGroup ?? $selectedGroup))
            <div class="meta-info">Material Group: {{ ($materialGroup ?? $selectedGroup)->name }}</div>
        @endif
        @if(!empty($query))
            <div class="meta-info">Search Filter: "{{ $query }}"</div>
        @endif
        <div class="meta-info">Total Items: {{ count($materialItems) }}</div>
        <div class="meta-info">Total Groups: {{ count($groupedMaterialItems ?? []) }}</div>
    </div>

    @if(count($groupedMaterialItems ?? []) > 0)
        @foreach($groupedMaterialItems as $group)
            <div class="group-section">
                <div class="group-header">
                    <span class="group-title">{{ $group['group_name'] }}</span>
                    <span class="group-count">({{ count($group['items']) }} {{ count($group['items']) == 1 ? 'item' : 'items' }})</span>
                </div>
                <table class="group-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;" class="text-center">#</th>
                            <th style="width: 8%;" class="text-center">ID</th>
                            <th style="width: 42%;" class="text-start">Material Name</th>
                            <th style="width: 30%;" class="text-start">Specification / Unit Packaging</th>
                            <th style="width: 15%;" class="text-start">Brand</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['items'] as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->specification_unit_packaging }}</td>
                                <td>{{ $item->brand ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <table class="group-table">
            <tbody>
                <tr>
                    <td class="text-center" style="padding: 20px;">No material items found.</td>
                </tr>
            </tbody>
        </table>
    @endif
</body>
</html>
