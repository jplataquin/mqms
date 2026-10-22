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
                    <span>List</span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Direct Material Requests</h5>
            <a href="/direct_material_quantity_request/create" class="btn btn-warning btn-sm">
                <i class="bi bi-plus-circle"></i> Create Request
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search Request #</label>
                    <input type="number" class="form-control" id="queryInput" placeholder="Enter ID...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusSelect">
                        <option value="">All Statuses</option>
                        <option value="DRFT">Draft</option>
                        <option value="PEND">Pending Review</option>
                        <option value="APRV">Approved</option>
                        <option value="DPRV">Disapproved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select class="form-select" id="projectSelect">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary me-2 w-100" id="filterBtn">Filter</button>
                    <button class="btn btn-secondary w-100" id="resetBtn">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Project</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Date Needed</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div id="pageInfo" class="text-muted small"></div>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-1" id="prevBtn" disabled>Previous</button>
                <button class="btn btn-sm btn-outline-secondary" id="nextBtn" disabled>Next</button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q, Template} from '/adarna.js';

    let page = 1;
    const limit = 10;
    const t = new Template();

    const queryInput    = $q('#queryInput').first();
    const statusSelect  = $q('#statusSelect').first();
    const projectSelect = $q('#projectSelect').first();
    const filterBtn     = $q('#filterBtn').first();
    const resetBtn      = $q('#resetBtn').first();
    const tableBody     = $q('#tableBody').first();
    const prevBtn       = $q('#prevBtn').first();
    const nextBtn       = $q('#nextBtn').first();
    const pageInfo      = $q('#pageInfo').first();

    async function loadData() {
        window.util.blockUI();
        let res = await window.util.$get('/api/direct_material_quantity_request/list', {
            page: page,
            limit: limit,
            query: queryInput.value.trim(),
            status: statusSelect.value,
            project_id: projectSelect.value
        });
        window.util.unblockUI();

        tableBody.innerHTML = '';

        if (res.status === 1 && res.data && res.data.length > 0) {
            res.data.forEach(item => {
                const tr = document.createElement('tr');
                const badgeColor = item.status === 'APRV' ? 'success' : (item.status === 'PEND' ? 'warning' : (item.status === 'DPRV' ? 'danger' : 'secondary'));
                const formattedId = String(item.id).padStart(6, '0');

                tr.innerHTML = `
                    <td class="font-monospace fw-bold">
                        <a href="/direct_material_quantity_request/${item.id}">#${formattedId}</a>
                    </td>
                    <td>${item.project ? item.project.name : '-'}</td>
                    <td>${item.section ? item.section.name : '-'}</td>
                    <td><span class="badge bg-${badgeColor}">${item.status}</span></td>
                    <td>${item.date_needed ? item.date_needed : '-'}</td>
                    <td>${item.creator ? item.creator.name : '-'}</td>
                    <td>${item.created_at ? item.created_at.substring(0, 10) : '-'}</td>
                    <td class="text-end">
                        <a href="/direct_material_quantity_request/${item.id}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            prevBtn.disabled = (page <= 1);
            nextBtn.disabled = (res.data.length < limit);
            pageInfo.textContent = `Page ${page}`;
        } else {
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No records found</td></tr>`;
            prevBtn.disabled = (page <= 1);
            nextBtn.disabled = true;
            pageInfo.textContent = `Page ${page}`;
        }
    }

    filterBtn.onclick = () => {
        page = 1;
        loadData();
    };

    resetBtn.onclick = () => {
        queryInput.value = '';
        statusSelect.value = '';
        projectSelect.value = '';
        page = 1;
        loadData();
    };

    prevBtn.onclick = () => {
        if (page > 1) {
            page--;
            loadData();
        }
    };

    nextBtn.onclick = () => {
        page++;
        loadData();
    };

    loadData();
</script>
</div>
@endsection
