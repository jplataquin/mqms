<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Accomplishment Form - {{ $project->name }}</title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 10pt; 
            line-height: 1.3;
            color: #000; 
            background: #fff; 
            margin: 0 auto;
            padding: 12mm;
            max-width: 210mm; /* A4 width */
        }

        h2 { 
            text-align: center; 
            margin: 0 0 4px 0; 
            text-transform: uppercase;
            font-size: 14pt;
            letter-spacing: 0.5px;
        }

        .meta-info { 
            text-align: center; 
            margin-bottom: 12px; 
            font-size: 8.5pt; 
            color: #333; 
        }

        .header-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-box td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 8.5pt;
            vertical-align: middle;
        }

        table.form-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            table-layout: fixed;
        }

        table.form-table thead {
            display: table-header-group;
        }

        table.form-table th, table.form-table td { 
            border: 1px solid #000; 
            padding: 4px 6px; 
            text-align: left; 
            vertical-align: middle; 
            word-wrap: break-word;
        }

        table.form-table th { 
            background-color: #f2f2f2 !important; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }

        .section-row { 
            background-color: #e0e0e0 !important; 
            font-weight: bold; 
            font-size: 9pt;
        }

        .section-row td {
            padding: 5px 6px;
        }

        .ci-row { 
            font-weight: bold; 
            background-color: #f5f5f5 !important; 
            font-size: 8.5pt;
        }

        .ci-row td {
            padding: 4px 6px 4px 14px;
        }

        .comp-row td { 
            font-size: 8.5pt;
            height: 24px;
            padding: 3px 6px;
        }

        .text-right { 
            text-align: right; 
        }

        .text-center { 
            text-align: center; 
        }

        .fill-box {
            background-color: #fff !important;
        }

        .signoff-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            table-layout: fixed;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .signoff-table td {
            border: none;
            width: 50%;
            padding: 0 15px;
            vertical-align: top;
            font-size: 8.5pt;
        }

        .sign-line {
            display: block;
            border-top: 1px solid #000;
            width: 85%;
            margin-top: 35px;
            padding-top: 4px;
            font-weight: bold;
        }

        .toolbar {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print { 
            display: inline-block; 
            padding: 6px 20px; 
            text-align: center; 
            border: 1px solid #000; 
            background: #eee; 
            cursor: pointer; 
            text-decoration: none; 
            color: #000;
            font-weight: bold;
            border-radius: 4px;
            font-size: 9pt;
        }

        .btn-print:hover {
            background: #ddd;
        }

        @media print {
            @page { 
                size: A4 portrait;
                margin: 10mm 10mm 12mm 10mm; 
            }

            body { 
                margin: 0; 
                padding: 0;
                max-width: none;
                width: 100%;
            }

            .no-print { 
                display: none !important; 
            }

            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            table.form-table {
                page-break-after: auto;
            }

            .signoff-table {
                break-inside: avoid;
                page-break-inside: avoid;
            }
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
