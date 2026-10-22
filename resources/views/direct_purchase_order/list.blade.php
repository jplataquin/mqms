@extends('layouts.app')

@section('content')
<div id="content">

    <div class="container">
        <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
            <ul>
                <li>
                    <a href="/direct_purchase_order">
                        <span>
                        Direct Purchase Order
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                            List
                        </span>
                        <i class="ms-2 bi bi-list-ul"></i>
                    </a>
                </li>
            </ul>
        </div>
        <hr>

        <div class="folder-form-container">
            <div class="folder-form-tab">
                Direct Purchase Orders
            </div>
            <div class="folder-form-body">
                <div class="row mb-3">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Sort By</label>
                            <select class="form-control" id="sortSelect">
                                <option value="1" selected>Latest Entry</option>
                                <option value="2">Oldest Entry</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>DPO ID</label>
                            <input type="text" id="query" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" id="statusSelect">
                                <option value=""> - </option>
                                <option value="DRFT">Draft</option>
                                <option value="PEND">Pending</option>
                                <option value="APRV">Approved</option>
                                <option value="DPRV">Disapproved</option>
                                <option value="VOID">Void</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Supplier</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="supplierInput" list="supplierList" placeholder="Search supplier..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearSupplierBtn"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="supplierList">
                                @foreach($suppliers as $supplier)
                                    <option value="{{$supplier->name}}" data-id="{{$supplier->id}}"></option>
                                @endforeach
                            </datalist>
                            <input type="hidden" id="supplierSelect" value="">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Project (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="projectInput" list="projectList" placeholder="Search project..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearProjectBtn"><i class="bi bi-x-lg"></i></button>
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
                        <div class="form-group">
                            <label>Section (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="sectionInput" list="sectionList" placeholder="Select a Project first" autocomplete="off" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="clearSectionBtn"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <datalist id="sectionList">
                            </datalist>
                            <input type="hidden" id="sectionSelect" value="">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 text-end">
                        <a href="/direct_purchase_order/create" class="btn btn-warning">Create Direct PO</a>
                        <button id="searchBtn" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table border table-hover">
                <thead>
                    <tr>
                        <th>DPO #</th>
                        <th>Supplier</th>
                        <th>Payment Term</th>
                        <th>Project</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Date Needed</th>
                        <th>Date Created</th>
                    </tr>
                </thead>
                <tbody id="list">
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-lg-12">
                <button id="showMoreBtn" class="btn w-100 btn-primary">Show More</button>
            </div>
        </div>

    </div>

    <script type="module">
        import {$q, Template, $el} from '/adarna.js';

        const list              = $q('#list').first();
        const query             = $q('#query').first();
        const searchBtn         = $q('#searchBtn').first();
        const showMoreBtn       = $q('#showMoreBtn').first();
        const sortSelect        = $q('#sortSelect').first();
        const statusSelect      = $q('#statusSelect').first();

        const supplierInput     = $q('#supplierInput').first();
        const supplierList      = $q('#supplierList').first();
        const supplierSelect    = $q('#supplierSelect').first();
        const clearSupplierBtn  = $q('#clearSupplierBtn').first();

        const projectInput      = $q('#projectInput').first();
        const projectList       = $q('#projectList').first();
        const projectSelect     = $q('#projectSelect').first();
        const clearProjectBtn   = $q('#clearProjectBtn').first();

        const sectionInput      = $q('#sectionInput').first();
        const sectionList       = $q('#sectionList').first();
        const sectionSelect     = $q('#sectionSelect').first();
        const clearSectionBtn   = $q('#clearSectionBtn').first();

        let page    = 1;
        let order   = 'DESC';
        let orderBy = 'id';

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
                sectionInput.placeholder = 'Search section...';

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

        function reinitalize() {
            page = 1;
            $el.clear(list);
        }

        function renderRows(data) {
            data.forEach(item => {
                let row = t.tr({class: 'selectable-div', style: 'cursor:pointer'}, () => {
                    t.td({}, () => {
                        t.a({href: '/direct_purchase_order/' + item.id}, String(item.id).padStart(6, '0'));
                    });
                    t.td({}, item.supplier ? item.supplier.name : '-');
                    t.td({}, item.payment_term ? (item.payment_term.text || item.payment_term.name) : '-');
                    t.td({}, item.project ? item.project.name : '-');
                    t.td({}, item.section ? item.section.name : '-');
                    t.td({}, item.status);
                    t.td({}, item.date_needed || '-');
                    t.td({}, item.created_at);
                });

                row.onclick = () => {
                    window.util.navTo('/direct_purchase_order/' + item.id);
                };

                list.appendChild(row);
            });
        }

        async function fetchList() {
            supplierSelect.value = findOptionId(supplierInput, supplierList);
            projectSelect.value = findOptionId(projectInput, projectList);
            sectionSelect.value = findOptionId(sectionInput, sectionList);

            window.util.blockUI();
            let res = await window.util.$get('/api/direct_purchase_order/list', {
                page: page,
                query: query.value,
                status: statusSelect.value,
                supplier_id: supplierSelect.value,
                project_id: projectSelect.value,
                section_id: sectionSelect.value,
                order: order,
                order_by: orderBy
            });
            window.util.unblockUI();

            if (res.status === 1) {
                renderRows(res.data);
            }
        }

        searchBtn.onclick = () => {
            reinitalize();
            fetchList();
        };

        showMoreBtn.onclick = () => {
            page++;
            fetchList();
        };

        sortSelect.onchange = () => {
            if (sortSelect.value == '1') {
                order = 'DESC';
            } else {
                order = 'ASC';
            }
            reinitalize();
            fetchList();
        };

        fetchList();
    </script>
</div>
@endsection
