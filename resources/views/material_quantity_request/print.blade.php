
<style>    
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    } 

    th,td{
        padding: 5px;
    }
</style>
<table>
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