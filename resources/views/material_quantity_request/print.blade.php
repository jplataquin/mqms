
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
</style>
<table class="mb-5">
    <tr>
        <th>ID No.</th>
        <td colspan="3">{{$material_quantity_request->id}}
    </tr>
    <tr>
        <th>Status</th>
        <td colspan="3">{{$material_quantity_request->statusq}}
    </tr>
    <tr>
        <th>Date Created</th>
        <td colspan="3">{{$material_quantity_request->created_at}}
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
    @foreach($request_items as $request_item)
    <tr>
        <th>
        </th>
        <th>
            Budget Quantity
        </th>
        <th>
            Approved Quantity
        </th>
        <th>
            Balance Quantity
        </th>
        <th>
            Requested Quantity
        </th>
    </tr>
    <tr>
        @php
            $request_item = $material_options[$request_item->component_item_id][$request_item->material_item_id];
        @endphp
        <td width="300px">
            {{ $request_item->text }}
        </td>
        <td>
            {{ $request_item->budget_quantity}}
        </td>
        <td>
            {{ $request_item->approved_quantity}}
        </td>
        <td>
            {{ ($request_item->budget_quantity - $request_item->budget_quantity ) }}
        </td>
        <td>
            
        </td>
    </tr>

    @endforeach
</table>

