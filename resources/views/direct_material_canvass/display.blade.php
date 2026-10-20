@extends('layouts.app')

@section('content')
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
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                {{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}
                <span class="badge bg-secondary ms-2">Qty: {{ number_format($item->quantity, 2) }}</span>
            </h6>
            <button type="button" class="btn btn-sm btn-primary add-canvass-btn" data-item-id="{{ $item->id }}" data-item-name="{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Item' }}">
                <i class="bi bi-plus-circle"></i> Add Quote
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
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
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-canvass-btn" data-canvass-id="{{ $canvass->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No supplier quotes recorded yet. Click "Add Quote" to record one.</td>
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
    const bsModal = new bootstrap.Modal(modalEl);

    const modalItemId = document.getElementById('modalItemId');
    const modalSupplierSelect = document.getElementById('modalSupplierSelect');
    const modalPaymentTermSelect = document.getElementById('modalPaymentTermSelect');
    const modalPriceInput = document.getElementById('modalPriceInput');
    const modalRemarksInput = document.getElementById('modalRemarksInput');
    const modalSaveQuoteBtn = document.getElementById('modalSaveQuoteBtn');

    document.querySelectorAll('.add-canvass-btn').forEach(btn => {
        btn.onclick = () => {
            modalItemId.value = btn.getAttribute('data-item-id');
            document.getElementById('modalTitle').textContent = 'Add Quote for ' + btn.getAttribute('data-item-name');
            modalSupplierSelect.value = '';
            modalPaymentTermSelect.value = '';
            modalPriceInput.value = '';
            modalRemarksInput.value = '';
            bsModal.show();
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
            bsModal.hide();
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
@endsection
