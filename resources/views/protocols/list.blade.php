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
    const projectSelect         = $q('#projectSelect').first();
    const manualProjectId       = $q('#manualProjectIdInput').first();
    const runBtn                = $q('#runBtn').first();
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
                // Card Header
                t.div({ class: 'card-header bg-danger bg-opacity-10 d-flex align-items-center justify-content-between flex-wrap gap-2 py-2' }, () => {
                    t.div({ class: 'd-flex align-items-center flex-wrap gap-2' }, () => {
                        t.span({ class: 'badge bg-danger' }, 'NON-CONFORMING');
                        t.strong({ class: 'text-light fs-6' }, `#${v.component_item_id} - ${v.component_item_name}`);

                        // Record Type Indicator
                        let typeBadgeClass = 'bg-secondary';
                        let typeLabel = v.contract_item_type_name || v.contract_item_type || 'Component Item';
                        if (v.contract_item_type === 'MATR') {
                            typeBadgeClass = 'bg-primary';
                        } else if (v.contract_item_type === 'NMAT') {
                            typeBadgeClass = 'bg-info text-dark';
                        } else if (v.contract_item_type === 'OPEX') {
                            typeBadgeClass = 'bg-warning text-dark';
                        }
                        t.span({ class: `badge ${typeBadgeClass}`, title: 'Record Type' }, `Type: ${typeLabel}`);

                        // Contract Item Link
                        if (v.contract_item_id) {
                            t.a({
                                href: v.contract_item_url || (`/project/section/contract_item/${v.contract_item_id}`),
                                target: '_blank',
                                class: 'badge bg-secondary bg-opacity-25 text-body border border-secondary text-decoration-none',
                                title: 'Open Contract Item in new tab'
                            }, () => {
                                t.i({ class: 'bi bi-file-earmark-text me-1' });
                                let cItemText = (v.contract_item_code ? `${v.contract_item_code} ` : '') + (v.contract_item_description || v.contract_item_name || `Contract Item #${v.contract_item_id}`);
                                t.span({}, `Contract Item: ${cItemText}`);
                                t.i({ class: 'bi bi-box-arrow-up-right ms-1 small' });
                            });
                        }

                        // Component (Problematic Record)
                        if (v.component_name) {
                            t.a({
                                href: v.component_url || '#',
                                target: '_blank',
                                class: 'badge bg-secondary text-decoration-none',
                                title: 'Open Component in new tab'
                            }, () => {
                                t.i({ class: 'bi bi-box me-1' });
                                t.span({}, `Component: ${v.component_name}`);
                                t.i({ class: 'bi bi-box-arrow-up-right ms-1 small' });
                            });
                        }
                    });

                    t.div({ class: 'd-flex align-items-center gap-2' }, () => {
                        // Direct Link Button to Problematic Record
                        if (v.component_url) {
                            t.a({
                                href: v.component_url,
                                target: '_blank',
                                class: 'btn btn-sm btn-outline-danger py-0 px-2 text-nowrap d-inline-flex align-items-center',
                                title: 'Open Problematic Component Record in new tab'
                            }, () => {
                                t.i({ class: 'bi bi-box-arrow-up-right me-1' });
                                t.span({}, 'View Problematic Record');
                            });
                        }

                        t.span({ class: 'badge bg-danger bg-opacity-25 text-danger' }, () => {
                            t.i({ class: 'bi bi-receipt me-1' });
                            t.span({}, `${breakdown.length} MQR Request(s) Traced`);
                        });

                        t.button({
                            type: 'button',
                            class: 'btn btn-sm btn-outline-secondary toggle-collapse-btn py-0 px-2',
                            title: 'Toggle MQR Breakdown'
                        }, () => {
                            t.i({ class: 'bi bi-chevron-down' });
                        });
                    });
                });

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

    runBtn.onclick = executeProtocol;

    loadProtocols();
    loadProjects();
</script>
</div>
@endsection
