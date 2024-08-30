
<style>    
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size:11px;
    } 

    table{
        
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


    <page_footer>
        <div style="font-size:11px;text-align:right;padding-bottom:20px">  
            [[page_cu]] / [[page_nb]]
        </div>
    </page_footer>

    <table class="mb-20">
        <tr>
            <td style="width:60%;text-align:center">
                <img src="storage/sys_images/header.png" style="width:400px"/>
            </td>
            <td class="text-center" style="width:30%;text-align:center">
                <h3>Material Canvass</h3>
            
            </td>
            <td style="text-align:center;width:10%">
                <qrcode value="{{json_encode(['t'=>'MC','id'=>$material_quantity_request->id])}}" ec="H" style="width: 20mm; background-color: white; color: black;"></qrcode>
            </td>
        </tr>
    </table>

    <table class="table">
        <tbody>
            <tr>
                <th style="width:20%">Material Request ID</th>
                <td style="width:50%">{{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
                <th style="width:20%">Date Generated</th>
                <td style="width:50%">{{ $current_datetime }}</td>
            </tr>
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
                
                <th>Section</th>
                <td>{{$section->name}}</td>
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
                $material_item      = $material_item_arr[$item->material_item_id]; 
                $component_item     = $component_item_arr[ $item->component_item_id ];
                $material_canvass   = $item->MaterialCanvass;

                if(!$material_canvass){
                    $continue;
                }

            @endphp
        
        <nobreak>
            <div></div>

            <table class="table" border="1" style="margin-bottom:10px;font-size:11px">
                
                <tr>
                    <th style="background-color:#cccccc; width:50%" colspan="3">
                        Material
                    </th>
                    <th style="background-color:#cccccc;text-align:center; width:20%">
                        Quantity
                    </th>
                    <th style="background-color:#cccccc;text-align:center; width:20%">
                        Budget Price
                    </th>
                </tr>
                <tr>
                    <td colspan="3">
                        {{$material_item->brand}} {{$material_item->name}} {{$material_item->specification_unit_packaging}}
                    </td>
                    <td style="text-align:center">
                        {{ $item->requested_quantity }}
                    </td>
                    <td style="text-align:center">
                        P {{ number_format($component_item->budget_price,2) }}
                    </td>
                </tr>
                <tr>
                    <th style="background-color:#cccccc;text-align:center">Status</th>
                    <th style="background-color:#cccccc;text-align:center">Supplier</th>
                    <th style="background-color:#cccccc;text-align:center">Payment Terms</th>
                    <th style="background-color:#cccccc;text-align:center">Price</th>
                    <th style="background-color:#cccccc;text-align:center">Total</th>
                </tr>             
                
                @foreach($material_canvass as $mc_item)
                    <tr>
                        <td>
                            {{$mc_item->status}}
                        </td>
                        <td>
                            {{  Str::wordWrap($supplier_arr[ $mc_item->supplier_id ]->name,5) }}
                        </td>
                        <td>
                            {{ $payment_term_arr[ $mc_item->payment_term_id ]->text }}
                        </td>
                        <td style="text-align:center">
                            P {{ number_format($mc_item->price,2) }}
                        </td>
                        <td style="text-align:center">
                            P {{ number_format($item->requested_quantity * $mc_item->price,2) }}
                        </td>
                    </tr>
                @endforeach
                
            </table>
        </nobreak>
        @endforeach

        

    </div>

</page>