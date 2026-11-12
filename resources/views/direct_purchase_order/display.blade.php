@extends('layouts.app')

@section('content')
<div id="content">

    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <div class="d-flex justify-content-between align-items-center">
                <ul>
                    <li>
                        <a href="/direct_purchase_order">
                            <span>Direct Purchase Order</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="active">
                            <span>{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </a>
                    </li>
                </ul>
                <button class="btn btn-outline-primary" id="reviewLinkBtn">
                    <i class="bi bi-link-45deg"></i> Review Link
                </button>
            </div>
        </div>
        <hr>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Direct Purchase Order #{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</h5>
                <span class="badge bg-secondary fs-6">{{ $direct_purchase_order->status }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($dpo_details as $key => $val)
                        <div class="col-md-6 mb-2">
                            <strong>{{ $key }}:</strong> 
                            <span>{{ is_array($val) ? $val['text'] : $val }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Ordered Items</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Remarks</th>
                            <th class="text-end">Ordered</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Remaining</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($items as $item)
                            @php 
                                $rowTotal = $item->quantity * $item->price; 
                                $subtotal += $rowTotal;
                            @endphp
                            <tr>
                                <td>{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}</td>
                                <td>{{ $item->remarks ?: '-' }}</td>
                                <td class="text-end">{{ (float)$item->quantity == intval($item->quantity) ? number_format($item->quantity, 0) : number_format($item->quantity, 2) }}</td>
                                <td class="text-end text-success fw-bold">{{ (float)$item->received_quantity == intval($item->received_quantity) ? number_format($item->received_quantity, 0) : number_format($item->received_quantity, 2) }}</td>
                                <td class="text-end text-danger fw-bold">{{ (float)$item->remaining_quantity == intval($item->remaining_quantity) ? number_format($item->remaining_quantity, 0) : number_format($item->remaining_quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                <td class="text-end font-monospace">{{ number_format($rowTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(!empty($extras) && count($extras) > 0)
        <!-- Extras Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Additional Charges / Discounts (Extras)</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($extras as $extra)
                            <tr>
                                <td>{{ $extra->text }}</td>
                                <td class="text-end font-monospace">{{ number_format($extra->value, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Grand Total Summary -->
        <div class="card mb-4">
            <div class="card-body text-end">
                <h3>Grand Total: <span class="text-primary font-monospace">{{ number_format($direct_purchase_order->getGrandTotal(), 2) }}</span></h3>
            </div>
        </div>

        @if(isset($received_records) && $received_records->count() > 0)
        @php
            $total_ordered_quantity = $direct_purchase_order->Items->sum('quantity');
            $total_received_quantity = $direct_purchase_order->Items->sum(function($item) {
                return (float) $item->received_quantity;
            });
            $total_remaining_quantity = $direct_purchase_order->Items->sum(function($item) {
                return (float) $item->remaining_quantity;
            });
        @endphp
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Receiving History</h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="small">
                        Total Received Quantity: <strong class="text-success">{{ (float)$total_received_quantity == intval($total_received_quantity) ? number_format($total_received_quantity, 0) : number_format($total_received_quantity, 2) }}</strong>
                    </span>
                    <span class="small">
                        Remaining Unreceived Quantity: <strong class="text-danger">{{ (float)$total_remaining_quantity == intval($total_remaining_quantity) ? number_format($total_remaining_quantity, 0) : number_format($total_remaining_quantity, 2) }}</strong>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Summary of Items -->
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold"><i class="bi bi-list-check me-1"></i> Receiving Summary</span>
                    </div>
                    <div class="table-responsive rounded border">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Material Item</th>
                                    <th class="text-center" style="width: 120px;">Ordered</th>
                                    <th class="text-center" style="width: 180px;">Total Received Quantity</th>
                                    <th class="text-center" style="width: 220px;">Remaining Unreceived Quantity</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($direct_purchase_order->Items as $dpoItem)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">
                                            {{ $dpoItem->MaterialItem ? $dpoItem->MaterialItem->formattedName : 'Unknown Item' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ (float)$dpoItem->quantity == intval($dpoItem->quantity) ? number_format($dpoItem->quantity, 0) : number_format($dpoItem->quantity, 2) }}
                                    </td>
                                    <td class="text-center text-success fw-bold">
                                        {{ (float)$dpoItem->received_quantity == intval($dpoItem->received_quantity) ? number_format($dpoItem->received_quantity, 0) : number_format($dpoItem->received_quantity, 2) }}
                                    </td>
                                    <td class="text-center text-danger fw-bold">
                                        {{ (float)$dpoItem->remaining_quantity == intval($dpoItem->remaining_quantity) ? number_format($dpoItem->remaining_quantity, 0) : number_format($dpoItem->remaining_quantity, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($dpoItem->remaining_quantity <= 0)
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($dpoItem->received_quantity > 0)
                                            <span class="badge bg-warning text-dark">Partial</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-top">
                                <tr class="fw-bold">
                                    <td class="text-end">Total:</td>
                                    <td class="text-center">
                                        {{ (float)$total_ordered_quantity == intval($total_ordered_quantity) ? number_format($total_ordered_quantity, 0) : number_format($total_ordered_quantity, 2) }}
                                    </td>
                                    <td class="text-center text-success">
                                        {{ (float)$total_received_quantity == intval($total_received_quantity) ? number_format($total_received_quantity, 0) : number_format($total_received_quantity, 2) }}
                                    </td>
                                    <td class="text-center text-danger">
                                        {{ (float)$total_remaining_quantity == intval($total_remaining_quantity) ? number_format($total_remaining_quantity, 0) : number_format($total_remaining_quantity, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Receiving History Logs -->
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-clock-history me-1"></i> Delivery / Receiving Logs</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
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
                                            {{ $rItem->DirectPurchaseOrderItem->MaterialItem->formattedName ?? 'Unknown Item' }}:
                                            <strong>{{ (float)$rItem->quantity_received == intval($rItem->quantity_received) ? number_format($rItem->quantity_received, 0) : number_format($rItem->quantity_received, 2) }}</strong>
                                        </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ $rec->remarks ?? '-' }}</td>
                                <td>{{ $rec->CreatedByUser->name ?? 'User #'.$rec->created_by }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Summary:</td>
                                <td>
                                    <span class="text-success me-3">
                                        Total Received Quantity: <strong>{{ (float)$total_received_quantity == intval($total_received_quantity) ? number_format($total_received_quantity, 0) : number_format($total_received_quantity, 2) }}</strong>
                                    </span>
                                    <span class="text-danger">
                                        Remaining Unreceived Quantity: <strong>{{ (float)$total_remaining_quantity == intval($total_remaining_quantity) ? number_format($total_remaining_quantity, 0) : number_format($total_remaining_quantity, 2) }}</strong>
                                    </span>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
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
                        <h5 class="modal-title" id="receiveModalLabel">Receive Items for Direct PO #{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</h5>
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
                                    <thead>
                                        <tr>
                                            <th>Material Item</th>
                                            <th class="text-center" style="width: 100px;">Ordered</th>
                                            <th class="text-center" style="width: 100px;">Remaining</th>
                                            <th class="text-center" style="width: 140px;">Receiving Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($direct_purchase_order->Items as $dpoItem)
                                        @if($dpoItem->remaining_quantity > 0)
                                        <tr>
                                            <td>
                                                {{ $dpoItem->MaterialItem ? $dpoItem->MaterialItem->formattedName : 'Unknown Item' }}
                                            </td>
                                            <td class="text-center">{{ (float)$dpoItem->quantity == intval($dpoItem->quantity) ? number_format($dpoItem->quantity, 0) : number_format($dpoItem->quantity, 2) }}</td>
                                            <td class="text-center"><span class="badge bg-secondary">{{ (float)$dpoItem->remaining_quantity == intval($dpoItem->remaining_quantity) ? number_format($dpoItem->remaining_quantity, 0) : number_format($dpoItem->remaining_quantity, 2) }}</span></td>
                                            <td>
                                                <input type="number" step="any" min="0" max="{{ $dpoItem->remaining_quantity }}" class="form-control form-control-sm text-end receive-item-input" data-dpo-item-id="{{ $dpoItem->id }}" placeholder="0" />
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

        <!-- Action Controls -->
        <div class="text-end mb-5">
            <a href="/direct_purchase_order" class="btn btn-secondary me-2">Back to List</a>

            @if($direct_purchase_order->status == 'DRFT')
                <button type="button" class="btn btn-danger me-2" id="deleteBtn">Delete</button>
                <button type="button" class="btn btn-success me-2" id="submitBtn">Submit for Review</button>
                <a href="/direct_purchase_order/print/{{$direct_purchase_order->id}}" target="_blank" class="btn btn-info me-2 text-white">
                    <i class="bi bi-printer"></i> Print
                </a>
            @endif

            @if($direct_purchase_order->status == 'APRV')
                @if($direct_purchase_order->received_status != 'COMP')
                    <button id="openReceiveModalBtn" type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#receiveModal">
                        <i class="bi bi-box-seam me-1"></i> Receive Items
                    </button>
                @endif
                <a href="/direct_purchase_order/print/{{$direct_purchase_order->id}}" target="_blank" class="btn btn-info me-2 text-white">
                    <i class="bi bi-printer"></i> Print
                </a>
                @if(!empty($canVoid))
                    <button type="button" class="btn btn-danger me-2" id="directVoidBtn">
                        <i class="bi bi-x-octagon me-1"></i> Void
                    </button>
                @endif
                @if(!empty($canRequestVoid) && empty($canVoid))
                    <button type="button" class="btn btn-warning" id="voidBtn">Request Void</button>
                @endif
            @endif
        </div>

    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const submitBtn        = $q('#submitBtn').first();
        const deleteBtn        = $q('#deleteBtn').first();
        const voidBtn          = $q('#voidBtn').first();
        const directVoidBtn    = $q('#directVoidBtn').first();
        const reviewLinkBtn    = $q('#reviewLinkBtn').first();
        const submitReceiveBtn = $q('#submitReceiveBtn').first();

        const dpoId = {{ $direct_purchase_order->id }};

        if (reviewLinkBtn) {
            reviewLinkBtn.onclick = async () => {
                await window.util.copyToClipboard('{{ url("/review/direct_purchase_order/".$direct_purchase_order->id) }}');
                alert('Review Link for Direct Purchase Order #{{ str_pad($direct_purchase_order->id, 6, "0", STR_PAD_LEFT) }} copied!');
            };
        }

        if (directVoidBtn) {
            directVoidBtn.onclick = async () => {
                if (!confirm('Are you sure you want to VOID this Direct Purchase Order? This action cannot be undone.')) return;

                window.util.blockUI();
                let res = await window.util.$post('/api/direct_purchase_order/void', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    location.reload();
                } else {
                    alert(res.message || 'Failed to void.');
                }
            };
        }

        if (submitBtn) {
            submitBtn.onclick = async () => {
                if (!confirm('Are you sure you want to submit this Direct PO for review?')) return;
                
                window.util.blockUI();
                let res = await window.util.$post('/api/direct_purchase_order/submit_for_review', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    location.reload();
                } else {
                    alert(res.message || 'Failed to submit.');
                }
            };
        }

        if (deleteBtn) {
            deleteBtn.onclick = async () => {
                if (!confirm('Are you sure you want to delete this Direct PO? This cannot be undone.')) return;
                
                window.util.blockUI();
                let res = await window.util.$post('/api/direct_purchase_order/delete', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    window.util.navTo('/direct_purchase_order');
                } else {
                    alert(res.message || 'Failed to delete.');
                }
            };
        }

        if (voidBtn) {
            voidBtn.onclick = async () => {
                if (!confirm('Are you sure you want to request void for this Direct PO?')) return;
                
                window.util.blockUI();
                let res = await window.util.$post('/api/direct_purchase_order/request_void', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    location.reload();
                } else {
                    alert(res.message || 'Failed to request void.');
                }
            };
        }

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
                        const dpoItemId = input.getAttribute('data-dpo-item-id') || input.dataset.dpoItemId;
                        items.push({
                            direct_purchase_order_item_id: parseInt(dpoItemId),
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

                window.util.$post('/api/direct_purchase_order/received/create', {
                    direct_purchase_order_id: dpoId,
                    receipt_no: receipt_no,
                    received_date: received_date,
                    remarks: remarks,
                    items: JSON.stringify(items)
                }).then(reply => {
                    window.util.unblockUI();

                    if (reply.status <= 0) {
                        const receiveModalEl = document.getElementById('receiveModal');
                        if (receiveModalEl) {
                            const closeBtn = receiveModalEl.querySelector('[data-bs-dismiss="modal"]');
                            if (closeBtn) closeBtn.click();
                        }
                        window.util.showMsg(reply);
                        return false;
                    }

                    const receiveModalEl = document.getElementById('receiveModal');
                    if (receiveModalEl) {
                        const closeBtn = receiveModalEl.querySelector('[data-bs-dismiss="modal"]');
                        if (closeBtn) closeBtn.click();
                    }

                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');

                    window.util.navReload();
                }).catch(err => {
                    window.util.unblockUI();
                    alert('An unexpected error occurred: ' + (err.message || err));
                });
            };
        }
    </script>
</div>
@endsection
