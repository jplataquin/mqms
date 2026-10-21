@extends('layouts.app')

@section('content')
<div id="content">

<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/direct_material_canvass">
                    <span>Review Direct Material Canvass</span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>#{{ $request->formatted_id }}</span>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Review Canvass for Request #{{ $request->formatted_id }}</h5>
            <span class="badge bg-secondary fs-6">{{ $request->status }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <strong>Project:</strong> {{ $request->Project ? $request->Project->name : 'None' }}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Section:</strong> {{ $request->Section ? $request->Section->name : 'None' }}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Requested By:</strong> {{ $request->Creator ? $request->Creator->name : 'Unknown' }}
                </div>
                <div class="col-md-6 mb-2">
                    <strong>Date Requested:</strong> {{ $request->created_at }}
                </div>
            </div>
        </div>
    </div>

    <!-- Items with Quotes for Approval -->
    @foreach($items as $item)
    <div class="card mb-4 border-0 shadow-sm overflow-hidden">
        <!-- Prominent Item & Quantity Hero Banner -->
        <div class="card-header border-bottom p-4">
            <div class="row align-items-center g-3">
                <!-- Left: Item Identification -->
                <div class="col-lg-7 col-md-6">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge bg-dark text-white px-2 py-1 text-uppercase font-monospace" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                            Item #{{ $loop->iteration }}
                        </span>
                        <h4 class="fw-bolder text-white mb-0 lh-sm">
                            {{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Material' }}
                        </h4>
                    </div>
                    @if($item->remarks)
                        <div class="text-white small mt-2">
                            <i class="bi bi-chat-left-text me-1"></i>Remarks: <strong class="text-white">{{ $item->remarks }}</strong>
                        </div>
                    @endif
                </div>

                <!-- Right: Quantity Callout -->
                <div class="col-lg-5 col-md-6">
                    <div class="d-flex align-items-center justify-content-md-end gap-3 flex-wrap">
                        <!-- Hero Quantity Display Box -->
                        <div class="px-4 py-2 rounded-3 text-center border border-2 border-primary bg-primary bg-opacity-10 shadow-sm" style="min-width: 140px;">
                            <div class="text-uppercase fw-bold text-primary" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Required Quantity
                            </div>
                            <div class="mt-1">
                                <span class="fs-2 fw-bolder font-monospace text-primary lh-1">
                                    {{ number_format($item->quantity, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table of Quotes for Approval -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-white">
                    <thead class="border-bottom text-uppercase small text-white" style="letter-spacing: 0.04em;">
                        <tr>
                            <th style="width: 26%; padding-left: 1.5rem;" class="text-white">Supplier</th>
                            <th style="width: 20%" class="text-white">Payment Term</th>
                            <th style="width: 16%" class="text-end text-white">Unit Price</th>
                            <th style="width: 16%" class="text-end text-white">Total Amount</th>
                            <th style="width: 10%" class="text-center text-white">Status</th>
                            <th style="width: 12%; padding-right: 1.5rem;" class="text-end text-white">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->Canvasses as $canvass)
                            <tr>
                                <td style="padding-left: 1.5rem;" class="fw-semibold text-white">
                                    <i class="bi bi-building me-1 text-white"></i>
                                    {{ $canvass->Supplier ? $canvass->Supplier->name : '-' }}
                                </td>
                                <td class="text-white">{{ $canvass->PaymentTerm ? $canvass->PaymentTerm->text : '-' }}</td>
                                <td class="text-end font-monospace fw-semibold text-white">{{ number_format($canvass->price, 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-white">{{ number_format($canvass->price * $item->quantity, 2) }}</td>
                                <td class="text-center">
                                    @if($canvass->status == 'APRV')
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                    @elseif($canvass->status == 'DPRV')
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Disapproved</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock me-1"></i>Pending</span>
                                    @endif
                                </td>
                                <td style="padding-right: 1.5rem;" class="text-end text-white text-nowrap">
                                    @if($canvass->status == 'PEND')
                                        @if($canReject)
                                            <button type="button" class="btn btn-sm btn-outline-danger reject-btn me-1" data-id="{{ $canvass->id }}" title="Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                        @if($canApprove)
                                            <button type="button" class="btn btn-sm btn-success approve-btn" data-id="{{ $canvass->id }}" title="Approve">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-white">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-white py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 text-white opacity-75"></i>
                                    <span>No quotes submitted for this item.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    <div class="text-end mb-5">
        <a href="/review/direct_material_canvass" class="btn btn-secondary">Back to Review List</a>
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.onclick = async () => {
            if (!await window.util.confirm('Are you sure you want to approve this canvass price?')) return;
            const canvassId = btn.getAttribute('data-id');
            window.util.blockUI();
            let res = await window.util.$post('/api/review/direct_material_canvass/approve', { id: canvassId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                window.util.showMsg(res);
            }
        };
    });

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.onclick = async () => {
            if (!await window.util.confirm('Are you sure you want to reject this canvass price?')) return;
            const canvassId = btn.getAttribute('data-id');
            window.util.blockUI();
            let res = await window.util.$post('/api/review/direct_material_canvass/reject', { id: canvassId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                window.util.showMsg(res);
            }
        };
    });
</script>
</div>
@endsection
