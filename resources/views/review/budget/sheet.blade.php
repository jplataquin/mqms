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

        .d-hide{
            display:none;
        }

        .d-inline{
            display:inline;
        }

        table, tr, td, th {
            border: solid 1px #00000040;
            border-collapse: separate;
            font-size: 11px;
            border-spacing:0px;
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

        .no-select{
            user-select: none;
            -moz-user-select: none;
            -webkit-user-select: none; /* Chrome, Opera and Safari*/
            -ms-user-select: none; /* IE, ms-edge */
            -webkit-tap-highlight-color: transparent;
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


        .contract-item{
            cursor:pointer
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
            color: rgb(255, 227, 0);
        }

        .pending-text{
            color:rgb(255, 227, 0);
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

        .status{
            font-size:24px;
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
            bottom:100px;
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

        #collapseBtn{
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

        #collapseBtn:hover{
            opacity: 0.4;
        }
        

        #collapseBtn > svg{
            margin-top:16px;
        }

        #searchBar{
            width:500px;
            position:fixed;
            top:50%;
            left:50%;
            padding:10px;
            background-color:grey;
            opacity: 0.7;
        }

        #searchBar > div > input[type="text"]{
            font-size:24px;
            width:320px;
        }

        .border-highlight{
            border: solid 3pxrgb(18, 18, 255) !important;
        }

        .background-highlight{
            background-color:rgb(174, 174, 254) !important;
        }

        .sticky{
            position: sticky;
        }

        .bg-white{
            background-color:#ffffff !important;
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
        <thead class="sticky bg-white sheet-header" style="top:0">
            @if($hide['total_contract_item'] > 0 || $hide['total_component'] > 0)
            <tr class="sticky bg-white" style="top:0">
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
                <th class="text-center" rowspan="2">%</th>
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

            <tr data-id="{{$row_1->contract_item->id}}" data-controller="contractItemController" class="
                sticky

                contract-item
                 
                @if($hide['contract_item'][$row_1->contract_item->id])
                    d-hide
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
                <td data-controller="pageBreaker" class="searchable">{{$row_1->contract_item->item_code}}</td>
                <td class="searchable">{{$row_1->contract_item->description}}</td>
                
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

                    owned-by-contract-item-{{$row_1->contract_item->id}}
                        
                    @if($hide['component'][$component_id])
                        d-hide
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
                            <div class="pending-text text-center status">⦿</div>
                        @endif

                        @if($row_2->component->status == 'APRV')
                            <div class="approved-text text-center status">⦿</div>
                        @endif

                        @if($row_2->component->status == 'REJC')
                            <div class="rejected-text text-center status">⦿</div>
                        @endif

                        <span class="searchable">
                            {{$row_2->component->name}}
                        </span>
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

                    owned-by-contract-item-{{$row_1->contract_item->id}}

                    @if($hide['component'][$component_id])
                        d-hide
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
                    
                    owned-by-contract-item-{{$row_1->contract_item->id}}

                    @if($hide['component_item'][$component_item_id])
                        d-hide
                    @endif
                    

                    component-item-row
                
                "> 
                    <td class="searchable" colspan="3">{{$component_item->name}}</td><!-- Component Item Name -->
                    
                    <!-- Contract -->
                   
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

                <tr 
                        class="owned-by-contract-item-{{$row_1->contract_item->id}}"
                >
                    <td colspan="5">
                        <table class="ml-5 material-quantity-table">
                            <tr class="material-quantity-tr">
                                <th class="material-quantity-th text-start" width="50%">Material Item</th>
                                <th class="material-quantity-th">Eqv</th>
                            </tr>

                        @foreach($component_item->material_quantities as $mq)
                            <tr class="material-quantity-tr">

                                <td class="material-quantity-td">
                                    {{$material_item[$mq->material_item_id]->formattedName}}
                                </td>

                                <td class="text-center material-quantity-td">
                                    {{ number_format($mq->equivalent) }} {{$component_item->unit_text}}
                                </td>

                            </tr>

                        @endforeach

                           
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
    
    <div id="searchBar" class="d-none" data-controller="searchBar">
        <div class="d-inline-block">
            <button data-el="close">X</button>
        </div>
        <div class="d-inline-block">
            <input type="text"/>
        </div>
        <div class="d-inline-block">
            <div class="text-center">
                <span data-el="index_count">0</span>/<span data-el="total_count">0</span>
                <button data-el="prev">▲</button>
                <button data-el="next">▼</button>
            </div>
        </div>
    </div>

    <div id="actionContainer" class="no-select">
        
        <div id="actionBtn" class="me-10 no-select">
            
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-fullscreen-exit no-select" viewBox="0 0 16 16">
                <path d="M5.5 0a.5.5 0 0 1 .5.5v4A1.5 1.5 0 0 1 4.5 6h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5m5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 10 4.5v-4a.5.5 0 0 1 .5-.5M0 10.5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 6 11.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5m10 1a1.5 1.5 0 0 1 1.5-1.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0z"/>
            </svg>
        
        </div>

        <div id="collapseBtn" class="me-10 no-select">
            
            <svg id="icon_collapse" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-arrows-collapse no-select" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8m7-8a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 4.293V.5A.5.5 0 0 1 8 0m-.5 11.707-1.146 1.147a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 11.707V15.5a.5.5 0 0 1-1 0z"/>
            </svg>


            <svg id="icon_expand" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-arrows-expand d-none no-select" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8M7.646.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 1.707V5.5a.5.5 0 0 1-1 0V1.707L6.354 2.854a.5.5 0 1 1-.708-.708zM8 10a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 14.293V10.5A.5.5 0 0 1 8 10"/>
            </svg>

        </div>

        <div id="searchBtn" class="no-select">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-search no-select" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
            </svg>
        </div>
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';
        import contextMenu from '/ui_components/ContextMenu.js';
        
        const actionBtn     = $q('#actionBtn').first();
        const searchBtn     = $q('#searchBtn').first();
        const collapseBtn   = $q('#collapseBtn').first();
        const sheet         = $q('#sheet').first();
        const icon_collapse = $q('#icon_collapse').first();
        const icon_expand   = $q('#icon_expand').first();

        const t = new Template();
        
        let toggleCollapseFlag = false;
   
        actionBtn.onclick = () =>{
            window.parent.exitFullscreen()
        }

        searchBtn.onclick = ()=>{
            let searchBar = $q('#searchBar').first();

            searchBar.classList.remove('d-none');
            searchBar.querySelector('input').focus();
        }

        collapseBtn.onclick = ()=>{
            
            $q('.contract-item').apply(el=>{
                let id = el.getAttribute('data-id');
               
                $q('.owned-by-contract-item-'+id).apply(el=>{
                    
                    if(!toggleCollapseFlag){
                        el.classList.add('d-none');
                        
                    }else{
                        el.classList.remove('d-none');
                    }
                    
                });

            });

            toggleCollapseFlag = !toggleCollapseFlag;

            if(!toggleCollapseFlag){
                icon_expand.classList.add('d-none');
                icon_collapse.classList.remove('d-none');
            }else{
                icon_expand.classList.remove('d-none');
                icon_collapse.classList.add('d-none');
            }
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

        function escapeRegex(string) {
            return string.replace(/[/\-\\^$*+?.()|[\]{}]/g, '\\$&');
        }

        function openAll(){
            $q('.contract-item').apply(el=>{
                let id = el.getAttribute('data-id');
            
                $q('.owned-by-contract-item-'+id).apply(el=>{
                    
                    el.classList.remove('d-none');
                    
                });

            });

            icon_expand.classList.add('d-none');
            icon_collapse.classList.remove('d-none');
            
            toggleCollapseFlag = false;
        }

        function searchBar(item){
            let elem = item[0];

            const input         = elem.querySelector('input');
            const total_count   = elem.querySelector('[data-el="total_count"]');
            const index_count   = elem.querySelector('[data-el="index_count"]');
            const next          = elem.querySelector('[data-el="next"]');
            const prev          = elem.querySelector('[data-el="prev"]');
            const close         = elem.querySelector('[data-el="close"]');
            
            let result          = [];
            let index           = 0;
            let result_count    = 0;

            input.onkeyup = (e) => {
                
                if(e.keyCode == 13){
                    next.click();
                }
            }

            input.oninput = (e)=>{

                openAll();

                result  = [];
                index   = 0;

                Array.from( document.querySelectorAll('.border-highlight') ).map(el=>{
                    el.classList.remove('border-highlight');
                });

                Array.from( document.querySelectorAll('.background-highlight') ).map(el=>{
                    el.classList.remove('background-highlight');
                });

                
                if(input.value.trim() == ''){
                    
                    total_count.innerText = 0;
                    index_count.innerText = 0;
                    return false;
                }

                let data = Array.from( sheet.querySelectorAll('.searchable') );
              

                let search = escapeRegex( input.value.toLowerCase() );
            
                let regex   = new RegExp(search+'.*');
               
                data.map( d => {
               
                    let text    = d.innerText.toLowerCase();
               
                    if( text.match(regex) ){
                        d.classList.add('border-highlight');
                        result.push(d);
                    }
                });
                
                result_count = result.length;

                if(result_count == 0) return false;

                total_count.innerText = result_count;
                index_count.innerText = index+1;

                result[0].scrollIntoView({ block: "center" });
                result[0].classList.add('background-highlight');

            }

            next.onclick = ()=>{
                
                openAll();

                index = index + 1;

                if(index >= result_count){
                    
                    index = index - 1;
                    return false;
                }


                Array.from( document.querySelectorAll('.background-highlight') ).map(el=>{
                    el.classList.remove('background-highlight');
                });


                result[index].scrollIntoView({ block: "center" });
                result[index].classList.add('background-highlight');

                index_count.innerText = index+1;
            }

            prev.onclick = ()=>{

                openAll();

                index = index - 1;

                if(index < 0){
                    index = index + 1;
                    return false;
                }

                Array.from( document.querySelectorAll('.background-highlight') ).map(el=>{
                    el.classList.remove('background-highlight');
                });


                result[index].scrollIntoView({ block: "center" });
                result[index].classList.add('background-highlight');

                index_count.innerText = index+1;
            }


            close.onclick = ()=>{

                openAll();

                result                  = [];
                result_count            = 0;
                index                   = 0;
                input.value             = '';
                total_count.innerText   = '0';
                index_count.innerText   = '0';

                Array.from( document.querySelectorAll('.border-highlight') ).map(el=>{
                    el.classList.remove('border-highlight');
                });

                Array.from( document.querySelectorAll('.background-highlight') ).map(el=>{
                    el.classList.remove('background-highlight');
                });

                elem.classList.add('d-none');
            }
        }

        function componentMenu(items){

            items.map(item=>{

                const status_indicator = item.querySelector('.status');

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

                                        status_indicator.classList.remove('pending-text');
                                        status_indicator.classList.remove('rejected-text');
                                        status_indicator.classList.add('approved-text');
                                        

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


                                        status_indicator.classList.remove('pending-text');
                                        status_indicator.classList.remove('approved-text');
                                        status_indicator.classList.add('rejected-text');
                                        

                                    });
                                }
                            },
                            {
                                name:'Open',
                                onclick:(e)=>{
                                    
                                    window.parent.document.body.classList.remove('no-scroll');

                                    window.parent.util.navTo('/project/section/contract_item/component/'+component_id+'?b='+encodeURI(window.parent.location.href));
                                }
                            },
                            {
                                name:'Revert (Pending)',
                                onclick: async (e)=>{
                                    
                                    let answer = await window.parent.util.confirm('Are you sure you want to REVERT this component?');
                                    
                                    if(!answer){
                                        return false;
                                    }

                                    window.parent.util.blockUI();

                                    window.parent.util.$post('/api/review/component/revert_to_pending',{
                                        id: component_id
                                    }).then(reply=>{

                                        
                                        window.parent.util.unblockUI();

                                        if(reply.status <= 0 ){
                                            window.parent.util.showMsg(reply);
                                            return false;
                                        };


                                        status_indicator.classList.remove('rejected-text');
                                        status_indicator.classList.remove('approved-text');
                                        status_indicator.classList.add('pending-text');
                                        

                                    });
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

        function contractItemController(items){

            let thead = sheet.querySelector('thead');
            
            let head_height             = thead.offsetHeight;
            let contract_item_height    = 0;

            items.map(item=>{

                let id = item.getAttribute('data-id');

                item.onclick = (e)=>{
                    $q('.owned-by-contract-item-'+id).apply(el=>{
                        el.classList.remove('d-none');
                    });
                }

                item.style.top = head_height+'px'; 

                let td = item.querySelector('td');

                if( td.offsetHeight > contract_item_height ){
                    contract_item_height = td.offsetHeight;
                }

            });


            items.map(item=>{

                let td = item.querySelector('td');

                td.height = contract_item_height+'px';
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