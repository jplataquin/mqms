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
    <div class="mt-5">
        @foreach($items as $item)
        <div class="border border-primary p-3 mb-3">
            <div class="row mb-5">
               <h5>  {{ $component_item_arr[ $item->component_item_id ]->name }}</h5>
                @php 
                    $component_item = $component_item_arr[ $item->component_item_id ];
                    $material_item = $material_item_arr[$item->material_item_id];
                @endphp
                <div class="col-6">
                    <div class="form-group">
                        <label>Material</label>
                        <input type="text" class="form-control" disabled="true" value="{{$material_item->brand}} {{$material_item->name}} {{$material_item->specification_unit_packaging}}"/>
                    </div>
                </div>

                <div class="col-3">
                    <label>Quantity</label>
                    <input type="text" class="form-control" disabled="true" value="{{ $item->requested_quantity }}"/>
                </div>
                <div class="col-3">
                    <label>Budget Price</label>
                    <input type="text" class="form-control" disabled="true" value="P {{ number_format($component_item->budget_price,2) }}"/>
                </div>
            </div>

            
        </div>   
        @endforeach

        

    </div>

</page>