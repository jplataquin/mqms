@extends('layouts.app')

@section('content')
<style>
    .bg-excluded-sum-component_item{
        background-color: #fffec8;
    }

    .bg-excluded-sum-component{
        background-color: #ADD8E6;
    }
</style>
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/components">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Component
                    </span>
                    <i class="ms-2 bi bi-display"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>
        <table class="record-table-horizontal">
            <tr>
                <th>
                    Project
                </th>
                <td>
                    {{$project->name}}
                </td>
            </tr>
            <tr>
                <th>
                    Section
                </th>
                <td>
                    {{$section->name}}
                </td>
            </tr>
            <tr>
                <th>
                    Created By
                </th>
                <td>
                    {{$component->CreatedByUser()->name}} {{$component->created_at}}
                </td>
            </tr>
        </table>
        <div class="folder-form-container">
            <div class="folder-form-tab">
                Review
            </div>
            <div class="folder-form-body">
                
                
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-container">
                            <div class="form-header">
                                Contract Item
                            </div>
                            <div class="form-body">
                                <div class="row mb-3">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Contract Item</label>
                                            <input type="text" class="form-control" disabled="true" value="{{$contract_item->item_code}} {{$contract_item->description}}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">    
                                    <div class="col-lg-6">
                                        <div class="form-container">
                                            <div class="form-header">Contract</div>
                                            <div class="form-body">
                                                <div class="row mb-3">
                                                    
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label>Quantity</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->contract_quantity,2) }}"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label>Unit</label>
                                                            <input type="text" class="form-control" disabled="true" value="@if(isset($unit_options[$contract_item->unit_id])) {{$unit_options[$contract_item->unit_id]->text}} @endif"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label>Price</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->contract_unit_price,2) }}"/>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label>Amount</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->contract_unit_price * $contract_item->contract_quantity,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-container">
                                            <div class="form-header">POW/DUPA</div>
                                            <div class="form-body">
                                                <div class="row mb-3">
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label>Quantity</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->ref_1_quantity,2) }}"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label>Unit</label>
                                                            <input type="text" class="form-control" disabled="true" value="@if(isset($unit_options[$contract_item->ref_1_unit_id])) {{$unit_options[$contract_item->ref_1_unit_id]->text}} @endif"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label>Price</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->ref_1_unit_price,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label>Amount</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->ref_unit_price * $contract_item->ref_quantity,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item_arr[$contract_item->id]->total_quantity,2) }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" class="form-control" disabled="true" value="@if(isset($unit_options[$contract_item->ref_1_unit_id])) {{$unit_options[$contract_item->ref_1_unit_id]->text}} @endif"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item_arr[$contract_item->id]->total_amount,2) }}"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-container">
                            <div class="form-header">Component</div>
                            <div class="form-body">
                                <div class="row mb-3">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Component</label>
                                            <input type="text" class="form-control" disabled="true" value="{{$component->name}}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <input type="text" class="form-control" disabled="true" value="{{$component->status}}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Use Count</label>
                                            <input type="text" class="form-control" disabled="true" value="{{$component->use_count}}"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format( $component->quantity,2)  }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" class="form-control" disabled="true" value="@if(isset($unit_options[$contract_item->unit_id])) {{$unit_options[$contract_item->unit_id]->text}} @endif"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" class="form-control" disabled="true" value=""/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-container">
                            <div class="form-header">
                                Items
                            </div>
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="text" disabled="true" class="form-control @if($component_arr[$component->id]->total_quantity > $component->quantity) text-danger @endif" value="{{ number_format($component_arr[$component->id]->total_quantity,2) }}"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" disabled="true" class="form-control" value="@if(isset($unit_options[$component->unit_id])) {{$unit_options[$component->unit_id]->text}} @endif"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" disabled="true" class="form-control" value="{{ number_format($component_arr[$component->id]->total_amount,2) }}"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                    @if($component->status == 'PEND')
                        <button class="btn btn-danger" id="rejectBtn">Reject</button>
                    @endif
                    </div>
                    <div class="col-lg-6 text-end">
                    @if($component->status == 'PEND')
                        <button class="btn btn-primary" id="approveBtn">Approve</button>
                    @endif
                        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    </div>
                </div>

            </div>
        </div>
        
        
        @php 
            $i = 1;
            $grand_total = 0;

            function formatFactor($factor){
                
                $factor_arr = explode('.',$factor);
                
                if($factor_arr[1] == '000000'){
                    return $factor_arr[0].'.00';
                }
                
                return $factor_arr[0].'.'.rtrim($factor_arr[1],'0');
            }
        @endphp
        @foreach($component_items as $component_item)
            <div class="form-container mb-3">

                @php 
                    $component_item_exlude_sum = '';

                    //If sum fag is explicity false
                    //If component Item does not have the same unit as the component and component item function type is not "equivalent"
                    
                    if(!$component_item->sum_flag || ($component_item->unit_id != $component->unit_id && $component_item->function_type_id != 4) ){
                        $component_item_exlude_sum = 'bg-excluded-sum-component_item';
                    }
                @endphp
                <div class="form-header text-start ps-3">
                    {{$i}}.) {{$component_item->name}}
                </div>
                <div class="form-body">

                    <div class="row mb-3 pb-3 {{$component_item_exlude_sum}}">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Factor</label>
                                @php

                                    $factor = '';

                                    if($component_item->function_type_id == 1){
                                        
                                        $factor = formatFactor(
                                            number_format(
                                                round( ($component_item->function_variable  / $component->use_count), 6 )
                                            ,6)
                                        );
                                        
                                        $factor .= ' '.$unit_options[$component_item->unit_id]->text;

                                        $factor .= ' / ';

                                        $factor .= $unit_options[$component->unit_id]->text;   
                                        
                                        $factor .= ' > ';

                                    }else if($component_item->function_type_id == 2){
                                        
                                        $factor = formatFactor(
                                                number_format(
                                                    round( (1 / $component_item->function_variable) / $component->use_count,6)
                                                ,6)
                                            ); 
                                        
                                        $factor .= ' '.$unit_options[$component_item->unit_id]->text;
                                        
                                        $factor .= ' / ';
                                        
                                        $factor .= $unit_options[$component->unit_id]->text;    
                                        

                                        $factor .=  ' >';
                                    }else if($component_item->function_type_id == 4){
                                    
                                        $factor = '< '; 

                                        
                                        $factor .= number_format(
                                            ($component_item->function_variable * $component->use_count),
                                            2
                                        );
                                        

                                        $factor .= ' '.$unit_options[$component->unit_id]->text;
                                        
                                        $factor .= ' / ';

                                        $factor .= $unit_options[$component_item->unit_id]->text;
                                    }else{
                                        $factor = $component_item->function_variable;
                                    }


                                    $grand_total = $grand_total + ($component_item->budget_price * $component_item->quantity);
                                
                                @endphp


                                <input type="text" disabled="true" value="{{$factor}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="text" disabled="true" value="{{$component_item->quantity}} {{$unit_options[$component_item->unit_id]->text}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Cost</label>
                                <input type="text" disabled="true" value="Php {{ number_format($component_item->budget_price,2) }}" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="text" disabled="true" value="Php {{ number_format($component_item->budget_price * $component_item->quantity,2) }}" class="form-control"/>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-auto">
                        <table border="1" class="table w-100">
                                

                                <tr>
                                    <th style="width:40%" class="text-center bg-divider">Material</th>
                                    <th style="width:20%" class="text-center bg-divider">Quantity</th>
                                    <th style="width:20%" class="text-center bg-divider">Equivalent</th>
                                    <th style="width:20%" class="text-center bg-divider">Total</th>
                                </tr>
                                
                                @php 
                                    $grand_total = 0;
                                @endphp

                                @foreach($component_item->materialQuantities as $mq)
                                <tr>
                                    <td>
                                        {{$materialItems[$mq->material_item_id]->name }} 
                                        {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                                        {{$materialItems[$mq->material_item_id]->brand }} 
                                    </td>
                                    <td class="text-center">
                                        {{$mq->quantity}}
                                    </td>
                                    
                                    <td class="text-center">
                                        {{$mq->equivalent}} {{ $unit_options[ $component_item->unit_id ]->text }}
                                    </td>
                                    <td class="text-center">
                                        {{$mq->equivalent * $mq->quantity}} {{ $unit_options[ $component_item->unit_id ]->text }}
                                    </td>
                                </tr>

                                    @php
                                        $grand_total = $grand_total + ($mq->equivalent * $mq->quantity);
                                    @endphp
                                @endforeach
                                <tr>
                                    <th colspan="3" class="text-end">
                                        Grand Total
                                    </th>
                                    <td class="@if($grand_total > $component_item->quantity) text-danger @endif text-center">
                                        {{$grand_total}} {{ $unit_options[ $component_item->unit_id ]->text }}
                                    </td>
                                </tr>

                        </table>
                    </div>
                </div>
            </div>
        @php $i++ @endphp
        @endforeach
    
        <table class="table bordered">
            <tr>
                <th class="text-center" style="background-color:#add8e6">
                    <h5>Grand Total</h5>
                </th>
                <th class="text-center">
                    <h5>Php {{number_format($grand_total,2)}}</h5>
                </th>
            </tr>
        </table>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    let approveBtn      = $q('#approveBtn').first();
    let rejectBtn       = $q('#rejectBtn').first();
    let cancelBtn       = $q('#cancelBtn').first();

    @if($component->status == 'PEND')

    approveBtn.onclick = async (e)=>{
        e.preventDefault();

        let answer = await window.util.confirm('Are you sure you want to APPROVE this component?');
        
        if(!answer){
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/review/component/approve',{
            id: '{{$component->id}}'
        }).then(reply=>{

            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };


            window.util.navTo('/review/components/');

        });
    }
    
    rejectBtn.onclick = (e)=>{
        e.preventDefault();
        
        if(!confirm('Are you sure you want to Reject this?')){
            return false;
        }

        window.util.blockUI();

        window.util.$post('/api/review/component/reject',{
            id: '{{$component->id}}'
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };


            window.util.navTo('/review/components/');

        });
    }

    @endif

    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/review/components/');
    }
</script>
</div>
@endsection