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
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="projectInput" list="projectList" placeholder="Search project by name or ID..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearProjectBtn" title="Clear Project"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <datalist id="projectList">
                            @if(!empty($projects))
                                @foreach($projects as $project)
                                    <option value="#{{$project->id}} - {{$project->name}}" data-id="{{$project->id}}" data-name="{{$project->name}}"></option>
                                @endforeach
                            @endif
                        </datalist>
                        <input type="hidden" id="projectSelect" value="">
                        <small class="form-text text-muted">Type project name/ID or enter manual ID below</small>
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
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard2-data me-1"></i> Diagnostic Execution Results</h5>
            </div>
            <div>
                <button id="printBtn" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer me-1"></i> Print Results
                </button>
            </div>
        </div>

        <!-- Status summary banner -->
        <div id="statusAlert" class="mb-3"></div>

        <!-- Metric summary strip -->
        <div id="metricsRow" class="row g-2 mb-3"></div>

        <!-- Violations Container -->
        <div id="violationsContainer" style="display: none;">
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-danger bg-opacity-10 d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Non-Conforming Items (<span id="violationsCountBadge">0</span>)
                        </span>
                        <span class="text-muted small">Items exceeding authorized budget &bull; Direct record traceability active</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="violationFilterInput" class="form-control form-control-sm" placeholder="Search item, component, or MQR #..." style="width: 260px;" />
                        <button id="toggleAllBtn" class="btn btn-sm btn-outline-secondary text-nowrap">
                            <i class="bi bi-arrows-expand me-1"></i> Expand All
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="violationsList" class="d-flex flex-column gap-3"></div>
                </div>
            </div>
        </div>

        <!-- Conforming Success Container -->
        <div id="conformingContainer" style="display: none;" class="card border-success p-4 text-center my-3">
            <div class="text-success mb-2">
                <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
            </div>
            <h4 class="text-success mb-1">Diagnostic Conformance Verified</h4>
            <p class="text-muted mb-0" id="conformingSummaryText">All evaluated component items and requisition records strictly conform to authorized budgets.</p>
        </div>
    </div>
</div>

