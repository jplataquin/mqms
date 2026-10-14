@extends('layouts.app')

@section('content')
<style>
    @keyframes defectSlideInRight {
        from {
            opacity: 0;
            transform: translateX(18px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    @keyframes defectSlideInLeft {
        from {
            opacity: 0;
            transform: translateX(-18px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .defect-slide-next {
        animation: defectSlideInRight 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .defect-slide-prev {
        animation: defectSlideInLeft 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Fixed Bottom Action Bar: Constrained on Desktop, Full Width & Horizontally Scrollable on Mobile */
    .bulk-action-bar {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 250px;
        z-index: 1050;
        background-color: #1e2227;
        border-top: 1px solid #373b3e;
        box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.4);
    }

    @media (max-width: 961px) {
        .bulk-action-bar {
            left: 0 !important;
            width: 100vw !important;
        }
    }

    .bulk-action-bar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        padding: 0.75rem 1.25rem;
    }

    .bulk-action-bar-inner::-webkit-scrollbar {
        height: 4px;
    }
    .bulk-action-bar-inner::-webkit-scrollbar-thumb {
        background: #495057;
        border-radius: 4px;
    }
</style>
<div id="content" class="pb-5 mb-4">
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
    <div class="card border border-secondary mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if($target_status_text == 'Approve')
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-success bg-opacity-25 text-success" style="width: 56px; height: 56px; min-width: 56px;">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-success">Batch Approved Successfully</h3>
                            <p class="text-muted mb-0 small">The selected purchase orders have been approved and updated in the system.</p>
                        </div>
                    @else
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-25 text-danger" style="width: 56px; height: 56px; min-width: 56px;">
                            <i class="bi bi-x-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1 text-danger">Batch Rejected Successfully</h3>
                            <p class="text-muted mb-0 small">The selected purchase orders have been rejected and updated in the system.</p>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="border border-secondary rounded px-3 py-2 text-center bg-dark bg-opacity-50">
                        <div class="text-muted small">Projects</div>
                        <div class="fw-bold fs-6">{{ count($pos) }}</div>
                    </div>
                    @php
                        $total_po_count = 0;
                        foreach($pos as $p_items) {
                            $total_po_count += count($p_items);
                        }
                    @endphp
                    <div class="border border-secondary rounded px-3 py-2 text-center bg-dark bg-opacity-50">
                        <div class="text-muted small">Total Orders</div>
                        <div class="fw-bold fs-6">{{ $total_po_count }}</div>
                    </div>
                    <div class="border border-info border-opacity-50 rounded px-3 py-2 text-center bg-info bg-opacity-10">
                        <div class="text-info small">Total Amount</div>
                        <div class="fw-bold fs-6 text-info">₱ {{ number_format($grand_total, 2) }}</div>
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

            <div class="card border border-secondary mb-4">
                <div class="card-header bg-dark bg-opacity-50 border-bottom border-secondary py-3 px-3 px-md-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-info fs-5"><i class="bi bi-folder2-open"></i></span>
                            <h5 class="fw-bold mb-0">{{ $project_arr[$project_id]->name ?? ('Project #' . $project_id) }}</h5>
                            <span class="badge bg-secondary rounded-pill ms-2">{{ count($po_list) }} PO{{ count($po_list) > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="text-muted small">
                            Subtotal: <span class="fw-bold text-light fs-6">₱ {{ number_format($project_subtotal, 2) }}</span>
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
                                <div class="card h-100 border border-secondary p-3 d-flex flex-column justify-content-between bg-dark bg-opacity-25">
                                    {{-- PO Header: ID & Status Transition Pill --}}
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <a href="/review/purchase_order/{{$item['po']->id}}" target="_blank" class="fw-bold font-monospace text-decoration-none text-info fs-6">
                                                <i class="bi bi-file-earmark-text me-1"></i>#{{ str_pad($item['po']->id, 6, '0', STR_PAD_LEFT) }}
                                            </a>

                                            <div class="d-flex align-items-center">
                                                @if(isset($original_statuses[$item['po']->id]))
                                                    <span class="badge bg-white text-dark py-1 px-2 font-monospace">{{ $original_statuses[$item['po']->id] }}</span>
                                                    <i class="bi bi-arrow-right text-muted small mx-1"></i>
                                                    @if($item['po']->status == 'APRV')
                                                        <span class="badge bg-success text-white py-1 px-2 font-monospace">APRV</span>
                                                    @elseif($item['po']->status == 'REJC')
                                                        <span class="badge bg-danger text-white py-1 px-2 font-monospace">REJC</span>
                                                    @elseif($item['po']->status == 'VOID')
                                                        <span class="badge bg-secondary text-white py-1 px-2 font-monospace">VOID</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark py-1 px-2 font-monospace">{{ $item['po']->status }}</span>
                                                    @endif
                                                @else
                                                    @if($item['po']->status == 'APRV')
                                                        <span class="badge bg-success text-white py-1 px-2 font-monospace">APRV</span>
                                                    @elseif($item['po']->status == 'REJC')
                                                        <span class="badge bg-danger text-white py-1 px-2 font-monospace">REJC</span>
                                                    @elseif($item['po']->status == 'VOID')
                                                        <span class="badge bg-secondary text-white py-1 px-2 font-monospace">VOID</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Amount --}}
                                        <div class="my-2">
                                            <div class="text-muted small">Total Amount</div>
                                            <div class="fs-5 fw-bold text-light font-monospace">
                                                ₱ {{ number_format($item['total'], 2) }}
                                            </div>
                                        </div>

                                        {{-- Supplier & Payment Term --}}
                                        <div class="small mb-2">
                                            <div class="d-flex align-items-center gap-1 text-truncate mb-1 text-muted" title="{{ $item['po']->Supplier->name ?? 'No Supplier' }}">
                                                <i class="bi bi-shop"></i>
                                                <span class="text-truncate">{{ $item['po']->Supplier->name ?? 'N/A' }}</span>
                                            </div>
                                            @if(isset($item['po']->PaymentTerm))
                                                <div class="d-flex align-items-center gap-1 text-truncate text-muted">
                                                    <i class="bi bi-credit-card"></i>
                                                    <span class="text-truncate">{{ $item['po']->PaymentTerm->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <hr class="my-2 border-secondary">

                                        {{-- Footer Metadata: Date & Validation Flag --}}
                                        <div class="d-flex justify-content-between align-items-center pt-1">
                                            <span class="small text-muted">
                                                <i class="bi bi-clock me-1"></i>{{ $item['created_at'] ?? '' }}
                                            </span>

                                            @if($item['flag'])
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill py-1 px-2 small">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Valid
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill py-1 px-2 small" title="{{ implode('; ', $item['failed'] ?? []) }}">
                                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Issues
                                                </span>
                                            @endif
                                        </div>

                                        @if(!$item['flag'] && !empty($item['failed']))
                                            @php
                                                $fails = $item['failed'];
                                                $total_fails = count($fails);
                                            @endphp
                                            @if($total_fails === 1)
                                                <div class="mt-2 p-2 rounded small bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.75rem; line-height: 1.3;">
                                                    <div class="d-flex align-items-start gap-1">
                                                        <i class="bi bi-exclamation-triangle-fill text-danger me-1 flex-shrink-0 mt-1" style="font-size: 0.75rem;"></i>
                                                        <span>{{ $fails[0] }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-2 p-2 rounded small bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 defect-carousel" data-total="{{ $total_fails }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div class="d-flex align-items-center gap-1 text-danger fw-semibold" style="font-size: 0.75rem;">
                                                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 0.75rem;"></i>
                                                            <span>Defects</span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span class="font-monospace text-danger me-1 defect-counter" style="font-size: 0.7rem;">1/{{ $total_fails }}</span>
                                                            <button type="button" class="btn btn-sm btn-link p-0 text-danger text-decoration-none defect-prev" style="line-height: 1; font-size: 0.85rem;">
                                                                <i class="bi bi-chevron-left"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-link p-0 text-danger text-decoration-none defect-next" style="line-height: 1; font-size: 0.85rem;">
                                                                <i class="bi bi-chevron-right"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="defect-slide-container" style="min-height: 38px; max-height: 54px; overflow-y: auto; font-size: 0.75rem; line-height: 1.3;">
                                                        @foreach($fails as $fIdx => $fMsg)
                                                            <div class="defect-slide {{ $fIdx === 0 ? '' : 'd-none' }}">{{ $fMsg }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
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
    <div class="card border border-secondary mb-4">
        <div class="card-header bg-dark bg-opacity-50 border-bottom border-secondary py-3 px-3 px-md-4">
            <div class="d-flex align-items-center gap-2">
                <span class="text-info fs-5"><i class="bi bi-wallet2"></i></span>
                <h5 class="fw-bold mb-0">Summary by Payment Terms</h5>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                @foreach($total_per_payment_terms as $term_id => $amount)
                    <div class="col">
                        <div class="p-3 rounded border border-secondary bg-dark bg-opacity-25 h-100 d-flex flex-column justify-content-between">
                            <div class="small fw-semibold text-muted mb-2 text-truncate">
                                <i class="bi bi-credit-card me-1"></i>
                                {{ $payment_terms_arr[$term_id]->name ?? ('Term #' . $term_id) }}
                            </div>
                            <div class="fs-5 fw-bold font-monospace text-light">
                                ₱ {{ number_format($amount, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Grand Total Card --}}
                <div class="col">
                    <div class="p-3 rounded border border-info border-opacity-50 bg-info bg-opacity-10 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="small fw-bold text-info">
                                <i class="bi bi-cash-stack me-1"></i> Grand Total
                            </span>
                            <span class="badge bg-info text-dark rounded-pill">{{ $total_po_count }} PO{{ $total_po_count > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="fs-4 fw-bold font-monospace text-info">
                            ₱ {{ number_format($grand_total, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fixed Bottom Action Bar: Full Width & Scrollable on Mobile, Constrained to Main on Desktop --}}
    <div class="bulk-action-bar">
        <div class="bulk-action-bar-inner">
            <div class="text-muted small flex-shrink-0 d-flex align-items-center">
                <i class="bi bi-info-circle text-warning fs-5 me-2 align-middle"></i>
                <span>You can revert this batch operation once before leaving this page.</span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button class="btn btn-warning px-3 py-2 fw-semibold d-flex align-items-center justify-content-center text-dark text-nowrap flex-shrink-0" id="revertBtn">
                    <i class="bi bi-arrow-counterclockwise me-1 fs-6"></i> Revert
                </button>
                <button id="cancelBtn" class="btn btn-secondary px-3 py-2 d-flex align-items-center justify-content-center text-nowrap flex-shrink-0">
                    <i class="bi bi-check2 me-1 fs-6"></i> Done
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

    document.querySelectorAll('.defect-carousel').forEach(carousel => {
        const total = parseInt(carousel.dataset.total);
        let current = 0;
        const counter = carousel.querySelector('.defect-counter');
        const slides = carousel.querySelectorAll('.defect-slide');
        const prevBtn = carousel.querySelector('.defect-prev');
        const nextBtn = carousel.querySelector('.defect-next');

        function updateSlide(idx, dir) {
            slides.forEach((s, i) => {
                if (i === idx) {
                    s.classList.remove('d-none', 'defect-slide-next', 'defect-slide-prev');
                    void s.offsetWidth;
                    s.classList.add(dir === 'next' ? 'defect-slide-next' : 'defect-slide-prev');
                } else {
                    s.classList.add('d-none');
                    s.classList.remove('defect-slide-next', 'defect-slide-prev');
                }
            });
            if (counter) counter.innerText = `${idx + 1}/${total}`;
        }

        if (prevBtn) {
            prevBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                current = (current - 1 + total) % total;
                updateSlide(current, 'prev');
            };
        }

        if (nextBtn) {
            nextBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                current = (current + 1) % total;
                updateSlide(current, 'next');
            };
        }
    });
</script>
@endsection
