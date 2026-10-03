<html>
    <head>
        <title>Purchase Report - {{ $project->name }} - {{ date('Y-m-d') }}</title>

        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                color: #000000;
                background-color: #ffffff;
                margin: 20px;
            }

            table, tr, td, th {
                border: solid 1px #000000;
                border-collapse: collapse;
                font-size: 11px;
                color: #000000;
            }
            
            table {
                width:100%;
            }

            th{
                text-align: center;
                background-color: #f2f2f2;
            }

            td, th {
                padding: 5px;
                font-size: 11px;
            }

            .record-table-horizontal th {
                text-align: left;
                width: 200px;
                background-color: #f2f2f2;
            }

            .text-end{
                text-align:right !important;
            }

            .text-start{
                text-align:left !important;
            }

            .text-center{
                text-align:center !important;
            }

            .w-100{
                width: 100%;
            }

            .mb-3{
                margin-bottom:3px;
            }

            .mb-5{
                margin-bottom:5px;
            }

        
            
            .text-italic{
                font-style: italic;
            }

            .wrap{
                word-wrap: break-word;
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
            
            @media print {

                body {
                    margin: 10px;
                }

                .no-print {
                    display: none !important;
                }

                td, th{
                    font-size:11px;
                }

                .page-break{
                    break-before:always;
                }
                
                thead{
                    background-color:silver;
                }
            }
        </style>
    </head>
    <body>
    

        <div class="no-print">
            <button onclick="window.print()">Print</button>
        </div>

        <div class="mb-5">
            <h1 class="mb-3">Purchase Report</h1>
            <table class="record-table-horizontal">
                <tbody>
                    <tr>
                        <th>Project</th>
                        <td>{{$project->name}}</td>
                    </tr>
                    <tr>
                        <th>Section</th>
                        <td>{{$section->name}}</td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>
                            @if($contract_item)
                                {{$contract_item->name}}
                            @else
                                *
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>
                            @if($component)
                                {{$component->name}}
                            @else
                                *
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Date Scope</th>
                        <td>
                            @if(!$from && !$to)
                                *
                            @else
                                @php 
                                    $from = $from || '*';
                                    $to   = $to || '*';
                                @endphp
                                (From: {{$from}}) - To: ({{$to}})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>No. of Supplier Filter</th>
                        <td>
                            {{$supplier_filter}}
                        </td>
                    </tr>
                    <tr>
                        <th>No. of Material Item</th>
                        <td>
                            {{$material_filter}}
                        </td>
                    </tr>
                </tbody>
            </table>    
        </div>
        
        <h2 class="mb-3 text-center">-- Per Supplier --</h2>

        @php 
            $supplier_grand_total = 0;
        @endphp

        @foreach($per_supplier as $supplier_id => $d)

        @php 
            $supplier_amount_total = 0;
        @endphp
        <div class="mb-5">

            <h3 class="mb-3">{{$d['supplier']->name}}</h3>
        
            <table class="table w-100 table-hover ">
                <tr>
                    <th>Material Item</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Total</th>
                </tr>
                @foreach($d['items'] as $po_item)
                <tr>
                    <td>
                        {{$po_item->MaterialItem->formatted_name}}
                    </td>
                    <td class="text-center">
                        {{ number_format($po_item->total_quantity,2) }}
                    </td>
                    <td class="text-end">
                        P {{$po_item->price}}
                    </td>
                    <td class="text-end">
                        P {{ number_format( ($po_item->total_quantity * $po_item->price), 2) }}
                    </td>
                </tr>

                @php
                    $supplier_amount_total += ($po_item->total_quantity * $po_item->price);
                @endphp

                @endforeach
                <tr>
                    <td colspan="3"></td>
                    <th class="text-end">
                        P {{ number_format($supplier_amount_total,2) }}
                    </th>
                </tr>
            </table>
        </div>

        @php 
            $supplier_grand_total += $supplier_amount_total;
        @endphp
        @endforeach
        <div class="mb-3 text-end">
            <h3>Grand Total: P {{number_format($supplier_grand_total,2)}} </h3>
        </div>

        <hr>


        <h2 class="mb-3 text-center">-- Per Material --</h2>
        <div>
            <table class="table w-100 table-hover table-striped">
                <tr>
                    <th>Material Item</th>
                    <th class="text-center">Quantity</th>
                </tr>
                @foreach($per_material as $m)
                <tr>
                    <td>{{$m->MaterialItem->formatted_name}}</td>
                    <td class="text-center">{{ number_format($m->total_quantity,2) }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <hr>

        <h2 class="mb-3 text-center">-- Per Purchase Order --</h2>
        <div>
            <table class="table w-100 table-hover table-striped">
                <thead>
                    <tr>
                        <th class="text-start">Material Item</th>
                        <th class="text-center" style="width: 150px;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($per_po as $po_id => $po_data)
                    <tr style="background-color: #f2f2f2;">
                        <th colspan="2" class="text-start" style="padding: 6px;">
                            PO # {{ $po_data['po_number'] }}
                        </th>
                    </tr>
                    @foreach($po_data['items'] as $item)
                    <tr>
                        <td style="padding-left: 15px;">{{ $item->MaterialItem->formatted_name }}</td>
                        <td class="text-center">{{ number_format($item->total_quantity, 2) }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
          

      


    </body>
</html>