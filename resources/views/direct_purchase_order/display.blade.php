@extends('layouts.app')

@section('content')
<div id="content">

    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
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
                            <th class="text-end">Quantity</th>
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
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
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

        <!-- Action Controls -->
        <div class="text-end mb-5">
            <a href="/direct_purchase_order" class="btn btn-secondary me-2">Back to List</a>

            @if($direct_purchase_order->status == 'DRFT')
                <button type="button" class="btn btn-danger me-2" id="deleteBtn">Delete</button>
                <button type="button" class="btn btn-success" id="submitBtn">Submit for Review</button>
            @endif

            @if($direct_purchase_order->status == 'APRV')
                <a href="/direct_purchase_order/print/{{$direct_purchase_order->id}}" target="_blank" class="btn btn-info me-2 text-white">
                    <i class="bi bi-printer"></i> Print
                </a>
                <button type="button" class="btn btn-warning" id="voidBtn">Request Void</button>
            @endif
        </div>

    </div>

    <script type="module">
        import {$q} from '/adarna.js';

        const submitBtn = $q('#submitBtn').first();
        const deleteBtn = $q('#deleteBtn').first();
        const voidBtn   = $q('#voidBtn').first();

        const dpoId = {{ $direct_purchase_order->id }};

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
    </script>
</div>
@endsection
