@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/review/purchase_orders">
                        <span>
                        Review
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                        Purchase Orders
                        </span>
                        <i class="ms-2 bi bi-display"></i>
                    </a>
                </li>
            </ul>
        </div>
        
        <hr>

        <table class="record-table-horizontal">
            <tbody>
                <tr>
                    <th>PO Number</th>
                    <td>{{ str_pad($purchase_order->id,6,0,STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <th>Material Request ID</th>
                    <td>{{ str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT) }}</td>
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
                    <th>Created By</th>
                    <td>{{$purchase_order->createdByUser()->name}} {{$purchase_order->created_at}}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>
                        <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Review Purchase Order
            </div>
            <div class="folder-form-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Supplier</label>
                            <input type="text" class="form-control" value="{{$supplier->name}}" id="supplier" disabled="true"/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Payment Terms</label>
                            <input type="text" class="form-control" value="{{$payment_term->text}}" id="payment_term" disabled="true"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="item_container">
            
            @php $sub_total = 0; @endphp

            @foreach($componentItemMaterialsArr as $id => $items)

                <div class="mb-3 border rounded p-3">
               
                    <h5>{{ $componentItemArr[$id]->name }}</h5>

                
                    @foreach($items as $item)
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Material Item</label>
                                <input type="text" class="form-control" disabled="true" value="{{ $materialItemArr[ $item->material_item_id]->brand }} {{ $materialItemArr[ $item->material_item_id]->name }} {{ $materialItemArr[ $item->material_item_id]->specification_unit_packaging }}"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="text" class="form-control" disabled="true" value="{{ number_format($item->price,2) }}"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Current Available</label>
                                @php 
                                    $remaining_quantity = 0;

                                    if(isset($remaining_quantity_arr[$id])){
                                        if( isset($remaining_quantity_arr[$id][$item->material_item_id]) ){
                                            $remaining_quantity = $remaining_quantity_arr[$id][$item->material_item_id];

                                            //echo $item->material_item_id.' '.$id.' '.$remaining_quantity;
                                        }
                                    }
                                @endphp
                                <input type="text" class="form-control" disabled="true" value="{{ number_format($remaining_quantity, 2) }}"/>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label>Order Quantity </label>
                               
                                <input type="text" class="form-control @if($item->quantity > $remaining_quantity && $purchase_order->status == 'PEND') is-invalid @endif" disabled="true" value="{{$item->quantity}}"/>
                                @if($item->quantity > $remaining_quantity && $purchase_order->status == 'PEND')
                                <div class="invalid-feedback">
                                    Order quantity is more than the available quantity
                                </div>
                                @endif
                            </div>
                        </div>
                       
                        <div class="col-lg-2">
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
            <table class="table w-auto table-border">
                <tr>
                    <td>
                        <input type="text" disabled="true" value="Sub Total" class="form-control"/>
                    </td>
                    <td>
                        <input type="text" id="sub_total" disabled="true" value="{{ number_format($sub_total,2) }}" class="form-control"/>
                    </td>
                </tr>

                @if($extras)
                <tr>
                    <th colspan="2" class="text-center">
                        Additional Charges / Discounts
                    </th>
                </tr>
                <tr>
                    <th class="text-center">Particular</th>
                    <th class="text-center">Amount</th>
                </tr>
                @endif

                @php $grand_total = $sub_total @endphp

                @foreach($extras as $extra)
                <tr class="extra">
                    <td>
                        <input type="text" disabled="true" value="{{$extra->text}}" class="extra_text form-control"/>
                    </td>
                    <td>
                        <input type="text" disabled="true" value="{{ number_format($extra->value,2) }}" class="extra_val form-control" />
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
            <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
                
                @if($purchase_order->status == 'PEND')
                    <button id="rejectBtn" class="btn btn-danger">Reject</button>
                @endif

                @if($purchase_order->status == 'REVO')
                    <button id="approveVoidBtn" class="btn btn-danger">Approve Void</button>
                @endif

                @if($purchase_order->status == 'PEND')
                    <button id="approveBtn" class="btn btn-primary">Approve</button>
                @endif

                @if($purchase_order->status == 'REVO')
                <button id="rejectVoidBtn" class="btn btn-primary">Reject Void</button>
                @endif
                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    

    <script type="module">
        import {$q} from '/adarna.js';
        
        let cancelBtn = $q('#cancelBtn').first();

        cancelBtn.onclick = ()=>{
            window.util.navTo('/review/purchase_order');
        }

        window.util.quickNav = {
            title:'Review Purchase Order',
            url: '/review/purchase_order'
        };

        @if($purchase_order->status == 'REVO')
            let approveVoidBtn = $q('#approveVoidBtn').first();

            approveVoidBtn.onclick = ()=>{

                if(!confirm('Are you sure you want to VOID this PO?')){
                    return false;
                }


                window.util.blockUI();

                window.util.$post('/api/review/purchase_order/void',{
                    id: '{{$purchase_order->id}}'
                }).then(reply=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){

                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.navTo('/review/purchase_orders');
                });
            }

            rejectVoidBtn.onclick = ()=>{

                window.util.blockUI();

                window.util.$post('/api/review/purchase_order/reject_void',{
                    id: '{{$purchase_order->id}}'
                }).then(reply=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){

                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.navTo("/review/purchase_orders");
                });
            }
        @endif

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

                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.navTo("/review/purchase_orders");
                });
        }

        approveBtn.onclick = (e)=>{
                e.preventDefault();

                window.util.confirm('Are you sure you want to APPROVE this PO?',(answer)=>{
                    
                    if(!answer){
                        return false;
                    }

                    window.util.blockUI();

                    window.util.$post('/api/review/purchase_order/approve',{
                        id: '{{$purchase_order->id}}'
                    }).then(reply=>{

                        window.util.unblockUI();

                        if(reply.status <= 0){

                            window.util.showMsg(reply);
                            return false;
                        }

                        window.util.navTo("/review/purchase_orders");
                    });
                });

        }
        @endif
    </script>
</div>
@endsection