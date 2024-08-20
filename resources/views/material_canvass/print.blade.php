
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

    .grey{
        background-color: #d3d3d3 !important;
    }

</style>

<page>
    <table class="mb-20">
        <tr>
            <td style="width:60%;text-align:center">
                <img src="storage/sys_images/header.png" style="width:400px"/>
            </td>
            <td class="text-center" style="width:30%;text-align:center">
                <h1>Material Canvass</h1>
            
            </td>
            <td style="text-align:center;width:10%">
                <qrcode value="{{json_encode(['t'=>'MC','id'=>$material_quantity_request->id])}}" ec="H" style="width: 20mm; background-color: white; color: black;"></qrcode>
            </td>
        </tr>
    </table>

    <table class="table mb-20">
        <tbody>
            <tr>
                <th>Material Request ID</th>
                <td colspan="3">{{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
            </tr>
            <tr>
                <th style="width:20%">Project</th>
                <td style="width:30%">{{$project->name}}</td>
                
                <th style="width:20%">Section</th>
                <td style="width:30%">{{$section->name}}</td>
            </tr>
            <tr>
                <th>Contract Item</th>
                <td>{{$contract_item->description}}</td>

                <th>Component</th>
                <td>{{$component->name}}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td colspan="3">
                    {{$material_quantity_request->description}}
                </td>
            </tr>
        </tbody>
        
    </table>
    
    <div>
        @foreach($items as $item)
            @php 
                $material_item = $material_item_arr[$item->material_item_id]; 
                $component_item = $component_item_arr[ $item->component_item_id ];
            @endphp
        
        <table class="table" border="1" style="margin-bottom:10px">
            
            <tr>
                <th style="width:50%;background-color:#d3d3d3" colspan="3">
                    Material
                </th>
                <th style="width:20%;background-color:#d3d3d3">
                    Quantity
                </th>
                <th style="width:20%;background-color:#d3d3d3">
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
                <th style="width:20%;text-align:center">Status</th>
                <th style="width:20%;text-align:center">Supplier</th>
                <th style="width:20%;text-align:center">Payment Terms</th>
                <th style="width:20%;text-align:center">Price</th>
                <th style="width:20%;text-align:center">Total</th>
            </tr>             
            
            @foreach($item->MaterialCanvass as $mcItem)
                <tr>
                    <td>
                        {{$mcItem->status}}
                    </td>
                    <td>
                        {{ $supplier_arr[ $mcItem->supplier_id ]->name }}
                    </td>
                    <td>
                        {{ $payment_term_arr[ $mcItem->payment_term_id ]->text }}
                    </td>
                    <td>
                        P {{ number_format($mcItem->price,2) }}
                    </td>
                    <td>
                        P {{ number_format($item->requested_quantity * $mcItem->price,2) }}
                    </td>
                </tr>
            @endforeach
            
        </table>

        @endforeach

        

    </div>

</page>