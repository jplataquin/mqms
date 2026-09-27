<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Accomplishment Form - {{ $project->name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            color: #000; 
            background: #fff; 
            margin: 20px; 
        }
        h2 { 
            text-align: center; 
            margin-bottom: 4px; 
            text-transform: uppercase;
            font-size: 16px;
        }
        .meta-info { 
            text-align: center; 
            margin-bottom: 15px; 
            font-size: 11px; 
            color: #333; 
        }
        .header-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .header-box td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11px;
            vertical-align: middle;
        }
        table.form-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px; 
        }
        table.form-table th, table.form-table td { 
            border: 1px solid #000; 
            padding: 6px 8px; 
            text-align: left; 
            vertical-align: middle; 
        }
        table.form-table th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 10px;
        }
        .section-row { 
            background-color: #e6e6e6; 
            font-weight: bold; 
            font-size: 11px;
        }
        .ci-row { 
            font-weight: bold; 
            background-color: #f9f9f9; 
            font-size: 10.5px;
        }
        .comp-row td { 
            font-size: 10.5px;
            height: 26px;
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .fill-box {
            background-color: #fff;
        }
        @media print {
            @page { 
                margin: 0.4in; 
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
            padding: 7px 18px; 
            text-align: center; 
            border: 1px solid #000; 
            background: #eee; 
            cursor: pointer; 
            text-decoration: none; 
            color: #000;
            font-weight: bold;
            border-radius: 4px;
        }
        .btn-print:hover {
            background: #ddd;
        }
        .toolbar {
            text-align: center;
            margin-bottom: 20px;
        }
        .signoff-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .signoff-table td {
            border: none;
            width: 50%;
            padding: 10px 20px;
            vertical-align: top;
        }
        .sign-line {
            display: block;
            border-top: 1px solid #000;
            width: 80%;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="no-print toolbar">
        <button class="btn-print" onclick="window.print()">Print Form</button>
    </div>

    <h2>Project Accomplishment Form</h2>
    <div class="meta-info">
        <strong>Project:</strong> {{ $project->name }} &nbsp;|&nbsp; 
        <strong>Generated Date:</strong> {{ \Carbon\Carbon::now()->format('F j, Y') }}
    </div>

    <table class="header-box">
        <tr>
            <td style="width: 50%;"><strong>Site Engineer:</strong> ____________________________________</td>
            <td style="width: 50%;"><strong>Actual Accomplishment Date:</strong> ____________________</td>
        </tr>
    </table>

    <table class="form-table">
        <thead>
            <tr>
                <th style="width: 44%;">Work Breakdown Structure (WBS)</th>
                <th style="width: 12%;" class="text-right">Scope Qty</th>
                <th style="width: 8%;" class="text-center">Unit</th>
                <th style="width: 18%;" class="text-center">Actual Accomplished Qty</th>
                <th style="width: 18%;" class="text-center">Remarks / Notes</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sectionsWithComponents = $project->Sections->filter(function($section) {
                    return $section->ContractItems->some(function($ci) {
                        return $ci->Components->isNotEmpty();
                    });
                });
            @endphp

            @forelse($sectionsWithComponents as $section)
                <tr class="section-row">
                    <td colspan="5">SECTION: {{ $section->name }}</td>
                </tr>
                @foreach($section->ContractItems->filter(fn($ci) => $ci->Components->isNotEmpty()) as $ci)
                    <tr class="ci-row">
                        <td colspan="5" style="padding-left: 18px;">
                            CONTRACT ITEM: {{ $ci->item_code ? $ci->item_code . ' - ' : '' }}{{ $ci->description ?: $ci->name }}
                        </td>
                    </tr>
                    @foreach($ci->Components as $comp)
                        <tr class="comp-row">
                            <td style="padding-left: 36px;">{{ $comp->name }}</td>
                            <td class="text-right">{{ number_format($comp->quantity, 2) }}</td>
                            <td class="text-center">{{ $comp->unit_text }}</td>
                            <td class="fill-box"></td>
                            <td class="fill-box"></td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; font-style: italic;">No components found for this project.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signoff-table">
        <tr>
            <td>
                <span class="sign-line">Site Engineer (Submitted By)</span>
                Date: ________________________
            </td>
            <td>
                <span class="sign-line">System Encoder (Verified & Encoded By)</span>
                Date: ________________________
            </td>
        </tr>
    </table>

</body>
</html>
