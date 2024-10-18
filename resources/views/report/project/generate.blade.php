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
        top:80px;
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


        <table class="table">
            @foreach($report as $contract_item_id => $contract_item)
            

                <tr>
                    <th colspan="2" class="contract_item">{{ $contract_item_arr[$contract_item_id]->item_code }} {{$contract_item_arr[$contract_item_id]->description}}</th>
                </tr>
                
                @foreach($contract_item as $component_id => $component)
                    
                    <tr>
                        <td colspan="2" style="padding-left:1em" class="component">
                            
                            {{ $component_arr[ $component_id ]->name }}
                            
                            <a class="link-offset-2 link-underline link-underline-opacity-0" href="/project/section/contract_item/component/{{$component_id}}?b={{$url}}" hx-boost="true" hx-select="#content" hx-target="#main">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>
                    </tr>

                    @foreach($component as $component_item_id => $component_item)
                        <tr>
                            <th colspan="2" style="padding-left:2em" class="component_item">{{$component_item_arr[$component_item_id]->name}}</th>
                        </tr>

                        @foreach($component_item as $material_quantity_id => $result)
                            <tr>
                                @php 
                                    $material_item = $material_item_arr[ $material_quantity_arr[$material_quantity_id]->material_item_id ];
                                @endphp
                                <td colspan="2" style="padding-left:3em" class="material_item">{{ $material_item->formatted_name() }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left:5em">
                                    Budget
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                                    </div>
                                </td>
                                <td>{{ number_format($result['budget_quantity'],2) }} Qty</td>
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
                                <td class="@if($result['request_quantity'] > $result['budget_quantity']) text-danger overbudget @endif">{{ number_format($result['request_quantity'],2) }} Qty</td>
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
                                    
                                    PO

                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{$po_percentage}}%;" aria-valuenow="{{$po_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$po_percentage}}%</div>
                                    </div>
                                    
                                </td>
                                <td class="@if($result['po_quantity'] > $result['budget_quantity'] || $result['po_quantity'] > $result['request_quantity']) text-danger overbudget @endif">{{ number_format($result['po_quantity'],2) }} Qty</td>
                            </tr>
                        @endforeach

                    @endforeach

                @endforeach

            @endforeach
        </table>
    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const callout_danger    = $q('#callout-danger').first();
        const callout_danger_p  = $q('#callout-danger-p').first();

        let overbudget_count = $q('.overbudget').items().length;

        if(overbudget_count){
            callout_danger.classList.remove('d-none');
            callout_danger_p.innerText = overbudget_count+' record(s) has been found to be overbudget';
        }
    </script>
</div>
@endsection