<script type="module">
    import {$q, Template, $el} from '/adarna.js';

    const protocolSelect        = $q('#protocolSelect').first();
    const protocolDesc          = $q('#protocolDesc').first();
    const projectInput          = $q('#projectInput').first();
    const projectList           = $q('#projectList').first();
    const projectSelect         = $q('#projectSelect').first();
    const clearProjectBtn       = $q('#clearProjectBtn').first();
    const manualProjectId       = $q('#manualProjectIdInput').first();
    const runBtn                = $q('#runBtn').first();
    const printBtn              = $q('#printBtn').first();
    const resultsArea           = $q('#resultsArea').first();
    const statusAlert           = $q('#statusAlert').first();
    const metricsRow            = $q('#metricsRow').first();
    const violationsContainer   = $q('#violationsContainer').first();
    const violationsCountBadge  = $q('#violationsCountBadge').first();
    const violationsList        = $q('#violationsList').first();
    const conformingContainer   = $q('#conformingContainer').first();
    const conformingSummaryText = $q('#conformingSummaryText').first();
    const violationFilterInput  = $q('#violationFilterInput').first();
    const toggleAllBtn          = $q('#toggleAllBtn').first();

    const t = new Template();
    let protocolList = [];
    let currentViolations = [];
    let allExpanded = false;

    function findOptionId(input, datalist) {
        if (!input) return '';
        const rawVal = input.value.trim();
        if (!rawVal) return '';
        const val = rawVal.toLowerCase();

        if (datalist && datalist.options) {
            const options = Array.from(datalist.options);

            // 1. Exact match on option value (e.g., "#1 - Project Name")
            let match = options.find(opt => opt.value && opt.value.trim().toLowerCase() === val);
            if (match) return match.getAttribute('data-id') || '';

            // 2. Exact match on project name
            match = options.find(opt => {
                const name = (opt.getAttribute('data-name') || '').trim().toLowerCase();
                return name && name === val;
            });
            if (match) return match.getAttribute('data-id') || '';

            // 3. Exact match on ID or #ID
            match = options.find(opt => {
                const id = opt.getAttribute('data-id') || '';
                return id && (val === id || val === `#${id}`);
            });
            if (match) return match.getAttribute('data-id') || '';

            // 4. Starts with #ID or ID -
            const prefixMatch = val.match(/^#?(\d+)\b/);
            if (prefixMatch) {
                const matchedOpt = options.find(opt => (opt.getAttribute('data-id') || '') === prefixMatch[1]);
                if (matchedOpt) return prefixMatch[1];
            }

            // 5. Unique partial match on name or value
            const partialMatches = options.filter(opt => {
                const optVal = (opt.value || '').toLowerCase();
                const name = (opt.getAttribute('data-name') || '').toLowerCase();
                return optVal.includes(val) || name.includes(val);
            });
            if (partialMatches.length === 1) {
                return partialMatches[0].getAttribute('data-id') || '';
            }
        }

        // Direct numeric ID fallback
        if (/^\d+$/.test(rawVal)) {
            return rawVal;
        }

        return '';
    }

    function syncProjectSelection() {
        if (!projectInput) return;
        const matchedId = findOptionId(projectInput, projectList);
        if (projectSelect) projectSelect.value = matchedId;
        if (matchedId && manualProjectId) {
            manualProjectId.value = matchedId;
        }
    }

    if (projectInput) {
        projectInput.addEventListener('input', syncProjectSelection);
        projectInput.addEventListener('change', syncProjectSelection);
    }

    if (clearProjectBtn) {
        clearProjectBtn.onclick = () => {
            if (projectInput) projectInput.value = '';
            if (projectSelect) projectSelect.value = '';
            if (manualProjectId) manualProjectId.value = '';
            if (projectInput) projectInput.focus();
        };
    }

    if (manualProjectId) {
        manualProjectId.addEventListener('input', () => {
            const id = manualProjectId.value.trim();
            if (projectSelect) projectSelect.value = id;
            if (id && projectList && projectList.options) {
                const opt = Array.from(projectList.options).find(o => o.getAttribute('data-id') === id);
                if (opt && projectInput) {
                    projectInput.value = opt.value;
                }
            } else if (!id && projectInput) {
                projectInput.value = '';
            }
        });
    }

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
            let res = await window.util.$get('/api/project/list', { limit: 500, status: 'ACTV', order_by: 'name', order: 'ASC' });
            if (res.status === 1 && res.data && res.data.length > 0) {
                $el.clear(projectList);
                res.data.forEach(p => {
                    let opt = document.createElement('option');
                    opt.value = `#${p.id} - ${p.name}`;
                    opt.setAttribute('data-id', p.id);
                    opt.setAttribute('data-name', p.name);
                    projectList.appendChild(opt);
                });

                // Sync initial selection if an ID is present
                const currentId = (manualProjectId && manualProjectId.value) || (projectSelect && projectSelect.value);
                if (currentId && projectList && projectList.options) {
                    const opt = Array.from(projectList.options).find(o => o.getAttribute('data-id') == currentId);
                    if (opt && projectInput && !projectInput.value) {
                        projectInput.value = opt.value;
                    }
                }
            }
        } catch (err) {
            console.error('Failed to load projects', err);
        }
    }

    // Run Protocol Diagnostic
    async function executeProtocol() {
        let protoSlug = protocolSelect.value;
        let projId = manualProjectId.value ? parseInt(manualProjectId.value) : parseInt(projectSelect.value);

        if ((!projId || isNaN(projId)) && projectInput && projectInput.value.trim()) {
            const detectedId = findOptionId(projectInput, projectList);
            if (detectedId) {
                projId = parseInt(detectedId);
                projectSelect.value = detectedId;
            }
        }

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
        $el.clear(metricsRow);
        $el.clear(violationsList);
        violationFilterInput.value = '';
        currentViolations = data.violations || [];
        allExpanded = false;

        const passed = data.passed;
        const totalItems = data.total_items || 0;
        const violationCount = data.violations_count || 0;
        const conformingCount = Math.max(0, totalItems - violationCount);
        const conformingPct = totalItems > 0 ? Math.round((conformingCount / totalItems) * 100) : 100;

        // Render Status Summary Banner
        if (passed) {
            statusAlert.className = 'p-3 rounded border border-success bg-success bg-opacity-10 text-success';
            let content = t.div({}, () => {
                t.div({ class: 'd-flex align-items-center mb-1' }, () => {
                    t.i({ class: 'bi bi-check-circle-fill me-2 fs-5' });
                    t.strong({ class: 'fs-6' }, 'DIAGNOSTIC PASSED: All Evaluated Records Within Budget');
                });
                t.p({ class: 'mb-0 small' }, data.summary);
            });
            $el.append(content).to(statusAlert);

            violationsContainer.style.display = 'none';
            conformingContainer.style.display = 'block';
            conformingSummaryText.textContent = data.summary;
        } else {
            statusAlert.className = 'p-3 rounded border border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger';
            let content = t.div({}, () => {
                t.div({ class: 'd-flex align-items-center mb-1' }, () => {
                    t.i({ class: 'bi bi-exclamation-octagon-fill me-2 fs-5' });
                    t.strong({ class: 'fs-6' }, 'DIAGNOSTIC FAILED: Non-Conforming Budget Overruns Found (' + violationCount + ')');
                });
                t.p({ class: 'mb-0 small' }, data.summary);
            });
            $el.append(content).to(statusAlert);

            conformingContainer.style.display = 'none';
            violationsContainer.style.display = 'block';
            violationsCountBadge.textContent = violationCount;

            renderViolationCards(currentViolations);
        }

        // Render Metrics Summary Row
        renderMetricsStrip(data, totalItems, violationCount, conformingCount, conformingPct);
    }

    function renderMetricsStrip(data, totalItems, violationCount, conformingCount, conformingPct) {
        const metrics = [
            {
                label: 'Project Context',
                value: `#${data.project_id} - ${data.project_name || 'Project'}`,
                icon: 'bi-building',
                textColor: 'text-light'
            },
            {
                label: 'Total Items Tested',
                value: `${totalItems} Component Item(s)`,
                icon: 'bi-collection',
                textColor: 'text-light'
            },
            {
                label: 'Non-Conforming',
                value: `${violationCount} Violation(s)`,
                icon: 'bi-exclamation-diamond',
                textColor: violationCount > 0 ? 'text-danger fw-bold' : 'text-success'
            },
            {
                label: 'Budget Conformance',
                value: `${conformingPct}% in Authorized Budget`,
                icon: 'bi-percent',
                textColor: conformingPct === 100 ? 'text-success' : 'text-warning'
            }
        ];

        metrics.forEach(m => {
            let col = t.div({ class: 'col-md-3 col-6' }, () => {
                t.div({ class: 'card p-2 bg-dark border-secondary h-100' }, () => {
                    t.div({ class: 'd-flex align-items-center' }, () => {
                        t.i({ class: `bi ${m.icon} fs-4 me-2 text-secondary` });
                        t.div({}, () => {
                            t.small({ class: 'text-muted d-block' }, m.label);
                            t.span({ class: `small ${m.textColor}` }, m.value);
                        });
                    });
                });
            });
            $el.append(col).to(metricsRow);
        });
    }

    function renderViolationCards(violations) {
        $el.clear(violationsList);

        if (!violations.length) {
            let emptyMsg = t.div({ class: 'p-4 text-center text-muted card border-secondary' }, () => {
                t.i({ class: 'bi bi-filter-circle fs-3 d-block mb-2' });
                t.span({}, 'No non-conforming items matched the current search filter.');
            });
            $el.append(emptyMsg).to(violationsList);
            return;
        }

        violations.forEach(v => {
            const breakdown = v.breakdown || [];
            const overrunPct = v.allowed_budget > 0 ? Math.round((v.over_budget_quantity / v.allowed_budget) * 100) : 100;
            const unitText = v.unit_text ? ` ${v.unit_text}` : '';

            let card = t.div({ class: 'card bg-dark violation-card mb-2', 'data-item-id': v.component_item_id }, () => {
                // Card Body
                t.div({ class: 'card-body p-3' }, () => {
                    // Context summary: Contract Item & Record Type
                    t.div({ class: 'd-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 p-2 rounded bg-secondary bg-opacity-10 border border-secondary small' }, () => {
                        t.div({}, () => {
                            t.span({ class: 'text-muted me-1' }, 'Contract Item:');
                            if (v.contract_item_id) {
                                t.a({
                                    href: v.contract_item_url || (`/project/section/contract_item/${v.contract_item_id}`),
                                    target: '_blank',
                                    class: 'text-light fw-bold text-decoration-none'
                                }, `${v.contract_item_code ? v.contract_item_code + ' ' : ''}${v.contract_item_description || v.contract_item_name || ('#' + v.contract_item_id)}`);
                            } else {
                                t.span({ class: 'text-muted' }, 'None');
                            }
                        });
                        t.div({}, () => {
                            t.span({ class: 'text-muted me-1' }, 'Record Type:');
                            t.span({ class: 'badge bg-secondary me-1' }, v.contract_item_type_name || v.contract_item_type || 'Component Item');
                            t.span({ class: 'badge bg-dark border border-secondary text-light' }, v.record_type || 'Component Item');
                        });
                    });

                    // Budget comparison row
                    t.div({ class: 'row g-2 mb-3 bg-secondary bg-opacity-10 p-2 rounded border border-secondary' }, () => {
                        t.div({ class: 'col-md-3 col-6' }, () => {
                            t.small({ class: 'text-muted d-block' }, 'Allowed Budget');
                            t.strong({ class: 'text-light' }, `${v.allowed_budget}${unitText}`);
                        });
                        t.div({ class: 'col-md-3 col-6' }, () => {
                            t.small({ class: 'text-muted d-block' }, 'Total Requested (Actual)');
                            t.strong({ class: 'text-danger' }, `${v.total_requested}${unitText}`);
                        });
                        t.div({ class: 'col-md-3 col-6' }, () => {
                            t.small({ class: 'text-muted d-block' }, 'Over Budget Variance');
                            t.strong({ class: 'text-danger' }, `+${v.over_budget_quantity}${unitText}`);
                        });
                        t.div({ class: 'col-md-3 col-6' }, () => {
                            t.small({ class: 'text-muted d-block' }, 'Variance Status');
                            t.span({ class: 'badge bg-danger' }, `+${overrunPct}% Over Budget`);
                        });
                    });

                    // Traceability breakdown table
                    t.div({ class: 'traceability-section' }, () => {
                        t.div({ class: 'd-flex align-items-center justify-content-between mb-2' }, () => {
                            t.span({ class: 'small fw-bold text-uppercase text-muted' }, () => {
                                t.i({ class: 'bi bi-diagram-2 me-1 text-primary' });
                                t.span({}, 'Traced Non-Conforming Requisition Records (Material Quantity Requests)');
                            });
                        });

                        if (breakdown.length) {
                            t.div({ class: 'table-responsive' }, () => {
                                t.table({ class: 'table table-sm table-bordered align-middle mb-0' }, () => {
                                    t.thead({ class: 'table-dark text-uppercase small' }, () => {
                                        t.tr({}, () => {
                                            t.th({ style: 'width: 90px;' }, 'MQR ID');
                                            t.th({}, 'Requested Material Item');
                                            t.th({ class: 'text-end', style: 'width: 110px;' }, 'Req. Qty');
                                            t.th({ class: 'text-center', style: 'width: 90px;' }, 'Factor');
                                            t.th({ class: 'text-end', style: 'width: 130px;' }, 'Charged Qty');
                                            t.th({ class: 'text-center', style: 'width: 80px;' }, 'Status');
                                            t.th({ style: 'width: 120px;' }, 'Date Needed');
                                            t.th({ class: 'text-center', style: 'width: 90px;' }, 'Trace');
                                        });
                                    });

                                    t.tbody({}, () => {
                                        breakdown.forEach(item => {
                                            t.tr({}, () => {
                                                // MQR ID
                                                t.td({ class: 'font-monospace' }, String(item.material_quantity_request_id));

                                                // Material item
                                                t.td({}, () => {
                                                    t.span({ class: 'fw-bold' }, item.material_item_name || `Item #${item.material_item_id}`);
                                                });

                                                // Requested quantity
                                                t.td({ class: 'text-end font-monospace' }, String(item.requested_quantity));

                                                // Equivalent conversion factor
                                                t.td({ class: 'text-center font-monospace text-muted' }, `× ${item.equivalent}`);

                                                // Charged actual quantity
                                                t.td({ class: 'text-end font-monospace text-danger fw-bold' }, `${item.actual_quantity}${unitText}`);

                                                // Status badge
                                                t.td({ class: 'text-center' }, () => {
                                                    let statusClass = item.request_status === 'APRV' ? 'bg-success' : 'bg-info';
                                                    t.span({ class: `badge ${statusClass}` }, item.request_status || item.status);
                                                });

                                                // Date needed
                                                t.td({ class: 'small text-muted text-nowrap' }, item.request_date_needed || '-');

                                                // Action to navigate directly
                                                t.td({ class: 'text-center' }, () => {
                                                    let mqrUrl = item.material_quantity_request_url || `/material_quantity_request/${item.material_quantity_request_id}`;
                                                    let btn = t.button({
                                                        type: 'button',
                                                        class: 'btn btn-sm btn-primary py-0 px-2 text-nowrap',
                                                        title: 'View Material Quantity Request Record'
                                                    }, () => {
                                                        t.i({ class: 'bi bi-eye-fill me-1' });
                                                        t.span({}, 'View');
                                                    });
                                                    btn.onclick = (e) => {
                                                        window.util.navTo(mqrUrl, e);
                                                    };
                                                });
                                            });
                                        });
                                    });
                                });
                            });
                        } else {
                            t.p({ class: 'text-muted small mb-0 p-2 border rounded' }, 'No requisition breakdown items recorded.');
                        }
                    });
                });
            });

            // Wire up toggle button on this card
            const toggleBtn = card.querySelector('.toggle-collapse-btn');
            const traceSection = card.querySelector('.traceability-section');
            if (toggleBtn && traceSection) {
                toggleBtn.onclick = () => {
                    const isHidden = traceSection.style.display === 'none';
                    traceSection.style.display = isHidden ? 'block' : 'none';
                    const icon = toggleBtn.querySelector('i');
                    if (icon) {
                        icon.className = isHidden ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
                    }
                };
            }

            $el.append(card).to(violationsList);
        });
    }

    // Toggle expand/collapse all cards
    toggleAllBtn.onclick = () => {
        allExpanded = !allExpanded;
        const sections = violationsList.querySelectorAll('.traceability-section');
        const icons = violationsList.querySelectorAll('.toggle-collapse-btn i');

        sections.forEach(s => {
            s.style.display = allExpanded ? 'block' : 'none';
        });

        icons.forEach(ic => {
            ic.className = allExpanded ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
        });

        toggleAllBtn.innerHTML = allExpanded
            ? '<i class="bi bi-arrows-collapse me-1"></i> Collapse All'
            : '<i class="bi bi-arrows-expand me-1"></i> Expand All';
    };

    // Filter cards
    violationFilterInput.oninput = () => {
        const query = violationFilterInput.value.trim().toLowerCase();
        if (!query) {
            renderViolationCards(currentViolations);
            return;
        }

        const filtered = currentViolations.filter(v => {
            const itemName = (v.component_item_name || '').toLowerCase();
            const compName = (v.component_name || '').toLowerCase();
            const itemId   = String(v.component_item_id || '');
            
            // Check breakdown records as well for MQR ID or material name
            const matchesBreakdown = (v.breakdown || []).some(b => {
                const mqrId = String(b.material_quantity_request_id || '');
                const matName = (b.material_item_name || '').toLowerCase();
                return mqrId.includes(query) || matName.includes(query);
            });

            return itemName.includes(query) || compName.includes(query) || itemId.includes(query) || matchesBreakdown;
        });

        renderViolationCards(filtered);
    };

    if (printBtn) {
        printBtn.onclick = () => {
            let protoSlug = protocolSelect.value;
            let projId = manualProjectId.value ? parseInt(manualProjectId.value) : parseInt(projectSelect.value);

            if ((!projId || isNaN(projId)) && projectInput && projectInput.value.trim()) {
                const detectedId = findOptionId(projectInput, projectList);
                if (detectedId) {
                    projId = parseInt(detectedId);
                    projectSelect.value = detectedId;
                }
            }

            if (!protoSlug) {
                alert('Please select a protocol.');
                return;
            }

            if (!projId || isNaN(projId)) {
                alert('Please select a project or enter a valid Project ID.');
                return;
            }

            window.open(`/protocols/print?protocol=${encodeURIComponent(protoSlug)}&project_id=${projId}`, '_blank');
        };
    }

    // Handle deep-linked project_id query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const initialProjectId = urlParams.get('project_id');
    if (initialProjectId) {
        if (manualProjectId) manualProjectId.value = initialProjectId;
        if (projectSelect) projectSelect.value = initialProjectId;
        if (projectList && projectList.options) {
            const opt = Array.from(projectList.options).find(o => o.getAttribute('data-id') == initialProjectId);
            if (opt && projectInput) projectInput.value = opt.value;
        }
    }

    runBtn.onclick = executeProtocol;

    loadProtocols();
    loadProjects();
</script>
</div>
@endsection
