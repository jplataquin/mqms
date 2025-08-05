<html>
    <head>
        <title>Material Request {{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }} ({{$project->name}})</title>
        <style>    
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            } 

            table{
                width: 100%;
            }

            th,td{
                padding: 5px;
            }

            .mb-20{
                margin-bottom: 20px;
            }

            .ps-20{
                padding-left: 20px;
            }

            .text-center{
                text-align: center !important;
            }

            .text-red{
                color: #ff0000 !important;
            }

            .text-bold{
                font-weight: bold !important;
            }

            .qr_code{
                width:100px;
                height:100px;
            }

            @media print {

                .qr_code{
                    width:100px;
                    height:100px;
                }
                
                td, th{
                    font-size:10px;
                }

                .page-break{
                    break-before:always;
                }
                
            
            }

        </style>
    </head>

    <body>
        <table class="mb-20">
            <tr>
                <td style="width:50%;text-align:center">
                    <img src="/storage/sys_images/header.png" style="width:500px"/>
                </td>
                <td class="text-center" style="width:35%;text-align:center">
                    <h1>Material Request</h1>

                </td>
                <td class="text-center" style="width:10%;text-align:center">
                    <img class="qr_code" src="/qrcode?d=www.google.com"/>
                    <div class="text-bold">{{$hash_code}}</div>
                </td>
            </tr>
        </table>

        <table class="mb-20">
            <tr>
                <th style="width:10%">ID No.</th>
                <td colspan="3">{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}</td>
            </tr>
         
            <tr>
                <th>Requested By</th>
                <td colspan="3">{{$material_quantity_request->CreatedByUser()->name}}</td>
            </tr>

            <tr>
                <th>Status</th>
                <td style="width:40%">{{$material_quantity_request->status}}</td>
                <th style="width:10%">Date Needed</th>
                <td style="width:40%">{{$material_quantity_request->date_needed}}</td>
            </tr>
            <tr>
                <th>Date Requested</th>
                <td style="width:40%">{{$material_quantity_request->created_at}}</td>
                <th style="width:10%">Date Printed</th>
                <td style="width:40%">{{$date_printed}}</td>
            </tr>
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
                <th>Section</th>
                <td>{{$section->name}}</td>
            </tr>
            <tr>
                <th>Contract Item</th>
                <td>{{$contract_item->item_code}} {{$contract_item->description}}</td>
                <th>Component</th>
                <td>{{$component->name}}</td>
            </tr>
        </table>

        <table>
            <tr>
                <th style="width:5%;text-align:center">
                    #
                </th>
                <th style="text-align:center">
                    {{count($request_items)}} Item(s)
                </th>
                <th style="width:10%;text-align:center">
                    Budget
                </th>
                <th style="width:10%;text-align:center">
                    Approved
                </th>
                <th style="width:10%;text-align:center">
                    Available
                </th>
                <th style="width:10%;text-align:center">
                    Requested
                </th>
                
                <th style="width:10%;text-align:center">
                    Equivalent
                </th>

                <th style="width:10%;text-align:center">
                Total Request
                </th>
                
                <th style="width:10%;text-align:center">
                    Balance
                </th>
            </tr>

            @php
                $count = 1;
            @endphp
            @foreach($request_items as $request_item)
            
            <tr>
                @php
                    $item = $item_options[$request_item->component_item_id][$request_item->material_item_id];
                @endphp
                <td class="text-center" style="text-align:center">
                    {{$count}}
                </td>
                <td>
                    <div class="text-bold" style="width:400px;font-weight:bold">
                        {{ $component_item_options[$request_item->component_item_id]->text }}
                    </div>
                    <div class="ps-20" style="width:400px">
                        {{ $item->text }}
                    </div>
                </td>
                <td class="text-center" style="text-align:center">
                    {{ number_format($item->budget_quantity, 2) }} {{$item->component_unit_text}}
                </td>
                <td class="text-center" style="text-align:center">
                    {{ number_format($item->approved_quantity,2) }} {{$item->component_unit_text}}
                </td>

                
                    @php 
                        $remaining = $item->budget_quantity - $item->approved_quantity;

                        $red = '#000000';

                        if($remaining < 0){
                            $red = '#ff0000';
                        }

                    @endphp

                <td class="text-center" style="color:{{$red}};text-align:center">
                    {{ number_format($remaining, 2) }} {{$item->component_unit_text}}
                </td>
                <td  class="text-center text-bold" style="font-weight:bold;text-align:center">
                    {{$request_item->requested_quantity}}
                </td>
                <td class="text-center" style="color:{{$red}};text-align:center">
                    {{ number_format($item->equivalent, 2) }} {{$item->component_unit_text}}
                </td>
                <td class="text-center" style="color:{{$red}};text-align:center">
                    {{ number_format($item->equivalent * $request_item->requested_quantity, 2) }} {{$item->component_unit_text}}
                </td>
                @php
                    $balance = $remaining - ($item->equivalent * $request_item->requested_quantity);

                    $red = '#000000';

                    if($balance < 0){
                        $red = '#ff0000';
                    }
                @endphp
                <td class="text-center" style="color:{{$red}};text-align:center">
                    {{ number_format($balance,2) }} {{$item->component_unit_text}}
                </td>
            </tr>
                
                @php
                        $count++;
                @endphp
            @endforeach
        </table>

    </body>
</html>

