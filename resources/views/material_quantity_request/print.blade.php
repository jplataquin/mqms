
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


</style>

<table class="mb-20">
    <tr>
        <td style="width:50%;text-align:center">
            <img src="storage/sys_images/header.png" style="width:500px"/>
        </td>
        <td class="text-center" style="width:40%;text-align:center">
            <h1>Material Request</h1>
        
        </td>
        <td style="text-align:center;width:10%">
            <qrcode value="{{json_encode(['t'=>'MR','id'=>$material_quantity_request->id])}}" ec="H" style="width: 20mm; background-color: white; color: black;"></qrcode>
        </td>
    </tr>
</table>

<table class="mb-20">
    <tr>
        <th style="width:10%">ID No.</th>
        <td colspan="3">{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td colspan="3">{{$material_quantity_request->status}}</td>
    </tr>
    <tr>
        <th>Requested By</th>
        <td colspan="3">{{$material_quantity_request->CreatedByUser()->name}}</td>
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
        <th style="width:35%;text-align:center">
            Item
        </th>
        <th style="width:10%;text-align:center">
            Budget
        </th>
        <th style="width:10%;text-align:center">
            Approved
        </th>
        <th style="width:10%;text-align:center">
            Remaining
        </th>
        <th style="width:10%;text-align:center">
            Requested
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
            <div class="text-bold" style="width:450px;font-weight:bold">
                {{ $component_item_options[$request_item->component_item_id]->text }}
            </div>
            <div class="ps-20" style="width:450px">
                {{ $item->text }}
            </div>
        </td>
        <td class="text-center" style="text-align:center">
            {{ $item->budget_quantity}}
        </td>
        <td class="text-center" style="text-align:center">
            {{ $item->approved_quantity}}
        </td>

        
             @php 
                $remaining = $item->budget_quantity - $item->approved_quantity;

                $red = '#000000';

                if($remaining < 0){
                    $red = '#ff0000';
                }

            @endphp

        <td class="text-center" style="color:{{$red}};text-align:center">
            {{$remaining}}
        </td>
        <td  class="text-center text-bold" style="font-weight:bold;text-align:center">
            {{$request_item->requested_quantity}}
        </td>
        @php
            $balance = $remaining - $request_item->requested_quantity;

            $red = '#000000';

            if($balance < 0){
                $red = '#ff0000';
            }
        @endphp
        <td class="text-center" style="color:{{$red}};text-align:center">
            {{$balance}}
        </td>
    </tr>
        
        @php
                $count++;
        @endphp
    @endforeach
</table>

