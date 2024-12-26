@extends('layouts.app')

@section('content')

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
       
        <div id="callout-danger" class="callout callout-danger d-none">
            <h4 id="callout-danger-p"></h4> 
        </div>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Review
            </div>
            <div class="folder-form-body">
                
                            
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Project</label>
                            <input type="text" disabled="true" class="form-control" value="{{$project->name}}"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Section</label>
                            <input type="text" disabled="true" class="form-control" value="{{$section->name}}"/>
                        </div>
                    </div>
                </div>


                <div class="row mb-3">
                    <div class="col-lg-12">
                    <div class="form-container">
                        <div class="form-header">Grand Total</div>
                        <div class="form-body">
                            <div class="row mb-3">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Contract</label>
                                        <input type="text" disabled="true" class="form-control" value="P {{ number_format($contract_grand_total_amount,2) }}"/>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>DUPA/POW</label>
                                        <input type="text" disabled="true" class="form-control" value="P {{ number_format($ref_1_grand_total_amount,2) }}"/>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Material Budget</label>
                                        <input type="text" disabled="true" class="form-control @if($budget_grand_total_amount > $contract_grand_total_amount) is-invalid non-conforming @endif" value="P {{ number_format($budget_grand_total_amount,2) }}"/>
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
                                Contract Item
                            </div>
                            <div class="form-body">
                                
                            
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Item Code & Description</label>

                                        <div class="input-group">
                                            <span class="input-group-text" id="basic-addon2" onclick="document.querySelector('#contract_item').focus();"><i class="bi bi-list"></i></span>
                                        
                                            <select id="contract_item" class="form-select">
                                                @foreach($contract_item_arr as $con_item)

                                                    <option value="{{$con_item->data->id}}" @if($con_item->data->id == $contract_item->id) selected @endif>{{$con_item->data->item_code}} {{$con_item->data->description}}</option>

                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <div class="row mb-3">    
                                    <div class="col-lg-4 mb-3">
                                        <div class="form-container">
                                            <div class="form-header">Contract</div>
                                            <div class="form-body">
                                                <div class="row mb-3">
                                                    
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>Quantity</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->contract_quantity,2) }} @if(isset($unit_options[$contract_item->unit_id])) {{$unit_options[$contract_item->unit_id]->text}} @endif"/>
                                                        </div>
                                                    </div>


                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>Unit Price</label>
                                                            <input type="text" class="form-control" disabled="true" value="P {{ number_format($contract_item->contract_unit_price,2) }}"/>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            @php
                                                                if($contract_grand_total_amount > 0){
                                                                    $contract_amount_percentage = ( ($contract_amount / $contract_grand_total_amount) * 100);
                                                                    $contract_amount_percentage = number_format($contract_amount_percentage,2);
                                                                }else{
                                                                    $contract_amount_percentage = 0.00;
                                                                }
                                                            @endphp
                                                            <label>Total Amount ({{$contract_amount_percentage}}%)</label>
                                                            <input type="text" class="form-control" disabled="true" value="P {{ number_format($contract_amount,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="form-container">
                                            <div class="form-header">POW/DUPA</div>
                                            <div class="form-body">
                                                <div class="row mb-3">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>Quantity</label>
                                                            <input type="text" class="form-control" disabled="true" value="{{ number_format($contract_item->ref_1_quantity,2) }} @if(isset($unit_options[$contract_item->ref_1_unit_id])) {{$unit_options[$contract_item->ref_1_unit_id]->text}} @endif"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>Unit Price</label>
                                                            <input type="text" class="form-control" disabled="true" value="P {{ number_format($contract_item->ref_1_unit_price,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            @php
                                                                $ref_1_amount = $contract_item->ref_1_unit_price * $contract_item->ref_1_quantity;
                                                           
                                                                if($ref_1_grand_total_amount > 0){
                                                                    $ref_1_amount_percentage = ( ($ref_1_amount / $ref_1_grand_total_amount) * 100);
                                                                    $ref_1_amount_percentage = number_format($ref_1_amount_percentage,2);
                                                                }else{
                                                                    $ref_1_amount_percentage = 0.00;
                                                                }
                                                            @endphp
                                                            <label>Total Amount ({{$ref_1_amount_percentage}}%)</label>
                                                            <input type="text" class="form-control" disabled="true" value="P {{ number_format($ref_1_amount,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="form-container">
                                            <div class="form-header">
                                                Material Budget
                                            </div>
                                            <div class="form-body">

                                                <div class="row mb-3">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label>Total Quantity</label>
                                                            <input type="text" class="form-control @if($contract_item_arr[$contract_item->id]->total_quantity > $contract_item->contract_quantity) is-invalid non-conforming @endif" disabled="true" value="{{ number_format($contract_item_arr[$contract_item->id]->total_quantity,2) }} @if(isset($unit_options[$contract_item->unit_id])) {{$unit_options[$contract_item->unit_id]->text}} @endif"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            
                                                            @php
                                                                if($budget_grand_total_amount > 0){
                                                                    $material_budget_percentage = ( ($contract_item_arr[$contract_item->id]->total_amount / $budget_grand_total_amount) * 100);
                                                                    $material_budget_percentage = number_format($material_budget_percentage,2);
                                                                }else{
                                                                    $material_budget_percentage = 0.00;
                                                                }
                                                            @endphp

                                                            <label>Total Amount ({{ $material_budget_percentage }}%)</label>
                                                            <input type="text" class="form-control @if($contract_item_arr[$contract_item->id]->total_amount > $contract_amount) is-invalid non-conforming @endif" disabled="true" value="P {{ number_format($contract_item_arr[$contract_item->id]->total_amount,2) }}"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            
                                            </div>
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
                            <div class="form-header @if(!$component->sum_flag || $component->unit_id != $contract_item->unit_id) bg-excluded-sum-component @endif">Component</div>
                            <div class="form-body">

                            <div class="row mb-3">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Component Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic-addon2" onclick="document.querySelector('#component').focus();"><i class="bi bi-list"></i></span>
                                            
                                                <select class="form-select" id="component">
                                                    @foreach($component_arr as $comp)

                                                        @if($comp->contract_item_id == $contract_item->id)
                                                            <option class="" value="{{$comp->data->id}}" @if($comp->data->id == $component->id) selected @endif>
                                                                {{$comp->data->name}}

                                                                @if($comp->data->id != $component->id)
                                                                    »
                                                                    {{ $comp->data->status }}
                                                                    »
                                                                    {{ number_format($comp->total_quantity,2) }} {{ $unit_options[$comp->data->unit_id]->text }}
                                                                    » 
                                                                    P {{ number_format($comp->total_amount,2) }}
                                                                    » 

                                                                    @if($contract_item_arr[$contract_item->id]->total_amount > 0)

                                                                        @php
                                                                            $comp_amt_percentage = ($comp->total_amount / $contract_item_arr[$contract_item->id]->total_amount) * 100;
                                                                            $comp_amt_percentage = number_format($comp_amt_percentage,2);
                                                                        @endphp
                                                                        ({{$comp_amt_percentage}}%)
                                                                    @else
                                                                        0.00%;
                                                                    @endif

                                                                    @if(!$comp->data->sum_flag || $comp->data->unit_id != $contract_item->unit_id)
                                                                    » Excluded
                                                                    @endif
                                                                @endif
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                        </div>
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
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Budget Quantity</label>
                                            <input type="text" class="form-control" disabled="true" value="{{ number_format( $component->quantity,2)  }} @if(isset($unit_options[$component->unit_id])) {{$unit_options[$component->unit_id]->text}} @endif"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Material Quantity</label>
                                            <input type="text" disabled="true" class="form-control @if($component_arr[$component->id]->total_quantity > $component->quantity) is-invalid non-conforming @endif" value="{{ number_format($component_arr[$component->id]->total_quantity,2) }} @if(isset($unit_options[$component->unit_id])) {{$unit_options[$component->unit_id]->text}} @endif"/>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            @php
                                                if($contract_item_arr[$contract_item->id]->total_amount > 0){

                                                    $component_amount_percentage = ($component_arr[$component->id]->total_amount / $contract_item_arr[$contract_item->id]->total_amount) * 100;
                                                    $component_amount_percentage = number_format($component_amount_percentage,2);

                                                }else{
                                                    $component_amount_percentage = 0.00;
                                                }
                                            @endphp
                                            <label>Total Amount ({{$component_amount_percentage}}%)</label>
                                            <input type="text" disabled="true" class="form-control" value="P {{ number_format($component_arr[$component->id]->total_amount,2) }}"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
        
        
        @php 
            $i = 1;
            
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
                                <label>
                                    Factor
                                    (
                                    @if($component_item->function_type_id == 1)
                                        As Factor
                                    @elseif($component_item->function_type_id == 2)
                                        As Divisor
                                    @elseif($component_item->function_type_id == 3)
                                        As Direct
                                    @elseif($component_item->function_type_id == 4)
                                        As Equivalent
                                    @endif
                                    )
                                </label>
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


                                
                                @endphp


                                <input type="text" disabled="true" value="{{$factor}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="text" disabled="true" value="{{ number_format($component_item->quantity,2) }} {{$unit_options[$component_item->unit_id]->text}}" class="form-control"/>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Unit Cost</label>
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
                                        {{number_format($mq->quantity,2) }}
                                    </td>
                                    
                                    <td class="text-center">
                                        {{ number_format($mq->equivalent,2) }} {{ $unit_options[ $component_item->unit_id ]->text }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($mq->equivalent * $mq->quantity,2) }} {{ $unit_options[ $component_item->unit_id ]->text }}
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
                                    <td class="@if(round($grand_total,2)  > round($component_item->quantity,2) ) is-invalid text-danger non-conforming @endif text-center">
                                       
                                        {{ number_format($grand_total,2) }} {{ $unit_options[ $component_item->unit_id ]->text }}
                                    </td>
                                </tr>

                        </table>
                    </div>
                </div>
            </div>
        @php $i++ @endphp
        @endforeach

        <div class="row mb-3" id="comment-box"></div>
    
        <div class="row mt-5">
            <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
                @if($component->status == 'PEND')
                    <button class="btn btn-danger" id="rejectBtn">Reject</button>
                @endif
                @if($component->status == 'PEND')
                    <button class="btn btn-primary" id="approveBtn">Approve</button>
                @endif

                <button class="btn btn-warning" id="printBtn">Print</button>

                @if($component->status == 'APRV')
                    <button class="btn btn-primary" id="revertPendBtn">Revert to Pending</button>
                @endif
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            </div>
        </div>
       
