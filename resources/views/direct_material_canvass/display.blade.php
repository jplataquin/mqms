@extends('layouts.app')

@section('content')
<div id="content">

<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/direct_material_canvass">
                    <span>Direct Material Canvass</span>
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
            <h5 class="mb-0">Canvassing for Direct Material Request #{{ $request->formatted_id }}</h5>
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
                @if($request->description)
                <div class="col-12">
                    <strong>Purpose:</strong> {{ $request->description }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Items with Canvass Cards -->
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

                <!-- Right: Quantity Callout & Add Quote Action -->
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

                        <!-- Action Button -->
                        <button type="button" class="btn btn-primary add-canvass-btn d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm" data-item-id="{{ $item->id }}" data-item-name="{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Item' }}" data-item-qty="{{ number_format($item->quantity, 2) }}">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>Add Quote</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table of Supplier Quotes -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="border-bottom text-uppercase small text-muted" style="letter-spacing: 0.04em;">
                        <tr>
                            <th style="width: 28%; padding-left: 1.5rem;">Supplier</th>
                            <th style="width: 22%">Payment Term</th>
                            <th style="width: 16%" class="text-end">Unit Price</th>
                            <th style="width: 16%" class="text-end">Total Amount</th>
                            <th style="width: 10%" class="text-center">Status</th>
                            <th style="width: 8%; padding-right: 1.5rem;" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->Canvasses as $canvass)
                            <tr>
                                <td style="padding-left: 1.5rem;" class="fw-semibold text-dark">
                                    <i class="bi bi-building me-1 text-muted"></i>
                                    {{ $canvass->Supplier ? $canvass->Supplier->name : '-' }}
                                </td>
                                <td>{{ $canvass->PaymentTerm ? $canvass->PaymentTerm->text : '-' }}</td>
                                <td class="text-end font-monospace fw-semibold">{{ number_format($canvass->price, 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-dark">{{ number_format($canvass->price * $item->quantity, 2) }}</td>
                                <td class="text-center">
                                    @if($canvass->status == 'APRV')
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                    @elseif($canvass->status == 'DPRV')
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Disapproved</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock me-1"></i>Pending</span>
                                    @endif
                                </td>
                                <td style="padding-right: 1.5rem;" class="text-end">
                                    @if($canvass->status == 'PEND')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-canvass-btn" data-canvass-id="{{ $canvass->id }}" title="Delete quote">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                    <span>No supplier quotes recorded yet. Click <strong>"Add Quote"</strong> to record one.</span>
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
        <a href="/direct_material_canvass" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<!-- Modal for adding quote -->
<div class="modal fade" id="addQuoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Supplier Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalItemId">
                <div class="p-3 mb-3 rounded-2 border d-flex justify-content-between align-items-center shadow-sm">
                    <div>
                        <span class="text-muted small d-block">Item</span>
                        <strong id="modalItemName" class="text-dark"></strong>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small d-block">Target Quantity</span>
                        <strong id="modalItemQty" class="text-primary font-monospace fs-6"></strong>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select class="form-select" id="modalSupplierSelect" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Payment Term <span class="text-danger">*</span></label>
                    <select class="form-select" id="modalPaymentTermSelect" required>
                        <option value="">Select Payment Term</option>
                        @foreach($payment_terms as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->text }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Price per Unit <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="modalPriceInput" placeholder="0.00" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Remarks (Optional)</label>
                    <input type="text" class="form-control" id="modalRemarksInput" placeholder="Optional notes...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="modalSaveQuoteBtn">Save Quote</button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    const dmqrId = {{ $request->id }};
    const modalEl = document.getElementById('addQuoteModal');

    function showModal() {
        if (window.bootstrap && window.bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        modalEl.setAttribute('role', 'dialog');
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        document.body.classList.add('modal-open');
    }

    function hideModal() {
        if (window.bootstrap && window.bootstrap.Modal) {
            const instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
        }
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.removeAttribute('aria-modal');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
        btn.onclick = () => hideModal();
    });

    const modalItemId = document.getElementById('modalItemId');
    const modalSupplierSelect = document.getElementById('modalSupplierSelect');
    const modalPaymentTermSelect = document.getElementById('modalPaymentTermSelect');
    const modalPriceInput = document.getElementById('modalPriceInput');
    const modalRemarksInput = document.getElementById('modalRemarksInput');
    const modalSaveQuoteBtn = document.getElementById('modalSaveQuoteBtn');

    const modalItemName = document.getElementById('modalItemName');
    const modalItemQty  = document.getElementById('modalItemQty');

    document.querySelectorAll('.add-canvass-btn').forEach(btn => {
        btn.onclick = () => {
            modalItemId.value = btn.getAttribute('data-item-id');
            const itemName = btn.getAttribute('data-item-name');
            const itemQty  = btn.getAttribute('data-item-qty');
            document.getElementById('modalTitle').textContent = 'Add Quote';
            if (modalItemName) modalItemName.textContent = itemName;
            if (modalItemQty) modalItemQty.textContent = itemQty;
            modalSupplierSelect.value = '';
            modalPaymentTermSelect.value = '';
            modalPriceInput.value = '';
            modalRemarksInput.value = '';
            showModal();
        };
    });

    modalSaveQuoteBtn.onclick = async () => {
        const supplierId = modalSupplierSelect.value;
        const paymentTermId = modalPaymentTermSelect.value;
        const price = parseFloat(modalPriceInput.value);

        if (!supplierId || !paymentTermId || isNaN(price) || price < 0) {
            alert('Please complete all required fields.');
            return;
        }

        modalSaveQuoteBtn.disabled = true;
        window.util.blockUI();

        let res = await window.util.$post('/api/direct_material_canvass/create', {
            direct_material_quantity_request_id: dmqrId,
            direct_material_quantity_request_item_id: modalItemId.value,
            supplier_id: supplierId,
            payment_term_id: paymentTermId,
            price: price,
            remarks: modalRemarksInput.value.trim()
        });

        window.util.unblockUI();
        modalSaveQuoteBtn.disabled = false;

        if (res.status === 1) {
            hideModal();
            window.location.reload();
        } else {
            alert(res.message || 'Error saving quote');
        }
    };

    document.querySelectorAll('.delete-canvass-btn').forEach(btn => {
        btn.onclick = async () => {
            if (!confirm('Are you sure you want to delete this quote?')) return;
            const canvassId = btn.getAttribute('data-canvass-id');
            window.util.blockUI();
            let res = await window.util.$post('/api/direct_material_canvass/delete', { id: canvassId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                alert(res.message || 'Error deleting quote');
            }
        };
    });
</script>
</div>
@endsection
