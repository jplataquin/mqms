@extends('layouts.app')

@section('content')
<div id="content">

    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/direct_purchase_order">
                        <span>Direct Purchase Order</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>Create</span>
                        <i class="ms-2 bi bi-plus-circle"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">New Direct Purchase Order</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="form-group mb-2">
                            <label class="form-label fw-bold">Direct Material Request # (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                <input type="text" class="form-control" id="dmqrInput" list="dmqrList" placeholder="Type or select Direct Material Request # to auto-populate items..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearDmqrBtn" title="Clear Direct Material Request"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="dmqrList">
                                @if(!empty($available_dmqrs))
                                    @foreach($available_dmqrs as $d)
                                        <option value="#{{ $d->formatted_id }} - {{ $d->Project ? $d->Project->name : 'N/A' }}" data-id="{{ $d->id }}"></option>
                                    @endforeach
                                @endif
                            </datalist>
                            <input type="hidden" id="dmqrSelect" value="">
                            <small class="form-text text-muted">Selecting an approved Direct Material Request will automatically populate the project, section, material items, and locked canvass prices.</small>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="supplierInput" list="supplierList" placeholder="Type to search supplier..." autocomplete="off" required>
                                <button class="btn btn-outline-secondary" type="button" id="clearSupplierBtn" title="Clear Supplier"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="supplierList">
                                @foreach($suppliers as $supplier)
                                    <option value="{{$supplier->name}}" data-id="{{$supplier->id}}"></option>
                                @endforeach
                            </datalist>
                            <input type="hidden" id="supplierSelect" value="">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Payment Term <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentTermSelect" required>
                                <option value="">Select Payment Term</option>
                                @foreach($payment_terms as $term)
                                    <option value="{{$term->id}}">{{$term->text}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="projectInput" list="projectList" placeholder="Type to search project..." autocomplete="off" required>
                                <button class="btn btn-outline-secondary" type="button" id="clearProjectBtn" title="Clear Project"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="projectList">
                                @foreach($projects as $project)
                                    <option value="{{$project->name}}" data-id="{{$project->id}}"></option>
                                @endforeach
                            </datalist>
                            <input type="hidden" id="projectSelect" value="">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Section (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="sectionInput" list="sectionList" placeholder="Select a Project first" autocomplete="off" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="clearSectionBtn" title="Clear Section"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="sectionList">
                            </datalist>
                            <input type="hidden" id="sectionSelect" value="">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Date Needed</label>
                            <input type="date" class="form-control" id="dateNeededInput">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Items Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Items</h5>
                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                    <i class="bi bi-plus"></i> Add Item
                </button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 50%">Material Item</th>
                            <th style="width: 15%">Quantity</th>
                            <th style="width: 15%">Price</th>
                            <th style="width: 15%" class="text-end">Total</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Extras Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Additional Charges / Discounts (Extras)</h5>
                <button type="button" class="btn btn-sm btn-secondary" id="addExtraBtn">
                    <i class="bi bi-plus"></i> Add Extra
                </button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 60%">Description</th>
                            <th style="width: 30%">Amount</th>
                            <th style="width: 10%"></th>
                        </tr>
                    </thead>
                    <tbody id="extrasTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grand Total Summary -->
        <div class="card mb-4">
            <div class="card-body text-end">
                <h4>Grand Total: <span id="grandTotalText" class="text-primary">0.00</span></h4>
            </div>
        </div>

        <div class="text-end mb-5">
            <a href="/direct_purchase_order" class="btn btn-secondary me-2">Cancel</a>
            <button type="button" class="btn btn-primary" id="saveBtn">Save Draft</button>
        </div>

    </div>

    <!-- Datalist for Material Items -->
    <datalist id="materialList">
        @foreach($material_items as $mi)
            <option value="{{$mi->formattedName}}" data-id="{{$mi->id}}"></option>
        @endforeach
    </datalist>

    <script type="module">
        import {$q, Template, $el} from '/adarna.js';

        const dmqrInput         = $q('#dmqrInput').first();
        const dmqrList          = $q('#dmqrList').first();
        const dmqrSelect        = $q('#dmqrSelect').first();
        const clearDmqrBtn      = $q('#clearDmqrBtn').first();

        const supplierInput     = $q('#supplierInput').first();
        const supplierList      = $q('#supplierList').first();
        const supplierSelect    = $q('#supplierSelect').first();
        const clearSupplierBtn  = $q('#clearSupplierBtn').first();

        const paymentTermSelect = $q('#paymentTermSelect').first();

        const projectInput      = $q('#projectInput').first();
        const projectList       = $q('#projectList').first();
        const projectSelect     = $q('#projectSelect').first();
        const clearProjectBtn   = $q('#clearProjectBtn').first();

        const sectionInput      = $q('#sectionInput').first();
        const sectionList       = $q('#sectionList').first();
        const sectionSelect     = $q('#sectionSelect').first();
        const clearSectionBtn   = $q('#clearSectionBtn').first();

        const dateNeededInput   = $q('#dateNeededInput').first();

        const itemsTableBody    = $q('#itemsTableBody').first();
        const extrasTableBody   = $q('#extrasTableBody').first();
        const addItemBtn        = $q('#addItemBtn').first();
        const addExtraBtn       = $q('#addExtraBtn').first();
        const saveBtn           = $q('#saveBtn').first();
        const grandTotalText    = $q('#grandTotalText').first();
        const materialList      = $q('#materialList').first();

        const initialSupplierOptionsHtml = supplierList.innerHTML;
        const initialPaymentTermOptionsHtml = paymentTermSelect.innerHTML;
        let currentDmqrItems = [];

        const t = new Template();

        function findOptionId(input, datalist) {
            const val = input.value.trim().toLowerCase();
            if (!val || !datalist) return '';
            const options = datalist.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value.trim().toLowerCase() === val) {
                    return options[i].getAttribute('data-id') || '';
                }
            }
            return '';
        }

        dmqrInput.oninput = async () => {
            const matchedDmqrId = findOptionId(dmqrInput, dmqrList);
            if (matchedDmqrId && matchedDmqrId !== dmqrSelect.value) {
                dmqrSelect.value = matchedDmqrId;
                await loadDmqr(matchedDmqrId);
            }
        };

        clearDmqrBtn.onclick = () => {
            dmqrInput.value = '';
            dmqrSelect.value = '';
            if (dateNeededInput) dateNeededInput.value = '';
            unlockForm();
        };

        function renderDmqrItems(supplierId, paymentTermId) {
            itemsTableBody.innerHTML = '';
            if (!supplierId || !paymentTermId) {
                recalculate();
                updateItemRemoveButtons();
                return;
            }

            const matchingItems = currentDmqrItems.filter(item => 
                item.supplier_id == supplierId && item.payment_term_id == paymentTermId
            );

            matchingItems.forEach(item => {
                createDmqrItemRow(item);
            });

            recalculate();
            updateItemRemoveButtons();
        }

        function updatePaymentTermsAndItemsForSupplier(supplierId) {
            const supplierItems = currentDmqrItems.filter(item => item.supplier_id == supplierId);

            const uniquePaymentTermsMap = new Map();
            supplierItems.forEach(item => {
                if (item.payment_term_id && !uniquePaymentTermsMap.has(item.payment_term_id)) {
                    uniquePaymentTermsMap.set(item.payment_term_id, {
                        id: item.payment_term_id,
                        name: item.payment_term_name
                    });
                }
            });

            const uniquePaymentTerms = Array.from(uniquePaymentTermsMap.values());

            paymentTermSelect.innerHTML = '<option value="">Select Payment Term</option>';
            uniquePaymentTerms.forEach(pt => {
                const opt = document.createElement('option');
                opt.value = pt.id;
                opt.textContent = pt.name;
                paymentTermSelect.appendChild(opt);
            });

            if (uniquePaymentTerms.length === 1) {
                paymentTermSelect.value = uniquePaymentTerms[0].id;
                paymentTermSelect.disabled = true;
                renderDmqrItems(supplierId, uniquePaymentTerms[0].id);
            } else if (uniquePaymentTerms.length > 1) {
                paymentTermSelect.disabled = false;
                paymentTermSelect.value = '';
                itemsTableBody.innerHTML = '';
                recalculate();
                updateItemRemoveButtons();
                paymentTermSelect.focus();
            } else {
                paymentTermSelect.disabled = false;
                paymentTermSelect.value = '';
                itemsTableBody.innerHTML = '';
                recalculate();
                updateItemRemoveButtons();
            }
        }

        async function loadDmqr(id) {
            window.util.blockUI();
            let res = await window.util.$get('/api/direct_material_quantity_request/for_direct_po/' + id);
            window.util.unblockUI();

            if (res.status === 1 && res.data) {
                const data = res.data;
                if (!data.items || data.items.length === 0) {
                    alert('No items with remaining balance and approved canvass prices found for this request.');
                    dmqrInput.value = '';
                    dmqrSelect.value = '';
                    currentDmqrItems = [];
                    return;
                }

                currentDmqrItems = data.items;

                // Lock project
                projectInput.value = data.project_name;
                projectSelect.value = data.project_id;
                projectInput.disabled = true;
                clearProjectBtn.disabled = true;

                // Lock section
                sectionInput.value = data.section_name || '';
                sectionSelect.value = data.section_id || '';
                sectionInput.disabled = true;
                clearSectionBtn.disabled = true;

                // Auto-fill date needed if present in DMQR
                if (data.date_needed && dateNeededInput) {
                    dateNeededInput.value = data.date_needed;
                }

                // Extract unique suppliers from items
                const uniqueSuppliersMap = new Map();
                data.items.forEach(item => {
                    if (item.supplier_id && !uniqueSuppliersMap.has(item.supplier_id)) {
                        uniqueSuppliersMap.set(item.supplier_id, {
                            id: item.supplier_id,
                            name: item.supplier_name
                        });
                    }
                });

                const uniqueSuppliers = Array.from(uniqueSuppliersMap.values());

                addItemBtn.style.display = 'none';

                if (uniqueSuppliers.length === 1) {
                    // Only 1 supplier: auto-select and lock
                    const singleSupplier = uniqueSuppliers[0];
                    supplierInput.value = singleSupplier.name;
                    supplierSelect.value = singleSupplier.id;
                    supplierInput.disabled = true;
                    clearSupplierBtn.disabled = true;

                    updatePaymentTermsAndItemsForSupplier(singleSupplier.id);
                } else if (uniqueSuppliers.length > 1) {
                    // Multiple suppliers: unlock supplier selection and populate list with only these suppliers
                    supplierInput.disabled = false;
                    clearSupplierBtn.disabled = false;
                    supplierInput.value = '';
                    supplierSelect.value = '';

                    supplierList.innerHTML = '';
                    uniqueSuppliers.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.name;
                        opt.setAttribute('data-id', s.id);
                        supplierList.appendChild(opt);
                    });

                    paymentTermSelect.innerHTML = '<option value="">Select Payment Term</option>';
                    paymentTermSelect.value = '';
                    paymentTermSelect.disabled = false;

                    itemsTableBody.innerHTML = '';
                    recalculate();
                    updateItemRemoveButtons();

                    supplierInput.focus();
                } else {
                    itemsTableBody.innerHTML = '';
                    recalculate();
                }
            } else {
                alert(res.message || 'Error fetching Direct Material Request');
            }
        }

        function unlockForm() {
            currentDmqrItems = [];

            projectInput.disabled = false;
            clearProjectBtn.disabled = false;
            projectInput.value = '';
            projectSelect.value = '';

            sectionInput.disabled = true;
            sectionInput.placeholder = 'Select a Project first';
            clearSectionBtn.disabled = false;
            sectionInput.value = '';
            sectionSelect.value = '';
            $el.clear(sectionList);

            supplierList.innerHTML = initialSupplierOptionsHtml;
            supplierInput.disabled = false;
            clearSupplierBtn.disabled = false;
            supplierInput.value = '';
            supplierSelect.value = '';

            paymentTermSelect.innerHTML = initialPaymentTermOptionsHtml;
            paymentTermSelect.disabled = false;
            paymentTermSelect.value = '';

            if (dateNeededInput) dateNeededInput.value = '';

            addItemBtn.style.display = '';
            itemsTableBody.innerHTML = '';
            createItemRow();
            recalculate();
            updateItemRemoveButtons();
        }

        function createDmqrItemRow(item) {
            const trMain = document.createElement('tr');
            trMain.className = 'item-main-row';
            trMain.innerHTML = `
                <td>
                    <input type="text" class="form-control item-material-input" value="${item.material_item_name}" readonly disabled />
                    <input type="hidden" class="item-material" value="${item.material_item_id}">
                    <input type="hidden" class="item-dmqr-item-id" value="${item.direct_material_quantity_request_item_id}">
                    <input type="hidden" class="item-dmqr-canvass-id" value="${item.direct_material_canvass_id}">
                </td>
                <td>
                    <input type="number" step="any" min="0.01" max="${item.balance_quantity}" class="form-control item-qty" value="${item.balance_quantity}" required />
                    <small class="text-muted d-block">Remaining Balance: ${item.balance_quantity}</small>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control item-price font-monospace" value="${item.price.toFixed(2)}" readonly />
                </td>
                <td class="align-middle text-end font-monospace">
                    <span class="item-total">0.00</span>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-danger remove-row-btn" title="At least one item is required">&times;</button>
                </td>
            `;

            const trRemarks = document.createElement('tr');
            trRemarks.className = 'item-remarks-row';
            trRemarks.innerHTML = `
                <td colspan="5" class="pt-1 pb-2">
                    <div class="d-flex align-items-center mb-1">
                        <span class="small text-muted fw-semibold"><i class="bi bi-card-text me-1"></i> Remarks</span>
                    </div>
                    <textarea class="form-control item-remarks" rows="2" placeholder="Optional remarks...">${item.remarks || ''}</textarea>
                </td>
            `;

            trMain._remarksRow = trRemarks;
            trMain.querySelector('.item-qty').oninput = () => {
                const qtyVal = parseFloat(trMain.querySelector('.item-qty').value);
                if (qtyVal > item.balance_quantity) {
                    alert(`Quantity cannot exceed remaining balance of ${item.balance_quantity}`);
                    trMain.querySelector('.item-qty').value = item.balance_quantity;
                }
                recalculate();
            };

            trMain.querySelector('.remove-row-btn').onclick = () => {
                const currentRows = itemsTableBody.querySelectorAll('tr.item-main-row');
                if (currentRows.length <= 1) {
                    alert('At least one item is required to make a direct PO.');
                    return;
                }
                trMain.remove();
                trRemarks.remove();
                updateItemRemoveButtons();
                recalculate();
            };

            itemsTableBody.appendChild(trMain);
            itemsTableBody.appendChild(trRemarks);
        }

        supplierInput.oninput = () => {
            const matchedSupplierId = findOptionId(supplierInput, supplierList);
            supplierSelect.value = matchedSupplierId;

            if (currentDmqrItems.length > 0) {
                if (matchedSupplierId) {
                    updatePaymentTermsAndItemsForSupplier(matchedSupplierId);
                } else {
                    paymentTermSelect.innerHTML = '<option value="">Select Payment Term</option>';
                    paymentTermSelect.disabled = false;
                    paymentTermSelect.value = '';
                    itemsTableBody.innerHTML = '';
                    recalculate();
                    updateItemRemoveButtons();
                }
            }
        };

        paymentTermSelect.onchange = () => {
            if (currentDmqrItems.length > 0 && supplierSelect.value) {
                renderDmqrItems(supplierSelect.value, paymentTermSelect.value);
            }
        };

        clearSupplierBtn.onclick = () => {
            supplierInput.value = '';
            supplierSelect.value = '';
            if (currentDmqrItems.length > 0) {
                paymentTermSelect.innerHTML = '<option value="">Select Payment Term</option>';
                paymentTermSelect.disabled = false;
                paymentTermSelect.value = '';
                itemsTableBody.innerHTML = '';
                recalculate();
                updateItemRemoveButtons();
            }
            supplierInput.focus();
        };

        projectInput.oninput = async () => {
            const newProjId = findOptionId(projectInput, projectList);
            const currProjId = projectSelect.value;

            if (newProjId !== currProjId) {
                projectSelect.value = newProjId;
                sectionInput.value = '';
                sectionSelect.value = '';
                $el.clear(sectionList);

                if (!newProjId) {
                    sectionInput.disabled = true;
                    sectionInput.placeholder = 'Select a Project first';
                    return;
                }

                sectionInput.disabled = false;
                sectionInput.placeholder = 'Type to search section (Optional)...';

                window.util.blockUI();
                let res = await window.util.$get('/api/section/list', {
                    project_id: newProjId,
                    orderBy: 'name',
                    order: 'ASC'
                });
                window.util.unblockUI();

                if (res.status === 1 && res.data) {
                    res.data.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.name;
                        opt.setAttribute('data-id', s.id);
                        sectionList.appendChild(opt);
                    });
                }
            }
        };

        clearProjectBtn.onclick = () => {
            projectInput.value = '';
            projectSelect.value = '';
            sectionInput.value = '';
            sectionSelect.value = '';
            $el.clear(sectionList);
            sectionInput.disabled = true;
            sectionInput.placeholder = 'Select a Project first';
            projectInput.focus();
        };

        sectionInput.oninput = () => {
            sectionSelect.value = findOptionId(sectionInput, sectionList);
        };

        clearSectionBtn.onclick = () => {
            sectionInput.value = '';
            sectionSelect.value = '';
            sectionInput.focus();
        };

        function recalculate() {
            let total = 0;

            const itemRows = itemsTableBody.querySelectorAll('tr.item-main-row');
            itemRows.forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const rowTotal = qty * price;
                row.querySelector('.item-total').textContent = rowTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                total += rowTotal;
            });

            const extraRows = extrasTableBody.querySelectorAll('tr');
            extraRows.forEach(row => {
                const val = parseFloat(row.querySelector('.extra-value').value) || 0;
                total += val;
            });

            grandTotalText.textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function updateItemRemoveButtons() {
            const rows = itemsTableBody.querySelectorAll('tr.item-main-row');
            rows.forEach(row => {
                const btn = row.querySelector('.remove-row-btn');
                if (btn) {
                    if (rows.length <= 1) {
                        btn.disabled = true;
                        btn.setAttribute('title', 'At least one item is required');
                    } else {
                        btn.disabled = false;
                        btn.removeAttribute('title');
                    }
                }
            });
        }

        function createItemRow() {
            const trMain = document.createElement('tr');
            trMain.className = 'item-main-row';
            trMain.innerHTML = `
                <td>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control item-material-input" list="materialList" placeholder="Type to search material..." autocomplete="off" required>
                        <button class="btn btn-outline-secondary clear-item-material-btn" type="button" title="Clear Material"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <input type="hidden" class="item-material" value="">
                </td>
                <td>
                    <input type="number" step="any" min="0" class="form-control item-qty" placeholder="0" required />
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control item-price" placeholder="0.00" required />
                </td>
                <td class="align-middle text-end font-monospace">
                    <span class="item-total">0.00</span>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-danger remove-row-btn" title="At least one item is required">&times;</button>
                </td>
            `;

            const trRemarks = document.createElement('tr');
            trRemarks.className = 'item-remarks-row';
            trRemarks.innerHTML = `
                <td colspan="5" class="pt-1 pb-2">
                    <div class="d-flex align-items-center mb-1">
                        <span class="small text-muted fw-semibold"><i class="bi bi-card-text me-1"></i> Remarks</span>
                    </div>
                    <textarea class="form-control item-remarks" rows="2" placeholder="Optional remarks..."></textarea>
                </td>
            `;

            trMain._remarksRow = trRemarks;

            const matInput    = trMain.querySelector('.item-material-input');
            const matHidden   = trMain.querySelector('.item-material');
            const clearMatBtn = trMain.querySelector('.clear-item-material-btn');

            matInput.oninput = () => {
                matHidden.value = findOptionId(matInput, materialList);
            };
            matInput.onchange = () => {
                matHidden.value = findOptionId(matInput, materialList);
            };

            clearMatBtn.onclick = () => {
                matInput.value = '';
                matHidden.value = '';
                matInput.focus();
            };

            trMain.querySelector('.item-qty').oninput = recalculate;
            trMain.querySelector('.item-price').oninput = recalculate;
            trMain.querySelector('.remove-row-btn').onclick = () => {
                const currentRows = itemsTableBody.querySelectorAll('tr.item-main-row');
                if (currentRows.length <= 1) {
                    alert('At least one item is required to make a direct PO.');
                    return;
                }
                trMain.remove();
                trRemarks.remove();
                updateItemRemoveButtons();
                recalculate();
            };

            itemsTableBody.appendChild(trMain);
            itemsTableBody.appendChild(trRemarks);
            updateItemRemoveButtons();
        }

        function createExtraRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" class="form-control extra-text" placeholder="e.g. Delivery Fee, Discount" required />
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control extra-value" placeholder="0.00" required />
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-danger remove-row-btn">&times;</button>
                </td>
            `;

            tr.querySelector('.extra-value').oninput = recalculate;
            tr.querySelector('.remove-row-btn').onclick = () => {
                tr.remove();
                recalculate();
            };

            extrasTableBody.appendChild(tr);
        }

        addItemBtn.onclick = () => createItemRow();
        addExtraBtn.onclick = () => createExtraRow();

        // Add 1 default row
        createItemRow();

        saveBtn.onclick = async () => {
            const matchedSupplierId = findOptionId(supplierInput, supplierList);
            if (!matchedSupplierId) {
                alert('Please select a valid Supplier from the list.');
                supplierInput.focus();
                return;
            }
            supplierSelect.value = matchedSupplierId;

            if (!paymentTermSelect.value || parseInt(paymentTermSelect.value) <= 0) {
                alert('Please select a Payment Term.');
                paymentTermSelect.focus();
                return;
            }

            const matchedProjId = findOptionId(projectInput, projectList);
            if (!matchedProjId) {
                alert('Please select a valid Project from the list.');
                projectInput.focus();
                return;
            }
            projectSelect.value = matchedProjId;

            if (sectionInput.value.trim() !== '') {
                const matchedSecId = findOptionId(sectionInput, sectionList);
                if (!matchedSecId) {
                    alert('Please select a valid Section from the list, or clear the Section field.');
                    sectionInput.focus();
                    return;
                }
                sectionSelect.value = matchedSecId;
            } else {
                sectionSelect.value = '';
            }

            const items = [];
            const itemRows = itemsTableBody.querySelectorAll('tr.item-main-row');
            for (let row of itemRows) {
                const matInput = row.querySelector('.item-material-input');
                const matHidden = row.querySelector('.item-material');
                const matchedMatId = findOptionId(matInput, materialList);
                if (matchedMatId) {
                    matHidden.value = matchedMatId;
                }

                const material_item_id = matHidden.value;
                const order_quantity = parseFloat(row.querySelector('.item-qty').value);
                const price = parseFloat(row.querySelector('.item-price').value);
                
                const remarksRow = row._remarksRow || row.nextElementSibling;
                const remarksTextarea = remarksRow ? remarksRow.querySelector('.item-remarks') : null;
                const remarks = remarksTextarea ? remarksTextarea.value.trim() : '';

                const dmqrItemIdEl = row.querySelector('.item-dmqr-item-id');
                const dmqrCanvassIdEl = row.querySelector('.item-dmqr-canvass-id');

                if (!material_item_id) {
                    alert('Please select a valid Material Item from the list.');
                    matInput.focus();
                    return;
                }

                if (isNaN(order_quantity) || order_quantity <= 0 || isNaN(price) || price < 0) {
                    alert('Please complete all fields in the Items table.');
                    return;
                }

                const itemData = {
                    material_item_id: parseInt(material_item_id),
                    order_quantity: order_quantity,
                    price: price,
                    remarks: remarks
                };

                if (dmqrItemIdEl && dmqrItemIdEl.value) {
                    itemData.direct_material_quantity_request_item_id = parseInt(dmqrItemIdEl.value);
                }
                if (dmqrCanvassIdEl && dmqrCanvassIdEl.value) {
                    itemData.direct_material_canvass_id = parseInt(dmqrCanvassIdEl.value);
                }

                items.push(itemData);
            }

            if (items.length === 0) {
                alert('At least one item is required to make a direct PO.');
                return;
            }

            const extras = [];
            const extraRows = extrasTableBody.querySelectorAll('tr');
            for (let row of extraRows) {
                const text = row.querySelector('.extra-text').value.trim();
                const value = parseFloat(row.querySelector('.extra-value').value);

                if (!text || isNaN(value)) {
                    alert('Please complete all extra charges.');
                    return;
                }

                extras.push({
                    text: text,
                    value: value
                });
            }

            saveBtn.disabled = true;
            window.util.blockUI();

            const payload = {
                supplier_id: supplierSelect.value,
                payment_term_id: paymentTermSelect.value,
                project_id: projectSelect.value,
                items: JSON.stringify(items),
                extras: JSON.stringify(extras)
            };
            if (sectionSelect.value && parseInt(sectionSelect.value) > 0) {
                payload.section_id = sectionSelect.value;
            }
            if (dmqrSelect.value && parseInt(dmqrSelect.value) > 0) {
                payload.direct_material_quantity_request_id = dmqrSelect.value;
            }
            if (dateNeededInput && dateNeededInput.value) {
                payload.date_needed = dateNeededInput.value;
            }

            let res = await window.util.$post('/api/direct_purchase_order/create', payload);
            window.util.unblockUI();

            if (res.status === 1) {
                window.util.navTo('/direct_purchase_order/' + res.data.id);
            } else {
                alert(res.message || 'Error occurred while saving Direct PO.');
                saveBtn.disabled = false;
            }
        };
    </script>
</div>
@endsection
