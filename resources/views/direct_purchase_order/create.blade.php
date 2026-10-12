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
                            <select class="form-select" id="supplierSelect" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{$supplier->id}}">{{$supplier->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Payment Term <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentTermSelect" required>
                                <option value="">Select Payment Term</option>
                                @foreach($payment_terms as $term)
                                    <option value="{{$term->id}}">{{$term->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Project (Optional)</label>
                            <select class="form-select" id="projectSelect">
                                <option value="">None</option>
                                @foreach($projects as $project)
                                    <option value="{{$project->id}}">{{$project->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Section (Optional)</label>
                            <select class="form-select" id="sectionSelect">
                                <option value="">None</option>
                            </select>
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
                            <th style="width: 40%">Material Item</th>
                            <th style="width: 20%">Quantity</th>
                            <th style="width: 20%">Price</th>
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

        const supplierSelect    = $q('#supplierSelect').first();
        const paymentTermSelect = $q('#paymentTermSelect').first();
        const projectSelect     = $q('#projectSelect').first();
        const sectionSelect     = $q('#sectionSelect').first();
        const itemsTableBody    = $q('#itemsTableBody').first();
        const extrasTableBody   = $q('#extrasTableBody').first();
        const addItemBtn        = $q('#addItemBtn').first();
        const addExtraBtn       = $q('#addExtraBtn').first();
        const saveBtn           = $q('#saveBtn').first();
        const grandTotalText    = $q('#grandTotalText').first();
        const materialOptions   = $q('#materialOptions').first().innerHTML;

        const t = new Template();

        projectSelect.onchange = async () => {
            $el.clear(sectionSelect);
            sectionSelect.appendChild(t.option({value: ''}, 'None'));
            if (!projectSelect.value) return;

            window.util.blockUI();
            let res = await window.util.$get('/api/section/list', {
                project_id: projectSelect.value,
                orderBy: 'name',
                order: 'ASC'
            });
            window.util.unblockUI();

            if (res.status === 1) {
                res.data.forEach(s => {
                    sectionSelect.appendChild(t.option({value: s.id}, s.name));
                });
            }
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
            if (!supplierSelect.value) {
                alert('Please select a Supplier');
                return;
            }
            if (!paymentTermSelect.value) {
                alert('Please select a Payment Term');
                return;
            }

            const items = [];
            const itemRows = itemsTableBody.querySelectorAll('tr');
            for (let row of itemRows) {
                const material_item_id = row.querySelector('.item-material').value;
                const order_quantity = parseFloat(row.querySelector('.item-qty').value);
                const price = parseFloat(row.querySelector('.item-price').value);

                if (!material_item_id || isNaN(order_quantity) || isNaN(price)) {
                    alert('Please complete all fields in the Items table.');
                    return;
                }

                items.push({
                    material_item_id: parseInt(material_item_id),
                    order_quantity: order_quantity,
                    price: price
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
