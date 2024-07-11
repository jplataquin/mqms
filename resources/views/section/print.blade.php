<html>
    <head>
    </head>
    <body>
        <h1>TEst</h1>
        <table border="1">
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

                
                <tr>
                    <td>{{$contract_item->item_code}}</td>
                    <td>{{$contract_item->description}}</td>
                    <td>
                        {{$contract_item->contract_quantity}}
                    </td>
                    <td>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </td>
                    <td>
                        PHP {{$contract_item->contract_unit_price}}
                    </td>
                    <td>
                        PHP {{$contract_item->contract_quantity * $contract_item->contract_unit_price}}
                    </td>

                    <td>
                        {{$contract_item->ref_1_quantity}}
                    </td>
                    <td>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </td>
                    <td>
                        PHP {{$contract_item->ref_1_unit_price}}
                    </td>
                    <td>
                        PHP {{$contract_item->ref_1_quantity * $contract_item->ref_1_unit_price}}
                    </td>
                    <td></td>
                </tr>
                @foreach($contract_item->components as $component)

                    @php
                        $component_items = $component->ComponentItems;
                        $first = true;
                    @endphp
                
                    @foreach($component_items as $component_item)
                    
                    <tr>

                            @if($first)
                            <td rowspan="{{count($component_items)}}">
                                {{$component->name}}
                            </td>

                                @php
                                    $first = false;
                                @endphp
                                
                            @endif
                            
                    
                            
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
                                {{$component_item->function_variable}}
                            </td>
                            <td>
                                {{$component_item->quantity}}
                            </td>
                            <td>
                                {{$unit_options[$component_item->unit_id]->text}}
                            </td>
                            <td>
                                PHP {{$component_item->budget_price}}
                            </td>
                        </tr>    
                    @endforeach
                @endforeach
            @endforeach
            
        </table>
    </body>
</html>