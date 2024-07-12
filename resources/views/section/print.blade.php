<html>
    <head>
        <style>
            table{
                border-collapse:collapse;

            }

            td{
              padding-left:3px;
              padding-right:3px;  
            }

            th{
              padding-left:3px;
              padding-right:3px;  
            }

            .text-right{
                text-align: right;
            }
        </style>
    </head>
    <body>
        <h1>TEst</h1>
        <table border="1">
            <tr>
                <td colspan="15" width="100%"></td>
            </tr>
            <tr>
                <th rowspan="2">Item Code</th>
                <th rowspan="2">Description</th>
                <th colspan="4">Contract</th>
                <th colspan="4">POW/DUPA</th>
                <th>Factor</th>
                <th colspan="4">Material Budget</th>
            </tr>
            <tr>
                <th>QTY</th>
                <th>UNIT</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>
                <th>QTY</th>
                <th>UNIT</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>
                <th>QTY / UNIT</th>
                <th>QTY</th>
                <th>UNIT</th>
                <th>UNIT COST</th>
                <th>AMOUNT</th>
            </tr>
                
            @foreach($contract_items as $contract_item)
                
                @php 
                    $components = $contract_item->Components;

                    $component_total_quantity       = 0;
                    $component_items_total_amount   = 0;
                    $component_items_arr            = [];

                    foreach($components as $component){
                        $component_total_quantity = $component_total_quantity + $component->quantity;

                        $component_items_arr[$component->id] = $component->ComponentItems;
                        
                        foreach($component_items_arr[$component->id] as $component_item){
                            $component_items_total_amount = $component_items_total_amount + ($component_item->quantity * $component_item->budget_price);
                        }
                    }   
                @endphp
                <tr>
                    <th>{{$contract_item->item_code}}</th>
                    <th>{{$contract_item->description}}</th>
                    <th class="text-right">
                        {{$contract_item->contract_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th class="text-right">
                        PHP {{$contract_item->contract_unit_price}}
                    </th>
                    <th>
                        PHP {{$contract_item->contract_quantity * $contract_item->contract_unit_price}}
                    </th>

                    <th class="text-right">
                        {{$contract_item->ref_1_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th class="text-right">
                        PHP {{$contract_item->ref_1_unit_price}}
                    </th>
                    <th class="text-right">
                        PHP {{$contract_item->ref_1_quantity * $contract_item->ref_1_unit_price}}
                    </th>
                    <th></th>
                    <th class="text-right">
                        {{$component_total_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th></th>
                    <th>
                        PHP {{$component_items_total_amount}}
                    </th>
                </tr>

                
                @foreach($components as $component)
                    
                    @php
                        $first = true;
                    @endphp
                    <tr>
                            @if($first)
                            <td rowspan="{{count($component_items)+1}}">
                                {{$component->name}}
                            </td>
                                
                            
                                @php 
                                    $first = false;
                                @endphp
                            @endif
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th class="text-right">
                            {{$component->quantity}}
                        </th>
                        <th>
                            {{$unit_options[$component->unit_id]->text}}
                        </th>
                        <td></td>
                        <td></td>
                    </tr>
                   
                    @foreach($component_items_arr[$component->id] as $component_item)
                        <tr>
                            
                            <td>
                                {{$component_item->name}}
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                @if($component_item->function_type == 1)
                                    {{ round( ($component_item->function_variable / $component->quantity) / $component->use_count,2 ) }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]}}     
                                @endif
                                
                                @if($component_item->function_type == 2)
                                    {{ round( ($component_item->quantity / $component->function_variable) / $component->use_count,2 ) }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]}}     
                                @endif

                            </td>
                            <td class="text-right">
                                {{$component_item->quantity}}
                            </td>
                            <td>
                                {{$unit_options[$component_item->unit_id]->text}}
                            </td>
                            <td class="text-right">
                                PHP {{$component_item->budget_price}}
                            </td>
                            <td class="text-right">
                                PHP {{$component_item->quantity * $component_item->budget_price}}
                            </td>
                        </tr>    
                    @endforeach

                @endforeach
            @endforeach
            
        </table>
    </body>
</html>