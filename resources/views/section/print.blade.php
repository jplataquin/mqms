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
                text-align: right !important;
            }

            .text-left{
                text-align: left !important;
            }
            
            .text-center{
                text-align: center !important;
                padding-left: auto;
                padding-right:auto;
            }

            .min-col-width{
               
            }

            .desc-col-width{
                
            }

            .factor-col-width{
               
            }

            #main{
                font-size:9px;
            }

            .bg-contract-item{
                background-color: #d3d3d3;
            }

            .bg-excluded-sum-component_item{
                background-color: #fffec8;
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
                <th class="text-center" rowspan="2" style="width:5%">Item Code</th>
                <th class="text-center" rowspan="2" class="desc-col-width">Description</th>
                <th class="text-center" colspan="4">Contract</th>
                <th class="text-center" colspan="4">POW/DUPA</th>
                <th class="text-center" class="factor-col-width">Factor</th>
                <th class="text-center" colspan="4">Material Budget</th>
            </tr>
            <tr>
                <th class="min-col-width text-center">QTY</th>
                <th class="min-col-width text-center">UNIT</th>
                <th class="min-col-width text-center">PRICE</th>
                <th class="min-col-width text-center">AMOUNT</th>
                <th class="min-col-width text-center">QTY</th>
                <th class="min-col-width text-center">UNIT</th>
                <th class="min-col-width text-center">RATE</th>
                <th class="min-col-width text-center">AMOUNT</th>
                <th class="min-col-width text-center">QTY / UNIT</th>
                <th class="min-col-width text-center">QTY</th>
                <th class="min-col-width text-center">UNIT</th>
                <th class="min-col-width text-center">COST</th>
                <th class="min-col-width text-center">AMOUNT</th>
            </tr>
                
            @foreach($contract_items as $contract_item)
                
                @php 
                    $components = $contract_item->Components;

                    $component_total_quantity                    = 0;
                    $component_items_total_amount                = 0;
                    $component_items_total_quantity              = 0;
                    $component_items_arr                         = [];
                    $component_item_quantity_total_per_component = [];

                    foreach($components as $component){
                        $component_total_quantity = $component_total_quantity + $component->quantity;

                        $component_items_arr[$component->id] = $component->ComponentItems;
                        
                        //Each component item row
                        foreach($component_items_arr[$component->id] as $component_item){
                           
                            //Total the amount for each row
                            $component_items_total_amount = $component_items_total_amount + ($component_item->quantity * $component_item->budget_price);
                            
                            //Total the quantity for all component item
                            if($component_item->sum_flag && ($component_item->unit_id == $component->unit_id)){
                                $component_items_total_quantity = $component_items_total_quantity + $component_item->quantity;
                            }
                        }

                        $component_item_quantity_total_per_component[$component->id] = $component_items_total_quantity;
                    }   
                @endphp
                <tr class="bg-contract-item">
                    <th class="text-left">{{ Str::wordWrap($contract_item->item_code,10,"\n",false) }}</th>
                    <th>
                        {!! Str::wordWrap($contract_item->description,30,"<br>",false) !!}
                    </th>
                    <th class="text-right">
                        {!! Str::wordWrap(number_format($contract_item->contract_quantity,2),8,"<br>",false) !!}
                    </th>
                    <th>
                        {!! Str::wordWrap($unit_options[$contract_item->unit_id]->text,8,"<br>",false) !!}
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
                        <th class="text-right">
                            {{ number_format($component->quantity,2) }}
                        </th>
                        <th class="text-center">
                            {{$unit_options[$component->unit_id]->text}}
                        </th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>

                        <th>
                            {{ $component_item_quantity_total_per_component[$component->id] }}
                        </th>
                        <th>
                            {{$unit_options[$component->unit_id]->text}}
                        </th>
                        
                        <td></td>
                        <td></td>
                    </tr>
                   
                    @foreach($component_items_arr[$component->id] as $component_item)
                        <tr class="@if(!$component_item->sum_flag || ($component_item->unit_id != $component->unit_id) ) bg-excluded-sum-component_item @endif">
                            
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