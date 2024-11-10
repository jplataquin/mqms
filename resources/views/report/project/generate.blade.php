@extends('layouts.app')

@section('content')
<style>

    .contract_item{
        background-color: #c5d8f0 !important;
        /** position:sticky !important; **/
        top:40px;
    }

    .component{
        background-color: #c2d0d1 !important;
        /** position:sticky !important; **/
        top:140px;
    }

    .bar{
        padding:2px;
    }

    .bar-request{
        background-color:#343aeb !important;
    }

    .horizontal-bar-stacked{
        width: 100%;
        min-width: 100%;
        min-height:1em;
        max-height:1em;
        border-collapse: collapse;
    }

    .horizontal-bar-stacked td{
        text-align:center;
        color: #ffffff;
    }

    .horizontal-bar-stacked-expense{
        background-color: rgb(13, 110, 253);
        width:0%;
    }

    .horizontal-bar-stacked-overhead{
        background-color: rgb(255, 193, 7);
        width:0%;
    }

    .horizontal-bar-stacked-default{
        background-color: rgb(233, 236, 239);
        width:100%;
    }
</style>
<div id="content">
    <div class="container">

        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/roles">
                        <span>
                        Report
                        </span>                    
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Project
                        </span>                    
                        <i class="ms-2 bi bi-bar-chart"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>


        <div id="callout-danger" class="callout callout-danger d-none">
            <h4>Alert</h4> 
            <p id="callout-danger-p"></p>
        </div>



        <div class="row">
            <div class="col-lg-12 col-md-12 text-center p-3">
                <div class="border rounded border-primary">
                    <h3>Total Budget</h3>
                    <h5 id="material_budget_grand_total" data-value="0">-</h5>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 text-center p-3">
                <div class="border rounded border-primary">
                    <h3>Total Expense</h3>
                    <h5 id="material_expense_grand_total" class="check" data-check-target="#material_budget_grand_total" data-value="0">-</h5>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 text-center p-3">
                <div class="border rounded border-primary">
                    <h3>Total Overhead</h3>
                    <h5 id="material_overhead_grand_total" data-value="0">-</h5>
                </div>
            </div>
        </div>
        <div class="mb-5 row">
            <div class="col-lg-12">
            
                <div class="progress">
                    <table cellpadding="0" cellspacing="0" class="horizontal-bar-stacked" id="material_expense_grand_total_percent">
                        <tr>
                            <td class="horizontal-bar-stacked-expense"></td>
                            <td class="horizontal-bar-stacked-overhead"></td>
                            <td class="horizontal-bar-stacked-default"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <table class="table">
            @foreach($report as $contract_item_id => $contract_item)

                <tr>
                    <th colspan="2" class="contract_item">
                        {{ $contract_item_arr[$contract_item_id]->item_code }} {{$contract_item_arr[$contract_item_id]->description}}
                    </th>
                </tr>


                <tr>
                    <td class="contract_item">
                        <p>Expense / Budget</p>
                        <div class="progress">
                            <table cellpadding="0" cellspacing="0" class="horizontal-bar-stacked contract_item_percent" data-id="{{$contract_item_id}}">
                                <tr>
                                    <td class="horizontal-bar-stacked-expense"></td>
                                    <td class="horizontal-bar-stacked-overhead"></td>
                                    <td class="horizontal-bar-stacked-default"></td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td class="contract_item text-end">
                        <div class="fw-bold text-end mb-0 contract_item_material_budget_total" data-id="{{$contract_item_id}}" data-value="0">
                            (MB) P 0.00
                        </div>
                        <div class="fw-bold contract_item_material_expense_total text-end check" data-check-target=".contract_item_material_budget_total[data-id='{{$contract_item_id}}']" data-id="{{$contract_item_id}}" data-value="0">
                            (ME) P 0.00
                        </div>
                        <div class="fw-bold contract_item_material_overhead_total text-end" data-id="{{$contract_item_id}}" data-value="0">
                            (MO) P 0.00
                        </div>       
                    </td>
                </tr>
                    
                
                @foreach($contract_item as $component_id => $component)
                    
                    <tr>
                        <td style="padding-left:1em" class="component">
                            
                            {{ $component_arr[ $component_id ]->name }}
                            
                            <a class="ms-3 link-offset-2 link-underline link-underline-opacity-0" href="/project/section/contract_item/component/{{$component_id}}?b={{ urlencode($url) }}" hx-boost="true" hx-select="#content" hx-target="#main">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>

                        <td class="text-end component">
                            
                            <div class="fw-bold component_material_budget_total" data-contract_item_id="{{$contract_item_id}}" data-id="{{$component_id}}" data-value="0"> - </div>

                            <div class="fw-bold component_material_expense_total check" data-check-target=".component_material_budget_total[data-id='{{$component_id}}']" data-contract_item_id="{{$contract_item_id}}" data-id="{{$component_id}}" data-value="0" > - </div> 

                            <div class="fw-bold component_material_overhead_total" data-value="{{$total_po_overhead_arr[$component_id]}}" data-id="{{$component_id}}" data-contract_item_id="{{$contract_item_id}}">
                                (MO) P {{ number_format($total_po_overhead_arr[$component_id],2) }}
                            </div>
                        </td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            @php 
                                $component_item_material_budget = $component_item_arr[$component_item_id]->quantity * $component_item_arr[$component_item_id]->budget_price;
                            @endphp
                            <th style="padding-left:2em" class="component_item" data-value="{{$component_item_material_budget}}">{{$component_item_arr[$component_item_id]->name}}</th>
                            <td class="text-end">
                                
                                <div class="fw-bold component_item_material_budget_total" data-id="{{$component_item_id}}" data-component_id="{{$component_id}}" data-value="{{$component_item_material_budget}}">
                                    (MB) P {{ number_format($component_item_material_budget,2) }}
                                </div>
                                <div 
                                    class="fw-bold component_item_material_expense_total check" 
                                    data-check-target=".component_item_material_budget_total[data-id='{{$component_item_id}}']" 
                                    data-component_id="{{$component_id}}" 
                                    data-id="{{$component_item_id}}" 
                                    data-value="0"
                                > 
                                    - 
                                    
                                </div>
                            </td>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)

                            
                            @php 
                                $material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                            @endphp

                            <tr>

                                <td style="padding-left:3em" class="material_item">{{ $material_item->formatted_name() }}</td>
                                <td class="component_item_material_expense text-end" data-component_item_id="{{$component_item_id}}" data-value="{{$result['po_amount']}}">
                                    
                                    (ME) P {{ number_format($result['po_amount'],2) }}
                                    <br>
                                    <span data-value="{{$result['budget_quantity']}}" class="budget_quantity" data-id="{{$material_quantity_id}}">{{ number_format($result['budget_quantity'],2) }} Qty</span>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    @php 
                                        if($result['request_quantity']){
                                            $request_percentage = ($result['request_quantity'] / $result['budget_quantity']) * 100;
                                            $request_percentage = round($request_percentage,2);
                                        }else{
                                            $request_percentage = 0;
                                        }
                                    @endphp
                                    
                                    Request

                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{$request_percentage}}%;" aria-valuenow="{{$request_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$request_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td style="padding-top:1.8em" class="text-end">
                                    <span data-value="{{$result['request_quantity']}}" class="request_quantity check" data-check-level="1" data-check-target=".budget_quantity[data-id='{{$material_quantity_id}}']" data-id="{{$material_quantity_id}}">{{ number_format($result['request_quantity'],2) }} Qty</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    @php 
                                        if($result['po_quantity']){
                                            $po_percentage = ($result['po_quantity'] / $result['budget_quantity']) * 100;
                                            $po_percentage = round($po_percentage,2);
                                        }else{
                                            $po_percentage = 0;
                                        }
                                    @endphp
                                    
                                    PO

                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{$po_percentage}}%;" aria-valuenow="{{$po_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$po_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td style="padding-top:1.8em" class="check text-end" data-value="{{$result['po_quantity']}}" data-check-level="1" data-check-target=".request_quantity[data-id='{{$material_quantity_id}}']">
                                    {{ number_format($result['po_quantity'],2) }} Qty
                                </td>
                            </tr>
                        @endforeach

                    @endforeach

                @endforeach

            @endforeach
        </table>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';
        
        let overbudget_count = 0;

        function total_component_item_material_expense(){

            $q('.component_item_material_expense_total').apply(elem=>{
                let component_item_id = elem.getAttribute('data-id');
                let total = 0;
               
                $q('.component_item_material_expense[data-component_item_id="'+component_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(ME) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_component_material_expense(){

            $q('.component_material_expense_total').apply(elem=>{
                let component_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_item_material_expense_total[data-component_id="'+component_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(ME) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_component_material_budget(){
            $q('.component_material_budget_total').apply(elem=>{
                let component_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_item_material_budget_total[data-component_id="'+component_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(MB) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_contract_item_material_expense(){
            $q('.contract_item_material_expense_total').apply(elem=>{
                let contract_item_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_material_expense_total[data-contract_item_id="'+contract_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(ME) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_contract_item_material_budget(){
            $q('.contract_item_material_budget_total').apply(elem=>{
                let contract_item_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_material_budget_total[data-contract_item_id="'+contract_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(MB) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            });
        }

        function total_contract_item_material_overhead(){
            
            $q('.contract_item_material_overhead_total').apply(elem=>{
                let contract_item_id = elem.getAttribute('data-id');
                let total = 0;
            
                $q('.component_material_overhead_total[data-contract_item_id="'+contract_item_id+'"]').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = '(MO) P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',10000);
            });
        }

        function grand_total_material_expense(){

            let elem  = $q('#material_expense_grand_total').first();
            let total = 0;
            
                $q('.contract_item_material_expense_total').apply(el=>{
                    
                    let value = parseFloat( el.getAttribute('data-value') );
                    
                    if(isNaN(value)){
                        value = 0;
                    }

                    total = total + value;
                });

                elem.innerText = 'P '+window.util.numberFormat(total);
                elem.setAttribute('data-value',total);
            
        }

        function grand_total_material_budget(){

            let elem  = $q('#material_budget_grand_total').first();
            let total = 0;

            $q('.contract_item_material_budget_total').apply(el=>{
                
                let value = parseFloat( el.getAttribute('data-value') );
                
                if(isNaN(value)){
                    value = 0;
                }

                total = total + value;
            });

            elem.innerText = 'P '+window.util.numberFormat(total);
            elem.setAttribute('data-value',total);

        }

        function grand_total_material_overhead(){

            let elem  = $q('#material_overhead_grand_total').first();
            let total = 0;

            $q('.contract_item_material_overhead_total').apply(el=>{
                
                let value = parseFloat( el.getAttribute('data-value') );
                
                if(isNaN(value)){
                    value = 0;
                }

                total = total + value;
            });

            elem.innerText = 'P '+window.util.numberFormat(total);
            elem.setAttribute('data-value',total);

        }

        function contract_item_percentage(){

            $q('.contract_item_percent').apply(elem=>{

                let contract_item_id    = elem.getAttribute('data-id');


                let total_budget        = parseFloat( $q('.contract_item_material_budget_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                let total_expense       = parseFloat( $q('.contract_item_material_expense_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                let total_overhead      = parseFloat( $q('.contract_item_material_overhead_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                
                //Skip if total budget is zero
                if(total_budget <= 0) return false;

                let expense_percentage  = (total_expense / total_budget) * 100;
                expense_percentage      = Math.round(expense_percentage);

                let overhead_percentage = (total_overhead / total_budget) * 100;
                overhead_percentage     = Math.round(overhead_percentage);

                let default_percentage  = 100 - (expense_percentage + overhead_percentage);
                let total_percentage    = expense_percentage + overhead_percentage + default_percentage;

                let expense_td  = elem.querySelector('.horizontal-bar-stacked-expense');
                let overhead_td = elem.querySelector('.horizontal-bar-stacked-overhead');
                let default_td  = elem.querySelector('.horizontal-bar-stacked-default');

                if(total_percentage > 100){
                    expense_percentage          = 50;
                    overhead_percentage         = 50;
                    default_percentage          = 0;
                }

                if(expense_percentage){
                    expense_td.style.width      = expense_percentage+'%';
                    expense_td.style.minWidth   = expense_percentage+'%';
                    expense_td.innerText        = expense_percentage+'%';
                }

                if(overhead_percentage){
                    overhead_td.style.width      = overhead_percentage+'%';
                    overhead_td.style.minWidth   = overhead_percentage+'%';
                    overhead_td.innerText        = overhead_percentage+'%';
                }

                if(default_percentage){
                    default_td.style.width      = default_percentage+'%';
                    default_td.style.minWidth   = default_percentage+'%';
                    default_td.innerText        = default_percentage+'%';
                }

                if(default_percentage <= 0){
                    default_td.style.display = 'none';
                }

                if(overhead_percentage <= 0){
                    overhead_td.style.display = 'none';
                }

                if(expense_percentage <= 0){
                    expense_td.style.display = 'none';
                }
            });
        }

        function grand_total_material_percentage(){

            let elem                            = $q('#material_expense_grand_total_percent').first();
            let material_expense_grand_total    = parseFloat( $q('#material_expense_grand_total').first().getAttribute('data-value') );
            let material_budget_grand_total     = parseFloat( $q('#material_budget_grand_total').first().getAttribute('data-value') );
            let material_overhead_grand_total   = parseFloat( $q('#material_overhead_grand_total').first().getAttribute('data-value') );

            if(material_budget_grand_total <= 0 ) return false;

            let expense_percentage  = (material_expense_grand_total / material_budget_grand_total) * 100;
            expense_percentage      = Math.round(expense_percentage);

            let overhead_percentage  = (material_overhead_grand_total / material_budget_grand_total) * 100;
            overhead_percentage      = Math.round(overhead_percentage);

            let default_percentage  = 100 - (expense_percentage + overhead_percentage);
            let total_percentage    = expense_percentage + overhead_percentage + default_percentage;

            let expense_td  = elem.querySelector('.horizontal-bar-stacked-expense');
            let overhead_td = elem.querySelector('.horizontal-bar-stacked-overhead');
            let default_td  = elem.querySelector('.horizontal-bar-stacked-default');

            if(total_percentage > 100){
                expense_percentage          = 50;
                overhead_percentage         = 50;
                default_percentage          = 0;
            }

            if(expense_percentage){
                expense_td.style.width      = expense_percentage+'%';
                expense_td.style.minWidth   = expense_percentage+'%';
                expense_td.innerText        = expense_percentage+'%';
            }

            if(overhead_percentage){
                overhead_td.style.width      = overhead_percentage+'%';
                overhead_td.style.minWidth   = overhead_percentage+'%';
                overhead_td.innerText        = overhead_percentage+'%';
            }

            if(default_percentage){
                default_td.style.width      = default_percentage+'%';
                default_td.style.minWidth   = default_percentage+'%';
                default_td.innerText        = default_percentage+'%';
            }

            if(default_percentage <= 0){
                default_td.style.display = 'none';
            }

            if(overhead_percentage <= 0){
                overhead_td.style.display = 'none';
            }

            if(expense_percentage <= 0){
                expense_td.style.display = 'none';
            }
        }

        function check(){

            let level_6 = 0;
            let level_5 = 0;
            let level_4 = 0;
            let level_3 = 0;
            let level_2 = 0;
            let level_1 = 0;

            $q('.check').apply(elem=>{

                elem.classList.remove('text-danger');

                let target_query = elem.getAttribute('data-check-target');
                let value  = parseFloat( elem.getAttribute('data-value') );
                
                if(isNaN(value)){
                    value = 0;
                }

                if(!target_query) return false;

                let target = $q(target_query).first();

                if(!target) return false;

                let target_value = parseFloat( target.getAttribute('data-value') );

                if(isNaN(target_value)){
                    target_value = 0;
                }
                
                if(value > target_value){
                    elem.classList.add('text-danger');
                    overbudget_count++;
                }
            });


            let material_budget_grand_total_el      = $q('#material_budget_grand_total').first();
            let material_expene_grand_total_el      = $q('#material_expense_grand_total').first();
            let materiaL_overhead_grand_total_el    = $q('#material_overhead_grand_total').first();

            let material_budget_grand_total     = parseFloat( material_budget_grand_total_el.getAttribute('data-value') );
            let material_expense_grand_total    = parseFloat( material_expene_grand_total_el.getAttribute('data-value') );
            let material_overhead_grand_total   = parseFloat( materiaL_overhead_grand_total_el.getAttribute('data-value') );

            if(isNaN(material_expense_grand_total)){
                material_expense_grand_total = 0;
            }

            if(isNaN(material_budget_grand_total)){
                material_budget_grand_total = 0;
            }

            if(isNaN(material_budget_grand_total)){
                material_overhead_grand_total = 0;
            }

            let total_expense_and_overhead = material_expense_grand_total + material_overhead_grand_total;

            if(total_expense_and_overhead > material_budget_grand_total){
                material_expene_grand_total_el.classList.add('text-danger');
                materiaL_overhead_grand_total_el.classList.add('text-danger');
            }

        }

        /** Note the function call must run in order **/

        total_component_item_material_expense();

        total_component_material_expense();
        
        total_component_material_budget();
        
        total_contract_item_material_expense();
        
        total_contract_item_material_budget();

        total_contract_item_material_overhead();

        grand_total_material_expense();

        grand_total_material_budget();

        grand_total_material_overhead();

        contract_item_percentage();

        grand_total_material_percentage();

        check();
    </script>
</div>
@endsection