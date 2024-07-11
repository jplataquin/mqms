<html>
    <head>
        <style>
            table{
                border-collapse:collapse;

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

                
                <tr>
                    <th>{{$contract_item->item_code}}</th>
                    <th>{{$contract_item->description}}</th>
                    <th>
                        {{$contract_item->contract_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th>
                        PHP {{$contract_item->contract_unit_price}}
                    </th>
                    <th>
                        PHP {{$contract_item->contract_quantity * $contract_item->contract_unit_price}}
                    </th>

                    <th>
                        {{$contract_item->ref_1_quantity}}
                    </th>
                    <th>
                        {{$unit_options[$contract_item->unit_id]->text}}
                    </th>
                    <th>
                        PHP {{$contract_item->ref_1_unit_price}}
                    </th>
                    <th>
                        PHP {{$contract_item->ref_1_quantity * $contract_item->ref_1_unit_price}}
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>

                
                @foreach($contract_item->components as $component)
                    
                    @php
                        $component_items = $component->ComponentItems;
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
                        <th>
                            {{$component->quantity}} {{$unit_options[$component->unit_id]->text}}
                        </th>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                   
                    @foreach($component_items as $component_item)
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
                            <td>
                                PHP {{$component_item->quantity * $component_item->budget_price}}
                            </td>
                        </tr>    
                    @endforeach

                @endforeach
            @endforeach
            
        </table>
    </body>
</html>