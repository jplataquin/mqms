<html>
    <head>
        <style>
            table{
                border-collapse:collapse;

            }

            td{
              padding-left:5px;
              padding-right:5px;  
            }

            th{
              padding-left:5px;
              padding-right:5px;  
            }

            .text-right{
                text-align: right;
            }

            .text-left{
                text-align: left;
            }

            .min-col-width{
                width:7%;
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
                <th class="min-col-width">QTY</th>
                <th class="min-col-width">UNIT</th>
                <t class="min-col-width"h>UNIT PRICE</th>
                <th class="min-col-width">AMOUNT</th>
                <th class="min-col-width">QTY</th>
                <th class="min-col-width">UNIT</th>
                <th class="min-col-width">UNIT PRICE</th>
                <th class="min-col-width">AMOUNT</th>
                <th class="min-col-width">QTY / UNIT</th>
                <th class="min-col-width">QTY</th>
                <th class="min-col-width">UNIT</th>
                <th class="min-col-width">UNIT COST</th>
                <th class="min-col-width">AMOUNT</th>
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
                    <th class="text-left">{{$contract_item->item_code}}</th>
                    <th>{{$contract_item->description}}</th>
                    <th class="text-right">
                        {{$contract_item->contract_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th class="text-right">
                        PHP {{ number_format($contract_item->contract_unit_price,2) }}
                    </th>
                    <th>
                        PHP {{ number_format($contract_item->contract_quantity * $contract_item->contract_unit_price,2) }}
                    </th>

                    <th class="text-right">
                        {{ number_format($contract_item->ref_1_quantity,2) }}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th class="text-right">
                        PHP {{ number_format($contract_item->ref_1_unit_price,2) }}
                    </th>
                    <th class="text-right">
                        PHP {{ number_format($contract_item->ref_1_quantity * $contract_item->ref_1_unit_price,2) }}
                    </th>
                    <th></th>
                    <th class="text-right">
                        {{ number_format($component_total_quantity,2) }}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th></th>
                    <th class="text-right">
                        PHP {{ number_format($component_items_total_amount,2) }}
                    </th>
                </tr>

                
                @foreach($components as $component)
                    
                    @php
                        $first = true;
                        $item_count = 1;
                    @endphp
                    <tr>
                            @if($first)
                            <td rowspan="{{count($component_items_arr[$component->id])+1}}">
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
                            {{ number_format($component->quantity,2) }}
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
                                {{$item_count}} {{$component_item->name}}

                                @php
                                    $item_count++;
                                @endphp
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
                                @if($component_item->function_type_id == 1)
                                    {{ 
                                        number_format(round( ($component_item->function_variable  / $component->use_count), 6 ),6) 
                                    }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]->text}}     
                                @endif
                                
                                @if($component_item->function_type_id == 2)
                                    {{ number_format(round( (1 / $component_item->function_variable) / $component->use_count,6 ),6) }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]->text}}     
                                @endif

                            </td>
                            <td class="text-right">
                                {{ number_format($component_item->quantity,2) }}
                            </td>
                            <td>
                                {{$unit_options[$component_item->unit_id]->text}}
                            </td>
                            <td class="text-right">
                                PHP {{ number_format($component_item->budget_price,2) }}
                            </td>
                            <td class="text-right">
                                PHP {{ number_format($component_item->quantity * $component_item->budget_price,2) }}
                            </td>
                        </tr>    
                    @endforeach

                @endforeach
            @endforeach
            
        </table>
    </body>
</html>