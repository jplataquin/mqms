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

        th{
            text-align: center;
        }

        td, th {
            padding: 5px;
        }

        .contract-item-row td{
            background-color: grey;
        }

        .component-row td{
            background-color: green;
        }

        .component-item-row td{
            background-color: yellow;
        }

        .text-end{
            text-align:right !important;
        }

        .text-start{
            text-align:left !important;
        }

        .text-center{
            text-align:center !important;
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
            <tr class="contract-item-row" id="contract_item_{{$contract_item_id}}">
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
                <td class="text-end">P {{ number_format($row_1->contract_item->ref_1_unit_price,2) }}</td>
                <td class="text-end">P {{ number_format($row_1->contract_item->ref_1_amount,2) }}</td>

                <!-- Factor -->
                 <td></td>

                <!-- Material-->
                 <td class="material-quantity"></td>
                 <td></td>
                 <td></td>
                 <td class="text-end">P {{ number_format($total_amount->contract_item[$contract_item_id]->material,2) }}</td>
            </tr>


            <!-- Components -->
            @foreach($row_1->components as $component_id => $row_2)
                <tr class="component-row" id="component_{{$component_id}}">
                    <td rowspan="{{ ( count( (array) $row_2->component_items) + 1) }}">{{$row_2->component->name}}</td>
                    <td></td><!-- Description -->
                    
                    <td></td><!-- Contract -->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td><!-- Ref 1 -->
                    <td></td>
                    <td></td>
                    <td class="text-end">P {{ number_format( $total_amount->component[$component_id]->ref_1, 2) }}</td>

                    <td></td><!-- Factor -->
                    
                     <!-- Material -->
                    <td class="text-center material-quantity" data-value="{{$row_2->component->quantity}}">{{$row_2->component->quantity}}</td>
                    <td class="text-center">{{$row_2->component->unit_text}}</td>
                    <td></td>
                    <td class="text-end">P {{ number_format( $total_amount->component[$component_id]->material, 2) }}</td>
                </tr>
                
         

                <!-- Component Items -->
                @foreach($row_2->component_items as $component_item_id => $component_item)
              
                <!-- Component Item data row -->
                <tr class="component-item-row belongs_to_component_{{$component_id}}" id="component_item_{{$component_item_id}}"> 
                    <td>{{$component_item->name}}</td><!-- Component Item Name -->
                    
                    <!-- Contract -->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

                    <!-- Ref 1 -->
                    <td class="text-center ref-1-quantity" data-value="{{$component_item->ref_1_quantity}}">{{$component_item->ref_1_quantity}}</td>
                    <td class="text-center">{{$component_item->ref_1_unit_text}}</td>
                    <td class="text-end">P {{ number_format($component_item->ref_1_unit_price,2) }}</td>
                    <td class="text-end ref-1-amount">P {{ number_format($component_item->ref_1_amount,2) }}</td>

                    <td></td><!-- Factor -->
                    
                    <td class="text-center material-quantity" data-value="{{$component_item->quantity}}">{{$component_item->quantity}}</td><!-- Materia; -->
                    <td class="text-center">{{$component_item->unit_text}}</td>
                    <td class="text-end">P {{ number_format($component_item->budget_price,2) }}</td>
                    <td class="text-end" data-value="{{$component_item->amount}}">P {{ number_format($component_item->amount,2) }}</td>
                </tr>
                @endforeach

            @endforeach

        
        @endforeach
    </table>
    
    <script type="module">
        import {$q} from '/adarna.js';
        
     

        function totalAmountContractItem(el){
            let parent = el.parentElement;


        }

       
        
        $q('[data-controller]').items().map( item => {

            let func        = null;
            let func_name   = item.getAttribute('data-controller');

            if( /^[a-z0-9]+$/i.test(func_name) ){
                eval('func = '+func_name);
            }

            if(typeof func === 'function' && (typeof item._controlled == 'undefined' || item._controlled == false) ){
                
                func(item);

                item._controlled = true;
            }
         
        });
    </script>
</body>
</html>