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

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            color: #000000;
        }

        table, tr, td, th {
            border: solid 1px #000000;
            font-size: 11px;
            color: #000000;
        }

        th {
            text-align: center;
            background-color: #f2f2f2;
            padding: 6px;
            font-weight: bold;
        }

        td {
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

        .group-header-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            padding: 7px 8px;
            font-size: 11px;
        }

        .group-count {
            font-weight: normal;
            font-size: 10px;
            color: #475569;
            margin-left: 6px;
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

            tr {
                page-break-inside: avoid;
            }

            .group-header-row td {
                background-color: #e2e8f0 !important;
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

    <table>
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
            @forelse($groupedMaterialItems ?? [] as $group)
                <tr class="group-header-row">
                    <td colspan="5">
                        <strong>Group: {{ $group['group_name'] }}</strong>
                        <span class="group-count">({{ count($group['items']) }} {{ count($group['items']) == 1 ? 'item' : 'items' }})</span>
                    </td>
                </tr>
                @foreach($group['items'] as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->specification_unit_packaging }}</td>
                        <td>{{ $item->brand ?: '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px;">No material items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
