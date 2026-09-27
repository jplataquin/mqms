<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Accomplishment Report - {{ $project->name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            color: #000; 
            background: #fff; 
            margin: 20px; 
        }
        h2 { 
            text-align: center; 
            margin-bottom: 5px; 
            text-transform: uppercase;
        }
        .meta-info { 
            text-align: center; 
            margin-bottom: 20px; 
            font-size: 11px; 
            color: #333; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 6px 8px; 
            text-align: left; 
            vertical-align: middle; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 11px;
        }
        .section-row { 
            background-color: #e6e6e6; 
            font-weight: bold; 
            font-size: 12px;
        }
        .ci-row { 
            font-weight: bold; 
            background-color: #f9f9f9; 
            font-size: 11px;
        }
        .comp-row td { 
            font-size: 11px;
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        @media print {
            @page { 
                margin: 0.5in; 
                size: portrait;
            }
            body { 
                margin: 0; 
            }
            .no-print { 
                display: none !important; 
            }
            tr {
                page-break-inside: avoid;
            }
        }
        .btn-print { 
            display: inline-block; 
            padding: 6px 16px; 
            text-align: center; 
            border: 1px solid #000; 
            background: #eee; 
            cursor: pointer; 
            text-decoration: none; 
            color: #000;
            font-weight: bold;
            border-radius: 3px;
        }
        .btn-print:hover {
            background: #ddd;
        }
        .toolbar {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="no-print toolbar">
        <button class="btn-print" onclick="window.print()">Print Report</button>
    </div>

    <h2>Project Breakdown Report</h2>
    <div class="meta-info">
        <strong>Project:</strong> {{ $project->name }}<br>
        <strong>Generated On:</strong> {{ \Carbon\Carbon::now()->format('F j, Y, g:i a') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Work Breakdown Structure (WBS)</th>
                <th style="width: 15%;" class="text-right">Scope Quantity</th>
                <th style="width: 15%;" class="text-center">Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($project->Sections as $section)
                <tr class="section-row">
                    <td colspan="3">SECTION: {{ $section->name }}</td>
                </tr>
                @forelse($section->ContractItems as $ci)
                    <tr class="ci-row">
                        <td colspan="3" style="padding-left: 20px;">
                            CONTRACT ITEM: {{ $ci->item_code ? $ci->item_code . ' - ' : '' }}{{ $ci->description ?: $ci->name }}
                        </td>
                    </tr>
                    @forelse($ci->Components as $comp)
                        <tr class="comp-row">
                            <td style="padding-left: 40px;">{{ $comp->name }}</td>
                            <td class="text-right">{{ number_format($comp->quantity, 2) }}</td>
                            <td class="text-center">{{ $comp->unit_text }}</td>
                        </tr>
                    @empty
                        <tr class="comp-row">
                            <td colspan="3" style="padding-left: 40px; font-style: italic; color: #555;">No components in this contract item.</td>
                        </tr>
                    @endforelse
                @empty
                    <tr class="ci-row">
                        <td colspan="3" style="padding-left: 20px; font-style: italic; color: #555;">No contract items in this section.</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 20px;">No sections found in this project.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
