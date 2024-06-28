@extends('layouts.app')

@section('content')
<div class="container">

        <div>
            <h3>
                {{$project->name}} - ( {{$section->name}} )
            </h3>
            <hr>
            <table class="table border">
                <tbody>
                    
                    <tr>
                        <th width="10%">Component</th>
                        <td>{{$component->name}}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{$component->status}}</td>
                    </tr>
                    <tr>
                        <th>Hash</th>
                        <td>{{$hash}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="row">
            <div class="col-6">
            <button class="btn btn-danger" id="disapproveBtn">Disapprove</button>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-primary" id="approveBtn">Approve</button>
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            </div>
        </div>

        @foreach($componentItems as $item)
        <table class="border table">
            <thead>
                <tr>
                    <th class="text-center bg-primary">{{$item->name}}</th>
                    <th class="text-center bg-primary"> {{$item->quantity}} {{$item->unit}} </th>
                </tr>
            </thead>
        </table>
        <table class="ms-3 border table">
            <thead>
                <tr>
                    <th width="50%">&nbsp;</th>
                    <th>Equivalent</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                
                @foreach($item->materialQuantities as $mq)
                <tr>
                    <td>
                        {{$materialItems[$mq->material_item_id]->name }} 
                        {{$materialItems[$mq->material_item_id]->specification_unit_packaging }} 
                        {{$materialItems[$mq->material_item_id]->brand }} 
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
        </li>
        @endforeach
    

</div>

<script type="module">
    import {$q} from '/adarna.js';

    let approveBtn      = $q('#approveBtn').first();
    let disapproveBtn   = $q('#disapproveBtn').first();
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

                if(reply.status <= 0 ){
                    window.util.unblockUI();
                    alert(reply.message);
                    return false;
                };

                window.util.unblockUI();

                document.location.href = '/review/components/';

            });
    }
    
    disapproveBtn.onclick = (e)=>{
        e.preventDefault();
        
    }
    cancelBtn.onclick = (e)=>{
        e.preventDefault();
        
    }
</script>
@endsection