@extends('layouts.app')

@section('content')
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/direct_material_quantity_requests">
                    <span>Review Direct Material Request</span>
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
            <h5 class="mb-0">Review Direct Material Request #{{ $request->formatted_id }}</h5>
            <span class="badge bg-light text-dark fs-6">{{ $request->status }}</span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <strong>Project:</strong> {{ $request->Project ? $request->Project->name : 'None' }}
                </div>
                <div class="col-lg-6">
                    <strong>Section:</strong> {{ $request->Section ? $request->Section->name : 'None' }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-6">
                    <strong>Requested By:</strong> {{ $request->Creator ? $request->Creator->name : 'Unknown' }}
                </div>
                <div class="col-lg-6">
                    <strong>Requested At:</strong> {{ $request->created_at }}
                </div>
            </div>
            @if($request->description)
            <div class="row mb-3">
                <div class="col-12">
                    <strong>Purpose / Description:</strong>
                    <p class="text-muted mb-0">{{ $request->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Requested Items</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 65%">Material Item</th>
                        <th style="width: 30%" class="text-end">Requested Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                        </tr>
                        @if($item->remarks)
                        <tr>
                            <td colspan="3" class="pt-1 pb-2 ps-4">
                                <span class="small text-muted fw-semibold">Remarks:</span> {{ $item->remarks }}
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end mb-5">
        <a href="/review/direct_material_quantity_requests" class="btn btn-secondary me-2">Back to Review List</a>

        @if($request->status == 'PEND')
            @if($canReject)
                <button type="button" class="btn btn-danger me-2" id="rejectBtn">Disapprove</button>
            @endif
            @if($canApprove)
                <button type="button" class="btn btn-success" id="approveBtn">Approve</button>
            @endif
        @endif
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    const reqId = {{ $request->id }};
    const approveBtn = $q('#approveBtn').first();
    const rejectBtn  = $q('#rejectBtn').first();

    if (approveBtn) {
        approveBtn.onclick = async () => {
            if (!confirm('Are you sure you want to approve this Direct Material Request?')) return;
            window.util.blockUI();
            let res = await window.util.$post('/api/review/direct_material_quantity_request/approve', { id: reqId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.util.navTo('/review/direct_material_quantity_requests');
            } else {
                alert(res.message || 'Error approving request');
            }
        };
    }

    if (rejectBtn) {
        rejectBtn.onclick = async () => {
            if (!confirm('Are you sure you want to disapprove this Direct Material Request?')) return;
            window.util.blockUI();
            let res = await window.util.$post('/api/review/direct_material_quantity_request/reject', { id: reqId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.util.navTo('/review/direct_material_quantity_requests');
            } else {
                alert(res.message || 'Error rejecting request');
            }
        };
    }
</script>
@endsection
