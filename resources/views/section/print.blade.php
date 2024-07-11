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
            <tr>
            <tr>
                <th>QTY</th>
                <th>UNIT</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>
            <tr>
                
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
                </tr>

                @php
                    $first = true;
                @endphp

                @foreach($contract_item->components as $component)
                    <tr>
                        @if($first)
                        <td rowspan="{{count($contract_item->components)}}">
                            {{$component->name}}
                        </td>
                            @php
                                $first = false;
                            @endphp
                        @endif
                        <td>
                            {{$component->name}}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>    
                @endforeach
                
            @endforeach
            
        </table>
    </body>
</html>