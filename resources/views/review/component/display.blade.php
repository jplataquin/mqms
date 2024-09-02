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
        <div class="folder-form-container">
            <div class="folder-form-tab">
                Review Component
            </div>
            <div class="folder-form-body">
                <table class="table-record-horizontal">
                    <tbody>
                        <tr>
                            <th>Project</th>
                            <td>
                                {{$project->name}}
                            </td>
                        </tr>
                        <tr>
                            <th>Section</th>
                            <td>{{$section->name}}</td>
                        </tr>
                        <tr>
                            <th>Component</th>
                            <td>{{$component->name}}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{$component->description}}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{$component->status}}</td>
                        </tr>
                        <tr>
                            <th>Quantity / Unit</th>
                            <td>{{$component->quantity}} {{$unit_options[$component->unit_id]->text}}</td>
                        </tr>
                        <tr>
                            <th>Use Count</th>
                            <td>{{$component->use_count}}</td>
                        </tr>
                        <tr>
                            <th>Created By</th>
                            <td>{{$component->createdByUser()->name}} {{$component->created_at}}</td>
                        </tr>
                        @if($component->updated_at)
                        <tr>
                            <th>Updated By</th>
                            <td>{{$component->updatedByUser()->name}} {{$component->updated_at}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Hash</th>
                            <td>{{$hash}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-6">
            @if($component->status == 'PEND')
                <button class="btn btn-danger" id="rejectBtn">Reject</button>
            @endif
            </div>
            <div class="col-6 text-end">
                @if($component->status == 'PEND')
                <button class="btn btn-primary" id="approveBtn">Approve</button>
                @endif
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            </div>
        </div>

        @php 
            $i = 1;
            $grand_total = 0;
        @endphp
        @foreach($componentItems as $item)

        <div class="mb-5">
                <table border="1" class="table">
                    <tr>
                        <th class="" colspan="4" style="background-color:#add8e6">
                            {{$i}}.) {{$item->name}}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center">Budget Price</th>
                        <th class="text-center">Factor</th>
                        <th class="text-center">ceil( Quantity )</th>
                        <th class="text-center">Total Amount</th>
                    </tr>
                    <tr>
                        <td class="text-center">
                            Php {{ number_format($item->budget_price,2) }}
                        </td>
                        <td class="text-center">

                            @if($item->function_type_id == 1)
                             {{$item->function_variable}} {{ $unit_options[ $item->unit_id ]->text }} / {{$unit_options[$component->unit_id]->text}}
                            @elseif($item->function_type_id == 2)
                             1 {{$unit_options[$component->unit_id]->text}} / {{$item->function_variable}} {{ $unit_options[ $item->unit_id ]->text }}
                            @else
                                N/A
                            @endif
                            
                            <br>
                            _________
                            <br>
                            {{$component->use_count}} Use(s)
                        </td>
                        <td class="text-center">
                            {{$item->quantity}} {{$unit_options[$item->unit_id]->text}}
                        </td>
                        <td class="text-center">
                            @php
                                $grand_total = $grand_total + ($item->budget_price * $item->quantity);
                            @endphp
                            Php {{ number_format($item->budget_price * $item->quantity,2) }}
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