<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$project->name}} - {{$section->name}} - {{$datetime_generated}}</title>
    <style>

        .d-none{
            display:none;
        }

        .d-inline{
            display:inline;
        }


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


        
        .material-quantity-table{
            border: none !important;
        }

        .material-quantity-tr{
            border: none !important;
        }

        .material-quantity-td{
            border: none !important;
        }

        .material-quantity-th{
            border: none !important;
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

        .component{
            user-select: none;
            -moz-user-select: none;
            -webkit-user-select: none; /* Chrome, Opera and Safari*/
            -ms-user-select: none; /* IE, ms-edge */
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

        .ml-3{
            margin-left: 3px;
        }

        .ml-5{
            margin-left: 5px;
        }


        .mb-3{
            margin-bottom:3px;
        }

        .mb-5{
            margin-bottom:5px;
        }

        .me-10{
            margin-right:10px;
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

        .d-inline-block{
            display:inline-block;
        }

        @media print {

            td, th{
                font-size:10px;
            }

            .page-break{
                break-before:always;
            }
            
        
        }


        #actionContainer{
            position:fixed;
            bottom:50px;
            right:50px;
        }


        #actionBtn{
            width: 75px; /* Or any desired size */
            height: 75px; /* Must match width for a perfect circle */
            border-radius: 50%; /* Creates the circular shape */
            /* Add other styling as needed, e.g., background color, etc. */
            background-color: rgb(90, 90, 90);
            opacity: 0.2;
            text-align:center;
            cursor:pointer;
            display:inline-block;
        }

        #actionBtn:hover{
            opacity: 0.4;
        }
        

        #actionBtn > svg{
            margin-top:16px;
        }


        #searchBtn{
            width: 75px; /* Or any desired size */
            height: 75px; /* Must match width for a perfect circle */
            border-radius: 50%; /* Creates the circular shape */
            /* Add other styling as needed, e.g., background color, etc. */
            background-color: rgb(90, 90, 90);
            opacity: 0.2;
            text-align:center;
            cursor:pointer;
            display:inline-block;
        }

        #searchBtn:hover{
            opacity: 0.4;
        }
        

        #searchBtn > svg{
            margin-top:16px;
        }


        #searchBar{
            width:56%;
            position:fixed;
            top:10px;
            left:20%;
            padding:10px;
            background-color:grey;
        }

        #searchBar > div > input[type="text"]{
            font-size:24px;
            width:320px;
        }

        #searchBar > div > button{
      
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

    <table id="sheet">
        
        <!--Headers -->
        <thead>
            @if($hide['total_contract_item'] > 0 || $hide['total_component'] > 0)
            <tr>
                <th data-controller="pageBreaker" class="text-center rejected-text" colspan="8">
                    Hidden Contract Items: {{ number_format($hide['total_contract_item']) }}
                </th>
                <th class="text-center rejected-text" colspan="8">
                    Hidden Components: {{ number_format($hide['total_component']) }}
                </th>
            </tr>
            @endif
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
                <th class="text-center">%</th>
            </tr>
        </thead>
        <tbody>
        
       

        @foreach($data as $contract_item_id => $row_1)

            <!-- Contract Item -->

            <tr class="
                @if($hide['contract_item'][$row_1->contract_item->id])
                    d-none
                @endif

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

                    @php 
                        $contract_item_budget_total_amount = $total_amount->contract_item[$contract_item_id]->budget
                    @endphp

                    <td></td>
                    <th class="
                        text-end    
                        
                        @if( $total_amount->contract_item[$contract_item_id]->budget > $row_1->contract_item->contract_amount)
                            non-conforming 
                        @endif
                    ">P {{ number_format($total_amount->contract_item[$contract_item_id]->budget,2) }}</th>
                @endif

                <!-- Contract Item Percent -->
                <th class="text-center">
                    @php 
                        $contract_item_budget_percentage = 0;

                        if($section->gross_total_amount > 0){
                            $contract_item_budget_percentage = ($contract_item_budget_total_amount / $section->gross_total_amount) * 100;
                          
                        }
                     
                        $contract_item_budget_percentage = number_format($contract_item_budget_percentage,2);

                 
                    @endphp
                 
                    {{$contract_item_budget_percentage}}%
                </th>
            </tr>


            <!-- Components -->
            @foreach($row_1->components as $component_id => $row_2)
        
                <tr class="
                    @if($hide['component'][$component_id])
                        d-none
                    @endif
                    
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
                    <td role="dialog" class="component" data-controller="pageBreaker componentMenu" data-id="{{$row_2->component->id}}"  rowspan="{{ ( ( count( (array) $row_2->component_items) * 2) + 2) }}">
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

                    <!-- Factor -->
                    <td class="text-center">
                        Use Count: {{$row_2->component->use_count}} 
                    </td>
                    
                     <!-- Material -->
                    <th class="text-center" >{{ number_format($row_2->component->quantity,2) }}</th>
                    <th class="text-center">{{$row_2->component->unit_text}}</th>
                    <td></td>
                    <th class="text-end">P {{ number_format( $total_amount->component[$component_id]->budget, 2) }}</th>
                    
                    <!--Component Percent -->
                    <td class="text-center text-italic">

                        @php
                            $component_budget_amount_percentage = 0;

                            if($contract_item_budget_total_amount > 0){
                                $component_budget_amount_percentage = ($total_amount->component[$component_id]->budget / $contract_item_budget_total_amount) * 100;
                                $component_budget_amount_percentage = number_format($component_budget_amount_percentage,2);
                            }

                        @endphp

                        {{$component_budget_amount_percentage}}%
                    </td>
                </tr>
                
         
                <tr class="
                    @if($hide['component'][$component_id])
                        d-none
                    @endif
                ">
           
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
                    <td></td>
                </tr>


                <!-- Component Items -->
                @foreach($row_2->component_items as $component_item_id => $row_3)

                @php
                    $component_item = $row_3->component_item;

                @endphp

                <!-- Component Item data row -->
                <tr class="

                    @if($hide['component_item'][$component_item_id])
                        d-none
                    @endif
                    

                    component-item-row
                
                "> 
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
                    
                    <!-- Material -->
                    <td class="text-center">
                        @if($component_item->approximation == 'CEIL')
                        ↑ 
                        @elseif($component_item->approximation == 'FLOR')
                        ↓ 
                        @endif

                        {{ number_format( $component_item->quantity,2) }}
                        
                    </td>
                    <td class="text-center">{{$component_item->unit_text}}</td>
                    <td class="text-end">P {{ number_format($component_item->budget_price,2) }}</td>
                    <td class="text-end">P {{ number_format($component_item->amount,2) }}</td>
                    <td></td>
                </tr>

                <tr>
                    <td colspan="5">
                        <table class="ml-5 material-quantity-table">
                            <tr class="material-quantity-tr">
                                <th class="material-quantity-th text-start" width="50%">Material Item</th>
                                <th class="material-quantity-th">Eqv</th>
                                <th class="material-quantity-th">Qty</th>
                                <th class="material-quantity-th" width="20%">Total</th>
                            </tr>

                        @php $total_equivalent = 0; @endphp
                        @foreach($component_item->material_quantities as $mq)
                            <tr class="material-quantity-tr">

                                <td class="material-quantity-td">
                                    {{$material_item[$mq->material_item_id]->formattedName}}
                                </td>

                                <td class="text-center material-quantity-td">
                                    {{ number_format($mq->equivalent) }}
                                </td>

                                <td class="text-center material-quantity-td">
                                    {{ number_format($mq->quantity) }}
                                </td>


                                <td class="text-center material-quantity-td">    
                                    {{ number_format($mq->total_equivalent) }} {{$component_item->unit_text}}
                                </td>    
                            </tr>

                            @php $total_equivalent = $total_equivalent + $mq->total_equivalent; @endphp
                        @endforeach
                            <tr class="material-quantity-tr">
                                <td class="material-quantity-td" colspan="3"></td>
                                <td class="
                                    text-center
                                     
                                    @if($total_equivalent > $component_item->quantity)
                                        rejected-text non-conforming
                                    @endif
                                " style="border:solid 1px #000000">
                                    {{ number_format($total_equivalent) }} {{$component_item->unit_text}}
                                </td>
                            </tr>
                        </table>
                    </td>
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
                <th class="text-center"></th>
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
                  
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->budget_material, 2) }}</th>
                <th class="text-center">
                    @php
                        $budget_material_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $budget_material_grand_percentage = ($grand_total_amount->budget_material / $section->gross_total_amount) * 100;
                        }

                        $budget_material_grand_percentage = number_format($budget_material_grand_percentage,2);
                    @endphp

                    {{$budget_material_grand_percentage}}%
                </th>
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
                   
                </td>
                <th class="text-end">P {{ number_format( $grand_total_amount->budget_nonmaterial, 2) }}</th>
                <th class="text-center">
                    @php
                        $budget_nonmaterial_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $budget_nonmaterial_grand_percentage = ($grand_total_amount->budget_nonmaterial / $section->gross_total_amount) * 100;
                        }

                        $budget_nonmaterial_grand_percentage = number_format($budget_nonmaterial_grand_percentage,2);
                    @endphp

                    {{$budget_nonmaterial_grand_percentage}}%
                </th>
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
                   
                </td>
                <th class="
                    text-end 
                    
                    @if( $grand_total_amount->budget_opex > $grand_total_amount->contract_opex)
                        non-conforming 
                    @endif
                ">P {{ number_format( $grand_total_amount->budget_opex, 2) }}</th>
                <th class="text-center">
                    @php
                        $opex_material_grand_percentage = 0;

                        if($section->gross_total_amount > 0){

                            $opex_material_grand_percentage = ($grand_total_amount->budget_opex / $section->gross_total_amount) * 100;
                        }

                        $opex_material_grand_percentage = number_format($opex_material_grand_percentage,2);
                    @endphp

                    {{$opex_material_grand_percentage}}%
                </th>
            </tr>
    </table>
    
    <div id="searchBar" data-controller="searchBar">
        <div class="d-inline-block">
            <button>X</button>
        </div>
        <div class="d-inline-block">
            <input type="text"/>
        </div>
        <div class="d-inline-block">
            <div class="text-center">
                <span>00</span>
                <button>▲</button>
                <button>▼</button>
            </div>
        </div>
    </div>

    <div id="actionContainer">
        
        <div id="actionBtn" class="me-10">
            
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-fullscreen-exit" viewBox="0 0 16 16">
                <path d="M5.5 0a.5.5 0 0 1 .5.5v4A1.5 1.5 0 0 1 4.5 6h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 10 4.5v-4a.5.5 0 0 1 .5-.5M0 10.5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 6 11.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5m10 1a1.5 1.5 0 0 1 1.5-1.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0z"/>
            </svg>
        
        </div>

        <div id="searchBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
            </svg>
        </div>
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';
        import contextMenu from '/ui_components/ContextMenu.js';
        
        const actionBtn = $q('#actionBtn').first();
        const sheet     = $q('#sheet').first();

        const t = new Template();
        
   
        actionBtn.onclick = () =>{
            window.parent.exitFullscreen()
        }

        function pageBreaker(items){

            let total_height    = 0;
            let item_before     = null;

            items.map(item => {
                
                if(item_before == null){
                    item_before = item;
                }

                total_height += item.offsetHeight;

                if(total_height >= 965){

                    item_before.parentElement.classList.add('page-break');
                    total_height = 0;
                    item_before = null;

                
                }else{
                    item_before = item;
                }

                
            });

            
            
        }

        
        function searchBar(item){
            let elem = item[0];

            let input = elem.querySelector('input');
            
            input.onkeyup = (e)=>{
                let data = Array.from( sheet.querySelectorAll('th, td') );
              

                let search = input.value;
                
                let result = [];
                let regex  = new RegExp(search+'.*');

                data.map( d => {

                    if( d.innerText.match(regex) ){
                        result.push(d);
                    }

                });

                console.log(result);

            }
        }

        function componentMenu(items){

            items.map(item=>{

                item.addEventListener('mousedown',function(e){
                    e.preventDefault();
                });

                item.addEventListener('selectstart',function(e){
                    e.preventDefault();
                });

                let component_id = item.getAttribute('data-id');

                item.oncontextmenu = async (e)=>{
                    e.preventDefault();

                    let cm = contextMenu({
                        onOpen:()=>{
                            document.body.style.overflow = 'hidden';
                        },
                        onClose:()=>{
                            document.body.style.overflow = '';
                        },
                        items:[
                            {
                                name:'Approve',
                                onclick: async (e)=>{
                                    let answer = await window.parent.util.confirm('Are you sure you want to APPROVE this component?');
            
                                    if(!answer){
                                        return false;
                                    }

                                    window.parent.util.blockUI();

                                    window.parent.util.$post('/api/review/component/approve',{
                                        id: component_id
                                    }).then(reply=>{

                                        window.parent.util.unblockUI();

                                        if(reply.status <= 0 ){
                                            window.parent.util.showMsg(reply);
                                            return false;
                                        };


                                        window.document.location.reload();

                                    });
                                }
                            },
                            {
                                name:'Reject',
                                onclick: async (e)=>{
                                    
                                    let answer = await window.parent.util.confirm('Are you sure you want to REJECT this component?');
                                    
                                    if(!answer){
                                        return false;
                                    }

                                    window.parent.util.blockUI();

                                    window.parent.util.$post('/api/review/component/reject',{
                                        id: component_id
                                    }).then(reply=>{

                                        
                                        window.parent.util.unblockUI();

                                        if(reply.status <= 0 ){
                                            window.parent.util.showMsg(reply);
                                            return false;
                                        };


                                        document.location.reload();

                                    });
                                }
                            },
                            {
                                name:'Open',
                                onclick:(e)=>{
                                    window.parent.util.navTo('/project/section/contract_item/component/'+component_id);
                                }
                            },
                            {
                                name:'Revert to Pending',
                                onclick:(e)=>{
                                    alert('Revert to pending');
                                }
                            },
                        ]
                    });


                    let sheet_pos = window.parent.getSheetPos();


                    let posX = e.clientX + sheet_pos.left;
                    let posY = e.clientY;
                    
                    if(sheet_pos.top != 0){
                        posY = posY + sheet_pos.top + document.documentElement.scrollTop;
                    }else{
                        posY = posY + sheet_pos.top;
                    }

            
                    cm.handler.show(posX,posY);
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