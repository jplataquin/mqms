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
            <span class="badge bg-light text-dark fs-6">{{ $request->status }}</span>
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
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold">
                {{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}
                <span class="badge bg-secondary ms-2">Qty: {{ number_format($item->quantity, 2) }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30%">Supplier</th>
                            <th style="width: 25%">Payment Term</th>
                            <th style="width: 15%" class="text-end">Price</th>
                            <th style="width: 15%" class="text-center">Status</th>
                            <th style="width: 15%" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->Canvasses as $canvass)
                            <tr>
                                <td>{{ $canvass->Supplier ? $canvass->Supplier->name : '-' }}</td>
                                <td>{{ $canvass->PaymentTerm ? $canvass->PaymentTerm->text : '-' }}</td>
                                <td class="text-end font-monospace">{{ number_format($canvass->price, 2) }}</td>
                                <td class="text-center">
                                    @if($canvass->status == 'APRV')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($canvass->status == 'DPRV')
                                        <span class="badge bg-danger">Disapproved</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($canvass->status == 'PEND')
                                        @if($canReject)
                                            <button type="button" class="btn btn-sm btn-outline-danger reject-btn me-1" data-id="{{ $canvass->id }}">
                                                Reject
                                            </button>
                                        @endif
                                        @if($canApprove)
                                            <button type="button" class="btn btn-sm btn-success approve-btn" data-id="{{ $canvass->id }}">
                                                Approve
                                            </button>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No quotes submitted for this item.</td>
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
            if (!confirm('Are you sure you want to approve this canvass price?')) return;
            const canvassId = btn.getAttribute('data-id');
            window.util.blockUI();
            let res = await window.util.$post('/api/review/direct_material_canvass/approve', { id: canvassId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                alert(res.message || 'Error approving canvass');
            }
        };
    });

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.onclick = async () => {
            if (!confirm('Are you sure you want to reject this canvass price?')) return;
            const canvassId = btn.getAttribute('data-id');
            window.util.blockUI();
            let res = await window.util.$post('/api/review/direct_material_canvass/reject', { id: canvassId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                alert(res.message || 'Error rejecting canvass');
            }
        };
    });
</script>
</div>
@endsection
