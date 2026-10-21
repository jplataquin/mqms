@extends('layouts.app')

@section('content')
<div id="content">

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
                    <span>List</span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Approved Direct Material Requests for Canvassing</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search Request #</label>
                    <input type="number" class="form-control" id="queryInput" placeholder="Enter ID...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project</label>
                    <select class="form-select" id="projectSelect">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
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
                    <thead class="border-bottom">
                        <tr>
                            <th>#</th>
                            <th>Project</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Requested By</th>
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
    import {$q} from '/adarna.js';

    let page = 1;
    const limit = 10;

    const queryInput    = $q('#queryInput').first();
    const projectSelect = $q('#projectSelect').first();
    const filterBtn     = $q('#filterBtn').first();
    const resetBtn      = $q('#resetBtn').first();
    const tableBody     = $q('#tableBody').first();
    const prevBtn       = $q('#prevBtn').first();
    const nextBtn       = $q('#nextBtn').first();
    const pageInfo      = $q('#pageInfo').first();

    async function loadData() {
        window.util.blockUI();
        let res = await window.util.$get('/api/direct_material_canvass/list', {
            page: page,
            limit: limit,
            query: queryInput.value.trim(),
            project_id: projectSelect.value
        });
        window.util.unblockUI();

        tableBody.innerHTML = '';

        if (res.status === 1 && res.data && res.data.length > 0) {
            res.data.forEach(item => {
                const tr = document.createElement('tr');
                const formattedId = String(item.id).padStart(6, '0');

                tr.innerHTML = `
                    <td class="font-monospace fw-bold">
                        <a href="/direct_material_canvass/${item.id}">#${formattedId}</a>
                    </td>
                    <td>${item.project ? item.project.name : '-'}</td>
                    <td>${item.section ? item.section.name : '-'}</td>
                    <td><span class="badge bg-success">${item.status}</span></td>
                    <td>${item.creator ? item.creator.name : '-'}</td>
                    <td>${item.created_at ? item.created_at.substring(0, 10) : '-'}</td>
                    <td class="text-end">
                        <a href="/direct_material_canvass/${item.id}" class="btn btn-sm btn-primary">Canvass</a>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            prevBtn.disabled = (page <= 1);
            nextBtn.disabled = (res.data.length < limit);
            pageInfo.textContent = `Page ${page}`;
        } else {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No approved direct material requests found</td></tr>`;
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
