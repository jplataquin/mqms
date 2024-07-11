<style>
    .table{
        border-collapse: collapse;
        width: 100%;
    }

    .table td{
        padding: 3px;
    }

    .table th{
        padding: 3px;
    }

    .text-center{
        text-align:center;
    }

    .teal-bg{
        background-color: #add8e6;
    }

    
</style>
<page>
    <div>
        <div style="margin-bottom: 10px">
            <h3>
                {{$component->name}}
            </h3>
            <hr>
            <table border="1" class="table">
                   
                    <tr>
                        <th style="width:20%">Project / Section</th>
                        <td style="width:80%">
                            {{$project->name}} - ( {{$section->name}} )
                        </td>
                    </tr>
                    <tr>
                        <th>Contract Item</th>
                        <td>{{$contract_item->item_code}} - {{$contract_item->description}}</td>
                    </tr>
                    <tr>
                        <th>Component ID</th>
                        <td>{{str_pad($component->id,6,0,STR_PAD_LEFT)}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$component->status}}</td>
                    </tr>
                    <tr>
                        <th>Quantity / Unit</th>
                        <td>{{$component->quantity}} {{$unit_options[$component->unit_id]->text}}</td>
                    </tr>
                    <tr>
                        <th>Use Count</th>
                        <td>{{$component->use_count}}</td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{$component->createdByUser()->name}} {{$component->created_at}}</td>
                    </tr>
                    <tr>
                        <th>Updated By</th>
                        <td>{{$component->updatedByUser()->name}} {{$component->updated_at}}</td>
                    </tr>
                    <tr>
                        <th>Hash</th>
                        <td>{{$hash}}</td>
                    </tr>
            </table>
        </div>
        @php 
            $i              = 1;
            $grand_total    = 0;
        @endphp
        <div class="font-size-11">
        @foreach($componentItems as $item)
        

            <div style="margin-bottom:10px">
                <table border="1" class="table">
                    <tr>
                        <th class="teal-bg" colspan="4" style="width:100%">
                            {{$i}}.) {{$item->name}}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center">ceil( Quantity )</th>
                        <th class="text-center">Factor</th>
                        <th class="text-center">Budget Price</th>
                        <th class="text-center">Total Cost</th>
                    </tr>
                    <tr>
                        <td class="text-center">
                            {{$item->quantity}} {{$unit_options[$item->unit_id]->text}}
                        </td>
                        <td class="text-center">

                            @if($item->function_type_id == 1)
                             {{$item->function_variable}} {{ $unit_options[ $item->unit_id ]->text }} / {{$unit_options[$component->unit_id]->text}}
                            @elseif($item->function_type_id == 2)
                             1 {{$unit_options[$component->unit_id]->text}} / {{$item->function_variable}} {{ $unit_options[ $item->unit_id ]->text }}
                            @else
                                N/A
                            @endif
                            
                            <br>
                            _________
                            <br>
                            {{$component->use_count}} Use(s)
                        </td>
                        
                        <td class="text-center">
                            Php {{ number_format($item->budget_price,2) }}
                        </td>
                        <td class="text-center">
                            @php
                                $grand_total = $grand_total + ($item->budget_price * $item->quantity);
                            @endphp
                            Php {{ number_format($item->budget_price * $item->quantity,2) }}
                        </td>
                    </tr>
                </table>
                
                <table border="1" class="table" style="margin-top:10px">
                        <tr>
                            <th style="width:40%" class="text-center">Material</th>
                            <th style="width:20%" class="text-center">Equivalent</th>
                            <th style="width:20%" class="text-center">Quantity</th>
                            <th style="width:20%" class="text-center">Total</th>
                        </tr>
                        
                        @foreach($item->materialQuantities as $mq)
                        <tr>
                            <td>
                                {{$materialItems[$mq->material_item_id]->name }} 
                                {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                                {{$materialItems[$mq->material_item_id]->brand }} 
                            </td>
                            <td class="text-center">
                                {{$mq->equivalent}} {{$item->unit}}
                            </td>
                            <td class="text-center">
                                {{$mq->quantity}}
                            </td>
                            <td class="text-center">
                                {{$mq->equivalent * $mq->quantity}} {{$item->unit}}
                            </td>
                        </tr>
                        @endforeach

                </table>
            </div>

           @php $i++ @endphp
        @endforeach
        </div>
        <br>

        <table class="table" border="1">
            <tr>
                <td class="text-center teal-bg" style="width:50%">
                    <h4>Grand Total</h4>
                </td>
                <td class="text-center" style="width:50%">
                    <h4>Php {{number_format($grand_total,2)}}</h4>
                </td>
            </tr>
        </table>
    </div>

    <page_footer>
        <div style="text-align:right;padding-bottom:5px">
        [[page_cu]]/[[page_nb]]
        </div>
    </page_footer>

</page>