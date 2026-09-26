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

    <!-- MQMS Assets (Adarna, Drawer Modal, Bootstrap utilities) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

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

        /* Main Workspace Layout (Full Width) */
        .studio-container {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .studio-main {
            width: 100%;
            height: 100%;
            background-color: var(--bg-darker);
            display: flex;
            flex-direction: column;
            overflow: hidden;
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

        /* Sticky Left Columns - 100% Solid & Opaque */
        .sticky-col-tree {
            position: sticky;
            left: 0;
            background-color: #1e2125 !important;
            z-index: 4;
            min-width: 280px;
            max-width: 340px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            opacity: 1 !important;
        }

        .gantt-table th.sticky-col-tree {
            z-index: 6 !important;
            background-color: #1a1d20 !important;
        }

        .sticky-col-metric {
            position: sticky;
            left: 280px;
            background-color: #1e2125 !important;
            z-index: 4;
            min-width: 80px;
            text-align: right;
            white-space: nowrap;
            opacity: 1 !important;
        }

        .gantt-table th.sticky-col-metric {
            z-index: 6 !important;
            background-color: #1a1d20 !important;
        }

        .sticky-col-progress {
            position: sticky;
            left: 360px;
            background-color: #1e2125 !important;
            z-index: 4;
            min-width: 90px;
            border-right: 2px solid var(--border-color) !important;
            opacity: 1 !important;
        }

        .gantt-table th.sticky-col-progress {
            z-index: 6 !important;
            background-color: #1a1d20 !important;
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

        .zoomed-month-header {
            background-color: #1a1d20 !important;
            color: #0dcaf0 !important;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-align: center;
            padding: 6px 12px !important;
            border-bottom: 1px solid var(--border-color);
        }

        .gantt-table thead tr.day-sub-row th {
            top: 33px !important;
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
        .row-section td.sticky-col-tree,
        .row-section td.sticky-col-metric,
        .row-section td.sticky-col-progress {
            background-color: #212529 !important;
        }

        .row-contract-item {
            background-color: #1a1e21 !important;
            color: #ced4da;
            font-weight: 500;
        }
        .row-contract-item td {
            background-color: #1a1e21 !important;
        }
        .row-contract-item td.sticky-col-tree,
        .row-contract-item td.sticky-col-metric,
        .row-contract-item td.sticky-col-progress {
            background-color: #1a1e21 !important;
        }

        .row-component {
            cursor: pointer;
            transition: background-color 0.1s;
        }
        .row-component td.sticky-col-tree,
        .row-component td.sticky-col-metric,
        .row-component td.sticky-col-progress {
            background-color: #1e2125 !important;
        }
        .row-component:hover td {
            background-color: var(--bg-hover) !important;
        }
        .row-component:hover td.sticky-col-tree,
        .row-component:hover td.sticky-col-metric,
        .row-component:hover td.sticky-col-progress {
            background-color: #2c3035 !important;
        }
        .row-component.active td {
            background-color: rgba(255, 193, 7, 0.16) !important;
            border-top: 1px solid #ffc107 !important;
            border-bottom: 1px solid #ffc107 !important;
        }
        .row-component.active td.sticky-col-tree,
        .row-component.active td.sticky-col-metric,
        .row-component.active td.sticky-col-progress {
            background-color: #383015 !important; /* Fully solid dark amber for active sticky columns */
            border-top: 1px solid #ffc107 !important;
            border-bottom: 1px solid #ffc107 !important;
        }
        .row-component.active td.sticky-col-tree span {
            color: #ffe082 !important;
            font-weight: 600;
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

        /* Custom Context Menu */
        .gantt-context-menu {
            position: fixed;
            z-index: 1050;
            background-color: var(--bg-panel);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.6);
            min-width: 190px;
            padding: 6px 0;
            display: none;
            backdrop-filter: blur(8px);
        }

        .gantt-context-menu-header {
            padding: 5px 14px 6px 14px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 4px;
        }

        .gantt-context-menu-item {
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background-color 0.12s, color 0.12s;
        }

        .gantt-context-menu-item:hover {
            background-color: var(--bg-hover);
            color: #fff;
        }

        .gantt-context-menu-item i {
            font-size: 15px;
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

    <!-- Main Workspace (Full Width) -->
    <div class="studio-container" id="studioContainer">
        
        <!-- Full-Width Gantt Panel -->
        <div class="studio-main" id="mainPanel">
            <!-- Toolbar -->
            <div class="gantt-toolbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Search Section, Item, Component..." autocomplete="off">
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button id="btnZoomOut" class="btn btn-sm btn-primary py-1 px-2 fw-bold text-white shadow-sm" style="font-size: 11px; display: none;" onclick="zoomOut()">
                        <i class="bi bi-arrow-left-circle-fill me-1"></i> Back to Months
                    </button>
                    <button id="btnToggleAll" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11px;">
                        <i class="bi bi-arrows-expand me-1"></i> Expand All
                    </button>
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

    </div>

    <!-- Floating Context Menu -->
    <div id="ganttContextMenu" class="gantt-context-menu">
        <div class="gantt-context-menu-header text-truncate" id="contextMenuTitle">Component Actions</div>
        <div class="gantt-context-menu-item text-primary" onclick="openAccomplishmentDrawer('ACTUAL')">
            <i class="bi bi-check-circle-fill text-primary"></i>
            <span>Add Actual</span>
        </div>
        <div class="gantt-context-menu-item text-success" onclick="openAccomplishmentDrawer('TARGET')">
            <i class="bi bi-bullseye text-success"></i>
            <span>Add Target</span>
        </div>
    </div>

    <!-- Drawer Modal -->
    <div class="drawer_modal_background"></div>
    <div class="drawer_modal bg-dark">
        <div class="drawer_modal_header pe-3 ps-3 d-flex justify-content-between align-items-stretch">
            <div class="p-2">
                <h5 class="drawer_modal_title"></h5>
            </div>
            <div class="p-2">
                <button type="button" onclick="window.util.drawerModal.close()" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <div class="drawer_modal_body p-3">
        </div>
    </div>

    <!-- Scripts -->
    <script type="module">
        import CreateAccomplishmentForm from '/ui_components/create_forms/CreateAccomplishmentForm.js';

        const projectId = {{ $project->id }};
        let projectData = null;
        let selectedComponentId = null;

        // Context menu target state
        let contextMenuTarget = { compId: null, compName: '', cellDate: '' };

        // Timeline state (months to show)
        let timelineMonthsCount = 8;
        let timelineStartOffset = -2; // e.g. 2 months in past, 6 months in future

        // Zoom state tracking
        let currentViewMode = 'month'; // 'month' or 'day'
        let zoomedMonthKey = null;     // e.g. '2024-05'

        // DOM elements
        const container = document.getElementById('studioContainer');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const ganttHead = document.getElementById('ganttHead');
        const ganttBody = document.getElementById('ganttBody');
        const searchInput = document.getElementById('searchInput');
        const btnToggleAll = document.getElementById('btnToggleAll');
        const btnRefresh = document.getElementById('btnRefresh');
        const btnPrevWindow = document.getElementById('btnPrevWindow');
        const btnNextWindow = document.getElementById('btnNextWindow');
        const btnCurrentWindow = document.getElementById('btnCurrentWindow');
        const ganttContextMenu = document.getElementById('ganttContextMenu');
        const contextMenuTitle = document.getElementById('contextMenuTitle');

        // Collapsed state tracking
        const collapsedSections = new Set();
        const collapsedContractItems = new Set();
        let allExpanded = true;

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

            // Extract formatted current month title if in day view
            let currentMonthTitle = '';
            if (currentViewMode === 'day' && zoomedMonthKey) {
                const parts = zoomedMonthKey.split('-').map(Number);
                const monthDate = new Date(parts[0], parts[1] - 1, 1);
                currentMonthTitle = monthDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            }

            // Update Toolbar Zoom Out Button
            const btnZoomOut = document.getElementById('btnZoomOut');

            if (btnZoomOut) {
                if (currentViewMode === 'day' && zoomedMonthKey) {
                    btnZoomOut.innerHTML = `<i class="bi bi-arrow-left-circle-fill me-1"></i> Back to Months`;
                    btnZoomOut.title = 'Click to zoom back out to rolling monthly view';
                    btnZoomOut.style.display = 'inline-flex';
                } else {
                    btnZoomOut.style.display = 'none';
                }
            }

            // Build Head
            let headHtml = '';
            if (currentViewMode === 'day') {
                // Multi-tier header: Month banner spanning all day columns
                headHtml += `<tr>
                    <th rowspan="2" class="sticky-col-tree" style="vertical-align: middle;">WBS Work Item</th>
                    <th rowspan="2" class="sticky-col-metric" style="vertical-align: middle;">Scope</th>
                    <th rowspan="2" class="sticky-col-progress" style="vertical-align: middle;">Accomplished</th>
                    <th colspan="${columns.length}" class="zoomed-month-header">
                        <i class="bi bi-calendar3 me-1"></i> ${escapeHtml(currentMonthTitle)}
                    </th>
                </tr>
                <tr class="day-sub-row">`;
                columns.forEach(col => {
                    headHtml += `<th class="day-header" data-month="${col.monthKey}" style="cursor: pointer;" title="${col.fullLabel}">${col.label}</th>`;
                });
                headHtml += `</tr>`;
            } else {
                headHtml += `<tr>
                    <th class="sticky-col-tree">WBS Work Item</th>
                    <th class="sticky-col-metric">Scope</th>
                    <th class="sticky-col-progress">Accomplished</th>`;

                columns.forEach(col => {
                    headHtml += `<th class="month-header" data-month="${col.monthKey}" style="cursor: pointer;" title="Click zoom icon or double click to view days">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <span>${col.label}</span>
                            <i class="bi bi-zoom-in text-info" style="font-size: 11px; opacity: 0.85;" onclick="event.stopPropagation(); zoomIn('${col.monthKey}')" title="Zoom into ${col.label}"></i>
                        </div>
                    </th>`;
                });
                headHtml += `</tr>`;
            }
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
                <tr class="row-section" data-section-id="${section.id}" onclick="toggleSection(${section.id}, event)">
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
                        <tr class="row-contract-item" style="${displayCi}" data-section-parent="${section.id}" data-ci-id="${ci.id}" onclick="toggleContractItem(${ci.id}, event)">
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

                                    bodyHtml += `<td class="${cellClass}" data-month="${col.monthKey}" data-comp-id="${comp.id}" data-comp-name="${escapeHtml(comp.name)}" data-cell-key="${col.key}" data-cell-label="${escapeHtml(col.label)}">`;
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
        window.toggleSection = function(sectionId, e) {
            if (e && e.target && e.target.closest('.month-cell, .day-cell')) {
                return;
            }
            if (collapsedSections.has(sectionId)) {
                collapsedSections.delete(sectionId);
            } else {
                collapsedSections.add(sectionId);
            }
            renderGantt();
        };

        window.toggleContractItem = function(ciId, e) {
            if (e && e.target && e.target.closest('.month-cell, .day-cell')) {
                return;
            }
            if (collapsedContractItems.has(ciId)) {
                collapsedContractItems.delete(ciId);
            } else {
                collapsedContractItems.add(ciId);
            }
            renderGantt();
        };

        /* ================= Zoom In / Out ================= */
        window.zoomIn = function(monthKey) {
            zoomedMonthKey = monthKey;
            currentViewMode = 'day';
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
        window.selectComponent = function(compId) {
            selectedComponentId = compId;
            document.querySelectorAll('.row-component').forEach(row => row.classList.remove('active'));
            const row = document.getElementById(`comp-row-${compId}`);
            if (row) row.classList.add('active');
        };

        /* ================= Right-Click Context Menu & Drawer ================= */
        document.getElementById('ganttTable').addEventListener('contextmenu', (e) => {
            const cell = e.target.closest('.month-cell, .day-cell');
            if (!cell || !cell.dataset.compId) return;

            e.preventDefault();
            contextMenuTarget.compId = cell.dataset.compId;
            contextMenuTarget.compName = cell.dataset.compName;
            contextMenuTarget.cellDate = cell.dataset.cellKey;

            contextMenuTitle.innerText = `${cell.dataset.compName} (${cell.dataset.cellLabel || cell.dataset.cellKey})`;

            // Position context menu
            let x = e.clientX;
            let y = e.clientY;
            if (x + 210 > window.innerWidth) x = window.innerWidth - 220;
            if (y + 130 > window.innerHeight) y = window.innerHeight - 140;

            ganttContextMenu.style.left = `${x}px`;
            ganttContextMenu.style.top = `${y}px`;
            ganttContextMenu.style.display = 'block';
        });

        // Close context menu on outside click or escape
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#ganttContextMenu')) {
                ganttContextMenu.style.display = 'none';
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                ganttContextMenu.style.display = 'none';
            }
        });

        window.openAccomplishmentDrawer = function(type) {
            ganttContextMenu.style.display = 'none';
            if (!contextMenuTarget.compId) return;

            let entryDate = '';
            if (contextMenuTarget.cellDate) {
                if (contextMenuTarget.cellDate.length === 10) {
                    // Day view: exact day selected (YYYY-MM-DD)
                    entryDate = contextMenuTarget.cellDate;
                } else if (contextMenuTarget.cellDate.length === 7) {
                    // Month view: default to the 15th of the month (YYYY-MM-15)
                    entryDate = `${contextMenuTarget.cellDate}-15`;
                }
            }

            const title = `Add ${type === 'TARGET' ? 'Target' : 'Actual'} Accomplishment - ${contextMenuTarget.compName}`;
            window.util.drawerModal.content(title, CreateAccomplishmentForm({
                component_id: contextMenuTarget.compId,
                type: type,
                entry_data: entryDate,
                successCallback: () => {
                    loadProjectData();
                }
            })).open();
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

        // Expose to window for external communication
        window.refreshGanttChart = loadProjectData;

        btnRefresh.onclick = () => {
            loadProjectData();
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
