<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$project->name}} - {{$section->name}} - {{$datetime_generated}}</title>

    <style>

        table, tr, td, th {
            border: solid 1px #000000;
            border-collapse: collapse;
            font-size: 11px;
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

        .contract-item-row{
            background-color: #a1b2b7;
        }

        .contract-item-row:hover{
            background-color:rgb(177, 184, 227);
            cursor:pointer;
        }

        .component-row:hover{
            background-color:rgb(177, 209, 227);
            cursor:pointer;
        }

        .component-item-row:hover{
            background-color:rgb(177, 219, 227);
            cursor:pointer;
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

        .mb-5{
            margin-bottom:5px;
        }

        .amount-15{
            width: 15ch !important;
        }

        .amount-13{
            width: 13ch !important;
        }
        
        .text-italic{
            font-style: italic;
        }

        .wrap{
            word-wrap: break-word;
        }

        
        @media print {

            td, th{
                font-size:10px;
            }

            .component-row{
                background-color:rgb(177, 209, 227);
            }

            .page-break{
                break-before:always;
            }
            
            thead{
                background-color:silver;
            }
        }
    </style>
</head>
<body>
    <table class="mb-5">
        <tr>
            <td data-controller="pageBreaker" class="text-center" style="width:50%" colspan="2">
                <img src="/storage/sys_images/header.png" style="width:500px"/>
            </td>
            <td class="text-center" colspan="2">
                <h1>Material Budget</h1>
            </td>
        </tr>
        <tr>
            <th data-controller="pageBreaker">Project ( {{$project->id}} )</th>
            <td>{{$project->name}}</td>
            <th>Date & Time Generated</th>
            <td>{{$datetime_generated}}</td>
        </tr>
        <tr>
            <th data-controller="pageBreaker">Section ( {{$section->id}} )</th>
            <td>{{$section->name}}</td>
            <th>Generated By</th>
            <td>{{$user->name}}</td>
        </tr>
    </table>

    <table>

        <!--Headers -->
        <thead>
            <tr>
                <th data-controller="pageBreaker" rowspan="2" style="min-width:5%;max-width:5%">ITEM CODE</th>
                <th rowspan="2" style="min-width:20%;max-width:20%">DESCRIPTION</th>
                <th colspan="4" style="">Contract</th>
                <th colspan="4" style="">POW/DUPA</th>
                <th rowspan="2" style="min-width:8%;max-width:8%">Factor</th>
                <th colspan="4" style="">Material Budget</th>
            </tr>
            <tr>
         

                <!-- Contract -->
                <th>QTY</th>
                <th>UNIT</th>
                <th class="amount-13">RATE</th>
                <th class="amount-15">AMOUNT</th>

                <!--Referennce -->
                <th>QTY</th>
                <th>UNIT</th>
                <th class="amount-13">RATE</th>
                <th class="amount-15">AMOUNT</th>

                
                <!-- Material-->
                <th>QTY</th>
                <th>UNIT</th>
                <th class="amount-13">RATE</th>
                <th class="amount-15">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $contract_item_id => $row_1)

            <!-- Contract Item -->
            <tr class="contract-item-row">
                <td data-controller="pageBreaker">{{$row_1->contract_item->item_code}}</td>
                <td>{{$row_1->contract_item->description}}</td>
                
                <!-- Contract -->
                <td class="text-center">{{ number_format($row_1->contract_item->contract_quantity,2)}}</td>
                <td class="text-center">{{$row_1->contract_item->contract_unit_text}}</td>
                <td class="text-end">P {{ number_format($row_1->contract_item->contract_unit_price,2) }}</td>
                <td class="text-end">P {{ number_format($row_1->contract_item->contract_amount,2) }}</td>

                <!--Reference -->
                <td class="text-center">{{ number_format($row_1->contract_item->ref_1_quantity,2) }}</td>
                <td class="text-center">{{$row_1->contract_item->ref_1_unit_text}}</td>
                <td class="text-end">P {{ number_format($row_1->contract_item->ref_1_unit_price,2) }}</td>
                <td class="text-end">P {{ number_format($row_1->contract_item->ref_1_amount,2) }}</td>

                <!-- Factor -->
                 <td></td>

                <!-- Material-->
                 <td class="text-center">{{ number_format($contract_item_material_total_quantity[$contract_item_id],2) }}</td>
                 <td class="text-center">{{$row_1->contract_item->contract_unit_text}}</td>
                 <td></td>
                 <td class="text-end">P {{ number_format($total_amount->contract_item[$contract_item_id]->material,2) }}</td>
            </tr>


            <!-- Components -->
            @foreach($row_1->components as $component_id => $row_2)
        
                <tr class="component-row">
                    <td data-controller="pageBreaker"  rowspan="{{ ( count( (array) $row_2->component_items) + 2) }}">{{$row_2->component->name}}</td>
                    <td></td><!-- Description -->
                    
                    <td></td><!-- Contract -->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td><!-- Ref 1 -->
                    <td></td>
                    <td></td>
                    <td class="text-end">P {{ number_format( $total_amount->component[$component_id]->ref_1, 2) }}</td>

                    <td class="text-center"></td><!-- Factor -->
                    
                     <!-- Material -->
                    <th class="text-center" >{{ number_format($row_2->component->quantity,2) }}</th>
                    <th class="text-center">{{$row_2->component->unit_text}}</th>
                    <td></td>
                    <td class="text-end">P {{ number_format( $total_amount->component[$component_id]->material, 2) }}</td>
                </tr>
                
         
                <tr>
           
                    <td></td><!-- Description -->
                    
                    <td></td><!-- Contract -->
                    <td></td>
                    <td></td>
                    <td></td>

                    <td></td><!-- Ref 1 -->
                    <td></td>
                    <td></td>
                    <td></td>

                    <td class="text-end"></td><!-- Factor -->

                     <!-- Material -->
                    <td class="text-center text-italic">
                        {{ number_format( $component_material_total_quantity[$component_id],2) }}
                    </td>
                    <td class="text-center text-italic">
                        {{ $row_2->component->unit_text }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>


                <!-- Component Items -->
                @foreach($row_2->component_items as $component_item_id => $row_3)

                @php
                    $component_item = $row_3->component_item;
                @endphp

                <!-- Component Item data row -->
                <tr class="component-item-row"> 
                    <td>{{$component_item->name}}</td><!-- Component Item Name -->
                    
                    <!-- Contract -->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

                    <!-- Ref 1 -->
                    <td class="text-center">{{ number_format($component_item->ref_1_quantity,2) }}</td>
                    <td class="text-center">{{$component_item->ref_1_unit_text}}</td>
                    <td class="text-end">P {{ number_format($component_item->ref_1_unit_price,2) }}</td>
                    <td class="text-end">P {{ number_format($component_item->ref_1_amount,2) }}</td>

                    <!-- Factor -->
                    <td class="text-center wrap">
                        {{ $row_3->factor_text_value}}
                    </td>
                    
                    <td class="text-center">{{ number_format( $component_item->quantity,2) }}</td><!-- Material -->
                    <td class="text-center">{{$component_item->unit_text}}</td>
                    <td class="text-end">P {{ number_format($component_item->budget_price,2) }}</td>
                    <td class="text-end">P {{ number_format($component_item->amount,2) }}</td>
                </tr>
                @endforeach
       
            @endforeach

        
        @endforeach
        </tbody>

        
        <!-- Grand Total -->
     
            <tr>
                <td></td>
                <td></td>
                
                <td></td><!-- Contract -->
                <td></td>
                <td></td>
                <th class="text-end">P {{ number_format( $grand_total_amount->contract, 2) }}</th>
                <td></td><!-- Ref 1 -->
                <td></td>
                <td>
                    @php
                        $ref_1_grand_percentage = 0;

                        if($grand_total_amount->contract > 0){

                            $ref_1_grand_percentage = ($grand_total_amount->ref_1 / $grand_total_amount->contract) * 100;
                        }

                        $ref_1_grand_percentage = number_format($ref_1_grand_percentage,2);
                    @endphp

                    {{$ref_1_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->ref_1, 2) }}</th>

                <td></td><!-- Factor -->
                
                <!-- Material -->
                <td></td>
                <td></td>
                <td>
                    @php
                        $material_grand_percentage = 0;

                        if($grand_total_amount->contract > 0){

                            $material_grand_percentage = ($grand_total_amount->material / $grand_total_amount->contract) * 100;
                        }

                        $material_grand_percentage = number_format($material_grand_percentage,2);
                    @endphp

                    {{$material_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->material, 2) }}</th>
            </tr>
     
    </table>
    


    <script type="module">
      import {$q} from '/adarna.js';
        
        function pageBreaker(items){

            let total_height    = 0;
            let item_before     = null;

            items.map(item => {
                
                if(item_before == null){
                    item_before = item;
                }

                total_height += item.offsetHeight;

                if(total_height >= 965){

                    console.log(total_height,item_before)
                    item_before.parentElement.classList.add('page-break');
                    total_height = 0;
                    item_before = null;

                
                }else{
                    item_before = item;
                }

                
            });

            
            
        }

        let elem = {};
        
        $q('[data-controller]').items().map( item => {

            let func        = null;
            let func_list   = item.getAttribute('data-controller');
            
            let func_arr = func_list.split(' ');

            func_arr.map(func_name => {

                if( /^[a-z0-9]+$/i.test(func_name) ){
                
                    if(typeof elem[func_name] == 'undefined'){
                        elem[func_name] = [];
                    }

                    elem[func_name].push(item);
                
                }

            });
         
        });

        let func = null;

        for(let func_name in elem){

            eval('func = (typeof '+func_name+' == "function") ? '+func_name+' : null;');

            if(typeof func === 'function' && func != null){
                
                 func(elem[func_name]);

            }
        }
    </script>
</body>
</html>