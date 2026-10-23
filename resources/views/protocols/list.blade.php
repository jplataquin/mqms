@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/home">
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>Protocols Diagnostic Dashboard</span>
                    <i class="ms-2 bi bi-shield-check"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            <i class="bi bi-cpu me-1"></i> System Protocol Execution
        </div>
        <div class="folder-form-body">
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="form-group">
                        <label class="form-label fw-bold">Select Protocol</label>
                        <select class="form-control" id="protocolSelect">
                            <option value="">Loading protocols...</option>
                        </select>
                        <small class="form-text text-muted" id="protocolDesc"></small>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="form-group">
                        <label class="form-label fw-bold">Select Project</label>
                        <select class="form-control" id="projectSelect">
                            <option value="">-- Choose Project --</option>
                        </select>
                        <small class="form-text text-muted">Or enter specific ID below</small>
                    </div>
                </div>
                <div class="col-lg-2 d-flex align-items-end">
                    <button id="runBtn" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-play-fill me-1"></i> Run
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-lg-5 offset-lg-5">
                    <input type="number" id="manualProjectIdInput" class="form-control form-control-sm" placeholder="Manual Project ID (optional)" min="1"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnostic Results Section -->
    <div id="resultsArea" class="mt-4" style="display: none;">
        <!-- Status summary banner -->
        <div id="statusAlert" class="p-3 mb-3 rounded border"></div>

        <!-- Violations Container -->
        <div id="violationsContainer" style="display: none;">
            <div class="folder-form-container">
                <div class="folder-form-tab text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Budget Violations Detected
                </div>
                <div class="folder-form-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small">
                                    <th>Item ID</th>
                                    <th>Component Item Name</th>
                                    <th>Component</th>
                                    <th>Allowed Budget</th>
                                    <th>Total Requested (Actual)</th>
                                    <th>Over Budget Variance</th>
                                </tr>
                            </thead>
                            <tbody id="violationsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q, Template, $el} from '/adarna.js';

    const protocolSelect     = $q('#protocolSelect').first();
    const protocolDesc       = $q('#protocolDesc').first();
    const projectSelect      = $q('#projectSelect').first();
    const manualProjectId    = $q('#manualProjectIdInput').first();
    const runBtn             = $q('#runBtn').first();
    const resultsArea        = $q('#resultsArea').first();
    const statusAlert        = $q('#statusAlert').first();
    const violationsContainer = $q('#violationsContainer').first();
    const violationsTableBody = $q('#violationsTableBody').first();

    const t = new Template();
    let protocolList = [];

    // Load available protocols
    async function loadProtocols() {
        try {
            let res = await window.util.$get('/api/protocol/list');
            if (res.status === 1 && res.data) {
                protocolList = res.data;
                $el.clear(protocolSelect);

                protocolList.forEach((proto, idx) => {
                    let opt = document.createElement('option');
                    opt.value = proto.slug;
                    opt.textContent = proto.name;
                    if (idx === 0) opt.selected = true;
                    protocolSelect.appendChild(opt);
                });

                updateProtocolDesc();
            }
        } catch (err) {
            console.error('Failed to load protocols', err);
        }
    }

    function updateProtocolDesc() {
        const selected = protocolList.find(p => p.slug === protocolSelect.value);
        protocolDesc.textContent = selected ? selected.description : '';
    }

    protocolSelect.onchange = updateProtocolDesc;

    // Load active projects for selection
    async function loadProjects() {
        try {
            let res = await window.util.$get('/api/project/list', { limit: 100, status: 'ACTV' });
            if (res.status === 1 && res.data) {
                res.data.forEach(p => {
                    let opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `#${p.id} - ${p.name}`;
                    projectSelect.appendChild(opt);
                });
            }
        } catch (err) {
            console.error('Failed to load projects', err);
        }
    }

    // Run Protocol Diagnostic
    async function executeProtocol() {
        let protoSlug = protocolSelect.value;
        let projId = manualProjectId.value ? parseInt(manualProjectId.value) : parseInt(projectSelect.value);

        if (!protoSlug) {
            alert('Please select a protocol.');
            return;
        }

        if (!projId || isNaN(projId)) {
            alert('Please select a project or enter a valid Project ID.');
            return;
        }

        window.util.blockUI();
        resultsArea.style.display = 'none';

        try {
            let reply = await window.util.$post('/api/protocol/run', {
                protocol: protoSlug,
                project_id: projId
            });

            window.util.unblockUI();

            if (reply.status !== 1) {
                alert(reply.message || 'Error executing protocol.');
                return;
            }

            renderResults(reply.data);
        } catch (err) {
            window.util.unblockUI();
            alert('An unexpected error occurred while executing the protocol.');
            console.error(err);
        }
    }

    function renderResults(data) {
        resultsArea.style.display = 'block';
        $el.clear(statusAlert);
        $el.clear(violationsTableBody);

        const passed = data.passed;

        if (passed) {
            statusAlert.className = 'p-3 mb-3 rounded border border-success bg-success bg-opacity-10 text-success';
            let content = t.div({}, () => {
                t.div({ class: 'd-flex align-items-center mb-1' }, () => {
                    t.i({ class: 'bi bi-check-circle-fill me-2 fs-5' });
                    t.strong({ class: 'fs-6' }, 'DIAGNOSTIC PASSED: All Items Within Budget');
                });
                t.p({ class: 'mb-0 small' }, data.summary);
            });
            $el.append(content).to(statusAlert);
            violationsContainer.style.display = 'none';
        } else {
            statusAlert.className = 'p-3 mb-3 rounded border border-danger bg-danger bg-opacity-10 text-danger';
            let content = t.div({}, () => {
                t.div({ class: 'd-flex align-items-center mb-1' }, () => {
                    t.i({ class: 'bi bi-exclamation-octagon-fill me-2 fs-5' });
                    t.strong({ class: 'fs-6' }, 'DIAGNOSTIC FAILED: Budget Violations Found (' + data.violations_count + ')');
                });
                t.p({ class: 'mb-0 small' }, data.summary);
            });
            $el.append(content).to(statusAlert);

            // Populate violations table
            data.violations.forEach(v => {
                let tr = t.tr({}, () => {
                    t.td({ class: 'fw-bold' }, '#' + v.component_item_id);
                    t.td({}, v.component_item_name);
                    t.td({}, v.component_name || '-');
                    t.td({}, v.allowed_budget + ' ' + (v.unit_text || ''));
                    t.td({ class: 'fw-bold text-danger' }, v.total_requested + ' ' + (v.unit_text || ''));
                    t.td({ class: 'fw-bold text-danger' }, '+' + v.over_budget_quantity + ' ' + (v.unit_text || ''));
                });
                $el.append(tr).to(violationsTableBody);
            });

            violationsContainer.style.display = 'block';
        }
    }

    runBtn.onclick = executeProtocol;

    loadProtocols();
    loadProjects();
</script>
</div>
@endsection
