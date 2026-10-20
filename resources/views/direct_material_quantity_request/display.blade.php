@extends('layouts.app')

@section('content')
<div id="content">

<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <div class="d-flex justify-content-between align-items-center">
            <ul>
                <li>
                    <a href="/direct_material_quantity_requests">
                        <span>Direct Material Request</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>#{{ $request->formatted_id }}</span>
                    </a>
                </li>
            </ul>
            @if($request->status == 'PEND')
                <button class="btn btn-outline-primary" id="reviewLinkBtn">
                    <i class="bi bi-link-45deg"></i> Review Link
                </button>
            @endif
        </div>
    </div>
    <hr>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Direct Material Request #{{ $request->formatted_id }}</h5>
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
                    <strong>Created By:</strong> {{ $request->Creator ? $request->Creator->name : 'Unknown' }}
                </div>
                <div class="col-lg-6">
                    <strong>Created At:</strong> {{ $request->created_at }}
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
            <h5 class="mb-0">Items</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 65%">Material Item</th>
                        <th style="width: 30%" class="text-end">Quantity</th>
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
        <a href="/direct_material_quantity_requests" class="btn btn-secondary me-2">Back to List</a>
        <a href="/direct_material_quantity_request/print/{{$request->id}}" target="_blank" class="btn btn-info me-2 text-white">
            <i class="bi bi-printer"></i> Print
        </a>

        @if($request->status == 'PEND')
            <button type="button" class="btn btn-outline-primary me-2 review-link-btn">
                <i class="bi bi-link-45deg"></i> Review Link
            </button>
        @endif

        @if(($request->status == 'DRFT' || $request->status == 'PEND') && ($canDelete ?? $canEdit))
            <button type="button" class="btn btn-danger me-2" id="deleteBtn">Delete</button>
        @endif

        @if($request->status == 'DRFT' && $canEdit)
            <button type="button" class="btn btn-success" id="submitBtn">Submit For Review</button>
        @endif

        @if($request->status == 'APRV')
            <a href="/direct_material_canvass/{{$request->id}}" class="btn btn-primary">
                <i class="bi bi-tag"></i> View Canvass
            </a>
        @endif
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    const reqId = {{ $request->id }};
    const submitBtn = $q('#submitBtn').first();
    const deleteBtn = $q('#deleteBtn').first();

    document.querySelectorAll('#reviewLinkBtn, .review-link-btn').forEach(btn => {
        btn.onclick = async () => {
            await window.util.copyToClipboard('{{ url("/review/direct_material_quantity_request/".$request->id) }}');
            alert('Review Link for Direct Material Request #{{ $request->formatted_id }} copied!');
        };
    });

    if (submitBtn) {
        submitBtn.onclick = async () => {
            if (!confirm('Are you sure you want to submit this request for review?')) return;
            window.util.blockUI();
            let res = await window.util.$post('/api/direct_material_quantity_request/submit_for_review', { id: reqId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                alert(res.message || 'Error submitting request');
            }
        };
    }

    if (deleteBtn) {
        deleteBtn.onclick = async () => {
            if (!confirm('Are you sure you want to delete this request?')) return;
            window.util.blockUI();
            let res = await window.util.$post('/api/direct_material_quantity_request/delete', { id: reqId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.util.navTo('/direct_material_quantity_requests');
            } else {
                alert(res.message || 'Error deleting request');
            }
        };
    }
</script>
</div>
@endsection
