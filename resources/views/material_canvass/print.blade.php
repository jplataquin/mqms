<style>
    .table{
        border-collapse: collapse;
    }

    .table td{
        padding:3px;
    }

    .table th{
        padding:3px;
    }
</style>
<page>
    <h3>Material Canvass</h3>
    <table border="1" class="table">
        <tbody>
            <tr>
                <th style="width:10%">Material Quantity Request ID</th>
                <td style="width:90%">{{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
            </tr>
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
            </tr>
            <tr>
                <th>Section</th>
                <td>{{$section->name}}</td>
            </tr>
            <tr>
                <th>Component</th>
                <td>{{$component->name}}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{$material_quantity_request->status}}</td>
            </tr>
            <tr>
                <th>Created By</th>
                <td>{{$material_quantity_request->CreatedByUser()->name}} {{$material_quantity_request->created_at}}</td>
            </tr>
            <tr>
                <th>Updated By</th>
                <td>{{$material_quantity_request->UpdatedByUser()->name}} {{$material_quantity_request->updated_at}}</td>
            </tr>
            <tr>
                <th>Approved By</th>
                <td>{{$material_quantity_request->ApprovedByUser()->name}} {{$material_quantity_request->approve_at}}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>
                    {{$material_quantity_request->description}}
                </td>
            </tr>
        </tbody>
        
    </table>
    <hr>
    <div>
        @foreach($items as $item)
            @php  $material_item = $material_item_arr[$item->material_item_id]; @endphp
        <table>
            <tr>
                <td style="width:100%" colspan="3">
                    {{ $component_item_arr[ $item->component_item_id ]->name }}
                </td>
            </tr>
            <tr>
                <th>
                    Material
                </th>
                <th>
                    Quantity
                </th>
                <th>
                    Budget Price
                </th>
            </tr>
            <tr>
                <td>
                    {{$material_item->brand}} {{$material_item->name}} {{$material_item->specification_unit_packaging}}
                </td>
                <td>
                    {{ $item->requested_quantity }}
                </td>
                <td>
                    P {{ number_format($component_item->budget_price,2) }}
                </td>
            </tr>
        </table>
        
        @endforeach

        

    </div>

</page>