<style>
            table{
                border-collapse:collapse;

            }

            td{
              padding-left:5px;
              padding-right:5px;  
            }

            th{
              text-wrap: wrap;
              padding-left:5px;
              padding-right:5px;  
            }

            .text-right{
                text-align: right;
            }

            .text-left{
                text-align: left;
            }
            
            .text-center{
                text-align: center;
            }

            .min-col-width{
               
            }

            .desc-col-width{
                
            }

            .factor-col-width{
               
            }

            #main{
                font-size:10px;
            }

        </style>
    
<page>
    <div id="main">
        @php
            function formatFactor($factor){
                
                $factor_arr = explode('.',$factor);
                
                if($factor_arr[1] == '000000'){
                    return $factor_arr[0].'.00';
                }
                
                return $factor_arr[0].'.'.rtrim($factor_arr[1],'0');
            }
        @endphp
        <table border="1">
            <tr>
                <td colspan="15"></td>
            </tr>
            <tr>
                <th rowspan="2" style="width:5%">Item Code</th>
                <th rowspan="2" class="desc-col-width">Description</th>
                <th colspan="4">Contract</th>
                <th colspan="4">POW/DUPA</th>
                <th class="factor-col-width">Factor</th>
                <th colspan="4">Material Budget</th>
            </tr>
            <tr>
                <th class="min-col-width">QTY</th>
                <th class="min-col-width">UNIT</th>
                <th class="min-col-width">PRICE</th>
                <th class="min-col-width">AMOUNT</th>
                <th class="min-col-width">QTY</th>
                <th class="min-col-width">UNIT</th>
                <th class="min-col-width">RATE</th>
                <th class="min-col-width">AMOUNT</th>
                <th class="min-col-width">QTY / UNIT</th>
                <th class="min-col-width">QTY</th>
                <th class="min-col-width">UNIT</th>
                <th class="min-col-width">COST</th>
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
                    <th class="text-left">{{ Str::wordWrap($contract_item->item_code,10,"\n",false) }}</th>
                    <th>
                        {!! Str::wordWrap($contract_item->description,30,"<br>",false) !!}
                    </th>
                    <th class="text-right">
                        {{$contract_item->contract_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th class="text-right">
                        P {{ number_format($contract_item->contract_unit_price,2) }}
                    </th>
                    <th>
                        P {{ number_format($contract_item->contract_quantity * $contract_item->contract_unit_price,2) }}
                    </th>

                    <th class="text-right">
                        {{ number_format($contract_item->ref_1_quantity,2) }}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th class="text-right">
                        P {{ number_format($contract_item->ref_1_unit_price,2) }}
                    </th>
                    <th class="text-right">
                        P {{ number_format($contract_item->ref_1_quantity * $contract_item->ref_1_unit_price,2) }}
                    </th>
                    <th></th>
                    <th class="text-right">
                        {{ number_format($component_total_quantity,2) }}
                    </th>
                    <th class="text-center">
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th></th>
                    <th class="text-right">
                        P {{ number_format($component_items_total_amount,2) }}
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
                                {!! Str::wordWrap($component->name,10,"<br>",false) !!}
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
                        <th class="text-center">
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
                                        formatFactor(
                                            number_format(
                                                round( ($component_item->function_variable  / $component->use_count), 6 )
                                            ,6)
                                        )
                                    }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]->text}}     
                                @endif
                                
                                @if($component_item->function_type_id == 2)
                                    {{ 
                                        formatFactor(
                                            number_format(
                                                round( (1 / $component_item->function_variable) / $component->use_count,6)
                                            ,6)
                                        ) 
                                    }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]->text}}     
                                @endif

                            </td>
                            <td class="text-right">
                                {{ number_format($component_item->quantity,2) }}
                            </td>
                            <td class="text-center">
                                {{$unit_options[$component_item->unit_id]->text}}
                            </td>
                            <td class="text-right">
                                P {{ number_format($component_item->budget_price,2) }}
                            </td>
                            <td class="text-right">
                                P {{ number_format($component_item->quantity * $component_item->budget_price,2) }}
                            </td>
                        </tr>    
                    @endforeach

                @endforeach
            @endforeach
            
        </table>
    </div>
</page>