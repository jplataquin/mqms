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
                            <select class="form-select" id="supplierSelect">
                                <option value=""> - </option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{$supplier->id}}">{{$supplier->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Project (Optional)</label>
                            <select class="form-select" id="projectSelect">
                                <option value=""> - </option>
                                @foreach($projects as $project)
                                    <option value="{{$project->id}}">{{$project->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Section (Optional)</label>
                            <select class="form-select" id="sectionSelect">
                                <option value=""> - </option>
                            </select>
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

        const list           = $q('#list').first();
        const query          = $q('#query').first();
        const searchBtn      = $q('#searchBtn').first();
        const showMoreBtn    = $q('#showMoreBtn').first();
        const sortSelect     = $q('#sortSelect').first();
        const projectSelect  = $q('#projectSelect').first();
        const sectionSelect  = $q('#sectionSelect').first();
        const supplierSelect = $q('#supplierSelect').first();
        const statusSelect   = $q('#statusSelect').first();

        let page    = 1;
        let order   = 'DESC';
        let orderBy = 'id';

        const t = new Template();

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
                    t.td({}, item.payment_term ? item.payment_term.name : '-');
                    t.td({}, item.project ? item.project.name : '-');
                    t.td({}, item.section ? item.section.name : '-');
                    t.td({}, item.status);
                    t.td({}, item.created_at);
                });

                row.onclick = () => {
                    window.util.navTo('/direct_purchase_order/' + item.id);
                };

                list.appendChild(row);
            });
        }

        async function fetchList() {
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

        projectSelect.onchange = async () => {
            $el.clear(sectionSelect);
            sectionSelect.appendChild(t.option({value: ''}, ' - '));
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

        fetchList();
    </script>
</div>
@endsection