</div>

<script type="module">
    import {$q} from '/adarna.js';
    import CommentForm from '/ui_components/comment/CommentForm.js';

    
    const approveBtn        = $q('#approveBtn').first();
    const rejectBtn         = $q('#rejectBtn').first();
    const cancelBtn         = $q('#cancelBtn').first();
    const contract_item     = $q('#contract_item').first();
    const component         = $q('#component').first();
    const printBtn          = $q('#printBtn').first();
    const revertPendBtn     = $q('#revertPendBtn').first();
    const calloutDanger     = $q('#callout-danger').first();
    const calloutDangerText = $q('#callout-danger-p').first(); 
    const comment_box       = $q('#comment-box').first();
    
    window.util.quickNav = {
        title:'Component',
        url:'/review/component'
    };

    //Hack to prevent double comment box when using back button
    comment_box.innerHTML = '';

    comment_box.append(CommentForm({
        record_id       :'{{$component->id}}',
        record_type     :'COMPON'
    }));
    
    printBtn.onclick = ()=>{
        window.open('/project/section/print/{{$section->id}}','_blank').focus();
    }

    if(revertPendBtn){
        revertPendBtn.onclick = async ()=>{

            let answer = await window.util.confirm('Are you sure you want to revert this component to PENDING status?');

            if(!answer){
                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/component/revert_to_pending',{
                id: '{{$component->id}}'
            }).then(reply=>{

                
                window.util.unblockUI();

                if(reply.status <= 0 ){
                    window.util.showMsg(reply);
                    return false;
                };


                window.util.navReload();

            });
        }

    }

    @if($component->status == 'PEND')

        approveBtn.onclick = async (e)=>{
            e.preventDefault();


            if( $q('.non-conforming').items().length ){
                
                let answer1 = await window.util.prompt('Warning there are quantities that are over the limit, type "ok" to proceed.');

                if(answer1 != 'ok'){
                    return false;
                }
            }

            let answer2 = await window.util.confirm('Are you sure you want to APPROVE this component?');
            
            if(!answer2){
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


                window.util.navReload();

            });
        }
        
        rejectBtn.onclick = async (e)=>{
            e.preventDefault();
            
            let answer = await window.util.confirm('Are you sure you want to REJECT this component?');
            
            if(!answer){
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


                window.util.navReload();

            });
        }

       
    @endif

    component.onchange = ()=>{

        window.util.navTo('/review/component/'+contract_item.value+'/'+component.value);
    }

    contract_item.onchange = ()=>{

        window.util.navTo('/review/component/'+contract_item.value+'/0');
    }

    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/review/components/');
    }

    let non_conforming_count = $q('.non-conforming').items().length;

    if(non_conforming_count){
        calloutDanger.classList.remove('d-none');
        calloutDangerText.innerText = 'Warning: '+non_conforming_count+' item(s) are non-conforming';
    }
</script>
</div>
@endsection