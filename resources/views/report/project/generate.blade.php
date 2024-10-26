@extends('layouts.app')

@section('content')
<style>

    .contract_item{
        background-color: #c5d8f0 !important;
        position:sticky !important;
        top:40px;
    }

    .component{
        background-color: #c2d0d1 !important;
        position:sticky !important;
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
            <div class="mb-3">
                <h3>Contract Amount</h3>
                <h5 id="contract_grand_total"></h5>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                </div>
            </div>

            <div>
                <h3></h3>
                <h5 id="amount_grand_total"></h5>
                <div class="progress">
                    <div class="progress-bar bg-warning" id="amount_grand_total_percent" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"> - </div>
                </div>
            </div>
        </div>



        <table class="table">
            @foreach($report as $contract_item_id => $contract_item)

                @php 

                    $contract_item_amount = $contract_item_arr[$contract_item_id]->contract_quantity * $contract_item_arr[$contract_item_id]->contract_unit_price;
                @endphp

                <tr style="height:100px">
                    <th class="contract_item">
                        {{ $contract_item_arr[$contract_item_id]->item_code }} {{$contract_item_arr[$contract_item_id]->description}}
                        <br>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning contract_item_amount_percent" data-id="{{$contract_item_id}}" data-amount="{{$contract_item_amount}}" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"> - </div>
                        </div>
                    </th>

                    
                    <td class="contract_item text-end">
                        <br>
                        <div>
                            <p class="fw-bold text-end mb-0 ">
                                (CN) P {{ number_format( $contract_item_amount, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="contract_item_total_{{$contract_item_id}} text-end">P 0.00</p>
                        </div>
                    </td>
                </tr>
                
                @foreach($contract_item as $component_id => $component)
                    
                    <tr>
                        <td style="padding-left:1em" class="component">
                            
                            {{ $component_arr[ $component_id ]->name }}
                            
                            <a class="link-offset-2 link-underline link-underline-opacity-0" href="/project/section/contract_item/component/{{$component_id}}?b={{ urlencode($url) }}" hx-boost="true" hx-select="#content" hx-target="#main">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>

                        <td class="text-end component">
                            
                            <div class="component_mb" data-id="{{$component_id}}" data-value="0">(MB) P 0.00</div>

                            <div class="component_total_amount component_{{$contract_item_id}}" data-id="{{$component_id}}" data-value="0" > - </div> 
                        </td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            @php 
                                $component_item_mb = $component_item_arr[$component_item_id]->quantity * $component_item_arr[$component_item_id]->budget_price;
                            @endphp
                            <th style="padding-left:2em" class="component_item component_item_{{$component_id}}" data-value="{{$component_item_mb}}">{{$component_item_arr[$component_item_id]->name}}</th>
                            <th>(MB) P {{ number_format($component_item_mb,2) }}</th>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)
                            <tr>
                                @php 
                                    $material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                                @endphp
                                <td style="padding-left:3em" class="material_item">{{ $material_item->formatted_name() }}</td>
                                <td class="po_amount_{{$component_id}}" data-value="{{$result['po_amount']}}">
                                    (EX) P {{ number_format($result['po_amount'],2) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    Budget Qty
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                                    </div>
                                </td>
                                <td style="padding-top:1.8em">
                                    {{ number_format($result['budget_quantity'],2) }} Qty
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
                                <td style="padding-top:1.8em" class="@if($result['request_quantity'] > $result['budget_quantity']) text-danger overbudget @endif">
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
                                <td style="padding-top:1.8em" class="@if($result['po_quantity'] > $result['budget_quantity'] || $result['po_quantity'] > $result['request_quantity']) text-danger overbudget @endif">
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

        const callout_danger                = $q('#callout-danger').first();
        const callout_danger_p              = $q('#callout-danger-p').first();
        const amount_grand_total_percent    = $q('#amount_grand_total_percent').first();
        const amount_grand_total            = $q('#amount_grand_total').first();
        const contract_grand_total_el       = $q('#contract_grand_total').first();


        let overbudget_count    = $q('.overbudget').items().length;
        
        let contract_grand_total        = 0;
        let contract_item_grand_total   = 0;
        let grand_amount_percentage     = 0;
        
        if(overbudget_count){
            callout_danger.classList.remove('d-none');
            callout_danger_p.innerText = overbudget_count+' record(s) has been found to be overbudget';
        }

        //Material Budget
        $q('.component_mb').apply(elem=>{
            let comp_id = elem.getAttribute('data-id');
            let total   = 0;

            $q('.component_item_'+comp_id).apply(item=>{
                
                let val = parseFloat(item.getAttribute('data-value'));

                if(isNaN(val)){
                    val = 0;
                }

                total = total + val;
            });

            elem.innerText = '(MB) P '+window.util.numberFormat(total);
            elem.setAttribute('data-value',total);
        });

        //Expense
        $q('.component_total_amount').apply(elem =>{

            let comp_id = elem.getAttribute('data-id');

            let total = 0;

            $q('.po_amount_'+comp_id).apply(item=>{

                let val = parseFloat(item.getAttribute('data-value'));

                if(isNaN(val)){
                    val = 0;
                }

                total = total + val;
            });

            elem.innerText = '(EX) P '+window.util.numberFormat(total);
            elem.setAttribute('data-value',total);
        });


        $q('.contract_item_amount_percent').apply(elem=>{

            let contract_item_id        = elem.getAttribute('data-id');
            let contract_item_amount    = elem.getAttribute('data-amount'); 
            let total                   = 0;
            
            contract_item_amount = parseFloat(contract_item_amount);

            if(isNaN(contract_item_amount)){
                contract_item_amount = 0;
            }

            contract_grand_total = contract_grand_total + contract_item_amount;

            $q('.component_'+contract_item_id).apply(item=>{
                let val = parseFloat(item.getAttribute('data-value'));

                if(isNaN(val)){
                    val = 0;
                }

                total = total + val;
            });

            contract_item_grand_total = contract_item_grand_total + total;


            let percentage = (total / contract_item_amount) * 100;

            percentage = Math.round(percentage);

            elem.style.width    = percentage+'%';
            elem.innerText      = percentage+'%';
            elem.setAttribute('aria-valuenow',percentage);

            $q('.contract_item_total_'+contract_item_id).first().innerText = 'P '+window.util.numberFormat(total);
        });


        if(contract_grand_total){

            grand_amount_percentage = (contract_item_grand_total / contract_grand_total) * 100;
            grand_amount_percentage = Math.round(grand_amount_percentage);
        }

        amount_grand_total_percent.style.width = grand_amount_percentage+'%';
        amount_grand_total_percent.setAttribute('aria-valuenow',grand_amount_percentage);
        amount_grand_total_percent.innerText = grand_amount_percentage+'%';

        amount_grand_total.innerText = 'P '+window.util.numberFormat(contract_item_grand_total);
        contract_grand_total_el.innerText = 'P '+window.util.numberFormat(contract_grand_total);

    </script>
</div>
@endsection