
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

    .mb-5{
        margin-bottom: 5px;
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
<table class="mb-5">
    <tr>
        <th>ID No.</th>
        <td colspan="3">{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}
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
        <td colspan="3">{{$material_quantity_request->created_at}}
    </tr>
    <tr>
        <th>Date Printed</th>
        <td colspan="3">{{$date_printed}}
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
<table>


<table>
    <tr>
        <th>
        </th>
        <th>
            Budget
        </th>
        <th>
            Approved
        </th>
        <th>
            Remaining
        </th>
        <th>
            Requested
        </th>
        <th>
            Balance
        </th>
    </tr>

    @foreach($request_items as $request_item)
    
    <tr>
        @php
            $item = $item_options[$request_item->component_item_id][$request_item->material_item_id];
        @endphp
        <td width="500px">
            {{ $item->text }}
        </td>
        <td class="text-center" width="100px">
            {{ $item->budget_quantity}}
        </td>
        <td class="text-center"  width="100px">
            {{ $item->approved_quantity}}
        </td>

        
             @php 
                $remaining = $item->budget_quantity - $item->approved_quantity;

                $red = '';

                if($remaining < 0){
                    $red = 'text-red';
                }

            @endphp

        <td class="{{$red}} text-center"  width="100px">
            {{$remaining}}
        </td>
        <td  class="text-center text-bold" width="100px">
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

