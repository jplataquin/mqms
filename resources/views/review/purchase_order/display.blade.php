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

        <x-folder-details title="Review Purchase Order" :items="$po_details"></x-folder-details>
        

        <!--
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

                                            $remaining_quantity = round( $remaining_quantity_arr[$id][$item->material_item_id], 2);

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
        -->


         <div class="form-container mt-3 mb-3">
            <div class="form-header">
                &nbsp;
            </div>
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
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
        
 
        <div class="table-responsive" id="item_container">
            <table class="table form-container">    
                <thead>
                    <tr>
                        <th style="min-width:500px">Material Item</th>
                        <th style="min-width:200px">Price</th>
                        <th style="min-width:200px">Qantity</th>
                        <th style="min-width:200px">Total</th>
                    </tr>
                <thead>

            @php $sub_total = 0; @endphp

                    <tbody>
                    @foreach($componentItemMaterialsArr as $id => $items)
            
                    <!-- <div class="mb-3 border rounded p-3" style="max-width:120%"> -->
                    
                
                        @foreach($items as $item)
                            <tr>
                                <td colspan="4">
                                    {{ $componentItemArr[$id]->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-5">
                                    <input type="text" class="form-control" disabled="true" value="{{ $materialItemArr[ $item->material_item_id ]->brand }} {{ $materialItemArr[ $item->material_item_id ]->name }} {{ $materialItemArr[ $item->material_item_id]->specification_unit_packaging }}"/>
                                </td>    
                                <td>
                                    <input type="text" class="form-control text-center" disabled="true" value="{{ number_format($item->price,2) }}"/>
                                </td>    
                                <td>
                                    <input type="text" class="form-control text-center" disabled="true" value="{{$item->quantity}}"/>
                                </td>    
                                <td>
                                    <input type="text" class="form-control text-end" disabled="true" value="{{ number_format( $item->quantity * $item->price ) }}"/>
                                </td>
                            </tr>
                            @php $sub_total = $sub_total + ($item->quantity * $item->price); @endphp
                        @endforeach
                    <!-- </div> --> 
                
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"></td>
                        <th class="text-center">Sub Total</th>
                        <td>
                             <input type="text" id="sub_total" disabled="true" value="{{ number_format($sub_total, 2) }}" class="form-control text-end"/>
                        </td>
                    </tr>

                    @if($extras)
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="2" class="text-center">Additional Charges / Discounts</th>
                    </tr>
                    @endif

                    @php $grand_total = $sub_total; @endphp

                    @foreach($extras as $extra)
                        <tr class="extra">
                            <td colspan="2"></td>
                            <td>
                                <input type="text" disabled="true" value="{{$extra->text}}" class="extra_text form-control"/>
                            </td>
                            <td>
                                <input type="number" disabled="true" value="{{ number_format($extra->value,2) }}" class="extra_val form-control text-end" />
                            </td>
                        </tr>

                        @php $grand_total = $grand_total + $extra->value @endphp

                    @endforeach

                    <tr class="extra">
                        <td colspan="2"></td>
                        <th class="text-center">
                            Grand Total
                        </td>
                        <td>
                            <input type="text" disabled="true" value="{{ number_format($grand_total,2) }}" class="extra_val form-control text-end" />
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    


        <div class="row mt-3" id="comment-box"></div>

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
        import CommentForm from '/ui_components/comment/CommentForm.js';

        const cancelBtn = $q('#cancelBtn').first();
        const comment_box = $q('#comment-box').first();

        cancelBtn.onclick = ()=>{
            window.util.navTo('/review/purchase_orders');
        }


        //Hack to prevent double comment box when using back button
        comment_box.innerHTML = '';

        comment_box.append(CommentForm({
            record_id       : '{{$purchase_order->id}}',
            record_type     : 'PURORD'
        }));

        window.util.quickNav = {
            title:'Review Purchase Order',
            url: '/review/purchase_order'
        };

        @if($purchase_order->status == 'REVO')
            const approveVoidBtn = $q('#approveVoidBtn').first();

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
            
            const rejectBtn = $q('#rejectBtn').first();
            const approveBtn   = $q('#approveBtn').first();

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