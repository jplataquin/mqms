<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accomplishment Studio - {{ $project->name }}</title>

    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-darker: #18191a;
            --bg-dark: #242526;
            --bg-panel: #2b2d30;
            --bg-hover: #3a3b3c;
            --border-color: #3e4042;
            --text-main: #e4e6eb;
            --text-muted: #b0b3b8;
            --accent-target: #198754;
            --accent-actual: #0d6efd;
            --accent-highlight: #ffc107;
        }

        body {
            background-color: var(--bg-darker);
            color: var(--text-main);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .studio-topbar {
            height: 48px;
            background-color: var(--bg-dark);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            font-size: 14px;
            flex-shrink: 0;
            z-index: 10;
        }

        .studio-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Main Split Layout */
        .studio-container {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        /* Left Panel - Gantt Table */
        .studio-left {
            width: 60%;
            min-width: 420px;
            max-width: 85%;
            background-color: var(--bg-darker);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid var(--border-color);
        }

        /* Split Resizer */
        .studio-resizer {
            width: 6px;
            background-color: var(--border-color);
            cursor: col-resize;
            transition: background-color 0.15s;
            position: relative;
            z-index: 20;
            flex-shrink: 0;
        }

        .studio-resizer:hover, .studio-resizer.resizing {
            background-color: #0d6efd;
        }

        .resizing-active iframe {
            pointer-events: none !important;
        }

        /* Right Panel - Iframe Workspace */
        .studio-right {
            flex: 1;
            background-color: var(--bg-dark);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 320px;
        }

        /* Toolbar */
        .gantt-toolbar {
            padding: 8px 12px;
            background-color: var(--bg-dark);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-shrink: 0;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 280px;
        }

        .search-box input {
            background-color: var(--bg-panel);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            font-size: 13px;
            border-radius: 4px;
            padding: 4px 10px 4px 28px;
            width: 100%;
        }

        .search-box input:focus {
            outline: 1px solid #0d6efd;
            border-color: #0d6efd;
        }

        .search-box i {
            position: absolute;
            left: 8px;
            top: 7px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .legend-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Gantt Table Scrollable Area */
        .gantt-scroll-container {
            flex: 1;
            overflow: auto;
            position: relative;
        }

        .gantt-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-size: 12px;
        }

        /* Sticky Table Headers */
        .gantt-table th {
            position: sticky;
            top: 0;
            background-color: var(--bg-dark);
            color: var(--text-main);
            border-bottom: 2px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            padding: 8px 10px;
            font-weight: 600;
            z-index: 5;
            white-space: nowrap;
        }

        .gantt-table td {
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            padding: 6px 8px;
            vertical-align: middle;
            background-color: var(--bg-darker);
        }

        /* Sticky Left Column */
        .sticky-col-tree {
            position: sticky;
            left: 0;
            background-color: var(--bg-dark) !important;
            z-index: 4;
            min-width: 280px;
            max-width: 340px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gantt-table th.sticky-col-tree {
            z-index: 6 !important;
        }

        .sticky-col-metric {
            position: sticky;
            left: 280px;
            background-color: var(--bg-dark) !important;
            z-index: 4;
            min-width: 80px;
            text-align: right;
            white-space: nowrap;
        }

        .gantt-table th.sticky-col-metric {
            z-index: 6 !important;
        }

        .sticky-col-progress {
            position: sticky;
            left: 360px;
            background-color: var(--bg-dark) !important;
            z-index: 4;
            min-width: 90px;
            border-right: 2px solid var(--border-color) !important;
        }

        .gantt-table th.sticky-col-progress {
            z-index: 6 !important;
            border-right: 2px solid var(--border-color) !important;
        }

        /* Timeline Month Column */
        .month-header {
            min-width: 140px;
            text-align: center;
        }

        .day-header {
            min-width: 45px;
            text-align: center;
            font-size: 11px;
            padding: 4px 2px !important;
        }

        .month-cell {
            min-width: 140px;
            padding: 4px !important;
            background-color: rgba(255, 255, 255, 0.01);
        }

        .day-cell {
            min-width: 45px;
            padding: 2px !important;
            background-color: rgba(255, 255, 255, 0.01);
        }

        /* Row types */
        .row-section {
            background-color: #212529 !important;
            font-weight: bold;
            color: #adb5bd;
        }
        .row-section td {
            background-color: #212529 !important;
            border-top: 1px solid #495057;
        }

        .row-contract-item {
            background-color: #1a1e21 !important;
            color: #ced4da;
            font-weight: 500;
        }
        .row-contract-item td {
            background-color: #1a1e21 !important;
        }

        .row-component {
            cursor: pointer;
            transition: background-color 0.1s;
        }
        .row-component:hover td {
            background-color: var(--bg-hover) !important;
        }
        .row-component.active td {
            background-color: rgba(13, 110, 253, 0.18) !important;
            border-color: #0d6efd !important;
        }

        /* Gantt Bars / Pills */
        .gantt-pill {
            font-size: 11px;
            line-height: 1.3;
            min-height: 21px;
            border-radius: 3px;
            padding: 2px 6px;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 0 1px 2px rgba(0,0,0,0.3);
            box-sizing: border-box;
        }

        .gantt-pill:last-child {
            margin-bottom: 0;
        }

        .pill-target {
            background-color: rgba(25, 135, 84, 0.28);
            border: 1px solid var(--accent-target);
            color: #a3cfbb;
        }

        .pill-actual {
            background-color: rgba(13, 110, 253, 0.25);
            border: 1px solid var(--accent-actual);
            color: #9ec5fe;
        }

        .pill-placeholder {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(255, 255, 255, 0.22);
            color: var(--text-muted);
            box-shadow: none;
            justify-content: center;
            opacity: 0.7;
            user-select: none;
        }

        .gantt-progress-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #343a40;
            overflow: hidden;
            display: flex;
            margin-top: 4px;
        }
        .progress-actual {
            background-color: var(--accent-actual);
            height: 100%;
        }

        /* Right Panel Elements */
        .workspace-tabs {
            height: 38px;
            background-color: var(--bg-dark);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            flex-shrink: 0;
        }

        .tab-title {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
        }

        .iframe-container {
            flex: 1;
            position: relative;
            background-color: var(--bg-darker);
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .empty-workspace {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: var(--text-muted);
            text-align: center;
            padding: 24px;
        }

        .empty-workspace i {
            font-size: 48px;
            margin-bottom: 12px;
            color: #495057;
        }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(24, 25, 26, 0.85);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
    </style>
</head>
<body>

    <!-- Studio Topbar -->
    <div class="studio-topbar">
        <div class="studio-brand">
            <a href="/accomplishment/project/{{ $project->id }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Back to Project View">
                <i class="bi bi-arrow-left"></i>
            </a>
            <i class="bi bi-kanban text-primary fs-5"></i>
            <span class="fw-bold">Accomplishment Studio</span>
            <span class="text-muted">|</span>
            <span class="badge bg-secondary text-truncate" style="max-width: 240px;">{{ $project->name }}</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Timeline window navigation -->
            <div class="btn-group btn-group-sm">
                <button id="btnPrevWindow" class="btn btn-outline-secondary" title="Shift Timeline Left">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button id="btnCurrentWindow" class="btn btn-outline-secondary" title="Center around Today">
                    Today
                </button>
                <button id="btnNextWindow" class="btn btn-outline-secondary" title="Shift Timeline Right">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            <button id="btnRefresh" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" title="Reload Project Data">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Main Workspace Split -->
    <div class="studio-container" id="studioContainer">
        
        <!-- Left Panel: Table Gantt Chart -->
        <div class="studio-left" id="leftPanel">
            <!-- Toolbar -->
            <div class="gantt-toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search Section, Item, Component..." autocomplete="off">
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button id="btnToggleAll" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11px;">
                        <i class="bi bi-arrows-expand me-1"></i> Expand All
                    </button>
                    <div class="legend-badge pill-target">
                        <i class="bi bi-bullseye"></i> Target
                    </div>
                    <div class="legend-badge pill-actual">
                        <i class="bi bi-check-circle-fill"></i> Actual
                    </div>
                </div>
            </div>

            <!-- Gantt Scroll Container -->
            <div class="gantt-scroll-container" id="ganttScroll">
                <div id="loadingOverlay" class="loading-overlay">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted small">Loading Project Structure & Accomplishments...</div>
                </div>

                <table class="gantt-table" id="ganttTable">
                    <thead id="ganttHead">
                        <!-- Populated dynamically -->
                    </thead>
                    <tbody id="ganttBody">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resizer Bar -->
        <div class="studio-resizer" id="resizer"></div>

        <!-- Right Panel: Form and Display Iframe -->
        <div class="studio-right" id="rightPanel">
            <div class="workspace-tabs">
                <div class="tab-title">
                    <i class="bi bi-sliders text-info"></i>
                    <span id="activeComponentName">Component Detail & Registry</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button id="btnViewComponent" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px; display: none;" title="Overview">
                        <i class="bi bi-card-checklist me-1"></i> View
                    </button>
                    <button id="btnAddRecord" class="btn btn-sm btn-success py-0 px-2" style="font-size: 12px; display: none;" title="Add Record">
                        <i class="bi bi-plus-lg me-1"></i> Add Entry
                    </button>
                    <button id="btnReloadFrame" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px; display: none;" title="Reload Frame">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button id="btnOpenTab" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px; display: none;" title="Open in New Tab">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </button>
                </div>
            </div>

            <div class="iframe-container">
                <div class="empty-workspace" id="emptyWorkspace">
                    <i class="bi bi-kanban"></i>
                    <h5 class="fw-semibold">No Component Selected</h5>
                    <p class="small text-muted mb-0">Select any component row on the left Gantt table to record or inspect accomplishments.</p>
                </div>
                <iframe id="studioFrame" name="studioFrame" style="display: none;"></iframe>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        const projectId = {{ $project->id }};
        let projectData = null;
        let selectedComponentId = null;

        // Timeline state (months to show)
        let timelineMonthsCount = 8;
        let timelineStartOffset = -2; // e.g. 2 months in past, 6 months in future

        // Zoom state tracking
        let currentViewMode = 'month'; // 'month' or 'day'
        let zoomedMonthKey = null;     // e.g. '2024-05'

        // DOM elements
        const container = document.getElementById('studioContainer');
        const leftPanel = document.getElementById('leftPanel');
        const resizer = document.getElementById('resizer');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const ganttHead = document.getElementById('ganttHead');
        const ganttBody = document.getElementById('ganttBody');
        const searchInput = document.getElementById('searchInput');
        const btnToggleAll = document.getElementById('btnToggleAll');
        const btnRefresh = document.getElementById('btnRefresh');
        const btnPrevWindow = document.getElementById('btnPrevWindow');
        const btnNextWindow = document.getElementById('btnNextWindow');
        const btnCurrentWindow = document.getElementById('btnCurrentWindow');

        const studioFrame = document.getElementById('studioFrame');
        const emptyWorkspace = document.getElementById('emptyWorkspace');
        const activeComponentName = document.getElementById('activeComponentName');
        const btnViewComponent = document.getElementById('btnViewComponent');
        const btnAddRecord = document.getElementById('btnAddRecord');
        const btnReloadFrame = document.getElementById('btnReloadFrame');
        const btnOpenTab = document.getElementById('btnOpenTab');

        // Collapsed state tracking
        const collapsedSections = new Set();
        const collapsedContractItems = new Set();
        let allExpanded = true;

        /* ================= Resizable Split Pane ================= */
        let isResizing = false;

        resizer.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.body.classList.add('resizing-active');
            resizer.classList.add('resizing');
        });

        document.addEventListener('mousemove', (e) => {
            if (!isResizing) return;
            const containerRect = container.getBoundingClientRect();
            let newWidth = e.clientX - containerRect.left;
            if (newWidth < 380) newWidth = 380;
            if (newWidth > containerRect.width - 320) newWidth = containerRect.width - 320;
            leftPanel.style.width = newWidth + 'px';
        });

        document.addEventListener('mouseup', () => {
            if (isResizing) {
                isResizing = false;
                document.body.classList.remove('resizing-active');
                resizer.classList.remove('resizing');
            }
        });

        /* ================= Timeline Calculation ================= */
        function getTimelineColumns() {
            if (currentViewMode === 'day' && zoomedMonthKey) {
                const parts = zoomedMonthKey.split('-').map(Number);
                const year = parts[0];
                const month = parts[1];
                const daysInMonth = new Date(year, month, 0).getDate();
                const days = [];
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayStr = day < 10 ? '0' + day : '' + day;
                    const monthStr = month < 10 ? '0' + month : '' + month;
                    const key = `${year}-${monthStr}-${dayStr}`;
                    days.push({
                        key: key,
                        label: `${day}`,
                        fullLabel: `${year}-${monthStr}-${dayStr}`,
                        monthKey: zoomedMonthKey
                    });
                }
                return days;
            }

            const months = [];
            const now = new Date();
            const startYear = now.getFullYear();
            const startMonth = now.getMonth() + timelineStartOffset;

            for (let i = 0; i < timelineMonthsCount; i++) {
                const d = new Date(startYear, startMonth + i, 1);
                const year = d.getFullYear();
                const month = d.getMonth() + 1;
                const monthStr = month < 10 ? '0' + month : '' + month;
                const key = `${year}-${monthStr}`;
                const label = d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                months.push({ key, label, year, month, monthKey: key });
            }
            return months;
        }

        /* ================= Render Gantt Table ================= */
        function renderGantt() {
            const columns = getTimelineColumns();
            const filter = searchInput.value.toLowerCase().trim();

            // Build Head
            let headHtml = '';
            if (currentViewMode === 'day') {
                const parts = zoomedMonthKey.split('-').map(Number);
                const monthDate = new Date(parts[0], parts[1] - 1, 1);
                const monthTitle = monthDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

                headHtml += `<tr>
                    <th colspan="3" class="text-start bg-dark">
                        <button class="btn btn-sm btn-link text-decoration-none text-info p-0" onclick="zoomOut()" title="Zoom out to monthly view">
                            <i class="bi bi-arrow-left"></i> Back to Months
                        </button>
                    </th>
                    <th colspan="${columns.length}" class="text-center bg-dark text-info fw-bold">
                        ${monthTitle} (Daily View)
                    </th>
                </tr>`;
            }

            headHtml += `<tr>
                <th class="sticky-col-tree">WBS Work Item</th>
                <th class="sticky-col-metric">Scope</th>
                <th class="sticky-col-progress">Accomplished</th>`;

            columns.forEach(col => {
                const isDay = currentViewMode === 'day';
                const headerClass = isDay ? 'day-header' : 'month-header';
                const headerTitle = isDay ? col.fullLabel : 'Double click to zoom into days';
                headHtml += `<th class="${headerClass}" data-month="${col.monthKey}" style="cursor: pointer;" title="${headerTitle}">${col.label}</th>`;
            });
            headHtml += `</tr>`;
            ganttHead.innerHTML = headHtml;

            // Build Body
            let bodyHtml = '';
            if (!projectData || !projectData.sections || projectData.sections.length === 0) {
                bodyHtml = `<tr><td colspan="${3 + columns.length}" class="text-center py-4 text-muted">No sections or components found in this project.</td></tr>`;
                ganttBody.innerHTML = bodyHtml;
                return;
            }

            projectData.sections.forEach(section => {
                const sectionMatches = !filter || section.name.toLowerCase().includes(filter);
                const isSectionCollapsed = collapsedSections.has(section.id);
                const chevronIcon = isSectionCollapsed ? 'bi-chevron-right' : 'bi-chevron-down';

                // Section row
                bodyHtml += `
                <tr class="row-section" data-section-id="${section.id}" onclick="toggleSection(${section.id})">
                    <td class="sticky-col-tree">
                        <i class="bi ${chevronIcon} me-1 text-muted" id="sec-chevron-${section.id}"></i>
                        <i class="bi bi-folder2 text-warning me-1"></i>
                        <span>${escapeHtml(section.name)}</span>
                    </td>
                    <td class="sticky-col-metric text-muted">-</td>
                    <td class="sticky-col-progress text-muted">-</td>`;

                columns.forEach(col => {
                    const cellClass = currentViewMode === 'day' ? 'day-cell' : 'month-cell';
                    bodyHtml += `<td class="${cellClass}" data-month="${col.monthKey}"></td>`;
                });
                bodyHtml += `</tr>`;

                // Contract Items
                if (section.contract_items) {
                    section.contract_items.forEach(ci => {
                        const ciName = (ci.item_code ? ci.item_code + ' ' : '') + (ci.description || ci.name || '');
                        const ciMatches = !filter || ciName.toLowerCase().includes(filter);
                        const isCiCollapsed = collapsedContractItems.has(ci.id);
                        const ciChevronIcon = isCiCollapsed ? 'bi-chevron-right' : 'bi-chevron-down';
                        const displayCi = isSectionCollapsed ? 'display: none;' : '';

                        bodyHtml += `
                        <tr class="row-contract-item" style="${displayCi}" data-section-parent="${section.id}" data-ci-id="${ci.id}" onclick="toggleContractItem(${ci.id})">
                            <td class="sticky-col-tree" style="padding-left: 28px;">
                                <i class="bi ${ciChevronIcon} me-1 text-muted" id="ci-chevron-${ci.id}"></i>
                                <i class="bi bi-diagram-3 text-info me-1"></i>
                                <span>${escapeHtml(ciName)}</span>
                            </td>
                            <td class="sticky-col-metric text-muted">-</td>
                            <td class="sticky-col-progress text-muted">-</td>`;

                        columns.forEach(col => {
                            const cellClass = currentViewMode === 'day' ? 'day-cell' : 'month-cell';
                            bodyHtml += `<td class="${cellClass}" data-month="${col.monthKey}"></td>`;
                        });
                        bodyHtml += `</tr>`;

                        // Components
                        if (ci.components) {
                            ci.components.forEach(comp => {
                                const compMatches = !filter || comp.name.toLowerCase().includes(filter) || sectionMatches || ciMatches;
                                if (filter && !compMatches) return;

                                const displayComp = (isSectionCollapsed || isCiCollapsed) ? 'display: none;' : '';
                                const isActive = selectedComponentId === comp.id ? 'active' : '';

                                // Scope and Accomplishment metrics
                                const totalScope = parseFloat(comp.quantity) || 0;
                                const unitText = comp.unit_text || '';
                                
                                let overallLatestActual = null;
                                const timeKeyAccomplishments = {};

                                if (comp.accomplishments) {
                                    comp.accomplishments.forEach(acc => {
                                        // Track overall latest actual
                                        if (acc.type === 'ACTUAL') {
                                            if (!overallLatestActual) {
                                                overallLatestActual = acc;
                                            } else {
                                                const dateCurr = new Date(acc.entry_data || acc.created_at || 0);
                                                const dateLatest = new Date(overallLatestActual.entry_data || overallLatestActual.created_at || 0);
                                                if (dateCurr > dateLatest || (dateCurr.getTime() === dateLatest.getTime() && (acc.id || 0) > (overallLatestActual.id || 0))) {
                                                    overallLatestActual = acc;
                                                }
                                            }
                                        }

                                        // Track latest Target and latest Actual per month or day
                                        if (acc.entry_data) {
                                            const timeKey = currentViewMode === 'month' 
                                                ? acc.entry_data.substring(0, 7) 
                                                : acc.entry_data.substring(0, 10);

                                            if (!timeKeyAccomplishments[timeKey]) {
                                                timeKeyAccomplishments[timeKey] = {
                                                    latestTarget: null,
                                                    latestActual: null
                                                };
                                            }

                                            const entry = timeKeyAccomplishments[timeKey];

                                            if (acc.type === 'TARGET') {
                                                if (!entry.latestTarget) {
                                                    entry.latestTarget = acc;
                                                } else {
                                                    const dateCurr = new Date(acc.entry_data || acc.created_at || 0);
                                                    const dateLatest = new Date(entry.latestTarget.entry_data || entry.latestTarget.created_at || 0);
                                                    if (dateCurr > dateLatest || (dateCurr.getTime() === dateLatest.getTime() && (acc.id || 0) > (entry.latestTarget.id || 0))) {
                                                        entry.latestTarget = acc;
                                                    }
                                                }
                                            } else if (acc.type === 'ACTUAL') {
                                                if (!entry.latestActual) {
                                                    entry.latestActual = acc;
                                                } else {
                                                    const dateCurr = new Date(acc.entry_data || acc.created_at || 0);
                                                    const dateLatest = new Date(entry.latestActual.entry_data || entry.latestActual.created_at || 0);
                                                    if (dateCurr > dateLatest || (dateCurr.getTime() === dateLatest.getTime() && (acc.id || 0) > (entry.latestActual.id || 0))) {
                                                        entry.latestActual = acc;
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }

                                const totalActual = overallLatestActual ? (parseFloat(overallLatestActual.quantity) || 0) : 0;
                                const percentActual = totalScope > 0 ? Math.min(100, Math.round((totalActual / totalScope) * 100)) : 0;
                                const actualDateStr = overallLatestActual && overallLatestActual.entry_data ? overallLatestActual.entry_data.substring(0, 10) : '';

                                bodyHtml += `
                                <tr class="row-component ${isActive}" id="comp-row-${comp.id}" style="${displayComp}" data-section-parent="${section.id}" data-ci-parent="${ci.id}" onclick="selectComponent(${comp.id}, '${escapeHtml(comp.name)}')">
                                    <td class="sticky-col-tree" style="padding-left: 48px;">
                                        <i class="bi bi-box-seam text-secondary me-1"></i>
                                        <span title="${escapeHtml(comp.name)}">${escapeHtml(comp.name)}</span>
                                    </td>
                                    <td class="sticky-col-metric">
                                        <span class="fw-semibold">${formatNumber(totalScope)}</span>
                                        <small class="text-muted ms-1">${escapeHtml(unitText)}</small>
                                    </td>
                                    <td class="sticky-col-progress">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-semibold text-primary">${formatNumber(totalActual)}</span>
                                            <span class="badge ${percentActual >= 100 ? 'bg-primary' : 'bg-secondary'}" style="font-size: 10px;">${percentActual}%</span>
                                        </div>
                                        <div class="gantt-progress-bar">
                                            <div class="progress-actual" style="width: ${percentActual}%;"></div>
                                        </div>
                                        ${actualDateStr ? `<div class="text-truncate" style="font-size: 9px; color: var(--text-muted); line-height: 1.2; margin-top: 2px;" title="As of ${escapeHtml(actualDateStr)}">as of ${escapeHtml(actualDateStr)}</div>` : ''}
                                    </td>`;

                                // Timeline Columns Cells (Month or Day)
                                columns.forEach(col => {
                                    const entry = timeKeyAccomplishments[col.key];
                                    const hasTarget = !!(entry && entry.latestTarget);
                                    const hasActual = !!(entry && entry.latestActual);
                                    const cellClass = currentViewMode === 'day' ? 'day-cell' : 'month-cell';

                                    bodyHtml += `<td class="${cellClass}" data-month="${col.monthKey}">`;
                                    if (hasTarget || hasActual) {
                                        // Target row (always on top)
                                        if (hasTarget) {
                                            const targetQty = parseFloat(entry.latestTarget.quantity) || 0;
                                            const targetDateStr = entry.latestTarget.entry_data ? entry.latestTarget.entry_data.substring(0, 10) : '';
                                            bodyHtml += `
                                            <div class="gantt-pill pill-target" title="Target: ${formatNumber(targetQty)} ${escapeHtml(unitText)} as of ${escapeHtml(targetDateStr)}">
                                                <span>🎯 ${formatNumber(targetQty)}</span>
                                                <small>${escapeHtml(unitText)}</small>
                                            </div>`;
                                        } else {
                                            bodyHtml += `
                                            <div class="gantt-pill pill-placeholder pill-placeholder-target" title="No Target">
                                                <span>&mdash;</span>
                                            </div>`;
                                        }

                                        // Actual row (always on bottom)
                                        if (hasActual) {
                                            const actualQty = parseFloat(entry.latestActual.quantity) || 0;
                                            const monthActualDateStr = entry.latestActual.entry_data ? entry.latestActual.entry_data.substring(0, 10) : '';
                                            bodyHtml += `
                                            <div class="gantt-pill pill-actual" title="Actual: ${formatNumber(actualQty)} ${escapeHtml(unitText)} as of ${escapeHtml(monthActualDateStr)}">
                                                <span>✅ ${formatNumber(actualQty)}</span>
                                                <small>${escapeHtml(unitText)}</small>
                                            </div>`;
                                        } else {
                                            bodyHtml += `
                                            <div class="gantt-pill pill-placeholder pill-placeholder-actual" title="No Actual">
                                                <span>&mdash;</span>
                                            </div>`;
                                        }
                                    }
                                    bodyHtml += `</td>`;
                                });

                                bodyHtml += `</tr>`;
                            });
                        }
                    });
                }
            });

            ganttBody.innerHTML = bodyHtml;
        }

        /* ================= Expand / Collapse ================= */
        window.toggleSection = function(sectionId) {
            if (collapsedSections.has(sectionId)) {
                collapsedSections.delete(sectionId);
            } else {
                collapsedSections.add(sectionId);
            }
            renderGantt();
        };

        window.toggleContractItem = function(ciId) {
            if (collapsedContractItems.has(ciId)) {
                collapsedContractItems.delete(ciId);
            } else {
                collapsedContractItems.add(ciId);
            }
            renderGantt();
        };

        btnToggleAll.onclick = function() {
            if (allExpanded) {
                if (projectData && projectData.sections) {
                    projectData.sections.forEach(s => {
                        collapsedSections.add(s.id);
                        if (s.contract_items) {
                            s.contract_items.forEach(ci => collapsedContractItems.add(ci.id));
                        }
                    });
                }
                allExpanded = false;
                btnToggleAll.innerHTML = `<i class="bi bi-arrows-collapse me-1"></i> Expand All`;
            } else {
                collapsedSections.clear();
                collapsedContractItems.clear();
                allExpanded = true;
                btnToggleAll.innerHTML = `<i class="bi bi-arrows-expand me-1"></i> Collapse All`;
            }
            renderGantt();
        };

        /* ================= Component Selection ================= */
        window.selectComponent = function(compId, compName) {
            selectedComponentId = compId;

            // Highlight row
            document.querySelectorAll('.row-component').forEach(row => row.classList.remove('active'));
            const row = document.getElementById(`comp-row-${compId}`);
            if (row) row.classList.add('active');

            // Update right workspace header
            activeComponentName.innerText = compName;
            btnViewComponent.style.display = 'inline-block';
            btnAddRecord.style.display = 'inline-block';
            btnReloadFrame.style.display = 'inline-block';
            btnOpenTab.style.display = 'inline-block';

            // Show iframe
            emptyWorkspace.style.display = 'none';
            studioFrame.style.display = 'block';
            studioFrame.src = `/accomplishment/component/${compId}?studio=1`;
        };

        // Right panel toolbar actions
        btnViewComponent.onclick = () => {
            if (selectedComponentId) {
                studioFrame.src = `/accomplishment/component/${selectedComponentId}?studio=1`;
            }
        };

        btnAddRecord.onclick = () => {
            if (selectedComponentId) {
                let currentType = 'ACTUAL';
                try {
                    const frameUrl = new URL(studioFrame.contentWindow.location.href);
                    if (frameUrl.searchParams.get('type')) {
                        currentType = frameUrl.searchParams.get('type');
                    }
                } catch(e) {}
                studioFrame.src = `/accomplishment/component/${selectedComponentId}/create?studio=1&type=${currentType}`;
            }
        };

        btnReloadFrame.onclick = () => {
            if (selectedComponentId) {
                studioFrame.contentWindow.location.reload();
            }
        };

        btnOpenTab.onclick = () => {
            if (selectedComponentId) {
                window.open(`/accomplishment/component/${selectedComponentId}`, '_blank');
            }
        };

        /* ================= Zoom In / Out ================= */
        window.zoomOut = function() {
            currentViewMode = 'month';
            zoomedMonthKey = null;
            renderGantt();
        };

        // Double-click event to zoom into a specific month
        document.getElementById('ganttTable').addEventListener('dblclick', (e) => {
            const cell = e.target.closest('.month-cell, .month-header');
            if (cell && cell.dataset.month && currentViewMode === 'month') {
                zoomedMonthKey = cell.dataset.month;
                currentViewMode = 'day';
                renderGantt();
            }
        });

        /* ================= Timeline Navigation ================= */
        btnPrevWindow.onclick = () => {
            if (currentViewMode === 'day' && zoomedMonthKey) {
                const parts = zoomedMonthKey.split('-').map(Number);
                const prevDate = new Date(parts[0], parts[1] - 2, 1);
                const pYear = prevDate.getFullYear();
                const pMonth = prevDate.getMonth() + 1;
                zoomedMonthKey = `${pYear}-${pMonth < 10 ? '0' + pMonth : pMonth}`;
            } else {
                timelineStartOffset -= 3;
            }
            renderGantt();
        };

        btnNextWindow.onclick = () => {
            if (currentViewMode === 'day' && zoomedMonthKey) {
                const parts = zoomedMonthKey.split('-').map(Number);
                const nextDate = new Date(parts[0], parts[1], 1);
                const nYear = nextDate.getFullYear();
                const nMonth = nextDate.getMonth() + 1;
                zoomedMonthKey = `${nYear}-${nMonth < 10 ? '0' + nMonth : nMonth}`;
            } else {
                timelineStartOffset += 3;
            }
            renderGantt();
        };

        btnCurrentWindow.onclick = () => {
            if (currentViewMode === 'day') {
                const now = new Date();
                const cYear = now.getFullYear();
                const cMonth = now.getMonth() + 1;
                zoomedMonthKey = `${cYear}-${cMonth < 10 ? '0' + cMonth : cMonth}`;
            } else {
                timelineStartOffset = -2;
            }
            renderGantt();
        };

        /* ================= Search / Filter ================= */
        searchInput.oninput = () => {
            renderGantt();
        };

        /* ================= Data Loading ================= */
        function loadProjectData() {
            loadingOverlay.style.display = 'flex';
            fetch(`/api/accomplishment/project/${projectId}/studio-data`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                loadingOverlay.style.display = 'none';
                if (data.status === 1) {
                    projectData = data.data;
                    renderGantt();
                } else {
                    alert(data.message || 'Error loading project data');
                }
            })
            .catch(err => {
                loadingOverlay.style.display = 'none';
                console.error(err);
            });
        }

        // Expose to window for iframe communication
        window.refreshGanttChart = loadProjectData;

        btnRefresh.onclick = () => {
            loadProjectData();
            if (selectedComponentId) {
                studioFrame.contentWindow.location.reload();
            }
        };

        /* ================= Utilities ================= */
        function formatNumber(num) {
            return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                })[m];
            });
        }

        // Initial Load
        loadProjectData();
    </script>
</body>
</html>
