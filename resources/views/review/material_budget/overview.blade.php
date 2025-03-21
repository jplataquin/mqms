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

        tr:hover {
            background-color: #b5ffca !important;
        }

        /** Non-Material */
        .contract-item-nonmaterial-row{
            background-color:#9dccfa;
        }

        .nonmaterial-bg{
            background-color:#9dccfa;
        }

        .component-nonmaterial-row{
            background-color: #b9d9fa;
        }


        /** Material */
        .contract-item-material-row{
            background-color: #c7c5c5;
        }

        .material-bg{
            background-color: #c7c5c5;
        }

        .component-material-row{
            background-color: #f0f0f0;
        }


     
        /** Opex **/
        .contract-item-opex-row{
            background-color: #f7f2d6;
        }

        .opex-bg{
            background-color: #f7f2d6;
        }

        .component-opex-row{
            background-color: #fffcec
        }
       
        .non-conforming{
            color:red !important;
            background-color:#edb4b4 !important;
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

        .warning-text{
            color:rgb(234, 255, 5);
        }

        .pending-text{
            color:rgb(234, 255, 5);
        }

        .approved-text{
            color:rgb(11, 152, 1);
        }

        .rejected-text{
            color:rgb(255, 5, 5);
        }

        @media print {

            td, th{
                font-size:10px;
            }

            .page-break{
                break-before:always;
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
                <th colspan="4" style="">Budget</th>
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

            <tr class="
                @if($row_1->contract_item->item_type == 'NMAT') 
                    contract-item-nonmaterial-row 
                @endif

                @if($row_1->contract_item->item_type == 'MATR') 
                    contract-item-material-row 
                @endif

                @if($row_1->contract_item->item_type == 'OPEX') 
                    contract-item-opex-row 
                @endif
            
            ">
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

                @if($row_1->contract_item->budget_quantity_overwrite)
                    <td class="text-center">
                        <label class="warning-text">✦</label>
                        {{ number_format($row_1->contract_item->budget_quantity,2) }}
                    </td>
                    <td class="text-center">
                        <label class="warning-text">✦</label>
                        {{$row_1->contract_item->budget_unit_text}}
                    </td>
                @else
                    <td class="text-center">{{ number_format($contract_item_budget_total_quantity[$contract_item_id],2) }}</td>
                    <td class="text-center">{{$row_1->contract_item->contract_unit_text}}</td>
 
                @endif


                @if($row_1->contract_item->budget_total_amount_overwrite)

                    <td class="text-end">
                        <label class="warning-text">✦</label>
                        P {{ number_format($row_1->contract_item->budget_unit_price,2)}}
                    </td>
                    
                    @php 
                        $contract_item_budget_total_amount = $row_1->contract_item->budget_quantity * $row_1->contract_item->budget_unit_price;
                    @endphp
                    
                    <th class="
                        text-end    
                        
                        @if( $contract_item_budget_total_amount > $row_1->contract_item->contract_amount)
                            non-conforming 
                        @endif
                    ">
                        <label class="warning-text">✦</label>
                        P {{ number_format($contract_item_budget_total_amount,2) }}
                    </th>

                @else

                    <td></td>
                    <th class="
                        text-end    
                        
                        @if( $total_amount->contract_item[$contract_item_id]->budget > $row_1->contract_item->contract_amount)
                            non-conforming 
                        @endif
                    ">P {{ number_format($total_amount->contract_item[$contract_item_id]->budget,2) }}</th>
                @endif
            </tr>


            <!-- Components -->
            @foreach($row_1->components as $component_id => $row_2)
        
                <tr class="
                     @if($row_1->contract_item->item_type == 'NMAT') 
                        component-nonmaterial-row 
                    @endif

                    @if($row_1->contract_item->item_type == 'MATR') 
                        component-material-row 
                    @endif

                    @if($row_1->contract_item->item_type == 'OPEX') 
                        component-opex-row 
                    @endif
                ">
                    <td data-controller="pageBreaker"  rowspan="{{ ( count( (array) $row_2->component_items) + 2) }}">
                        @if($row_2->component->status == 'PEND')
                            <div class="pending-text text-center">⦿</div>
                        @endif

                        @if($row_2->component->status == 'APRV')
                            <div class="approved-text text-center">⦿</div>
                        @endif

                        @if($row_2->component->status == 'REJC')
                        <div class="rejected-text text-center">⦿</div>
                        @endif

                        {{$row_2->component->name}}
                    </td>
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
                    <th class="text-end">P {{ number_format( $total_amount->component[$component_id]->budget, 2) }}</th>
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
                        {{ number_format( $component_budget_total_quantity[$component_id],2) }}
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
            
            <!-- Gross -->
            <tr>
                <th colspan="5" class="text-end">Gross Total Amount</th>
                
             
                <th class="text-end">P {{ number_format( $section->gross_total_amount, 2) }}</th>
                <td></td><!-- Ref 1 -->
                <td></td>
                <td class="text-center">
               
                </td>
                <th class="text-end"></th>

                <td></td><!-- Factor -->
                
                <!-- Material -->
                <td></td>
                <td></td>
                <td class="text-center">
                    
                </td>
                <th class="text-end"></th>
            </tr>


            <!-- Material -->
            <tr>
                <th colspan="4" class="text-center material-bg">Material</th>
                <th class="text-center">
                    @php
                        $material_total_percentage = 0;

                        if($section->gross_total_amount > 0){
                            $material_total_percentage = ($grand_total_amount->contract_material / $section->gross_total_amount) * 100;
                        }

                        $material_total_percentage = number_format($material_total_percentage,2);
                    @endphp
                    {{$material_total_percentage}}%
                </th>
             
                <th class="text-end">P {{ number_format( $grand_total_amount->contract_material, 2) }}</th>
                <td></td><!-- Ref 1 -->
                <td></td>
                <td class="text-center">
                    @php
                        $ref_1_material_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $ref_1_material_grand_percentage = ($grand_total_amount->ref_1_material / $section->gross_total_amount) * 100;
                        }

                        $ref_1_material_grand_percentage = number_format($ref_1_material_grand_percentage,2);
                    @endphp

                    {{$ref_1_material_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->ref_1_material, 2) }}</th>

                <td></td><!-- Factor -->
                
                <!-- Material -->
                <td></td>
                <td class="text-center">
                
                </td>
                <td class="text-center">
                    @php
                        $budget_material_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $budget_material_grand_percentage = ($grand_total_amount->budget_material / $section->gross_total_amount) * 100;
                        }

                        $budget_material_grand_percentage = number_format($budget_material_grand_percentage,2);
                    @endphp

                    {{$budget_material_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->budget_material, 2) }}</th>
            </tr>
            
    

            <!-- Non Material -->
            <tr>
                <th colspan="4" class="text-center nonmaterial-bg">Non-Material</th>
                <th class="text-center">
                    @php
                        $nonmaterial_total_percentage = 0;

                        if($section->gross_total_amount > 0){
                            $nonmaterial_total_percentage = ($grand_total_amount->contract_nonmaterial / $section->gross_total_amount) * 100;
                        }

                        $nonmaterial_total_percentage = number_format($nonmaterial_total_percentage,2);
                    @endphp
                    {{$nonmaterial_total_percentage}}%
                </th>
             
                <th class="text-end">P {{ number_format( $grand_total_amount->contract_nonmaterial, 2) }}</th>
                <td></td><!-- Ref 1 -->
                <td></td>
                <td class="text-center">
                    @php
                        $ref_1_nonmaterial_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $ref_1_nonmaterial_grand_percentage = ($grand_total_amount->ref_1_nonmaterial / $section->gross_total_amount) * 100;
                        }

                        $ref_1_nonmaterial_grand_percentage = number_format($ref_1_nonmaterial_grand_percentage,2);
                    @endphp

                    {{$ref_1_nonmaterial_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->ref_1_nonmaterial, 2) }}</th>

                <td></td><!-- Factor -->
                
                <!-- Material -->
                <td></td>
                <td class="text-center">
                
                </td>
                <td class="text-center">
                    @php
                        $budget_nonmaterial_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $budget_nonmaterial_grand_percentage = ($grand_total_amount->budget_nonmaterial / $section->gross_total_amount) * 100;
                        }

                        $budget_nonmaterial_grand_percentage = number_format($budget_nonmaterial_grand_percentage,2);
                    @endphp

                    {{$budget_nonmaterial_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->budget_nonmaterial, 2) }}</th>
            </tr>


            <!-- OPEX -->
            <tr>
                <th colspan="4" class="text-center opex-bg">Operational Expense</th>
                <th class="text-center">
                    @php
                        $opex_total_percentage = 0;

                        if($section->gross_total_amount > 0){
                            $opex_total_percentage = ($grand_total_amount->contract_opex / $section->gross_total_amount) * 100;
                        }

                        $opex_total_percentage = number_format($opex_total_percentage,2);
                    @endphp
                    {{ $opex_total_percentage }}%
                </th>

                <th class="text-end">P {{ number_format( $grand_total_amount->contract_opex, 2) }}</th>
                <td></td><!-- Ref 1 -->
                <td></td>
                <td class="text-center">
                    @php
                        $ref_1_opex_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $ref_1_opex_grand_percentage = ($grand_total_amount->ref_1_opex / $section->gross_total_amount) * 100;
                        }

                        $ref_1_opex_grand_percentage = number_format($ref_1_opex_grand_percentage,2);
                    @endphp

                    {{$ref_1_opex_grand_percentage}}%
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->ref_1_opex, 2) }}</th>

                <td></td><!-- Factor -->
                
                <!-- Material -->
                <td></td>
                <td class="text-center">
                 
                </td>
                <td class="text-center">
                    @php
                        $opex_material_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $opex_material_grand_percentage = ($grand_total_amount->budget_opex / $section->gross_total_amount) * 100;
                        }

                        $opex_material_grand_percentage = number_format($opex_material_grand_percentage,2);
                    @endphp

                    {{$opex_material_grand_percentage}}%
                </td>
                <th class="
                    text-end 
                    
                    @if( $grand_total_amount->budget_opex > $grand_total_amount->contract_opex)
                        non-conforming 
                    @endif
                ">P {{ number_format( $grand_total_amount->budget_opex, 2) }}</th>
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