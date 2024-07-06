@extends('layouts.app')

@section('content')
<div class="container">
<h5>Review » Components » Display</h5>
<hr>
        <div>
            <table class="table border">
                <tbody>
                    <tr>
                        <th>Project / Section</th>
                        <td>
                            {{$project->name}} - ( {{$section->name}} )
                        </td>
                    </tr>
                    <tr>
                        <th>Component ID</th>
                        <td>{{str_pad($component->id,6,0,STR_PAD_LEFT)}}</td>
                    </tr>
                    <tr>
                        <th>Component</th>
                        <td>{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$component->status}}</td>
                    </tr>
                    <tr>
                        <th>Quantity / Unit</th>
                        <td>{{$component->quantity}} {{$unit_options[$component->component_unit_id]->text}}</td>
                    </tr>
                    <tr>
                        <th>Use Count</th>
                        <td>{{$component->use_count}}</td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td>{{$component->createdByUser()->name}} {{$component->created_at}}</td>
                    </tr>
                    <tr>
                        <th>Updated By</th>
                        <td>{{$component->updatedByUser()->name}} {{$component->updated_at}}</td>
                    </tr>
                    <tr>
                        <th>Hash</th>
                        <td>{{$hash}}</td>
                    </tr>
                </tbody>
            </table>
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

        @php $i = 1 @endphp
        @foreach($componentItems as $item)
        <table class="border table">
            <thead>
                <tr>
                    <th style="width:50%;background-color:#add8e6">#{{$i}} {{$item->name}}</th>
                    <th class="text-center" style="background-color:#add8e6"> {{$item->quantity}} {{$item->unit}} </th>
                    <th class="text-center" style="background-color:#add8e6"> P {{number_format($item->budget_price,2)}} </th>
                
                </tr>
            </thead>
        </table>
        <table class="ms-3 border table">
            <thead>
                <tr>
                    <th width="50%">Material</th>
                    <th>Equivalent</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                
                @foreach($item->materialQuantities as $mq)
                <tr>
                    <td>
                        {{$materialItems[$mq->material_item_id]->brand }}
                        {{$materialItems[$mq->material_item_id]->name }} 
                        {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                        
                    </td>
                    <td>
                        {{$mq->equivalent}} {{$item->unit}}
                    </td>
                    <td>
                        {{$mq->quantity}}
                    </td>
                    <td>
                        {{$mq->equivalent * $mq->quantity}} {{$item->unit}}
                    </td>
                </tr>
                @endforeach
                </tbody>
        </table>
        
        @php $i++ @endphp
        @endforeach
    

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
                window.util.showMsg(reply.message);
                return false;
            };


            document.location.href = '/review/components/';

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
                window.util.showMsg(reply.message);
                return false;
            };


            document.location.href = '/review/components/';

        });
    }
    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        document.location.href = '/review/components/';
    }
</script>
@endsection