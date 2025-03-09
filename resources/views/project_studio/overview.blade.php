<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>

        table, tr, td, th {
            border: solid 1px #000000;
            border-collapse: collapse;
        }
        
        table {
            width:100%;
        }
    </style>
</head>
<body>
    
    <table>

        <!--Headers -->
        <tr>
            <th rowspan="2">ITEM CODE</th>
            <th rowspan="2">DESCRIPTION</th>
            <th colspan="4">Contract</th>
            <th colspan="4">POW/DUPA</th>
            <th rowspan="2">Factor</th>
            <th colspan="4">Material Budget</th>
        </tr>
        <tr>
         

            <!-- Contract -->
            <th>QTY</th>
            <th>UNIT</th>
            <th>RATE</th>
            <th>AMOUNT</th>

            <!--Referennce -->
            <th>QTY</th>
            <th>UNIT</th>
            <th>RATE</th>
            <th>AMOUNT</th>

            
            <!-- Material-->
            <th>QTY</th>
            <th>UNIT</th>
            <th>RATE</th>
            <th>AMOUNT</th>
        </tr>


        @foreach($data as $contract_item_id => $row_1)

            <!-- Contract Item -->
            <tr>
                <td>{{$row_1->contract_item->item_code}}</td>
                <td>{{$row_1->contract_item->description}}</td>
                
                <!-- Contract -->
                <td>{{$row_1->contract_item->contract_quantity}}</td>
                <td>{{$row_1->contract_item->contract_unit_text}}</td>
                <td>P {{ number_format($row_1->contract_item->contract_unit_price,2) }}</td>
                <td>P {{ number_format($row_1->contract_item->contract_amount,2) }}</td>

                <!--Reference -->
                <td>{{$row_1->contract_item->ref_1_quantity}}</td>
                <td>{{$row_1->contract_item->ref_1_unit_text}}</td>
                <td>P {{ number_format($row_1->contract_item->ref_1_unit_price,2) }}</td>
                <td>P {{ number_format($row_1->contract_item->ref_1_amount,2) }}</td>

                <!-- Factor -->
                 <td></td>

                <!-- Material-->
                 <td></td>
                 <td></td>
                 <td></td>
                 <td></td>
            </tr>


            <!-- Components -->
            @foreach($row_1->components as $component_id => $row_2)
                <tr>
                    <td rowspan="{{count($row_2->component_items)}}">{{$row_2->component->name}}</td>
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
                    <td>{{$row_2->component->quantity}}</td>
                    <td>{{$row_2->component->unit_text}}</td>
                    <td></td>
                    <td></td>
                </tr>


                <!-- Component Items -->
                @foreach($row_2->component_items as $component_item_id => $row_3)
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
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach

            @endforeach

        
        @endforeach
    </table>
</body>
</html>