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


        <div class="mb-5">
            <div>
                <h3>Material Budget</h3>
                <h5 id="material_budget_grand_total"></h5>
            </div>
        </div>

        <div class="mb-5">
            <div>
                <h3>Total Expense</h3>
                <h5 id="material_expense_grand_total"></h5>
                <div class="progress">
                    <div class="progress-bar bg-warning" id="material_expense_grand_total_percent" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"> 100% </div>
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
                            <div class="progress-bar bg-warning contract_item_percent" data-id="{{$contract_item_id}}" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"> - </div>
                        </div>
                    </td>
                    <td class="contract_item text-end">
                        <p class="fw-bold text-end mb-0 contract_item_material_budget_total" data-id="{{$contract_item_id}}" data-value="0">
                            P 0.00
                        </p>
                        <p class="fw-bold contract_item_material_expense_total text-end" data-id="{{$contract_item_id}}" data-value="0">
                            P 0.00
                        </p>      
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

                            <div class="fw-bold component_material_expense_total" data-contract_item_id="{{$contract_item_id}}" data-id="{{$component_id}}" data-value="0" > - </div> 
                        </td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            @php 
                                $component_item_material_budget = $component_item_arr[$component_item_id]->quantity * $component_item_arr[$component_item_id]->budget_price;
                            @endphp
                            <th style="padding-left:2em" class="component_item" data-value="{{$component_item_material_budget}}">{{$component_item_arr[$component_item_id]->name}}</th>
                            <td>
                                <div>
                                    <p class="fw-bold component_item_material_budget_total" data-component_id="{{$component_id}}" data-value="{{$component_item_material_budget}}">
                                        (MB) P {{ number_format($component_item_material_budget,2) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="fw-bold component_item_material_expense_total" data-component_id="{{$component_id}}" data-id="{{$component_item_id}}" data-value="0"> - <p>
                                </div>
                            </td>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)

                            
                            @php 
                                $material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                            @endphp

                            <tr>

                                <td style="padding-left:3em" class="material_item">{{ $material_item->formatted_name() }}</td>
                                <td class="component_item_material_expense" data-component_item_id="{{$component_item_id}}" data-value="{{$result['po_amount']}}">
                                    {{ number_format($result['budget_quantity'],2) }} Qty
                                    <br>
                                    (ME) P {{ number_format($result['po_amount'],2) }}
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
                                    
                                    Request Qty

                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{$request_percentage}}%;" aria-valuenow="{{$request_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$request_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td style="padding-top:1.8em">
                                    {{ number_format($result['request_quantity'],2) }} Qty
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    @php 
                                        if($result['po_quantity']){
                                            $po_percentage = ($result['po_quantity'] / $result['budget_quantity']) * 100;
                                            $po_percentage = round($request_percentage,2);
                                        }else{
                                            $po_percentage = 0;
                                        }
                                    @endphp
                                    
                                    PO Qty

                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{$po_percentage}}%;" aria-valuenow="{{$po_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$po_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td style="padding-top:1.8em">
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

        function contract_item_percentage(){

            $q('.contract_item_percent').apply(elem=>{

                let contract_item_id    = elem.getAttribute('data-id');

                console.log(contract_item_id, $q('.contract_item_material_budget_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value'));

                let total_budget        = parseFloat( $q('.contract_item_material_budget_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );
                let total_expense       = parseFloat( $q('.contract_item_material_expense_total[data-id="'+contract_item_id+'"]').first().getAttribute('data-value') );

                console.log(total_budget, total_expense);
                //Skip if total budget is zero
                if(total_budget <= 0) return false;

                let percentage = (total_expense / total_budget) * 100;

                percentage = Math.round(percentage);

                elem.style.width    = percentage+'%';
                elem.innerText      = percentage+'%';
            });
        }

        function grand_total_material_expense_percentage(){

            let target                          = $q('#material_expense_grand_total_percent').first();
            let material_expense_grand_total    = parseFloat( $q('#material_expense_grand_total').first().getAttribute('data-value') );
            let material_budget_grand_total     = parseFloat( $q('#material_budget_grand_total').first().getAttribute('data-value') );

            if(material_budget_grand_total <= 0 ) return false;

            let percentage  = (material_expense_grand_total / material_budget_grand_total) * 100;
            percentage      = Math.round(percentage);

            target.style.width  = percentage+'%';
            target.innerText    = percentage+'%';
        }

        /** Note the function call must run in order **/

        total_component_item_material_expense();

        total_component_material_expense();
        
        total_component_material_budget();
        
        total_contract_item_material_expense();
        
        total_contract_item_material_budget();

        grand_total_material_expense();

        grand_total_material_budget();

        contract_item_percentage();

        grand_total_material_expense_percentage();
    </script>
</div>
@endsection