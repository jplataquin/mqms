@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">

        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/purchase_orders">
                        <span>
                            Purchase Orders
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Display
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
                    <th width="300px">PO Number</th>
                    <td>{{str_pad($purchase_order->id,6,0,STR_PAD_LEFT)}}</td>
                </tr>
                <tr>
                    <th>Material Request ID</th>
                    <td>
                        <a href="/material_quantity_request/{{$material_quantity_request->id}}">
                            {{str_pad($material_quantity_request->id,6,0,STR_PAD_LEFT)}}</td>
                        </a>
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
                    <th>Contract Item</th>
                    <td>{{$contract_item->item_code}} {{$contract_item->description}}</td>
                </tr>
                <tr>
                    <th>Component</th>
                    <td>{{$component->name}}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{$purchase_order->status}}</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>{{$purchase_order->CreatedByUser()->name}} {{$purchase_order->created_at}}</td>
                </tr>
                
                @if($purchase_order->updated_at)
                <tr>
                    <th>Updated By</th>
                    <td>{{$purchase_order->UpdatedByUser()->name}} {{$purchase_order->updated_at}}</td>
                </tr>
                @endif

                @if($purchase_order->approved_at)
                <tr>
                    <th>Approved By</th>
                    <td>{{$purchase_order->ApprovedByUser()->name}} {{$purchase_order->approved_at}}</td>
                </tr>
                @endif

                @if($purchase_order->rejected_at)
                <tr>
                    <th>Rejected By</th>
                    <td>{{$purchase_order->RejectedByUser()->name}} {{$purchase_order->rejected_at}}</td>
                </tr>
                @endif
                
                <tr>
                    <th>Description</th>
                    <td>
                        <textarea disabled="true" class="w-100" id="description">{{$material_quantity_request->description}}</textarea>
                    </td>
                </tr>

            </tbody>
        </table>
        
        <div class="w-100 text-end mt-3">
            @if($purchase_order->status == 'PEND')
            <button class="btn btn-outline-primary" id="reviewLinkBtn">
                Review Link
                <i class="bi bi-copy"></i>
            </button>
            @endif
        </div>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Purchase Order
            </div>

            <div class="folder-form-body">
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
            </div>
        </div>
    
        <div id="item_container">
            
            @php $sub_total = 0; @endphp

            @foreach($componentItemMaterialsArr as $id => $items)

                <div class="mb-3 border rounded p-3">
                  
            
                    @foreach($items as $item)
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Material Item</label>
                                    <input type="text" class="form-control" disabled="true" value="{{ $materialItemArr[ $item->material_item_id ]->brand }} {{ $materialItemArr[ $item->material_item_id ]->name }} {{ $materialItemArr[ $item->material_item_id]->specification_unit_packaging }}"/>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="text" class="form-control" disabled="true" value="{{ number_format($item->price,2) }}"/>
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
                                    <input type="text" class="form-control" disabled="true" value="{{ number_format( $item->quantity * $item->price ) }}"/>
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
                        <input type="text" id="sub_total" disabled="true" value="{{ number_format($sub_total, 2) }}" class="form-control"/>
                    </td>
                </tr>

                <tr>
                    <th colspan="2" class="text-center">
                        Additional Charges / Discounts
                    </th>
                </tr>
                <tr>
                    <th class="text-center">Particular</th>
                    <th class="text-center">Amount</th>
                </tr>

                @php $grand_total = $sub_total; @endphp

                @foreach($extras as $extra)
                    <tr class="extra">
                        <td>
                            <input type="text" disabled="true" value="{{$extra->text}}" class="extra_text form-control"/>
                        </td>
                        <td>
                            <input type="number" disabled="true" value="{{ number_format($extra->value,2) }}" class="extra_val form-control" />
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

        <div class="row mt-3" id="comment-box"></div>

        <div class="row mt-5">
            <div class="col-lg-12 text-end">


                @if($purchase_order->status == 'PEND' || $purchase_order->status == 'DRFT')
                    <button id="deleteBtn" class="btn btn-danger">Delete</button>
                @endif
                
                @if($purchase_order->status == 'DRFT')
                    <button id="submitPendingBtn" class="btn btn-warning">For Review</button>
                @endif

                @if($purchase_order->status == 'APRV')
                    <button id="voidBtn" class="btn btn-danger">Request Void</button>
                @endif
           
                @if($purchase_order->status == 'APRV')
                    <button id="printBtn" class="btn btn-warning">Print</button>
                @endif
                <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    
    </div>

    <script type="module">
        import {$q,Template} from '/adarna.js';
        import CommentForm from '/ui_components/comment/CommentForm.js';

        const cancelBtn         = $q('#cancelBtn').first();
        const deleteBtn         = $q('#deleteBtn').first();
        const reviewLinkBtn     = $q('#reviewLinkBtn').first();
        const comment_box       = $q('#comment-box').first();
        const submitReviewBtn   = $q('#submitForReviewBtn').first();


        //Hack to prevent double comment box when using back button
        comment_box.innerHTML = '';

        comment_box.append(CommentForm({
            record_id       : '{{$purchase_order->id}}',
            record_type     : 'PURORD'
        }));

        window.util.quickNav = {
            title:'Purchase Order',
            url:'/purchase_order'
        };

        if(reviewLinkBtn){
            reviewLinkBtn.onclick = async ()=>{
                let test = await window.util.copyToClipboard('{{ url("/review/purchase_order/".$purchase_order->id); }}');
                if(test){
                    alert('Review Link for "Purchase Order: {{$purchase_order->id}}" copied!');
                }else{
                    alert('Failed to copy');
                }
            }
        }

        cancelBtn.onclick = ()=>{
            window.util.navTo('/purchase_orders');
        }
        
        if(submitReviewBtn){
            submitReviewBtn.onclick = async (e)=>{
                e.preventDefault();
    
                if(! await window.util.confirm('Submit PO for review?')){

                    return false;
                }

                window.util.$post('/api/purchase_order/submit_for_review',{
                    id: '{{$purchase_order->id}}'
                }).then(reply=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){

                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.reload();
                });
            }
        }
        
        if(deleteBtn){

            deleteBtn.onclick = async (e)=>{
                e.preventDefault();

                if(! await window.util.confirm('Are you sure you want to delete this PO?')){

                    return false;
                }

                window.util.blockUI();

                window.util.$post('/api/purchase_order/delete',{
                    id: '{{$purchase_order->id}}'
                }).then(reply=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){

                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.navTo("/purchase_orders");
                });
            }   
        }
            
        const voidBtn = $q('#voidBtn').first();
        const printBtn = $q('#printBtn').first();
        
        if(voidBtn && printBtn){

            voidBtn.onclick = async (e)=>{
                e.preventDefault();

                if(! await window.util.confirm('Are you sure you want to request VOID this PO?')){

                    return false;
                }

                window.util.blockUI();

                window.util.$post('/api/purchase_order/request_void',{
                    id: '{{$purchase_order->id}}'
                }).then(reply=>{

                    window.util.unblockUI();

                    if(reply.status <= 0){

                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.navTo("/purchase_order/"+reply.data.id);
                });
            }

            printBtn.onclick = (e)=>{
                window.open('/purchase_order/print/{{$purchase_order->id}}','_blank').focus();
            }
        }
    </script>
</div>
@endsection