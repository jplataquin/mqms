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
                Review Component
            </div>
            <div class="folder-form-body">
                
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
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Component</label>
                            <input type="text" class="form-control" disabled="true" value="{{$component->name}}"/>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" class="form-control" disabled="true" value="{{$component->status}}"/>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" disabled="true" value=""/>
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
        @endphp
        @foreach($component_items as $component_item)
            <div class="form-container mb-3">
                <div class="form-header text-start p-3">
                    {{$i}}.) {{$component_item->name}}
                </div>
                <div class="form-body">
                    <table border="1" class="table">
                        
                        <tr>
                            <th class="text-center">Factor</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Cost</th>
                            <th class="text-center">Amount</th>
                        </tr>
                        <tr>
                            <td class="text-center">


                                @if($component_item->function_type_id == 1)
                                    {{ 
                                        formatFactor(
                                            number_format(
                                                round( ($component_item->function_variable  / $component->use_count), 6 )
                                            ,6)
                                        )
                                    }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]->text}}   
                                    
                                    <strong> > </strong>
                                @endif
                                
                                @if($component_item->function_type_id == 2)
                                    {{ 
                                        formatFactor(
                                            number_format(
                                                round( (1 / $component_item->function_variable) / $component->use_count,6)
                                            ,6)
                                        ) 
                                    }} 
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    /
                                    {{$unit_options[$component->unit_id]->text}}    
                                    

                                    <strong> > </strong>
                                @endif


                                @if($component_item->function_type_id == 4)
                                <strong> < </strong>

                                    {{ 
                                        number_format(
                                            ($component_item->function_variable * $component->use_count),
                                            2
                                        ) 
                                    }}  

                                    {{$unit_options[$component->unit_id]->text}}
                                    /
                                    {{$unit_options[$component_item->unit_id]->text}}
                                    
                                @endif


                              
                            </td>
                            
                            
                            <td class="text-center">
                                {{$component_item->quantity}} {{$unit_options[$component_item->unit_id]->text}}
                            </td>

                            <td class="text-center">
                                Php {{ number_format($component_item->budget_price,2) }}
                            </td>
                            
                            <td class="text-center">
                                @php
                                    $grand_total = $grand_total + ($component_item->budget_price * $component_item->quantity);
                                @endphp

                                Php {{ number_format($component_item->budget_price * $coponent_item->quantity,2) }}
                            </td>
                        </tr>
                    </table>
                    <table border="1" class="table">
                            

                            <tr>
                                <th style="width:40%" class="text-center">Material</th>
                                <th style="width:20%" class="text-center">Equivalent</th>
                                <th style="width:20%" class="text-center">Quantity</th>
                                <th style="width:20%" class="text-center">Total</th>
                            </tr>
                            
                            @foreach($item->materialQuantities as $mq)
                            <tr>
                                <td>
                                    {{$materialItems[$mq->material_item_id]->name }} 
                                    {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                                    {{$materialItems[$mq->material_item_id]->brand }} 
                                </td>
                                <td class="text-center">
                                    {{$mq->equivalent}} {{$item->unit}}
                                </td>
                                <td class="text-center">
                                    {{$mq->quantity}}
                                </td>
                                <td class="text-center">
                                    {{$mq->equivalent * $mq->quantity}} {{$item->unit}}
                                </td>
                            </tr>
                            @endforeach

                    </table>
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

    approveBtn.onclick = (e)=>{
        e.preventDefault();

        if(!confirm('Are you sure you want to Approve this?')){
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
    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        window.util.navTo('/review/components/');
    }
</script>
</div>
@endsection