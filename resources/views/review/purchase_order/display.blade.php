@extends('layouts.app')

@section('content')
<div class="container">
<h5>Purchase Order » Create</h5>
<hr>
    <table class="table">
        <tbody>
            <tr>
                <th>PO Number</th>
                <td>{{$purchase_order->id}}</td>
            </tr>
            <tr>
                <th>Material Quantity Request ID</th>
                <td>{{$material_quantity_request->id}}</td>
            </tr>
            <tr>
                <th>Project</th>
                <td>{{$project->name}}</td>
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
                <th>PO Status</th>
                <td>{{$purchase_order->status}}</td>
            </tr>
            <tr>
                <th>Date Created</th>
                <td>{{$purchase_order->created_at}}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>
                    <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                </td>
            </tr>
        </tbody>
    </table>

        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text" class="form-control" value="{{$supplier->name}}" id="supplier" disabled="true"/>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label>Payment Terms</label>
                    <input type="text" class="form-control" value="{{$payment_term->text}}" id="payment_term" disabled="true"/>
                </div>
            </div>
        </div>
        <hr>
        <div id="item_container">
            
            @php $sub_total = 0; @endphp

            @foreach($componentItemMaterialsArr as $id => $items)

                <div class="mb-3">
                <h3>{{ $componentItemArr[$id]->name }}</h3>

                
                @foreach($items as $item)
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Material Item</label>
                                <input type="text" class="form-control" disabled="true" value="{{ $materialItemArr[ $item->material_item_id]->brand }} {{ $materialItemArr[ $item->material_item_id]->name }} {{ $materialItemArr[ $item->material_item_id]->specification_unit_packaging }}"/>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="text" class="form-control" disabled="true" value="{{$item->price}}"/>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Order Quantity</label>
                                <input type="text" class="form-control" disabled="true" value="{{$item->quantity}}"/>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Total</label>
                                <input type="text" class="form-control" disabled="true" value="{{ number_format($item->quantity * $item->price,2) }}"/>
                            </div>
                        </div>
                    </div>

                    @php $sub_total = $sub_total + ($item->quantity * $item->price); @endphp
                @endforeach
                </div>
                
            @endforeach
        </div>
        
        <div class="d-flex justify-content-end">
            <table class="table w-50 table-border">
                <tr>
                    <td>
                        <input type="text" disabled="true" value="Sub Total" class="form-control"/>
                    </td>
                    <td>
                        <input type="text" id="sub_total" disabled="true" value="{{$sub_total}}" class="form-control"/>
                    </td>
                </tr>
                
                @php $grand_total = $sub_total @endphp

                @foreach($extras as $extra)
                <tr class="extra">
                    <td>
                        <input type="text" disabled="true" value="{{$extra->text}}" class="extra_text form-control"/>
                    </td>
                    <td>
                        <input type="text" disabled="true" value="{{$extra->value}}" class="extra_val form-control" />
                    </td>
                </tr>

                @php $grand_total = $grand_total + $extra->value @endphp

               @endforeach

               <tr class="extra">
                    <td>
                        <input type="text" disabled="true" value="Grand Total" class="extra_text form-control"/>
                    </td>
                    <td>
                        <input type="text" disabled="true" value="{{ number_format($grand_total,2) }}" class="extra_val form-control" />
                    </td>
                </tr>
            </table>
        </div>

        <div class="row mt-5">
            <div class="col-6 text-start">
                
                @if($purchase_order->status == 'PEND')
                    <button id="rejectBtn" class="btn btn-danger">Reject</button>
                @endif
            </div>
            <div class="col-6 text-end">

                @if($purchase_order->status == 'PEND')
                    <button id="approveBtn" class="btn btn-primary">Approve</button>
                @endif
                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';
    
    let cancelBtn = $q('#cancelBtn').first();

    cancelBtn.onclick = ()=>{
        window.location.href = '/review/purchase_order';
    }

    @if($purchase_order->status == 'PEND')
        
       let rejectBtn = $q('#rejectBtn').first();
       let approveBtn   = $q('#approveBtn').first();

       rejectBtn.onclick = (e)=>{
            e.preventDefault();

            if(!confirm('Are you sure you want to REJECT this PO?')){

                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/purchase_order/reject',{
                id: '{{$purchase_order->id}}'
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){

                    window.util.showMsg(reply.message);
                    return false;
                }

                window.location.href = "/review/purchase_orders";
            });
       }

       approveBtn.onclick = (e)=>{
            e.preventDefault();

            if(!confirm('Are you sure you want to APPROVE this PO?')){

                return false;
            }

            window.util.blockUI();

            window.util.$post('/api/review/purchase_order/approve',{
                id: '{{$purchase_order->id}}'
            }).then(reply=>{

                window.util.unblockUI();

                if(reply.status <= 0){

                    window.util.showMsg(reply.message);
                    return false;
                }

                window.location.href = "/review/purchase_orders";
            });
       }
    @endif
</script>
@endsection