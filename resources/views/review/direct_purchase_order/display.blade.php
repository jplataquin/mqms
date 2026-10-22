@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/review/direct_purchase_orders">
                        <span>Review</span>
                    </a>
                </li>
                <li>
                    <a href="/review/direct_purchase_orders">
                        <span>Direct Purchase Orders</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>#{{ str_pad($direct_purchase_order->id, 6, '0', STR_PAD_LEFT) }}</span>
                        <i class="ms-2 bi bi-display"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <x-folder-details title="Review Direct Purchase Order" :items="$dpo_details"></x-folder-details>

        <div class="form-container mt-3 mb-3">
            <div class="form-header">
                Supplier & Project Information
            </div>
            <div class="form-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label text-muted">Supplier</label>
                            <input type="text" class="form-control" value="{{ $supplier ? $supplier->name : 'N/A' }}" disabled/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label text-muted">Payment Term</label>
                            <input type="text" class="form-control" value="{{ $payment_term ? ($payment_term->text ?: $payment_term->name) : 'N/A' }}" disabled/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label text-muted">Project</label>
                            <input type="text" class="form-control" value="{{ $direct_purchase_order->Project ? $direct_purchase_order->Project->name : 'None' }}" disabled/>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label text-muted">Section</label>
                            <input type="text" class="form-control" value="{{ $direct_purchase_order->Section ? $direct_purchase_order->Section->name : 'None' }}" disabled/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Items Table -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Ordered Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 250px;">Material Item</th>
                            <th class="text-center" style="width: 140px;">Packaging</th>
                            <th style="min-width: 160px;">Remarks</th>
                            <th class="text-end" style="width: 140px;">Price</th>
                            <th class="text-center" style="width: 120px;">Quantity</th>
                            <th class="text-end" style="width: 160px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->MaterialItem->brand }}</strong> {{ $item->MaterialItem->name }}
                                </td>
                                <td class="text-center">{{ $item->MaterialItem->specification_unit_packaging ?: '-' }}</td>
                                <td>{{ $item->remarks ?: '-' }}</td>
                                <td class="text-end font-monospace">{{ number_format($item->price, 2) }}</td>
                                <td class="text-center font-monospace">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end font-monospace">{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($extras) > 0)
        <!-- Additional Costs / Extras -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Additional Charges / Discounts</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($extras as $extra)
                        <div class="col-md-6 mb-2">
                            <strong>{{ $extra->name }}:</strong> 
                            <span class="font-monospace text-primary ms-2">{{ number_format((float)$extra->value, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Grand Total Summary -->
        <div class="card mb-4">
            <div class="card-body text-end">
                <h3>Grand Total: <span class="text-primary font-monospace">{{ number_format($direct_purchase_order->getGrandTotal(), 2) }}</span></h3>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="text-end mb-5">
            <a href="/review/direct_purchase_orders" class="btn btn-secondary me-2">Back to Review List</a>

            @if($direct_purchase_order->status == 'APRV')
                <a href="/direct_purchase_order/print/{{$direct_purchase_order->id}}" target="_blank" class="btn btn-info me-2 text-white">
                    <i class="bi bi-printer"></i> Print
                </a>
                @if($canVoid)
                    <button type="button" class="btn btn-danger me-2" id="directVoidBtn">
                        <i class="bi bi-x-octagon me-1"></i> Void
                    </button>
                @endif
            @endif

            @if($direct_purchase_order->status == 'PEND')
                @if($canReject)
                    <button type="button" class="btn btn-danger me-2" id="rejectBtn">
                        <i class="bi bi-x-circle me-1"></i> Reject
                    </button>
                @endif
                @if($canApprove)
                    <button type="button" class="btn btn-success me-2" id="approveBtn">
                        <i class="bi bi-check-circle me-1"></i> Approve
                    </button>
                @endif
            @endif

            @if($direct_purchase_order->status == 'REVO')
                @if($canRejectVoid)
                    <button type="button" class="btn btn-warning me-2" id="rejectVoidBtn">
                        Reject Void Request
                    </button>
                @endif
                @if($canVoid)
                    <button type="button" class="btn btn-danger me-2" id="approveVoidBtn">
                        Confirm Void
                    </button>
                @endif
            @endif
        </div>

    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const dpoId = {{ $direct_purchase_order->id }};

        window.util.quickNav = {
            title: 'Review Direct Purchase Order',
            url: '/review/direct_purchase_orders'
        };

        const approveBtn     = $q('#approveBtn').first();
        const rejectBtn      = $q('#rejectBtn').first();
        const directVoidBtn  = $q('#directVoidBtn').first();
        const approveVoidBtn = $q('#approveVoidBtn').first();
        const rejectVoidBtn  = $q('#rejectVoidBtn').first();

        if (directVoidBtn) {
            directVoidBtn.onclick = async () => {
                if (!await window.util.confirm('Are you sure you want to VOID this Direct Purchase Order? This action cannot be undone.')) {
                    return false;
                }

                window.util.blockUI();
                let res = await window.util.$post('/api/review/direct_purchase_order/void', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    window.util.navTo('/review/direct_purchase_orders');
                } else {
                    window.util.showMsg(res);
                }
            };
        }

        if (approveBtn) {
            approveBtn.onclick = async () => {
                if (!await window.util.confirm('Are you sure you want to APPROVE this Direct Purchase Order?')) {
                    return false;
                }

                window.util.blockUI();
                let res = await window.util.$post('/api/review/direct_purchase_order/approve', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    window.util.navTo('/review/direct_purchase_orders');
                } else {
                    window.util.showMsg(res);
                }
            };
        }

        if (rejectBtn) {
            rejectBtn.onclick = async () => {
                if (!await window.util.confirm('Are you sure you want to REJECT this Direct Purchase Order?')) {
                    return false;
                }

                window.util.blockUI();
                let res = await window.util.$post('/api/review/direct_purchase_order/reject', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    window.util.navTo('/review/direct_purchase_orders');
                } else {
                    window.util.showMsg(res);
                }
            };
        }

        if (approveVoidBtn) {
            approveVoidBtn.onclick = async () => {
                if (!await window.util.confirm('Are you sure you want to VOID this Direct Purchase Order?')) {
                    return false;
                }

                window.util.blockUI();
                let res = await window.util.$post('/api/review/direct_purchase_order/void', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    window.util.navTo('/review/direct_purchase_orders');
                } else {
                    window.util.showMsg(res);
                }
            };
        }

        if (rejectVoidBtn) {
            rejectVoidBtn.onclick = async () => {
                if (!await window.util.confirm('Are you sure you want to REJECT the void request for this Direct Purchase Order?')) {
                    return false;
                }

                window.util.blockUI();
                let res = await window.util.$post('/api/review/direct_purchase_order/reject_void', {
                    id: dpoId
                });
                window.util.unblockUI();

                if (res.status === 1) {
                    window.util.navTo('/review/direct_purchase_orders');
                } else {
                    window.util.showMsg(res);
                }
            };
        }
    </script>
</div>
@endsection
