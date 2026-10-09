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

        <x-folder-details title="Purchase Order" :items="$po_details"></x-folder-details>
        


        <div class="w-100 text-end mt-3">
            @if($purchase_order->status == 'PEND')
            <button class="btn btn-outline-primary" id="reviewLinkBtn">
                Review Link
                <i class="bi bi-copy"></i>
            </button>
            @endif
        </div>


            
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
            <table class="table">    
                <thead>
                    <tr>
                        <th style="min-width:350px">Material Item</th>
                        <th style="min-width:150px">Price</th>
                        <th style="min-width:120px">Ordered</th>
                        <th style="min-width:120px">Received</th>
                        <th style="min-width:120px">Remaining</th>
                        <th style="min-width:150px">Total</th>
                    </tr>
                <thead>

            @php $sub_total = 0; @endphp

                    <tbody>
                    @foreach($componentItemMaterialsArr as $component_item_id => $items)
            
                    <!-- <div class="mb-3 border rounded p-3" style="max-width:120%"> -->
                    
                
                        @foreach($items as $item)
                            <tr>
                                <td colspan="6">
                                    {{ $componentItemArr[$component_item_id]->name }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="text" class="form-control" disabled="true" value="{{ $materialItemArr[ $item->material_item_id ]->brand }} {{ $materialItemArr[ $item->material_item_id ]->name }} {{ $materialItemArr[ $item->material_item_id]->specification_unit_packaging }}"/>
                                </td>    
                                <td>
                                    <input type="text" class="form-control text-center" disabled="true" value="{{ number_format($item->price,2) }}"/>
                                </td>    
                                <td>
                                    <input type="text" class="form-control text-center" disabled="true" value="{{$item->quantity}}"/>
                                </td>
                                <td>
                                    <input type="text" class="form-control text-center" disabled="true" value="{{$item->received_quantity}}"/>
                                </td>
                                <td>
                                    <input type="text" class="form-control text-center" disabled="true" value="{{$item->remaining_quantity}}"/>
                                </td>    
                                <td>
                                    <input type="text" class="form-control text-end" disabled="true" value="{{ number_format( $item->quantity * $item->price ) }}"/>
                                </td>
                            </tr>

                            @if( $check_quantity[ $item->material_item_id ] )
                                <tr>
                                    <td class="text-danger" colspan="6">
                                        @foreach($check_quantity[$item->material_item_id] as $msg)
                                            <div>{{$msg}}</div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif

                            @php $sub_total = $sub_total + ($item->quantity * $item->price); @endphp
                        @endforeach
                    <!-- </div> --> 
                
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                        <th class="text-center">Sub Total</th>
                        <td>
                             <input type="text" id="sub_total" disabled="true" value="{{ number_format($sub_total, 2) }}" class="form-control text-end"/>
                        </td>
                    </tr>

                    @if($extras)
                    <tr>
                        <th colspan="4"></th>
                        <th colspan="2" class="text-center">Additional Charges / Discounts</th>
                    </tr>
                    @endif

                    @php $grand_total = $sub_total; @endphp

                    @foreach($extras as $extra)
                        <tr class="extra">
                            <td colspan="4"></td>
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
                        <td colspan="4"></td>
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

        @if(isset($received_records) && $received_records->count() > 0)
        <div class="card mt-4 mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Receiving History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr class="table-secondary">
                                <th>Receipt / DR #</th>
                                <th>Date Received</th>
                                <th>Items & Quantities</th>
                                <th>Remarks</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($received_records as $rec)
                            <tr>
                                <td>{{ $rec->receipt_no ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($rec->received_date)->format('Y-m-d') }}</td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        @foreach($rec->Items as $rItem)
                                        <li>
                                            {{ $rItem->PurchaseOrderItem->MaterialItem->brand ?? '' }} {{ $rItem->PurchaseOrderItem->MaterialItem->name ?? '' }}: 
                                            <strong>{{ $rItem->quantity_received }}</strong>
                                        </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ $rec->remarks ?? '-' }}</td>
                                <td>{{ $rec->CreatedByUser->name ?? 'User #'.$rec->created_by }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Modal for Receiving Items -->
        <div class="modal fade" id="receiveModal" tabindex="-1" aria-labelledby="receiveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="receiveModalLabel">Receive Items for PO #{{ str_pad($purchase_order->id, 6, '0', STR_PAD_LEFT) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="receiveForm">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Receipt / DR No.</label>
                                    <input type="text" class="form-control" id="receipt_no" placeholder="e.g. DR-12345" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Received <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="received_date" value="{{ date('Y-m-d') }}" required />
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="receive_remarks" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                            <h6 class="mt-4 mb-2">Items to Receive:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Material Item</th>
                                            <th class="text-center" style="width: 100px;">Ordered</th>
                                            <th class="text-center" style="width: 100px;">Remaining</th>
                                            <th class="text-center" style="width: 140px;">Receiving Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase_order->Items as $poItem)
                                        @if($poItem->remaining_quantity > 0)
                                        <tr>
                                            <td>
                                                {{ $materialItemArr[$poItem->material_item_id]->brand ?? '' }} {{ $materialItemArr[$poItem->material_item_id]->name ?? '' }} {{ $materialItemArr[$poItem->material_item_id]->specification_unit_packaging ?? '' }}
                                            </td>
                                            <td class="text-center">{{ $poItem->quantity }}</td>
                                            <td class="text-center"><span class="badge bg-secondary">{{ $poItem->remaining_quantity }}</span></td>
                                            <td>
                                                <input type="number" step="any" min="0" max="{{ $poItem->remaining_quantity }}" class="form-control form-control-sm text-end receive-item-input" data-po-item-id="{{ $poItem->id }}" placeholder="0" />
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitReceiveBtn">Save Received</button>
                    </div>
                </div>
            </div>
        </div>
    

        <div class="row mt-3" id="comment-box"></div>

        <div class="row mt-5">
            <div class="col-lg-12 text-end">


                @if($purchase_order->status == 'PEND' || $purchase_order->status == 'DRFT')
                    <button id="deleteBtn" class="btn btn-danger">Delete</button>
                @endif
                
                @if($purchase_order->status == 'DRFT')
                    <button id="submitForReviewBtn" class="btn btn-warning">For Review</button>
                @endif

                @if($purchase_order->status == 'APRV' && $purchase_order->received_status != 'COMP')
                    <button id="openReceiveModalBtn" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#receiveModal">
                        <i class="bi bi-box-seam me-1"></i> Receive Items
                    </button>
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

                    window.util.navReload();
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

        const submitReceiveBtn = $q('#submitReceiveBtn').first();
        if (submitReceiveBtn) {
            submitReceiveBtn.onclick = async (e) => {
                e.preventDefault();

                const receipt_no = $q('#receipt_no').first().value;
                const received_date = $q('#received_date').first().value;
                const remarks = $q('#receive_remarks').first().value;

                if (!received_date) {
                    alert('Please select a received date.');
                    return;
                }

                const items = [];
                const inputs = document.querySelectorAll('.receive-item-input');
                inputs.forEach(input => {
                    const val = input.value.trim();
                    const qty = parseFloat(val);
                    if (!isNaN(qty) && qty > 0) {
                        const poItemId = input.getAttribute('data-po-item-id') || input.dataset.poItemId;
                        items.push({
                            purchase_order_item_id: parseInt(poItemId),
                            quantity_received: qty
                        });
                    }
                });

                if (items.length === 0) {
                    alert('Please enter a received quantity for at least one item.');
                    return;
                }

                if (!await window.util.confirm('Confirm saving received quantities?')) {
                    return;
                }

                window.util.blockUI();

                window.util.$post('/api/purchase_order/received/create', {
                    purchase_order_id: '{{$purchase_order->id}}',
                    receipt_no: receipt_no,
                    received_date: received_date,
                    remarks: remarks,
                    items: JSON.stringify(items)
                }).then(reply => {
                    window.util.unblockUI();

                    if (reply.status <= 0) {
                        window.util.showMsg(reply);
                        return false;
                    }

                    window.util.showMsg({ status: 1, message: 'Items received successfully!' });
                    window.util.navReload();
                });
            };
        }
    </script>
</div>
@endsection