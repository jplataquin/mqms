@extends('layouts.app')

@section('content')
<style>
    /* Scope dark-on-white high contrast theme for bulk action result */
    #bulk-action-content {
        color: #1e293b;
        padding-bottom: 130px;
    }
    #bulk-action-content .card {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
    #bulk-action-content .card-header {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    #bulk-action-content .po-item-card {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.625rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    #bulk-action-content .po-item-card:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.08);
    }
    #bulk-action-content .summary-box {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
    }
    #bulk-action-content .summary-grand-total {
        background-color: #eff6ff !important;
        border: 1.5px solid #3b82f6 !important;
        color: #1e3a8a !important;
    }
    #bulk-action-content .text-muted {
        color: #64748b !important;
    }
    #bulk-action-content .text-dark {
        color: #0f172a !important;
    }
    #bulk-action-content .stat-badge {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        color: #1e293b !important;
    }

    /* Full-width sticky action bar */
    .bulk-action-footer {
        position: fixed !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        z-index: 1050 !important;
        background-color: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08) !important;
    }
</style>

<div id="bulk-action-content">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/purchase_orders">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="/review/bulk/purchase_orders">
                    <span>
                       Bulk PO
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Action Result
                    </span>
                    <i class="ms-2 bi bi-check2-circle"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    {{-- Result Header Banner --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($target_status_text == 'Approve')
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 56px; height: 56px; min-width: 56px;">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-success">Batch Approved Successfully</h3>
                            <p class="text-muted mb-0 small">The selected purchase orders have been approved and updated in the system.</p>
                        </div>
                    @else
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width: 56px; height: 56px; min-width: 56px;">
                            <i class="bi bi-x-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-danger">Batch Rejected Successfully</h3>
                            <p class="text-muted mb-0 small">The selected purchase orders have been rejected and updated in the system.</p>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="stat-badge rounded px-3 py-2 text-center">
                        <div class="text-muted small">Projects</div>
                        <div class="fw-bold fs-6 text-dark">{{ count($pos) }}</div>
                    </div>
                    @php
                        $total_po_count = 0;
                        foreach($pos as $p_items) {
                            $total_po_count += count($p_items);
                        }
                    @endphp
                    <div class="stat-badge rounded px-3 py-2 text-center">
                        <div class="text-muted small">Total Orders</div>
                        <div class="fw-bold fs-6 text-dark">{{ $total_po_count }}</div>
                    </div>
                    <div class="stat-badge rounded px-3 py-2 text-center">
                        <div class="text-muted small">Total Amount</div>
                        <div class="fw-bold fs-6 text-primary">₱ {{ number_format($grand_total, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form with Hidden Inputs for Revert --}}
    <form id="form">
        {{-- List of POs grouped by Project --}}
        @foreach($pos as $project_id => $po_list)
            @php
                $project_subtotal = 0;
                foreach($po_list as $item) {
                    $project_subtotal += ($item['total'] ?? 0);
                }
            @endphp

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 px-3 px-md-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-primary"><i class="bi bi-folder2-open fs-5"></i></span>
                            <h5 class="fw-bold mb-0 text-dark">{{ $project_arr[$project_id]->name ?? ('Project #' . $project_id) }}</h5>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill ms-2">{{ count($po_list) }} PO{{ count($po_list) > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="text-muted small">
                            Subtotal: <span class="fw-bold text-dark fs-6">₱ {{ number_format($project_subtotal, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4">
                        @foreach($po_list as $item)
                            <input type="hidden" value="{{$item['po']->id}}" name="po[]"/>
                            @php
                                $orig_status = $original_statuses[$item['po']->id] ?? ($item['po']->status == 'VOID' ? 'REVO' : ($item['po']->status == 'PEND' ? 'REVO' : 'PEND'));
                            @endphp
                            <input type="hidden" class="revert-status-input" data-id="{{$item['po']->id}}" value="{{$orig_status}}" name="original_status[{{$item['po']->id}}]"/>

                            <div class="col">
                                <div class="po-item-card h-100 p-3 d-flex flex-column justify-content-between">
                                    {{-- PO Header: ID & Status Transition Pill --}}
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <a href="/review/purchase_order/{{$item['po']->id}}" target="_blank" class="fw-bold font-monospace text-decoration-none text-primary fs-6">
                                                <i class="bi bi-file-earmark-text me-1"></i>#{{ str_pad($item['po']->id, 6, '0', STR_PAD_LEFT) }}
                                            </a>

                                            <div class="d-flex align-items-center">
                                                @if(isset($original_statuses[$item['po']->id]))
                                                    <span class="badge bg-light text-dark border py-1 px-2 font-monospace">{{ $original_statuses[$item['po']->id] }}</span>
                                                    <i class="bi bi-arrow-right text-muted small mx-1"></i>
                                                    @if($item['po']->status == 'APRV')
                                                        <span class="badge bg-success py-1 px-2 font-monospace">APRV</span>
                                                    @elseif($item['po']->status == 'REJC')
                                                        <span class="badge bg-danger py-1 px-2 font-monospace">REJC</span>
                                                    @elseif($item['po']->status == 'VOID')
                                                        <span class="badge bg-dark py-1 px-2 font-monospace">VOID</span>
                                                    @else
                                                        <span class="badge bg-secondary py-1 px-2 font-monospace">{{ $item['po']->status }}</span>
                                                    @endif
                                                @else
                                                    @if($item['po']->status == 'APRV')
                                                        <span class="badge bg-success py-1 px-2 font-monospace">APRV</span>
                                                    @elseif($item['po']->status == 'REJC')
                                                        <span class="badge bg-danger py-1 px-2 font-monospace">REJC</span>
                                                    @elseif($item['po']->status == 'VOID')
                                                        <span class="badge bg-dark py-1 px-2 font-monospace">VOID</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Amount --}}
                                        <div class="my-2">
                                            <div class="text-muted small">Total Amount</div>
                                            <div class="fs-5 fw-bold text-dark font-monospace">
                                                ₱ {{ number_format($item['total'], 2) }}
                                            </div>
                                        </div>

                                        {{-- Supplier & Payment Term --}}
                                        <div class="small mb-2">
                                            <div class="d-flex align-items-center gap-1 text-truncate mb-1" style="color: #475569;" title="{{ $item['po']->Supplier->name ?? 'No Supplier' }}">
                                                <i class="bi bi-shop text-muted"></i>
                                                <span class="text-truncate">{{ $item['po']->Supplier->name ?? 'N/A' }}</span>
                                            </div>
                                            @if(isset($item['po']->PaymentTerm))
                                                <div class="d-flex align-items-center gap-1 text-truncate" style="color: #475569;">
                                                    <i class="bi bi-credit-card text-muted"></i>
                                                    <span class="text-truncate">{{ $item['po']->PaymentTerm->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <hr class="my-2" style="border-color: #e2e8f0;">

                                        {{-- Footer Metadata: Date & Validation Flag --}}
                                        <div class="d-flex justify-content-between align-items-center pt-1">
                                            <span class="small" style="color: #64748b;">
                                                <i class="bi bi-clock me-1"></i>{{ $item['created_at'] ?? '' }}
                                            </span>

                                            @if($item['flag'])
                                                <span class="badge rounded-pill py-1 px-2 small" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Valid
                                                </span>
                                            @else
                                                <span class="badge rounded-pill py-1 px-2 small" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;" title="{{ implode('; ', $item['failed'] ?? []) }}">
                                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Issues
                                                </span>
                                            @endif
                                        </div>

                                        @if(!$item['flag'] && !empty($item['failed']))
                                            <div class="mt-2 p-2 rounded small" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                                                @foreach($item['failed'] as $f)
                                                    <div><i class="bi bi-dot"></i> {{ $f }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </form>

    {{-- Payment Terms Summary Section --}}
    <div class="card border-0 shadow-sm mt-4 mb-4">
        <div class="card-header bg-white py-3 px-3 px-md-4">
            <div class="d-flex align-items-center gap-2">
                <span class="text-primary fs-5"><i class="bi bi-wallet2"></i></span>
                <h5 class="fw-bold mb-0 text-dark">Summary by Payment Terms</h5>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                @foreach($total_per_payment_terms as $term_id => $amount)
                    <div class="col">
                        <div class="summary-box p-3 rounded-3 h-100 d-flex flex-column justify-content-between">
                            <div class="small fw-semibold mb-2 text-truncate" style="color: #475569;">
                                <i class="bi bi-credit-card me-1 text-muted"></i>
                                {{ $payment_terms_arr[$term_id]->name ?? ('Term #' . $term_id) }}
                            </div>
                            <div class="fs-5 fw-bold text-dark font-monospace">
                                ₱ {{ number_format($amount, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Grand Total Card --}}
                <div class="col">
                    <div class="summary-grand-total p-3 rounded-3 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="small fw-bold text-primary">
                                <i class="bi bi-cash-stack me-1"></i> Grand Total
                            </span>
                            <span class="badge bg-primary text-white rounded-pill">{{ $total_po_count }} PO{{ $total_po_count > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="fs-4 fw-bold text-primary font-monospace">
                            ₱ {{ number_format($grand_total, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Full-Width Bottom Action Bar --}}
    <div class="bulk-action-footer py-3 px-3 px-md-5">
        <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center small" style="color: #475569;">
                <i class="bi bi-info-circle text-warning fs-5 me-2"></i>
                <span>You can revert this batch operation once before leaving this page.</span>
            </div>

            <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
                <button class="btn btn-warning px-4 py-2 fw-semibold d-flex align-items-center justify-content-center text-dark" id="revertBtn">
                    <i class="bi bi-arrow-counterclockwise me-2 fs-6"></i> Revert
                </button>
                <button id="cancelBtn" class="btn btn-secondary px-4 py-2 d-flex align-items-center justify-content-center">
                    <i class="bi bi-check2 me-2 fs-6"></i> Done
                </button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    const revertBtn = $q('#revertBtn').first();
    const cancelBtn = $q('#cancelBtn').first();

    if(cancelBtn){
        cancelBtn.onclick = (e) => {
            e.preventDefault();
            window.location.href = '/review/bulk/purchase_orders';
        };
    }

    if(revertBtn){
        revertBtn.onclick = async (e) => {
            e.preventDefault();

            let ans = await window.util.confirm('Are you sure you want to revert these records? This can only be done once.');

            if(ans){
                window.util.blockUI();

                const data = {};
                const inputs = document.querySelectorAll('.revert-status-input');
                inputs.forEach(input => {
                    if (input.dataset.id && input.value) {
                        data[input.dataset.id] = input.value;
                    }
                });

                if (Object.keys(data).length === 0) {
                    window.util.unblockUI();
                    window.util.alert('No records available to revert.', 'error');
                    return;
                }

                revertBtn.disabled = true;

                window.util.$post('/api/review/bulk/purchase_order/revert', {
                    data: JSON.stringify(data)
                }).then(reply => {
                    window.util.unblockUI();

                    if(reply.status == 1){
                        window.util.navTo('/review/bulk/purchase_orders');
                    } else {
                        revertBtn.disabled = false;
                        window.util.showMsg(reply);
                    }
                }).catch(err => {
                    revertBtn.disabled = false;
                    window.util.unblockUI();
                    window.util.alert('Error', 'An unexpected error occurred');
                });
            }
        };
    }
</script>
@endsection
