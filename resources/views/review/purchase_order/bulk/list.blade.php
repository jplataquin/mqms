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
                <a href="#" class="active">
                    <span>
                       Bulk PO
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    {{-- Header Banner matching success.blade.php --}}
    <div class="card border border-secondary mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-info bg-opacity-25 text-info" style="width: 56px; height: 56px; min-width: 56px;">
                        <i class="bi bi-stack fs-3"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-light">Bulk Purchase Order Review</h3>
                        <p class="text-muted mb-0 small">Select purchase orders below to approve or reject in batch.</p>
                    </div>
                </div>

                {{-- Master Batch Toggles --}}
                <div class="d-flex flex-wrap gap-3 align-items-center bg-dark bg-opacity-50 border border-secondary rounded p-2 px-3">
                    <div class="form-check m-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" value="" id="all_ok"/>
                        <label class="form-check-label text-success fw-semibold small" for="all_ok" style="cursor: pointer;">
                            <i class="bi bi-check-circle-fill me-1"></i>Select All Valid [✔]
                        </label>
                    </div>

                    <div class="vr bg-secondary opacity-50 d-none d-sm-block"></div>

                    <div class="form-check m-0 d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" value="" id="all_not_ok"/>
                        <label class="form-check-label text-danger fw-semibold small" for="all_not_ok" style="cursor: pointer;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>Select All Invalid [✖]
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Container where per-project cards are rendered --}}
    <div id="result_container"></div>

    {{-- Payment Terms Summary Section matching success.blade.php --}}
    <div class="card border border-secondary mb-4">
        <div class="card-header bg-dark bg-opacity-50 border-bottom border-secondary py-3 px-3 px-md-4">
            <div class="d-flex align-items-center gap-2">
                <span class="text-info fs-5"><i class="bi bi-wallet2"></i></span>
                <h5 class="fw-bold mb-0">Summary by Payment Terms</h5>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <div id="payment_terms_summary" class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4"></div>
        </div>
    </div>

    {{-- Fixed Bottom Action Bar: Full Width & Scrollable on Mobile, Constrained to Main on Desktop --}}
    <div class="bulk-action-bar">
        <div class="bulk-action-bar-inner">
            <div class="text-muted small flex-shrink-0 d-flex align-items-center">
                <i class="bi bi-check2-square text-info fs-5 me-2 align-middle"></i>
                <span>Selected: <strong class="text-light fs-6 font-monospace" id="selected_po_count">0</strong> purchase order(s)</span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button id="rejectBtn" class="btn btn-danger px-3 py-2 fw-semibold d-flex align-items-center justify-content-center text-nowrap flex-shrink-0">
                    <i class="bi bi-x-circle me-1 fs-6"></i> Reject
                </button>
                <button id="approveBtn" class="btn btn-primary px-3 py-2 fw-semibold d-flex align-items-center justify-content-center text-nowrap flex-shrink-0">
                    <i class="bi bi-check-circle me-1 fs-6"></i> Approve
                </button>
                <button id="cancelBtn" class="btn btn-secondary px-3 py-2 d-flex align-items-center justify-content-center text-nowrap flex-shrink-0">
                    <i class="bi bi-arrow-left me-1 fs-6"></i> Back
                </button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q, Template, $el} from '/adarna.js';

    const result_container      = $q('#result_container').first();
    const payment_terms_summary = $q('#payment_terms_summary').first();
    const selected_po_count     = $q('#selected_po_count').first();
    const all_ok                = $q('#all_ok').first();
    const all_not_ok            = $q('#all_not_ok').first();
    const approveBtn            = $q('#approveBtn').first();
    const rejectBtn             = $q('#rejectBtn').first();
    const cancelBtn             = $q('#cancelBtn').first();

    const t = new Template();
    let global_payment_terms = {};

    approveBtn.onclick = async ()=>{
        await approveSelection();
    };

    rejectBtn.onclick = async ()=>{
        await rejectSelection();
    };

    cancelBtn.onclick = ()=>{
        window.location.href = '/review/purchase_orders';
    };

    function updatePaymentTermsTotal(payment_terms){
        if (!payment_terms) {
            payment_terms = global_payment_terms;
        }

        payment_terms_summary.innerHTML = '';

        const t = new Template();
        let checkboxes = $q('.po[type="checkbox"]:checked').items();
        let summary = {};
        let selected_count = checkboxes.length;

        if (selected_po_count) {
            selected_po_count.innerText = selected_count;
        }

        checkboxes.forEach(c => {
            let payment_term_id = c.getAttribute('data-payment_term_id');
            if (typeof summary[payment_term_id] === 'undefined') {
                summary[payment_term_id] = 0;
            }
            summary[payment_term_id] += parseFloat(c.getAttribute('data-amount') || 0);
        });

        let grand_total = 0;

        for (let id in summary) {
            let term_name = (payment_terms && payment_terms[id]) ? (payment_terms[id].text || payment_terms[id].name) : ('Term #' + id);

            const summary_el = t.div({class: 'col'}, () => {
                t.div({class: 'p-3 rounded border border-secondary bg-dark bg-opacity-25 h-100 d-flex flex-column justify-content-between'}, () => {
                    t.div({class: 'small fw-semibold text-muted mb-2 text-truncate'}, () => {
                        t.i({class: 'bi bi-credit-card me-1'});
                        t.span(term_name);
                    });
                    t.div({class: 'fs-5 fw-bold font-monospace text-light'}, '₱ ' + window.util.numberFormat(summary[id], 2));
                });
            });

            grand_total += summary[id];
            payment_terms_summary.appendChild(summary_el);
        }

        // Grand Total Card matching success.blade.php
        payment_terms_summary.appendChild(t.div({class: 'col'}, () => {
            t.div({class: 'p-3 rounded border border-info border-opacity-50 bg-info bg-opacity-10 h-100 d-flex flex-column justify-content-between'}, () => {
                t.div({class: 'd-flex justify-content-between align-items-start mb-2'}, () => {
                    t.span({class: 'small fw-bold text-info'}, () => {
                        t.i({class: 'bi bi-cash-stack me-1'});
                        t.span('Grand Total');
                    });
                    t.span({class: 'badge bg-info text-dark rounded-pill'}, selected_count + ' PO' + (selected_count !== 1 ? 's' : ''));
                });
                t.div({class: 'fs-4 fw-bold font-monospace text-info'}, '₱ ' + window.util.numberFormat(grand_total, 2));
            });
        }));
    }

    function checkboxOnchangeController(payment_terms){
        updatePaymentTermsTotal(payment_terms);
    }

    async function submitAction(action, els){
        let ids = [];
        els.forEach(el => {
            ids.push(el.value);
        });

        const form = t.form({
            method: 'POST',
            action: '/review/bulk/purchase_order/action'
        });

        ids.forEach(id => {
            const input = t.input({type: 'hidden', name: 'ids[]', value: id});
            form.appendChild(input);
        });

        form.appendChild(
            t.input({type: 'hidden', name: 'action', value: action})
        );

        form.appendChild(
            t.input({type: 'hidden', name: '_token', value: '{{ csrf_token() }}'})
        );

        result_container.appendChild(form);

        setTimeout(() => {
            form.submit();
        }, 100);
    }

    async function approveSelection(){
        const po = $q('input[type="checkbox"].po:checked').items();

        if (po.length === 0) {
            await window.util.showMsg({ status: 0, message: 'Please select at least one purchase order.' });
            return false;
        }

        let ans = await window.util.confirm('Are you sure you want to APPROVE ' + po.length + ' items?');

        if (!ans) {
            return false;
        }

        submitAction('APRV', po);
    }

    async function rejectSelection(){
        const po = $q('input[type="checkbox"].po:checked').items();

        if (po.length === 0) {
            await window.util.showMsg({ status: 0, message: 'Please select at least one purchase order.' });
            return false;
        }

        let ans = await window.util.confirm('Are you sure you want to REJECT ' + po.length + ' items?');

        if (!ans) {
            return false;
        }

        submitAction('REJC', po);
    }

    all_ok.onchange = () => {
        let last_item = null;
        $q('.ok').items().forEach(item => {
            item.checked = all_ok.checked;
            last_item = item;
        });

        if (last_item) {
            last_item.onchange();
        } else {
            updatePaymentTermsTotal(global_payment_terms);
        }
    };

    all_not_ok.onchange = () => {
        let last_item = null;
        $q('.invalid').items().forEach(item => {
            item.checked = all_not_ok.checked;
            last_item = item;
        });

        if (last_item) {
            last_item.onchange();
        } else {
            updatePaymentTermsTotal(global_payment_terms);
        }
    };

    window.util.blockUI();

    window.util.$get('/api/review/bulk/purchase_order/list', {}).then(reply => {
        window.util.unblockUI();

        if (reply.status <= 0) {
            window.util.showMsg(reply);
            return false;
        }

        let result        = reply.data.result;
        let projects      = reply.data.projects;
        let suppliers     = reply.data.suppliers;
        let payment_terms = reply.data.payment_terms;
        global_payment_terms = payment_terms;

        let project_keys = Object.keys(result);

        if (project_keys.length === 0) {
            result_container.appendChild(t.div({class: 'card border border-secondary p-5 text-center my-4'}, () => {
                t.div({class: 'text-muted'}, () => {
                    t.i({class: 'bi bi-inbox fs-1 d-block mb-3 text-secondary'});
                    t.h5({class: 'fw-semibold text-light'}, 'No Pending Purchase Orders');
                    t.p({class: 'small mb-0 text-muted'}, 'All purchase orders have been reviewed or none are currently pending.');
                });
            }));
            updatePaymentTermsTotal(payment_terms);
            return;
        }

        project_keys.forEach(project_id => {
            let items = result[project_id];
            let proj_name = (projects && projects[project_id]) ? projects[project_id].name : ('Project #' + project_id);

            let project_subtotal = 0;
            items.forEach(it => {
                project_subtotal += parseFloat(it.total || 0);
            });

            const project_div = t.div({class: 'card border border-secondary mb-4'}, () => {
                // Project Card Header matching success.blade.php
                t.div({class: 'card-header bg-dark bg-opacity-50 border-bottom border-secondary py-3 px-3 px-md-4'}, () => {
                    t.div({class: 'd-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2'}, () => {
                        t.div({class: 'd-flex align-items-center gap-2'}, () => {
                            t.span({class: 'text-info fs-5'}, () => {
                                t.i({class: 'bi bi-folder2-open'});
                            });
                            t.h5({class: 'fw-bold mb-0 text-light'}, proj_name);
                            t.span({class: 'badge bg-secondary rounded-pill ms-2'}, items.length + ' PO' + (items.length > 1 ? 's' : ''));
                        });

                        t.div({class: 'd-flex align-items-center gap-3'}, () => {
                            t.div({class: 'text-muted small'}, () => {
                                t.span('Subtotal: ');
                                t.span({class: 'fw-bold text-light fs-6 font-monospace'}, '₱ ' + window.util.numberFormat(project_subtotal, 2));
                            });

                            t.div({class: 'form-check m-0 d-flex align-items-center gap-1'}, () => {
                                let chbx_project = t.input({
                                    class: 'form-check-input',
                                    type: 'checkbox',
                                    id: 'proj_chbx_' + project_id
                                });
                                t.label({
                                    class: 'form-check-label text-muted small',
                                    for: 'proj_chbx_' + project_id,
                                    style: 'cursor: pointer;'
                                }, 'Select All in Project');

                                chbx_project.onchange = () => {
                                    $q('.project_' + project_id).items().forEach(item => {
                                        item.checked = chbx_project.checked;
                                    });
                                    updatePaymentTermsTotal(payment_terms);
                                };
                            });
                        });
                    });
                });

                // Project Card Body: Responsive Grid of POs
                t.div({class: 'card-body p-3 p-md-4'}, () => {
                    t.div({class: 'row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4'}, () => {
                        items.forEach(item => {
                            t.div({class: 'col'}, () => {
                                t.div({class: 'card h-100 border border-secondary p-3 d-flex flex-column justify-content-between bg-dark bg-opacity-25'}, () => {
                                    // PO Header: Checkbox, ID Link, Status Badge
                                    t.div({}, () => {
                                        t.div({class: 'd-flex justify-content-between align-items-center mb-2'}, () => {
                                            t.div({class: 'd-flex align-items-center gap-2'}, () => {
                                                let chbx_class = item.flag ? 'po ok form-check-input project_' + project_id : 'po invalid form-check-input project_' + project_id;
                                                let chbx = t.input({
                                                    class: chbx_class,
                                                    type: 'checkbox',
                                                    value: item.po.id,
                                                    'data-payment_term_id': item.po.payment_term_id,
                                                    'data-amount': item.total
                                                });
                                                chbx.onchange = () => {
                                                    checkboxOnchangeController(payment_terms);
                                                };

                                                t.a({
                                                    href: '/review/purchase_order/' + item.po.id,
                                                    target: '_blank',
                                                    class: 'fw-bold font-monospace text-decoration-none text-info fs-6'
                                                }, () => {
                                                    t.i({class: 'bi bi-file-earmark-text me-1'});
                                                    t.span('#' + String(item.po.id).padStart(6, '0'));
                                                });
                                            });

                                            t.span({
                                                class: 'badge font-monospace py-1 px-2 ' + (item.po.status === 'PEND' ? 'bg-warning text-dark' : 'bg-secondary text-white')
                                            }, item.po.status);
                                        });

                                        // Total Amount
                                        t.div({class: 'my-2'}, () => {
                                            t.div({class: 'text-muted small'}, 'Total Amount');
                                            t.div({class: 'fs-5 fw-bold text-light font-monospace'}, '₱ ' + window.util.numberFormat(item.total, 2));
                                        });

                                        // Supplier & Payment Term
                                        t.div({class: 'small mb-2'}, () => {
                                            let sup_name = (suppliers && suppliers[item.po.supplier_id]) ? suppliers[item.po.supplier_id].name : 'N/A';
                                            t.div({class: 'd-flex align-items-center gap-1 text-truncate mb-1 text-muted', title: sup_name}, () => {
                                                t.i({class: 'bi bi-shop'});
                                                t.span({class: 'text-truncate'}, sup_name);
                                            });

                                            let term_name = (payment_terms && payment_terms[item.po.payment_term_id]) ? payment_terms[item.po.payment_term_id].text : 'N/A';
                                            t.div({class: 'd-flex align-items-center gap-1 text-truncate text-muted', title: term_name}, () => {
                                                t.i({class: 'bi bi-credit-card'});
                                                t.span({class: 'text-truncate'}, term_name);
                                            });
                                        });
                                    });

                                    // PO Footer: Date & Validation Pill
                                    t.div({}, () => {
                                        t.hr({class: 'my-2 border-secondary'});
                                        t.div({class: 'd-flex justify-content-between align-items-center pt-1'}, () => {
                                            t.span({class: 'small text-muted'}, () => {
                                                t.i({class: 'bi bi-clock me-1'});
                                                t.span(item.created_at || '');
                                            });

                                            if (item.flag) {
                                                t.span({class: 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill py-1 px-2 small'}, () => {
                                                    t.i({class: 'bi bi-check-circle-fill me-1'});
                                                    t.span('Valid');
                                                });
                                            } else {
                                                t.span({
                                                    class: 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 rounded-pill py-1 px-2 small',
                                                    title: (item.failed || []).join('; ')
                                                }, () => {
                                                    t.i({class: 'bi bi-exclamation-circle-fill me-1'});
                                                    t.span('Issues');
                                                });
                                            }
                                        });

                                        if (!item.flag && item.failed && item.failed.length > 0) {
                                            let fails = item.failed;
                                            let total_fails = fails.length;

                                            if (total_fails === 1) {
                                                t.div({class: 'mt-2 p-2 rounded small bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25', style: 'font-size: 0.75rem; line-height: 1.3;'}, () => {
                                                    t.div({class: 'd-flex align-items-start gap-1'}, () => {
                                                        t.i({class: 'bi bi-exclamation-triangle-fill text-danger me-1 flex-shrink-0 mt-1', style: 'font-size: 0.75rem;'});
                                                        t.span(fails[0]);
                                                    });
                                                });
                                            } else {
                                                let current_idx = 0;
                                                t.div({class: 'mt-2 p-2 rounded small bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25'}, () => {
                                                    let counter_span;
                                                    t.div({class: 'd-flex justify-content-between align-items-center mb-1'}, () => {
                                                        t.div({class: 'd-flex align-items-center gap-1 text-danger fw-semibold', style: 'font-size: 0.75rem;'}, () => {
                                                            t.i({class: 'bi bi-exclamation-triangle-fill', style: 'font-size: 0.75rem;'});
                                                            t.span('Defects');
                                                        });

                                                        t.div({class: 'd-flex align-items-center gap-1'}, () => {
                                                            counter_span = t.span({class: 'font-monospace text-danger me-1', style: 'font-size: 0.7rem;'}, `1/${total_fails}`);

                                                            let prev_btn = t.button({
                                                                type: 'button',
                                                                class: 'btn btn-sm btn-link p-0 text-danger text-decoration-none',
                                                                style: 'line-height: 1; font-size: 0.85rem;'
                                                            }, () => {
                                                                t.i({class: 'bi bi-chevron-left'});
                                                            });

                                                            let next_btn = t.button({
                                                                type: 'button',
                                                                class: 'btn btn-sm btn-link p-0 text-danger text-decoration-none',
                                                                style: 'line-height: 1; font-size: 0.85rem;'
                                                            }, () => {
                                                                t.i({class: 'bi bi-chevron-right'});
                                                            });

                                                            prev_btn.onclick = (e) => {
                                                                e.stopPropagation();
                                                                e.preventDefault();
                                                                current_idx = (current_idx - 1 + total_fails) % total_fails;
                                                                updateDefectSlide('prev');
                                                            };

                                                            next_btn.onclick = (e) => {
                                                                e.stopPropagation();
                                                                e.preventDefault();
                                                                current_idx = (current_idx + 1) % total_fails;
                                                                updateDefectSlide('next');
                                                            };
                                                        });
                                                    });

                                                    let slide_text = t.div({
                                                        class: 'defect-slide-next',
                                                        style: 'min-height: 38px; max-height: 54px; overflow-y: auto; font-size: 0.75rem; line-height: 1.3;'
                                                    }, fails[0]);

                                                    function updateDefectSlide(dir) {
                                                        slide_text.innerText = fails[current_idx];
                                                        if (counter_span) {
                                                            counter_span.innerText = `${current_idx + 1}/${total_fails}`;
                                                        }
                                                        slide_text.classList.remove('defect-slide-next', 'defect-slide-prev');
                                                        void slide_text.offsetWidth;
                                                        slide_text.classList.add(dir === 'next' ? 'defect-slide-next' : 'defect-slide-prev');
                                                    }
                                                });
                                            }
                                        }
                                    });
                                });
                            });
                        });
                    });
                });
            });

            result_container.appendChild(project_div);
        });

        setTimeout(() => {
            updatePaymentTermsTotal(payment_terms);
        }, 100);
    });
</script>
@endsection
