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
                            <label class="form-label">Project (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="projectInput" list="projectList" placeholder="Type to search project (Optional)..." autocomplete="off">
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
                            <th style="width: 30%">Material Item</th>
                            <th style="width: 20%">Remarks</th>
                            <th style="width: 15%">Quantity</th>
                            <th style="width: 15%">Price</th>
                            <th style="width: 15%">Total</th>
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

    <!-- Hidden Template Options for Material Items -->
    <div id="materialOptions" style="display:none;">
        <option value="">Select Material</option>
        @foreach($material_items as $mi)
            <option value="{{$mi->id}}">{{$mi->formattedName}}</option>
        @endforeach
    </div>

    <script type="module">
        import {$q, Template, $el} from '/adarna.js';

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

        const itemsTableBody    = $q('#itemsTableBody').first();
        const extrasTableBody   = $q('#extrasTableBody').first();
        const addItemBtn        = $q('#addItemBtn').first();
        const addExtraBtn       = $q('#addExtraBtn').first();
        const saveBtn           = $q('#saveBtn').first();
        const grandTotalText    = $q('#grandTotalText').first();
        const materialOptions   = $q('#materialOptions').first().innerHTML;

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

        supplierInput.oninput = () => {
            supplierSelect.value = findOptionId(supplierInput, supplierList);
        };

        clearSupplierBtn.onclick = () => {
            supplierInput.value = '';
            supplierSelect.value = '';
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

            const itemRows = itemsTableBody.querySelectorAll('tr');
            itemRows.forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const rowTotal = qty * price;
                row.querySelector('.item-total').textContent = rowTotal.toFixed(2);
                total += rowTotal;
            });

            const extraRows = extrasTableBody.querySelectorAll('tr');
            extraRows.forEach(row => {
                const val = parseFloat(row.querySelector('.extra-value').value) || 0;
                total += val;
            });

            grandTotalText.textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function createItemRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select class="form-select item-material" required>
                        ${materialOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control item-remarks" placeholder="Optional remarks..." />
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
                    <button type="button" class="btn btn-sm btn-danger remove-row-btn">&times;</button>
                </td>
            `;

            tr.querySelector('.item-qty').oninput = recalculate;
            tr.querySelector('.item-price').oninput = recalculate;
            tr.querySelector('.remove-row-btn').onclick = () => {
                tr.remove();
                recalculate();
            };

            itemsTableBody.appendChild(tr);
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

            if (projectInput.value.trim() !== '') {
                const matchedProjId = findOptionId(projectInput, projectList);
                if (!matchedProjId) {
                    alert('Please select a valid Project from the list, or clear the Project field.');
                    projectInput.focus();
                    return;
                }
                projectSelect.value = matchedProjId;
            } else {
                projectSelect.value = '';
            }

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
            const itemRows = itemsTableBody.querySelectorAll('tr');
            for (let row of itemRows) {
                const material_item_id = row.querySelector('.item-material').value;
                const order_quantity = parseFloat(row.querySelector('.item-qty').value);
                const price = parseFloat(row.querySelector('.item-price').value);
                const remarks = row.querySelector('.item-remarks') ? row.querySelector('.item-remarks').value.trim() : '';

                if (!material_item_id || isNaN(order_quantity) || isNaN(price)) {
                    alert('Please complete all fields in the Items table.');
                    return;
                }

                items.push({
                    material_item_id: parseInt(material_item_id),
                    order_quantity: order_quantity,
                    price: price,
                    remarks: remarks
                });
            }

            if (items.length === 0) {
                alert('Please add at least one Item.');
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

            let res = await window.util.$post('/api/direct_purchase_order/create', {
                supplier_id: supplierSelect.value,
                payment_term_id: paymentTermSelect.value,
                project_id: projectSelect.value || null,
                section_id: sectionSelect.value || null,
                items: JSON.stringify(items),
                extras: JSON.stringify(extras)
            });
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
