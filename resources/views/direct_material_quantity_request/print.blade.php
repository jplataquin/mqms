<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Direct Material Request #{{ $request->formatted_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .meta-table, .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { padding: 4px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 6px; }
        .items-table th { background-color: #f2f2f2; text-align: left; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 40px; }
        .signature-box { display: inline-block; width: 45%; }
        .qr_code { width: 90px; height: 90px; }
        @media print {
            .no-print { display: none; }
            .qr_code { width: 90px; height: 90px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">Print</button>
    </div>

    <table style="width: 100%; margin-bottom: 20px; border-collapse: collapse;">
        <tr>
            <td style="vertical-align: middle;">
                <h2 style="margin: 0;">Direct Material Request</h2>
                <p style="margin: 5px 0;">Request #: <strong>{{ $request->formatted_id }}</strong> | Status: <strong>{{ $request->status }}</strong></p>
            </td>
            <td style="width: 120px; text-align: center; vertical-align: middle;">
                <img class="qr_code" src="/qrcode?d={{ url('/review/direct_material_quantity_requests/qr/approve/'.$request->id.'/'.$hash_code) }}"/>
                <div style="font-weight: bold; font-size: 11px; margin-top: 2px;">{{ $hash_code }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 15%"><strong>Project:</strong></td>
            <td style="width: 35%">{{ $request->Project ? $request->Project->name : 'N/A' }}</td>
            <td style="width: 15%"><strong>Section:</strong></td>
            <td style="width: 35%">{{ $request->Section ? $request->Section->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Created By:</strong></td>
            <td>{{ $request->Creator ? $request->Creator->name : 'N/A' }}</td>
            <td><strong>Date Created:</strong></td>
            <td>{{ $request->created_at }}</td>
        </tr>
        @if($request->description)
        <tr>
            <td><strong>Purpose:</strong></td>
            <td colspan="3">{{ $request->description }}</td>
        </tr>
        @endif
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%" class="text-center">#</th>
                <th style="width: 70%">Material Item</th>
                <th style="width: 25%" class="text-end">Requested Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}
                        @if($item->remarks)
                            <br><small style="color: #666;"><em>Remarks: {{ $item->remarks }}</em></small>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Requested by:</p>
            <br><br>
            <p>_______________________<br>{{ $request->Creator ? $request->Creator->name : '' }}</p>
        </div>
        <div class="signature-box" style="float: right;">
            <p>Approved by:</p>
            <br><br>
            <p>_______________________<br>{{ $request->Approver ? $request->Approver->name : '' }}</p>
        </div>
    </div>
</body>
</html>
