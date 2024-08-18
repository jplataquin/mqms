
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
        <td style="width:50%">
            <img src="storage/sys_images/header.png" style="width:500px"/>
        </td>
        <td class="text-center" style="width:50%;text-align:center">
            <h1>Material Request</h1>
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
        <th style="width:5%">
            #
        </th>
        <th style="width:35%">
            Items
        </th>
        <th style="width:10%">
            Budget
        </th>
        <th style="width:10%">
            Approved
        </th>
        <th style="width:10%">
            Remaining
        </th>
        <th style="width:10%">
            Requested
        </th>
        <th style="width:20%">
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
        <td>
            {{$count}}
        </td>
        <td>
            <div class="text-bold">
                {{ $component_item_options[$request_item->component_item_id]->text }}
            </div>
            <div class="ps-20">
                {{ $item->text }}
            </div>
        </td>
        <td class="text-center">
            {{ $item->budget_quantity}}
        </td>
        <td class="text-center">
            {{ $item->approved_quantity}}
        </td>

        
             @php 
                $remaining = $item->budget_quantity - $item->approved_quantity;

                $red = '';

                if($remaining < 0){
                    $red = 'text-red';
                }

            @endphp

        <td class="{{$red}} text-center">
            {{$remaining}}
        </td>
        <td  class="text-center text-bold">
            {{$request_item->requested_quantity}}
        </td>
        @php
            $balance = $remaining - $request_item->requested_quantity;

            $red = '';

            if($balance < 0){
                $red = 'text-red';
            }
        @endphp
        <td class="{{$red}} text-center">
            {{$balance}}
        </td>
    </tr>

    @endforeach
</table>

