<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Direct Purchase Order - {{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        td, th {
            padding: 5px;
            border: 1px solid #ccc;
        }
        .mb-10 { margin-bottom: 10px; }
        .bg-grey { background-color: #eee; }
        .indent-left { padding-left: 30px; }
        @media print {
            .whole {
                break-inside: avoid;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Print this document</button>
    </div>

    @php
        $copies = ($direct_purchase_order->status == 'DRFT') 
            ? ['Purchaser'] 
            : ['Supplier', 'Accounting', 'Purchaser'];
    @endphp

    @foreach($copies as $copy)
    <div class="whole mb-10" style="margin-bottom: 30px;">
        <table class="table">
            <tr>
                <td colspan="4" class="text-center bold">
                    <img src="/storage/sys_images/header_with_address.png" style="width:500px"/>
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-center bold bg-grey">
                    DIRECT PURCHASE ORDER ({{ $copy }} Copy)
                </td>
            </tr>
            <tr>
                <td class="bold" style="width: 20%">DPO #</td>
                <td style="width: 30%">{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td class="bold" style="width: 20%">Supplier</td>
                <td style="width: 30%">{{ $supplier ? $supplier->name : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Date Created</td>
                <td>{{ $direct_purchase_order->created_at }}</td>
                <td class="bold">Payment Term</td>
                <td>{{ $payment_term ? ($payment_term->text ?? $payment_term->name) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Project</td>
                <td>{{ $project ? $project->name : 'None' }}</td>
                <td class="bold">Section</td>
                <td>{{ $section ? $section->name : 'None' }}</td>
            </tr>
            <tr>
                <td class="bold">Datetime</td>
                <td colspan="3" style="font-size: 11px;">
                    [Created] {{ $direct_purchase_order->created_at }} | [Approved] {{ $direct_purchase_order->approved_at }} | [Generated] {{ $current_datetime }}
                </td>
            </tr>
        </table>

        @if($copy == 'Purchaser' || $copy == 'Accounting')
        <table class="table" style="margin-top: 10px;">
            <thead>
                <tr class="bg-grey">
                    <th style="width: 5%;">#</th>
                    <th>Item Description</th>
                    <th class="text-right" style="width: 15%;">Quantity</th>
                    <th class="text-right" style="width: 15%;">Price</th>
                    <th class="text-right" style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach($items as $index => $item)
                    @php
                        $rowTotal = $item->quantity * $item->price;
                        $subtotal += $rowTotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="indent-left" style="padding-left: 30px;">
                            <strong>Remarks:</strong> {{ $item->remarks ?: '-' }}
                        </td>
                    </tr>
                @endforeach

                @if(!empty($extras))
                    @foreach($extras as $extra)
                        <tr>
                            <td colspan="4" class="text-right italic">{{ $extra->text }}</td>
                            <td class="text-right">{{ number_format($extra->value, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                <tr class="bg-grey">
                    <td colspan="4" class="text-right bold">Grand Total</td>
                    <td class="text-right bold">{{ number_format($direct_purchase_order->getGrandTotal(), 2) }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <table class="table" style="margin-top: 10px;">
            <thead>
                <tr class="bg-grey">
                    <th style="width: 5%;">#</th>
                    <th>Item Description</th>
                    <th class="text-right" style="width: 15%;">Quantity</th>
                    <th class="text-right" style="width: 15%;">Price</th>
                    <th class="text-right" style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach($items as $index => $item)
                    @php
                        $rowTotal = $item->quantity * $item->price;
                        $subtotal += $rowTotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach

                @if(!empty($extras))
                    @foreach($extras as $extra)
                        <tr>
                            <td colspan="4" class="text-right italic">{{ $extra->text }}</td>
                            <td class="text-right">{{ number_format($extra->value, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                <tr class="bg-grey">
                    <td colspan="4" class="text-right bold">Grand Total</td>
                    <td class="text-right bold">{{ number_format($direct_purchase_order->getGrandTotal(), 2) }}</td>
                </tr>
            </tbody>
        </table>
        @endif

        <div style="margin-top: 15px;">
            <table class="table">
                <tr>
                    <td style="width: 50%;">
                        <strong>Prepared By:</strong><br><br>
                        {{ $direct_purchase_order->CreatedByUser()->name }}
                    </td>
                    <td style="width: 50%;">
                        <strong>Approved By:</strong><br><br>
                        {{ $direct_purchase_order->ApprovedByUser()->name }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <hr style="border: 1px dashed #ccc; margin: 20px 0;">
    @endforeach

    @if($direct_purchase_order->status != 'DRFT')

    <!-- Site Copy -->
    <div class="whole mb-10" style="margin-bottom: 30px;">
        <table class="table">
            <tr>
                <td colspan="4" class="text-center bold">
                    <img src="/storage/sys_images/header.png" style="width:500px"/>
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-center bold bg-grey">
                    DIRECT PURCHASE ORDER (Site Copy)
                </td>
            </tr>
            <tr>
                <td class="bold" style="width: 20%">DPO #</td>
                <td style="width: 30%">{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td class="bold" style="width: 20%">Supplier</td>
                <td style="width: 30%">{{ $supplier ? $supplier->name : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Date Created</td>
                <td>{{ $direct_purchase_order->created_at }}</td>
                <td class="bold">Payment Term</td>
                <td>{{ $payment_term ? ($payment_term->text ?? $payment_term->name) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Project</td>
                <td>{{ $project ? $project->name : 'None' }}</td>
                <td class="bold">Section</td>
                <td>{{ $section ? $section->name : 'None' }}</td>
            </tr>
            <tr>
                <td class="bold">Datetime</td>
                <td colspan="3" style="font-size: 11px;">
                    [Created] {{ $direct_purchase_order->created_at }} | [Approved] {{ $direct_purchase_order->approved_at }} | [Generated] {{ $current_datetime }}
                </td>
            </tr>
        </table>

        <table class="table" style="margin-top: 10px;">
            <thead>
                <tr class="bg-grey">
                    <th style="width: 5%;">#</th>
                    <th>Item Description</th>
                    <th>Remarks</th>
                    <th class="text-right" style="width: 15%;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}</td>
                        <td>{{ $item->remarks ?: '-' }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            <table class="table">
                <tr>
                    <td style="width: 33.3%;">
                        <strong>Prepared By:</strong><br><br>
                        {{ $direct_purchase_order->CreatedByUser()->name }}
                    </td>
                    <td style="width: 33.3%;">
                        <strong>Approved By:</strong><br><br>
                        {{ $direct_purchase_order->ApprovedByUser()->name }}
                    </td>
                    <td style="width: 33.4%;">
                        <strong>Received By:</strong><br><br>
                        __________________________
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <hr style="border: 1px dashed #ccc; margin: 20px 0;">
    <!-- Site Copy -->

    <!-- Warehouse Copy -->
    <div class="whole mb-10" style="margin-bottom: 30px;">
        <table class="table">
            <tr>
                <td colspan="4" class="text-center bold">
                    <img src="/storage/sys_images/header.png" style="width:500px"/>
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-center bold bg-grey">
                    DIRECT PURCHASE ORDER (Warehouse Copy)
                </td>
            </tr>
            <tr>
                <td class="bold" style="width: 20%">DPO #</td>
                <td style="width: 30%">{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td class="bold" style="width: 20%">Supplier</td>
                <td style="width: 30%">{{ $supplier ? $supplier->name : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Date Created</td>
                <td>{{ $direct_purchase_order->created_at }}</td>
                <td class="bold">Payment Term</td>
                <td>{{ $payment_term ? ($payment_term->text ?? $payment_term->name) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold">Project</td>
                <td>{{ $project ? $project->name : 'None' }}</td>
                <td class="bold">Section</td>
                <td>{{ $section ? $section->name : 'None' }}</td>
            </tr>
            <tr>
                <td class="bold">Datetime</td>
                <td colspan="3" style="font-size: 11px;">
                    [Created] {{ $direct_purchase_order->created_at }} | [Approved] {{ $direct_purchase_order->approved_at }} | [Generated] {{ $current_datetime }}
                </td>
            </tr>
        </table>

        <table class="table" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th class="text-center bold bg-grey" colspan="4">Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td colspan="4" class="bold">
                            {{ $index + 1 }}.) {{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}
                            @if($item->remarks)
                                <span style="font-weight: normal; font-style: italic;">(Remarks: {{ $item->remarks }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg-grey">
                        <td class="text-center bold" style="width: 25%;">Quantity</td>
                        <td class="text-center bold" style="width: 25%;">Received</td>
                        <td class="text-center bold" style="width: 25%;">Rejected</td>
                        <td class="text-center bold" style="width: 25%;">Date</td>
                    </tr>
                    <tr>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                    </tr>
                    @for($b = 0; $b <= 2; $b++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                    <tr>
                        <td class="text-right bold bg-grey">Total:</td>
                        <td>&nbsp;</td>
                        <td class="bg-grey bold text-right">Diff:</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="bg-grey" colspan="4">&nbsp;</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            <table class="table">
                <tr>
                    <td style="width: 50%;">
                        <strong>Prepared By:</strong><br><br>
                        {{ $direct_purchase_order->CreatedByUser()->name }}
                    </td>
                    <td style="width: 50%;">
                        <strong>Validated By:</strong><br><br>
                        __________________________
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <hr style="border: 1px dashed #ccc; margin: 20px 0;">
    <!-- Warehouse Copy -->
    @endif

</body>
</html>
