@extends('layouts.app')

@section('content')
<div id="content">

<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/direct_material_quantity_requests">
                    <span>Direct Material Request</span>
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
            <h5 class="mb-0">New Direct Material Request</h5>
        </div>
        <div class="card-body">
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
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Purpose / Description (Optional)</label>
                        <textarea class="form-control" id="descriptionInput" rows="2" placeholder="Purpose of this material request..."></textarea>
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
                        <th style="width: 70%">Material Item</th>
                        <th style="width: 25%">Quantity</th>
                        <th style="width: 5%"></th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end mb-5">
        <a href="/direct_material_quantity_requests" class="btn btn-secondary me-2">Cancel</a>
        <button type="button" class="btn btn-primary" id="saveBtn">Submit Request</button>
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

    const projectInput    = $q('#projectInput').first();
    const projectList     = $q('#projectList').first();
    const projectSelect   = $q('#projectSelect').first();
    const clearProjectBtn = $q('#clearProjectBtn').first();

    const sectionInput    = $q('#sectionInput').first();
    const sectionList     = $q('#sectionList').first();
    const sectionSelect   = $q('#sectionSelect').first();
    const clearSectionBtn = $q('#clearSectionBtn').first();

    const descriptionInput = $q('#descriptionInput').first();
    const itemsTableBody  = $q('#itemsTableBody').first();
    const addItemBtn      = $q('#addItemBtn').first();
    const saveBtn         = $q('#saveBtn').first();
    const materialList    = $q('#materialList').first();

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
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger remove-row-btn" title="At least one item is required">&times;</button>
            </td>
        `;

        const trRemarks = document.createElement('tr');
        trRemarks.className = 'item-remarks-row';
        trRemarks.innerHTML = `
            <td colspan="3" class="pt-1 pb-2">
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

        trMain.querySelector('.remove-row-btn').onclick = () => {
            const currentRows = itemsTableBody.querySelectorAll('tr.item-main-row');
            if (currentRows.length <= 1) {
                alert('At least one item is required to make a Direct Material Request.');
                return;
            }
            trMain.remove();
            trRemarks.remove();
            updateItemRemoveButtons();
        };

        itemsTableBody.appendChild(trMain);
        itemsTableBody.appendChild(trRemarks);
        updateItemRemoveButtons();
    }

    addItemBtn.onclick = () => createItemRow();

    // Add 1 default row
    createItemRow();

    saveBtn.onclick = async () => {
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
            matHidden.value = matchedMatId;

            const material_item_id = matchedMatId;
            const quantity = parseFloat(row.querySelector('.item-qty').value);

            const remarksRow = row._remarksRow || row.nextElementSibling;
            const remarksTextarea = remarksRow ? remarksRow.querySelector('.item-remarks') : null;
            const remarks = remarksTextarea ? remarksTextarea.value.trim() : '';

            if (!material_item_id) {
                alert('Please select a valid Material Item from the list.');
                matInput.focus();
                return;
            }

            if (isNaN(quantity) || quantity <= 0) {
                alert('Please complete all quantity fields in the Items table.');
                return;
            }

            items.push({
                material_item_id: parseInt(material_item_id),
                quantity: quantity,
                remarks: remarks
            });
        }

        if (items.length === 0) {
            alert('At least one item is required to make a Direct Material Request.');
            return;
        }

        saveBtn.disabled = true;
        window.util.blockUI();

        const payload = {
            project_id: projectSelect.value,
            description: descriptionInput.value.trim(),
            items: JSON.stringify(items)
        };
        if (sectionSelect.value && parseInt(sectionSelect.value) > 0) {
            payload.section_id = sectionSelect.value;
        }

        let res = await window.util.$post('/api/direct_material_quantity_request/create', payload);
        window.util.unblockUI();

        if (res.status === 1) {
            window.util.navTo('/direct_material_quantity_request/' + res.data.id);
        } else {
            alert(res.message || 'Error occurred while saving Direct Material Request.');
            saveBtn.disabled = false;
        }
    };
</script>
</div>
@endsection
