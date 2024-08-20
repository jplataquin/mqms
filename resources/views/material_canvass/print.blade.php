
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

<page>
    <h3>Material Canvass</h3>
    <table border="1" class="table">
        <tbody>
            <tr>
                <th style="width:20%">Material Quantity Request ID</th>
                <td style="width:80%">{{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
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
            @php 
                $material_item = $material_item_arr[$item->material_item_id]; 
                $component_item = $component_item_arr[ $item->component_item_id ];
            @endphp
        
        <nobreak>
        <table class="table" border="1" style="margin-bottom:5px">
            
            <tr>
                <th style="width:60%" colspan="3">
                    Material
                </th>
                <th style="width:20%">
                    Quantity
                </th>
                <th style="width:20%">
                    Budget Price
                </th>
            </tr>
            <tr>
                <td colspan="3">
                    {{$material_item->brand}} {{$material_item->name}} {{$material_item->specification_unit_packaging}}
                </td>
                <td>
                    {{ $item->requested_quantity }}
                </td>
                <td>
                    P {{ number_format($component_item->budget_price,2) }}
                </td>
            </tr>
            <tr>
                <th style="width:20%">Status</th>
                <th style="width:20%">Supplier</th>
                <th style="width:20%">Payment Terms</th>
                <th style="width:20%">Price</th>
                <th style="width:20%">Total</th>
            </tr>             
            <tr>
                <td colspan="3">
                    <table class="table" border="1">
                        
                        @foreach($item->MaterialCanvass as $mcItem)
                        <tr>
                            <td style="width:10%">
                                {{$mcItem->status}}
                            </td>
                            <td style="width:30%">
                                {{ $supplier_arr[ $mcItem->supplier_id ]->name }}
                            </td>
                            <td style="width:30%">
                                {{ $payment_term_arr[ $mcItem->payment_term_id ]->text }}
                            </td>
                            <td style="width:10%">
                               P {{ number_format($mcItem->price,2) }}
                            </td>
                            <td style="width:20%">
                               P {{ number_format($item->requested_quantity * $mcItem->price,2) }}
                            </td>
                        </tr>
                        @endforeach
        
                    </table>
                </td>        
            </tr>
        </table>
        </nobreak>
        @endforeach

        

    </div>

</page>