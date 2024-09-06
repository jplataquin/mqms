
<style>    
    table, th, td {
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
        <div style="margin-top:10px;margin-bottom:10px">  
            <table style="font-size:11px">
                <tr>
                    <td style="width:50%">
                        <strong>Material Canvass:</strong> {{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}} - {{$current_datetime}}
                    </td>
                    <td style="text-align:right;width:50%">
                        [[page_cu]] / [[page_nb]]         
                    </td>
                </tr>
            </table>
           
        </div>
    </page_footer>

    <table class="mb-20" border="1">
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

    <table class="table" border="1">
        <tbody>
            <tr>
                <th style="width:20%">Material Request ID</th>
                <td>{{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
                <th style="width:10%">Date Generated</th>
                <td>{{ $current_datetime }}</td>
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
            <tr>
                <th>Description</th>
                <td style="width:80%" colspan="3">
                    {{$material_quantity_request->description}}
                </td>
            </tr>
        </tbody>
        
    </table>
    <br>
    <div>
        @foreach($items as $item)
            @php 
                $material_item      = $material_item_arr[$item->material_item_id]; 
                $component_item     = $component_item_arr[ $item->component_item_id ];
                $material_canvass   = $item->MaterialCanvass;

                //Skip render if not canvass item is available
                if(!count($material_canvass)){
                    continue;
                }

            @endphp
        
            <nobreak>
            <table class="table" border="1" style="margin-bottom:10px;font-size:11px">
                <tr>
                    <td colspan="5" style="width:100%;background-color:#cccccc;">
                    </td>
                </tr>
                <tr>
                    <th style="text-align:center" colspan="3">
                        Material
                    </th>
                    <th style="text-align:center;">
                        Quantity
                    </th>
                    <th style="text-align:center;">
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
                        <td style="text-align:center">
                            @php 
                                switch($mc_item->status){
                                    case 'APRV':

                                        $status_color = "#008000";
                                        break;

                                    case 'DPRV':
                                        
                                        $status_color = '#ff0000';
                                        
                                        break;
                                    default:

                                        $status_color = '#ffbf00';
                                
                                }
                            @endphp
                            <strong style="color:{{$status_color}}">
                                {{$mc_item->status}}
                            </strong>
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
            <nobreak>
        @endforeach

        

    </div>

</page>