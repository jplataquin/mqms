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

            .text-center{
                text-align: center !important;
                padding-left: auto;
                padding-right:auto;
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

            .bg-excluded-sum-component{
                background-color: #ADD8E6;
            }


            .font-color-danger{
                color: #ff0000;
            }
        </style>
    
<page id="main">
            

    <page_footer>
        <br>
        <table class="page_footer">
            <tr>
                <td style="width: 50%;font-size:12px;">
                    <strong>Material Budget:</strong> {{$project->name}} - {{$section->name}} - {{$current_datetime}}
                </td>
                <td style="width: 50%; text-align: right;font-size:12px">
                    [[page_cu]] / [[page_nb]]
                </td>
            </tr>
        </table>
    </page_footer>
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
                <td colspan="2" style="text-align:center">
                    <img src="storage/sys_images/header.png" style="width:500px"/>
                </td>
                <td colspan="2" style="text-align:center" valign="middle">
                    <h2>Material Budget</h2>
                </td>
            </tr>
            <tr>
                <th style="width:15%;text-align:center">
                    Project
                </th>
                <td style="width:35%">
                    {{$project->name}}
                </td>
            
                <th style="width:15%;text-align:center">
                    Date & Time Generated
                </th>
                <td style="width:35%">
                    {{$current_datetime}}
                </td>
            </tr>
            <tr>
                <th style="text-align:center">
                    Section
                </th>
                <td>
                    {{$section->name}}
                </td>
            
                <th style="text-align:center">
                    Generated By
                </th>
                <td>
                    {{$current_user->name}}
                </td>
            </tr>
        </table>
        <br>
        <table border="1">
          
            <tr>
                <td colspan="15" style="width:100%">
                    Color legend: 
                    <br>  
                    <div>
                        <span style="margin-left:10px;background-color:#add8e6;border:solid 1px #000000">__</span> - A component is excluded from sumation due to incompatible unit or the item was explicitly flagged by user.  
                        <span style="margin-left:10px;background-color:#fffec8;border:solid 1px #000000">__</span> - An item is excluded from sumation due to incompatible unit or the item was explicitly flagged by user.
                    
                        <span style="margin-left:10px;background-color:#ff0000;border:solid 1px #000000">__</span> - Red font indicates that the total is over budget.  
                        <span style="margin-left:10px;background-color:#d3d3d3;border:solid 1px #000000">__</span> - Contract Item or grand total.
                     </div>  
                     <br>
                </td>
            </tr>
        
            <tr>
                <th rowspan="2" style="text-align:center">Item Code</th>
                <th style="text-align:center" rowspan="2">Description</th>
                <th style="text-align:center" colspan="4">Contract</th>
                <th style="text-align:center" colspan="4">POW/DUPA</th>
                <th style="text-align:center">Factor</th>
                <th style="text-align:center" colspan="4">Material Budget</th>
            </tr>
            <tr>
                <th style="text-align:center">QTY</th>
                <th style="text-align:center">UNIT</th>
                <th style="text-align:center">PRICE</th>
                <th style="text-align:center">AMOUNT</th>
                <th style="text-align:center">QTY</th>
                <th style="text-align:center">UNIT</th>
                <th style="text-align:center">RATE</th>
                <th style="text-align:center">AMOUNT</th>
                <th style="text-align:center">QTY / UNIT</th>
                <th style="text-align:center">QTY</th>
                <th style="text-align:center">UNIT</th>
                <th style="text-align:center">COST</th>
                <th style="text-align:center">AMOUNT</th>
            </tr>

            @php 
                $grand_total_contract_amount        = 0;
                $grand_total_ref_1_amount           = 0;
                $grand_total_material_budget_amount = 0;
            @endphp
                
            @foreach($contract_items as $contract_item)
                
                @php 
                    $components = $contract_item->Components;

                    $component_total_quantity                    = 0;

                    $contract_item_total_amount                  = 0;
                    $component_items_arr                         = [];
                    $component_item_quantity_total_per_component = [];

                    $component_total_amount_arr                  = [];

                    foreach($components as $component){

                        

                        $component_items_total_quantity = 0;
                    
                        if($component->sum_flag && ($component->unit_id == $contract_item->unit_id) ){
                            $component_total_quantity = $component_total_quantity + $component->quantity;
                        }

                        $component_items_arr[$component->id] = $component->ComponentItems;
                        
                        //Each component item row
                        foreach($component_items_arr[$component->id] as $component_item){
                            
                            //Total the quantity for all component item
                            if($component_item->sum_flag && $component_item->function_type_id == 4){ //As Equivalent function type
                                $component_items_total_quantity = $component_items_total_quantity + ($component_item->quantity * $component_item->function_variable * $component->use_count);
                            
                            }else if($component_item->sum_flag && ($component_item->unit_id == $component->unit_id)){
                               
                                $component_items_total_quantity = $component_items_total_quantity + $component_item->quantity;
                            
                            }

                            //Total the amount for each row
                            $contract_item_total_amount = $contract_item_total_amount + ($component_item->quantity * $component_item->budget_price);
                            
                            if( !isset( $component_total_amount_arr[$component->id] )){
                                $component_total_amount_arr[$component->id] = 0;
                            }
                            
                            $component_total_amount_arr[$component->id] = $component_total_amount_arr[$component->id] + ($component_item->quantity * $component_item->budget_price);
                            

                        }

                        $component_item_quantity_total_per_component[$component->id] = $component_items_total_quantity;
                    }  
                    
                @endphp
                <tr class="bg-contract-item">
                    <th  style="text-align:left">{{ Str::wordWrap($contract_item->item_code,10,"\n",false) }}</th>
                    
                    <th style="text-align:left">
                        {!! Str::wordWrap($contract_item->description,30,"<br>",false) !!}
                    </th>
                    
                    <th  style="text-align:right">
                        {!! Str::wordWrap(number_format($contract_item->contract_quantity,2),8,"<br>",false) !!}
                    </th>
                    
                    <th style="text-align:center">
                        {!! Str::wordWrap($unit_options[$contract_item->unit_id]->text,8,"<br>",false) !!}
                    </th>
                    
                    <th style="text-align:right">
                        P {{ number_format($contract_item->contract_unit_price,2) }}
                    </th>
                    
                    <th style="text-align:right">
                        <!-- Contract Amount -->
                        @php 
                            $contract_amount                = $contract_item->contract_quantity * $contract_item->contract_unit_price;
                            $grand_total_contract_amount    = $grand_total_contract_amount + $contract_amount;
                        @endphp
                        P {{ number_format($contract_amount,2) }}
                    </th>

                    <th style="text-align:right">
                        {{ number_format($contract_item->ref_1_quantity,2) }}
                    </th>
                    
                    <th style="text-align:center">
                        @if( isset( $unit_options[$contract_item->ref_1_unit_id] ) )
                            {{ $unit_options[$contract_item->ref_1_unit_id]->text }}
                        @endif
                    </th>
                    
                    <th style="text-align:right">
                        P {{ number_format($contract_item->ref_1_unit_price,2) }}
                    </th>
                    
                    <th style="text-align:right">
                        <!-- POW/DUPA Amount -->
                        @php 
                            $ref_1_amount                   = $contract_item->ref_1_quantity * $contract_item->ref_1_unit_price;
                            $grand_total_ref_1_amount       = $grand_total_ref_1_amount + $ref_1_amount;
                        @endphp
                        P {{ number_format($ref_1_amount,2) }}
                    </th>
                    
                    <th></th>
                    
                    <th style="text-align:right" class="@if($component_total_quantity > $contract_item->contract_quantity) font-color-danger @endif">
                        {{ number_format($component_total_quantity,2) }}
                    </th>
                    <th style="text-align:center" class="text-center @if($component_total_quantity > $contract_item->contract_quantity) font-color-danger @endif">
                        @if(isset($unit_options[$contract_item->unit_id]))
                            {{$unit_options[$contract_item->unit_id]->text}}
                        @endif
                    </th>
                    <th></th>
                    <th style="text-align:right">
                        <!-- Material Budget Amount-->
                        @php 
                            $grand_total_material_budget_amount = $grand_total_material_budget_amount + $contract_item_total_amount;
                        @endphp
                        
                        P {{ number_format($contract_item_total_amount,2) }}
                    </th>
                </tr>

                
                @foreach($components as $component)
                    
                    @php
                        $first      = true;
                        $item_count = 1;

                    @endphp
                    <tr class="@if(!$component->sum_flag || ($component->unit_id != $contract_item->unit_id)) bg-excluded-sum-component @endif">
                            @if($first)
                            <td rowspan="{{count($component_items_arr[$component->id])+2}}">
                                {!! Str::wordWrap($component->name,10,"<br>",false) !!}
                            </td>
                                
                            
                                @php 
                                    $first = false;
                                @endphp
                            @endif
                        <td></td>
                        <th style="text-align:right">
                           
                        </th>
                        <th style="text-align:center">
                           
                        </th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>

                        <th style="text-align:right">
                            {{ number_format($component->quantity,2) }}
                        </th>
                        <th style="text-align:center">
                            {{ $unit_options[$component->unit_id]->text }}
                        </th>
                        
                        <td></td>
                        <td style="text-align:right">
                           
                        </td>
                    </tr>
                    <tr>
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
                        <td style="text-align:right;font-style: italic" class="@if($component_item_quantity_total_per_component[$component->id] > $component->quantity) font-color-danger @endif">
                            {{ $component_item_quantity_total_per_component[$component->id] }}
                        </td>
                        <td style="text-align:center;font-style: italic" class="@if($component_item_quantity_total_per_component[$component->id] > $component->quantity) font-color-danger @endif">
                            {{$unit_options[$component->unit_id]->text}}
                        </td>
                        <td></td>
                        <td style="text-align:right;font-style: italic">
                             <!-- Material Component Total Amount -->
                             @if(isset($component_total_amount_arr[ $component->id ]))
                                P {{ number_format($component_total_amount_arr[ $component->id ],2) }}
                            @endif
                        </td>
                    </tr>
                    @foreach($component_items_arr[$component->id] as $component_item)
                        <tr class="@if(!$component_item->sum_flag || ($component_item->unit_id != $component->unit_id && $component_item->function_type_id != 4) ) bg-excluded-sum-component_item @endif">
                            
                            <td>
                                {{$item_count}}.) {{$component_item->name}}

                                @php
                                    $item_count++;
                                @endphp
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{$component_item->ref_1_quantity}}</td>
                            <td style="text-align:center">
                                @if(isset($unit_options[$component_item->ref_1_unit_id]))
                                    {{ $unit_options[$component_item->ref_1_unit_id]->text }}
                                @endif
                            </td>
                            <td style="text-align:right">
                                @if($component_item->ref_1_unit_price)
                                    P {{ number_format($component_item->ref_1_unit_price,2) }}
                                @endif
                            </td>
                            <td style="text-align:right">
                                @php
                                    $ref_1_total = (float) $component_item->ref_1_unit_price * (float) $component_item->ref_1_quantity;
                                @endphp

                                @if($ref_1_total > 0)
                                    P {{ number_format($ref_1_total,2) }}
                                @endif
                            </td>
                            <td style="text-align:center">
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
                                    
                                    <strong> > </strong>
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
                                    

                                    <strong> > </strong>
                                @endif


                                @if($component_item->function_type_id == 4)
                                <strong> < </strong>

                                    {{ 
                                        number_format(
                                            ($component_item->function_variable * $component->use_count),
                                            2
                                        ) 
                                    }}  

                                    {{$unit_options[$component->unit_id]->text}}
                                    /
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    
                                @endif
                            </td>
                            <td style="text-align:right">
                                {{ number_format($component_item->quantity,2) }}
                            </td>
                            <td style="text-align:center">
                                {{$unit_options[$component_item->unit_id]->text}}
                            </td>
                            <td style="text-align:right">
                                P {{ number_format($component_item->budget_price,2) }}
                            </td>
                            <td style="text-align:right">
                                P {{ number_format($component_item->quantity * $component_item->budget_price,2) }}
                            </td>
                        </tr>    
                    @endforeach

                @endforeach
            @endforeach
            
            <!-- Footer --> 

            <tr class="bg-contract-item">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th style="text-align:center">Grand Total</th>
                <th style="text-align:right">
                    <!-- Contract Amount -->
                    P {{ number_format($grand_total_contract_amount,2) }}
                </th>
                <th></th>
                <th></th>
                <th style="text-align:center">Grand Total</th>
                <th style="text-align:right" class="@if($grand_total_contract_amount < $grand_total_ref_1_amount) font-color-danger @endif">
                    P {{ number_format($grand_total_ref_1_amount,2) }}
                </th>
                <th></th>
                <th></th>
                <th></th>
                <th style="text-align:center">Grand Total</th>
                <th style="text-align:right" class="@if($grand_total_contract_amount < $grand_total_material_budget_amount) font-color-danger @endif">
                    <!-- Material Budget Amount -->
                    P {{ number_format($grand_total_material_budget_amount,2) }}
                </th>
            </tr>

        </table>
                     
        
</page>