@extends('layouts.app')

@section('content')
<div id="content" class="pb-5 mb-5">
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
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 56px; height: 56px;">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-success">Batch Approved Successfully</h3>
                            <p class="text-muted mb-0 small">The selected purchase orders have been approved and updated.</p>
                        </div>
                    @else
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width: 56px; height: 56px;">
                            <i class="bi bi-x-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-danger">Batch Rejected Successfully</h3>
                            <p class="text-muted mb-0 small">The selected purchase orders have been rejected and updated.</p>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="bg-light border rounded px-3 py-2 text-center">
                        <div class="text-muted small">Projects</div>
                        <div class="fw-bold fs-6">{{ count($pos) }}</div>
                    </div>
                    @php
                        $total_po_count = 0;
                        foreach($pos as $p_items) {
                            $total_po_count += count($p_items);
                        }
                    @endphp
                    <div class="bg-light border rounded px-3 py-2 text-center">
                        <div class="text-muted small">Total Orders</div>
                        <div class="fw-bold fs-6">{{ $total_po_count }}</div>
                    </div>
                    <div class="bg-light border rounded px-3 py-2 text-center">
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
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-primary"><i class="bi bi-folder2-open fs-5"></i></span>
                            <h5 class="fw-bold mb-0">{{ $project_arr[$project_id]->name ?? ('Project #' . $project_id) }}</h5>
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
                                <div class="card h-100 border rounded-3 p-3 position-relative" style="background: #fafafa;">
                                    {{-- PO Header: ID & Status Pill --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <a href="/review/purchase_order/{{$item['po']->id}}" target="_blank" class="fw-bold font-monospace text-decoration-none text-primary fs-6">
                                            <i class="bi bi-file-earmark-text me-1"></i>#{{ str_pad($item['po']->id, 6, '0', STR_PAD_LEFT) }}
                                        </a>

                                        <div>
                                            @if(isset($original_statuses[$item['po']->id]))
                                                <span class="badge bg-light text-muted border py-1 px-2 font-monospace">{{ $original_statuses[$item['po']->id] }}</span>
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
                                    <div class="small text-muted mb-2">
                                        <div class="d-flex align-items-center gap-1 text-truncate mb-1" title="{{ $item['po']->Supplier->name ?? 'No Supplier' }}">
                                            <i class="bi bi-shop text-secondary"></i>
                                            <span class="text-truncate">{{ $item['po']->Supplier->name ?? 'N/A' }}</span>
                                        </div>
                                        @if(isset($item['po']->PaymentTerm))
                                            <div class="d-flex align-items-center gap-1 text-truncate">
                                                <i class="bi bi-credit-card text-secondary"></i>
                                                <span class="text-truncate">{{ $item['po']->PaymentTerm->name }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <hr class="my-2 opacity-25">

                                    {{-- Footer Metadata: Date & Validation Flag --}}
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-1">
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1"></i>{{ $item['created_at'] ?? '' }}
                                        </span>

                                        @if($item['flag'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2 small">
                                                <i class="bi bi-check-circle-fill me-1"></i>Valid
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill py-1 px-2 small" title="{{ implode('; ', $item['failed'] ?? []) }}">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Issues
                                            </span>
                                        @endif
                                    </div>

                                    @if(!$item['flag'] && !empty($item['failed']))
                                        <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded text-danger small">
                                            @foreach($item['failed'] as $f)
                                                <div><i class="bi bi-dot"></i> {{ $f }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </form>

    {{-- Payment Terms Summary Section --}}
    <div class="card border-0 shadow-sm mt-4 mb-5">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <div class="d-flex align-items-center gap-2">
                <span class="text-primary"><i class="bi bi-wallet2 fs-5"></i></span>
                <h5 class="fw-bold mb-0">Summary by Payment Terms</h5>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                @foreach($total_per_payment_terms as $term_id => $amount)
                    <div class="col">
                        <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fw-semibold text-secondary text-truncate">
                                    <i class="bi bi-credit-card me-1"></i>
                                    {{ $payment_terms_arr[$term_id]->name ?? ('Term #' . $term_id) }}
                                </span>
                            </div>
                            <div class="fs-5 fw-bold text-dark font-monospace">
                                ₱ {{ number_format($amount, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Grand Total Card --}}
                <div class="col">
                    <div class="p-3 rounded-3 border border-primary bg-primary bg-opacity-10 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-bold text-primary">
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

    {{-- Fixed / Floating Bottom Action Menu --}}
    <div class="footer-action-menu shadow-lg border-top py-3 px-3 px-md-5">
        <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center text-muted small">
                <i class="bi bi-info-circle text-warning me-2 fs-5"></i>
                <span>You can revert this batch operation once before leaving this page.</span>
            </div>

            <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
                <button class="btn btn-warning px-4 py-2 fw-semibold d-flex align-items-center justify-content-center" id="revertBtn">
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
