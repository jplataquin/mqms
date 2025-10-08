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
                                    <input type="text" class="form-control text-end" disabled="true" value="{{ number_format( $item->quantity * $item->price ) }}"/>
                                </td>
                            </tr>

                            @if(isset($quantity_check[ $item->material_item_id ]))
                            <tr>
                                <td class="text-danger">
                                    @foreach($quantity_check[$item->material_item_id] as $msg)
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
            <div class="col-lg-12 text-end">


                @if($purchase_order->status == 'PEND' || $purchase_order->status == 'DRFT')
                    <button id="deleteBtn" class="btn btn-danger">Delete</button>
                @endif
                
                @if($purchase_order->status == 'DRFT')
                    <button id="submitForReviewBtn" class="btn btn-warning">For Review</button>
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
    </script>
</div>
@endsection