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

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Direct Material Request #{{ $request->formatted_id }}</h5>
            <span class="badge border border-light text-white fs-6">{{ $request->status }}</span>
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
            <div class="row mb-3">
                <div class="col-lg-6">
                    <label class="form-label fw-bold mb-1">Date Needed:</label>
                    <div id="dateNeededDisplay">{{ $request->date_needed ?: 'None' }}</div>
                    <input type="date" class="form-control d-none" id="dateNeededInput" value="{{ $request->date_needed }}">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label fw-bold mb-1">Purpose / Description:</label>
                    <div id="descriptionDisplay" class="text-muted">{{ $request->description ?: 'None' }}</div>
                    <textarea class="form-control d-none" id="descriptionInput" rows="2">{{ $request->description }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Items</h5>
            <button type="button" class="btn btn-sm btn-success d-none" id="addItemBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Material Item</th>
                        <th style="width: 20%" class="text-end">Quantity</th>
                        <th style="width: 25%">Remarks</th>
                        <th style="width: 5%" class="text-center edit-col d-none">Action</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    @foreach($items as $index => $item)
                        <tr class="item-row" data-id="{{ $item->id }}" data-material-id="{{ $item->material_item_id }}">
                            <td class="row-index text-center">{{ $index + 1 }}</td>
                            <td class="material-col">
                                <span class="view-item-name">{{ $item->MaterialItem ? $item->MaterialItem->formattedName : 'Unknown Item' }}</span>
                                <div class="edit-material-wrapper d-none">
                                    <input type="text" class="form-control form-control-sm material-input" list="materialList" value="{{ $item->MaterialItem ? $item->MaterialItem->formattedName : '' }}" autocomplete="off">
                                    <input type="hidden" class="material-id-input" value="{{ $item->material_item_id }}">
                                </div>
                            </td>
                            <td class="quantity-col text-end">
                                <span class="view-quantity font-monospace">{{ number_format($item->quantity, 2) }}</span>
                                <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end edit-quantity-input d-none" value="{{ $item->quantity }}">
                            </td>
                            <td class="remarks-col">
                                <span class="view-remarks text-muted small">{{ $item->remarks ?: '-' }}</span>
                                <input type="text" class="form-control form-control-sm edit-remarks-input d-none" value="{{ $item->remarks ?: '' }}" placeholder="Remarks">
                            </td>
                            <td class="text-center edit-col d-none">
                                <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn" title="Remove item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Controls -->
    <div class="text-end mb-5">
        <button class="btn btn-primary me-2" id="showPoListBtn">PO List</button>
        <a href="/direct_material_quantity_requests" class="btn btn-secondary me-2" id="backBtn">Back to List</a>
        <a href="/direct_material_quantity_request/print/{{$request->id}}" target="_blank" class="btn btn-info me-2 text-white" id="printBtn">
            <i class="bi bi-printer"></i> Print
        </a>

        @if($request->status == 'PEND')
            <button type="button" class="btn btn-outline-primary me-2 review-link-btn" id="reviewLinkActionBtn">
                <i class="bi bi-link-45deg"></i> Review Link
            </button>
        @endif

        @if(($request->status == 'DRFT' || $request->status == 'PEND') && !empty($canEdit))
            <button type="button" class="btn btn-primary me-2" id="editBtn">
                <i class="bi bi-pencil me-1"></i> Edit
            </button>
            <button type="button" class="btn btn-warning me-2 d-none" id="updateBtn">
                <i class="bi bi-check-lg me-1"></i> Update
            </button>
            <button type="button" class="btn btn-secondary me-2 d-none" id="cancelEditBtn">Cancel</button>
        @endif

        @if(($request->status == 'DRFT' || $request->status == 'PEND') && !empty($canDelete))
            <button type="button" class="btn btn-danger me-2" id="deleteBtn">Delete</button>
        @endif

        @if($request->status == 'DRFT' && !empty($canEdit))
            <button type="button" class="btn btn-success me-2" id="submitBtn">Submit For Review</button>
        @endif

        @if($request->status == 'APRV')
            @if(!empty($canClose))
                <button type="button" class="btn btn-dark me-2" id="closeRequestBtn">
                    <i class="bi bi-check2-all me-1"></i> Close Request
                </button>
            @endif
            <a href="/direct_material_canvass/{{$request->id}}" class="btn btn-primary">
                <i class="bi bi-tag"></i> View Canvass
            </a>
        @endif
    </div>
</div>

<!-- Datalist for Material Items -->
<datalist id="materialList">
    @if(!empty($material_items))
        @foreach($material_items as $mi)
            <option value="{{$mi->formattedName}}" data-id="{{$mi->id}}"></option>
        @endforeach
    @endif
</datalist>

<script type="module">
    import {$q} from '/adarna.js';

    const reqId = {{ $request->id }};
    const showPoListBtn   = $q('#showPoListBtn').first();
    const submitBtn       = $q('#submitBtn').first();
    const deleteBtn       = $q('#deleteBtn').first();
    const editBtn         = $q('#editBtn').first();
    const updateBtn       = $q('#updateBtn').first();
    const cancelEditBtn   = $q('#cancelEditBtn').first();
    const addItemBtn      = $q('#addItemBtn').first();
    const closeRequestBtn = $q('#closeRequestBtn').first();
    const printBtn        = $q('#printBtn').first();
    const backBtn         = $q('#backBtn').first();

    const dateNeededDisplay = $q('#dateNeededDisplay').first();
    const dateNeededInput   = $q('#dateNeededInput').first();
    const descriptionDisplay = $q('#descriptionDisplay').first();
    const descriptionInput   = $q('#descriptionInput').first();
    const itemsTableBody    = $q('#itemsTableBody').first();

    let deletedItemIds = [];

    if (showPoListBtn) {
        showPoListBtn.onclick = () => {
            window.util.navTo('/direct_material_quantity_request/po_list/{{$request->id}}');
        };
    }

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

    if (closeRequestBtn) {
        closeRequestBtn.onclick = async () => {
            if (!confirm('Are you sure you want to CLOSE this Direct Material Request? This will calculate item quantities from Purchase Orders and finalize the request.')) {
                return;
            }
            window.util.blockUI();
            let res = await window.util.$post('/api/direct_material_quantity_request/close', { id: reqId });
            window.util.unblockUI();
            if (res.status === 1) {
                window.location.reload();
            } else {
                alert(res.message || 'Error closing request');
            }
        };
    }

    // Bind row delete buttons
    function attachRowEvents(row) {
        const delBtn = row.querySelector('.delete-row-btn');
        if (delBtn) {
            delBtn.onclick = () => {
                const totalRows = itemsTableBody.querySelectorAll('.item-row').length;
                if (totalRows <= 1) {
                    alert('At least one item is required in the request.');
                    return;
                }
                const rowId = row.getAttribute('data-id');
                if (rowId) {
                    deletedItemIds.push(parseInt(rowId));
                }
                row.remove();
                reindexRows();
            };
        }

        const matInput = row.querySelector('.material-input');
        const matIdInput = row.querySelector('.material-id-input');
        if (matInput && matIdInput) {
            matInput.onchange = () => {
                const datalist = document.getElementById('materialList');
                let foundId = '';
                if (datalist) {
                    const options = datalist.querySelectorAll('option');
                    for (let opt of options) {
                        if (opt.value === matInput.value) {
                            foundId = opt.getAttribute('data-id');
                            break;
                        }
                    }
                }
                matIdInput.value = foundId;
            };
        }
    }

    function reindexRows() {
        const rows = itemsTableBody.querySelectorAll('.item-row');
        rows.forEach((row, idx) => {
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = idx + 1;
        });
    }

    document.querySelectorAll('.item-row').forEach(row => attachRowEvents(row));

    if (editBtn) {
        editBtn.onclick = () => {
            // Toggle form fields
            dateNeededDisplay.classList.add('d-none');
            dateNeededInput.classList.remove('d-none');
            descriptionDisplay.classList.add('d-none');
            descriptionInput.classList.remove('d-none');

            // Toggle table cells
            document.querySelectorAll('.view-item-name, .view-quantity, .view-remarks').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.edit-material-wrapper, .edit-quantity-input, .edit-remarks-input, .edit-col').forEach(el => el.classList.remove('d-none'));

            // Toggle action buttons
            editBtn.classList.add('d-none');
            updateBtn.classList.remove('d-none');
            cancelEditBtn.classList.remove('d-none');
            addItemBtn.classList.remove('d-none');

            if (submitBtn) submitBtn.classList.add('d-none');
            if (deleteBtn) deleteBtn.classList.add('d-none');
        };
    }

    if (cancelEditBtn) {
        cancelEditBtn.onclick = () => {
            window.location.reload();
        };
    }

    if (addItemBtn) {
        addItemBtn.onclick = () => {
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.setAttribute('data-id', '');
            newRow.setAttribute('data-material-id', '');

            newRow.innerHTML = `
                <td class="row-index text-center"></td>
                <td class="material-col">
                    <span class="view-item-name d-none"></span>
                    <div class="edit-material-wrapper">
                        <input type="text" class="form-control form-control-sm material-input" list="materialList" placeholder="Search material..." autocomplete="off">
                        <input type="hidden" class="material-id-input" value="">
                    </div>
                </td>
                <td class="quantity-col text-end">
                    <span class="view-quantity font-monospace d-none"></span>
                    <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-end edit-quantity-input" value="1.00">
                </td>
                <td class="remarks-col">
                    <span class="view-remarks text-muted small d-none"></span>
                    <input type="text" class="form-control form-control-sm edit-remarks-input" placeholder="Remarks">
                </td>
                <td class="text-center edit-col">
                    <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn" title="Remove item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            itemsTableBody.appendChild(newRow);
            attachRowEvents(newRow);
            reindexRows();
        };
    }

    if (updateBtn) {
        updateBtn.onclick = async () => {
            const rows = itemsTableBody.querySelectorAll('.item-row');
            if (rows.length === 0) {
                alert('At least one item is required.');
                return;
            }

            let itemsData = [];
            for (let row of rows) {
                const id = row.getAttribute('data-id') ? parseInt(row.getAttribute('data-id')) : null;
                const matIdInput = row.querySelector('.material-id-input');
                const matInput = row.querySelector('.material-input');
                const qtyInput = row.querySelector('.edit-quantity-input');
                const remInput = row.querySelector('.edit-remarks-input');

                let materialId = matIdInput ? parseInt(matIdInput.value) : 0;
                let quantity = qtyInput ? parseFloat(qtyInput.value) : 0;
                let remarks = remInput ? remInput.value : '';

                if (!materialId || isNaN(materialId)) {
                    alert('Please select a valid material item for all rows.');
                    if (matInput) matInput.focus();
                    return;
                }

                if (!quantity || isNaN(quantity) || quantity <= 0) {
                    alert('Please enter a valid quantity greater than 0 for all items.');
                    if (qtyInput) qtyInput.focus();
                    return;
                }

                itemsData.push({
                    id: id,
                    material_item_id: materialId,
                    quantity: quantity,
                    remarks: remarks
                });
            }

            window.util.blockUI();
            let res = await window.util.$post('/api/direct_material_quantity_request/update', {
                id: reqId,
                description: descriptionInput ? descriptionInput.value : '',
                date_needed: dateNeededInput ? dateNeededInput.value : '',
                items: JSON.stringify(itemsData),
                delete_items: JSON.stringify(deletedItemIds)
            });
            window.util.unblockUI();

            if (res.status === 1) {
                window.location.reload();
            } else {
                alert(res.message || 'Error updating Direct Material Request');
            }
        };
    }
</script>
</div>
@endsection
