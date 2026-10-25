@extends('layouts.app')

@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/material_quantity_requests">
                        <span>
                        Material Request
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            Select
                        </span>
                        <i class="ms-2 bi bi-ui-checks-grid"></i>		
                    </a>
                </li>
            </ul>
        </div>
        <hr>
        
        <div class="folder-form-container">
            <div class="folder-form-tab">
                Material Quantity Select
            </div>
            <div class="folder-form-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Project <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="projectInput" list="projectList" placeholder="Type to search project..." autocomplete="off">
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
                    <div class="col-lg-3">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Section <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="sectionInput" list="sectionList" placeholder="Select Project first" autocomplete="off" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="clearSectionBtn" title="Clear Section"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="sectionList"></datalist>
                            <input type="hidden" id="sectionSelect" value="">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Contract Item <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="contractItemInput" list="contractItemList" placeholder="Select Section first" autocomplete="off" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="clearContractItemBtn" title="Clear Contract Item"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="contractItemList"></datalist>
                            <input type="hidden" id="contractItemSelect" value="">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Component <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="componentInput" list="componentList" placeholder="Select Contract Item first" autocomplete="off" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="clearComponentBtn" title="Clear Component"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="componentList"></datalist>
                            <input type="hidden" id="componentSelect" value="">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12 text-end">
                        <button id="createBtn" class="btn btn-warning">Create</button>
                        <button id="cancelBtn" class="btn btn-secondary me-3">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="module">
        import {$q, Template, $el} from '/adarna.js';
        
        const projectInput          = $q('#projectInput').first();
        const projectList           = $q('#projectList').first();
        const projectSelect         = $q('#projectSelect').first();
        const clearProjectBtn       = $q('#clearProjectBtn').first();

        const sectionInput          = $q('#sectionInput').first();
        const sectionList           = $q('#sectionList').first();
        const sectionSelect         = $q('#sectionSelect').first();
        const clearSectionBtn       = $q('#clearSectionBtn').first();

        const contractItemInput     = $q('#contractItemInput').first();
        const contractItemList      = $q('#contractItemList').first();
        const contractItemSelect    = $q('#contractItemSelect').first();
        const clearContractItemBtn  = $q('#clearContractItemBtn').first();

        const componentInput        = $q('#componentInput').first();
        const componentList         = $q('#componentList').first();
        const componentSelect       = $q('#componentSelect').first();
        const clearComponentBtn     = $q('#clearComponentBtn').first();

        const createBtn             = $q('#createBtn').first();
        const cancelBtn             = $q('#cancelBtn').first();

        cancelBtn.onclick = (e) => {
            e.preventDefault();
            window.util.navTo('/material_quantity_requests');
        };

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

        async function handleProjectChange() {
            const newProjId = findOptionId(projectInput, projectList);
            const currProjId = projectSelect.value;

            if (newProjId !== currProjId) {
                projectSelect.value = newProjId;

                // Reset Section
                sectionInput.value = '';
                sectionSelect.value = '';
                $el.clear(sectionList);

                // Reset Contract Item
                contractItemInput.value = '';
                contractItemSelect.value = '';
                $el.clear(contractItemList);
                contractItemInput.disabled = true;
                contractItemInput.placeholder = 'Select Section first';

                // Reset Component
                componentInput.value = '';
                componentSelect.value = '';
                $el.clear(componentList);
                componentInput.disabled = true;
                componentInput.placeholder = 'Select Contract Item first';

                if (!newProjId) {
                    sectionInput.disabled = true;
                    sectionInput.placeholder = 'Select Project first';
                    return;
                }

                sectionInput.disabled = false;
                sectionInput.placeholder = 'Type to search section...';

                window.util.blockUI();
                try {
                    const reply = await window.util.$get('/api/section/list', {
                        project_id: newProjId,
                        orderBy: 'name',
                        status: 'ACTV',
                        order: 'ASC'
                    });

                    if (reply.status <= 0) {
                        window.util.showMsg(reply);
                        return false;
                    }

                    if (reply.data) {
                        reply.data.forEach((item) => {
                            const opt = document.createElement('option');
                            opt.value = item.name;
                            opt.setAttribute('data-id', item.id);
                            sectionList.appendChild(opt);
                        });
                    }
                } finally {
                    window.util.unblockUI();
                }
            }
        }

        async function handleSectionChange() {
            const newSectionId = findOptionId(sectionInput, sectionList);
            const currSectionId = sectionSelect.value;

            if (newSectionId !== currSectionId) {
                sectionSelect.value = newSectionId;

                // Reset Contract Item
                contractItemInput.value = '';
                contractItemSelect.value = '';
                $el.clear(contractItemList);

                // Reset Component
                componentInput.value = '';
                componentSelect.value = '';
                $el.clear(componentList);
                componentInput.disabled = true;
                componentInput.placeholder = 'Select Contract Item first';

                if (!newSectionId) {
                    contractItemInput.disabled = true;
                    contractItemInput.placeholder = 'Select Section first';
                    return;
                }

                contractItemInput.disabled = false;
                contractItemInput.placeholder = 'Type to search contract item...';

                window.util.blockUI();
                try {
                    const reply = await window.util.$get('/api/contract_item/list', {
                        section_id: newSectionId,
                        orderBy: 'name',
                        status: 'APRV',
                        order: 'ASC'
                    });

                    if (reply.status <= 0) {
                        window.util.showMsg(reply);
                        return false;
                    }

                    if (reply.data) {
                        reply.data.forEach((item) => {
                            const opt = document.createElement('option');
                            opt.value = '[ ' + item.item_code + ' ] ' + item.description;
                            opt.setAttribute('data-id', item.id);
                            contractItemList.appendChild(opt);
                        });
                    }
                } finally {
                    window.util.unblockUI();
                }
            }
        }

        async function handleContractItemChange() {
            const newContractItemId = findOptionId(contractItemInput, contractItemList);
            const currContractItemId = contractItemSelect.value;

            if (newContractItemId !== currContractItemId) {
                contractItemSelect.value = newContractItemId;

                // Reset Component
                componentInput.value = '';
                componentSelect.value = '';
                $el.clear(componentList);

                if (!newContractItemId) {
                    componentInput.disabled = true;
                    componentInput.placeholder = 'Select Contract Item first';
                    return;
                }

                componentInput.disabled = false;
                componentInput.placeholder = 'Type to search component...';

                window.util.blockUI();
                try {
                    const reply = await window.util.$get('/api/component/list', {
                        contract_item_id: newContractItemId,
                        orderBy: 'name',
                        status: 'APRV',
                        order: 'ASC'
                    });

                    if (reply.status <= 0) {
                        window.util.showMsg(reply);
                        return false;
                    }

                    if (reply.data) {
                        reply.data.forEach((item) => {
                            const opt = document.createElement('option');
                            opt.value = item.name;
                            opt.setAttribute('data-id', item.id);
                            componentList.appendChild(opt);
                        });
                    }
                } finally {
                    window.util.unblockUI();
                }
            }
        }

        function handleComponentChange() {
            componentSelect.value = findOptionId(componentInput, componentList);
        }

        projectInput.oninput = handleProjectChange;
        projectInput.onchange = handleProjectChange;

        clearProjectBtn.onclick = () => {
            projectInput.value = '';
            handleProjectChange();
            projectInput.focus();
        };

        sectionInput.oninput = handleSectionChange;
        sectionInput.onchange = handleSectionChange;

        clearSectionBtn.onclick = () => {
            sectionInput.value = '';
            handleSectionChange();
            sectionInput.focus();
        };

        contractItemInput.oninput = handleContractItemChange;
        contractItemInput.onchange = handleContractItemChange;

        clearContractItemBtn.onclick = () => {
            contractItemInput.value = '';
            handleContractItemChange();
            contractItemInput.focus();
        };

        componentInput.oninput = handleComponentChange;
        componentInput.onchange = handleComponentChange;

        clearComponentBtn.onclick = () => {
            componentInput.value = '';
            handleComponentChange();
            componentInput.focus();
        };

        createBtn.onclick = (e) => {
            e.preventDefault();

            if (!projectSelect.value || !sectionSelect.value || !contractItemSelect.value || !componentSelect.value) {
                window.util.alert('Error', 'All fields are required to create a request');
                return false;
            }
            
            window.util.navTo('/material_quantity_request/create/' + projectSelect.value + '/' + sectionSelect.value + '/' + contractItemSelect.value + '/' + componentSelect.value);
        };
    </script>
</div>
@endsection
