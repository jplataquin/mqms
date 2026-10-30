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
            --col-wbs-width: 280px;
            --col-scope-width: 90px;
            --col-acc-width: 120px;
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
            height: 52px;
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
            gap: 12px;
            min-width: 0;
        }

        .studio-project-name {
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.2;
            letter-spacing: -0.01em;
            max-width: min(850px, 60vw);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .studio-project-name {
                font-size: 1.1rem;
                max-width: 300px;
            }
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
            width: var(--col-wbs-width);
            min-width: var(--col-wbs-width);
            max-width: var(--col-wbs-width);
            box-sizing: border-box;
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
            left: var(--col-wbs-width);
            background-color: #1e2125 !important;
            z-index: 4;
            width: var(--col-scope-width);
            min-width: var(--col-scope-width);
            max-width: var(--col-scope-width);
            box-sizing: border-box;
            text-align: right;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            opacity: 1 !important;
        }

        .gantt-table th.sticky-col-metric {
            z-index: 6 !important;
            background-color: #1a1d20 !important;
        }

        .sticky-col-progress {
            position: sticky;
            left: calc(var(--col-wbs-width) + var(--col-scope-width));
            background-color: #1e2125 !important;
            z-index: 4;
            width: var(--col-acc-width);
            min-width: var(--col-acc-width);
            max-width: var(--col-acc-width);
            box-sizing: border-box;
            border-right: 2px solid var(--border-color) !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            opacity: 1 !important;
        }

        .gantt-table th.sticky-col-progress {
            z-index: 6 !important;
            background-color: #1a1d20 !important;
            border-right: 2px solid var(--border-color) !important;
        }

        /* Column Resizer Handles */
        .col-resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 7px;
            bottom: 0;
            cursor: col-resize;
            user-select: none;
            z-index: 10;
        }

        .col-resizer:hover,
        .col-resizer.is-resizing {
            background-color: var(--accent-actual);
            opacity: 0.85;
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

        .month-header.current-month-col {
            background-color: rgba(255, 193, 7, 0.12) !important;
            border-bottom: 2px solid #ffc107 !important;
        }

        .month-header.current-month-col span {
            color: #ffc107 !important;
            font-weight: 700;
        }

        td.month-cell.current-month-col {
            background-color: rgba(255, 193, 7, 0.05) !important;
            border-left: 1px dashed rgba(255, 193, 7, 0.35) !important;
            border-right: 1px dashed rgba(255, 193, 7, 0.35) !important;
        }

        .row-component:hover td.month-cell.current-month-col {
            background-color: rgba(255, 193, 7, 0.12) !important;
        }

        .row-component.active td.month-cell.current-month-col {
            background-color: rgba(255, 193, 7, 0.2) !important;
        }

        .day-header.current-day-col {
            background-color: rgba(255, 193, 7, 0.22) !important;
            border-bottom: 2px solid #ffc107 !important;
            color: #ffc107 !important;
            font-weight: 700;
        }

        td.day-cell.current-day-col {
            background-color: rgba(255, 193, 7, 0.05) !important;
            border-left: 1px dashed rgba(255, 193, 7, 0.35) !important;
            border-right: 1px dashed rgba(255, 193, 7, 0.35) !important;
        }

        .row-component:hover td.day-cell.current-day-col {
            background-color: rgba(255, 193, 7, 0.12) !important;
        }

        .row-component.active td.day-cell.current-day-col {
            background-color: rgba(255, 193, 7, 0.2) !important;
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

        /* Excluded component styling (highlighted white) */
        .row-component-excluded td {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        .row-component-excluded td.sticky-col-tree,
        .row-component-excluded td.sticky-col-metric,
        .row-component-excluded td.sticky-col-progress {
            background-color: #2e3238 !important;
        }
        .row-component-excluded:hover td {
            background-color: rgba(255, 255, 255, 0.16) !important;
        }
        .row-component-excluded:hover td.sticky-col-tree,
        .row-component-excluded:hover td.sticky-col-metric,
        .row-component-excluded:hover td.sticky-col-progress {
            background-color: #3d434a !important;
        }
        .row-component-excluded td.sticky-col-tree span.comp-name-text {
            color: #ffffff !important;
            text-decoration: line-through;
        }
        .row-component-excluded.active td {
            background-color: rgba(255, 255, 255, 0.20) !important;
            border-top: 1px solid #ffffff !important;
            border-bottom: 1px solid #ffffff !important;
        }
        .row-component-excluded.active td.sticky-col-tree,
        .row-component-excluded.active td.sticky-col-metric,
        .row-component-excluded.active td.sticky-col-progress {
            background-color: #454b54 !important;
            border-top: 1px solid #ffffff !important;
            border-bottom: 1px solid #ffffff !important;
        }

        /* Behind target contract item styling (highlighted red) */
        .row-component-behind td {
            background-color: rgba(220, 53, 69, 0.12) !important;
        }
        .row-component-behind td.sticky-col-tree,
        .row-component-behind td.sticky-col-metric,
        .row-component-behind td.sticky-col-progress {
            background-color: #2c161c !important;
        }
        .row-component-behind:hover td {
            background-color: rgba(220, 53, 69, 0.22) !important;
        }
        .row-component-behind:hover td.sticky-col-tree,
        .row-component-behind:hover td.sticky-col-metric,
        .row-component-behind:hover td.sticky-col-progress {
            background-color: #3b1b24 !important;
        }
        .row-component-behind td.sticky-col-tree span.comp-name-text {
            color: #ff6b7d !important;
            font-weight: 600;
        }
        .row-component-behind.active td {
            background-color: rgba(220, 53, 69, 0.25) !important;
            border-top: 1px solid #dc3545 !important;
            border-bottom: 1px solid #dc3545 !important;
        }
        .row-component-behind.active td.sticky-col-tree,
        .row-component-behind.active td.sticky-col-metric,
        .row-component-behind.active td.sticky-col-progress {
            background-color: #421822 !important;
            border-top: 1px solid #dc3545 !important;
            border-bottom: 1px solid #dc3545 !important;
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

        .gantt-pill .delete-pill-btn {
            display: none;
            cursor: pointer;
            opacity: 0.8;
            font-size: 11px;
            transition: opacity 0.15s;
        }

        .gantt-pill .delete-pill-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .gantt-pill:hover .delete-pill-btn {
            display: inline-block;
        }

        /* Bulk Selection & Drag Box */
        .gantt-pill.selected {
            outline: 2px solid #0d6efd !important;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.8) !important;
            transform: scale(1.02);
            z-index: 2;
        }

        .drag-selection-box {
            position: fixed;
            background: rgba(13, 110, 253, 0.2);
            border: 1px solid rgba(13, 110, 253, 0.8);
            z-index: 9999;
            pointer-events: none;
            display: none;
        }

        .bulk-actions-toolbar {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
            display: none;
            animation: fadeIn 0.2s ease-in-out;
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

@php
    $totalAmount = $totalProjectAmount ?? (method_exists($project, 'getTotalContractAmount') ? $project->getTotalContractAmount(true) : 0);
    $formattedTotalAmount = 'P' . number_format($totalAmount, 2);
    $projectTitleWithAmount = "{$project->name} — {$formattedTotalAmount}";
@endphp

    <!-- Studio Topbar -->
    <div class="studio-topbar">
        <div class="studio-brand">
            <a href="/accomplishment/project/{{ $project->id }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Back to Project View">
                <i class="bi bi-arrow-left"></i>
            </a>
            <i class="bi bi-kanban text-primary fs-5"></i>
            <span class="text-muted fw-semibold d-none d-sm-inline" style="font-size: 13px;">Accomplishment Studio</span>
            <span class="text-muted opacity-50 d-none d-sm-inline">|</span>
            <span class="studio-project-name" title="{{ $projectTitleWithAmount }}">{{ $projectTitleWithAmount }}</span>
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
                    <button id="btnToggleExcluded" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11px;" onclick="toggleShowExcluded()">
                        <i class="bi bi-eye me-1"></i> Show Excluded
                    </button>
                    <button id="btnToggleAll" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11px;">
                        <i class="bi bi-arrows-expand me-1"></i> Expand All
                    </button>
                    <button id="btnOverallProgress" class="btn btn-sm btn-info py-1 px-2 fw-bold text-white shadow-sm" style="font-size: 11px;" onclick="showOverallProgress()">
                        <i class="bi bi-bar-chart-fill me-1"></i> Overall Progress
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

    <!-- Floating Bulk Actions Toolbar -->
    <div id="bulkActionsToolbar" class="bulk-actions-toolbar card shadow border-primary bg-dark text-light p-2">
        <div class="d-flex align-items-center gap-3">
            <span class="small fw-bold" id="bulkSelectedCount">0 items selected</span>
            <button class="btn btn-sm btn-danger d-flex align-items-center gap-1" onclick="deleteSelectedAccomplishments()">
                <i class="bi bi-trash"></i> Delete Selected
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="clearSelectedPills()">
                Cancel
            </button>
        </div>
    </div>

    <!-- Selection Box Element -->
    <div id="dragSelectionBox" class="drag-selection-box"></div>

    <!-- Floating Context Menu -->
    <div id="ganttContextMenu" class="gantt-context-menu">
        <div class="gantt-context-menu-header text-truncate" id="contextMenuTitle">Component Actions</div>
        <div class="gantt-context-menu-item text-primary" id="ctxMenuAddActual" onclick="openAccomplishmentDrawer('ACTUAL')">
            <i class="bi bi-check-circle-fill text-primary"></i>
            <span>Add Actual</span>
        </div>
        <div class="gantt-context-menu-item text-success" id="ctxMenuAddTarget" onclick="openAccomplishmentDrawer('TARGET')">
            <i class="bi bi-bullseye text-success"></i>
            <span>Add Target</span>
        </div>
        <div class="border-top border-secondary my-1" id="ctxMenuDivider"></div>
        <div class="gantt-context-menu-item text-danger" id="ctxMenuExclude" onclick="excludeComponentFromMonitoring()">
            <i class="bi bi-eye-slash text-danger"></i>
            <span>Exclude from Monitoring</span>
        </div>
        <div class="gantt-context-menu-item text-success" id="ctxMenuInclude" style="display: none;" onclick="includeComponentInMonitoring()">
            <i class="bi bi-eye text-success"></i>
            <span>Include in Monitoring</span>
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

    <!-- Primary Modal (for window.util.confirm / alert) -->
    <div id="primary_modal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-dark border-secondary text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="primary_modal_title"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="primary_modal_body"></div>
                <div class="modal-footer border-secondary" id="primary_modal_footer"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="module">
        import CreateAccomplishmentForm from '/ui_components/create_forms/CreateAccomplishmentForm.js';

        const projectId = {{ $project->id }};
        let projectData = null;
        let selectedComponentId = null;

        // Context menu target state
        let contextMenuTarget = { compId: null, compName: '', compQty: '', compUnit: '', cellDate: '' };

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
        let showExcludedComponents = false;

        window.toggleShowExcluded = function() {
            showExcludedComponents = !showExcludedComponents;
            const btn = document.getElementById('btnToggleExcluded');
            if (btn) {
                if (showExcludedComponents) {
                    btn.className = 'btn btn-sm btn-warning py-1 px-2 fw-semibold';
                    btn.innerHTML = `<i class="bi bi-eye-slash me-1"></i> Hide Excluded`;
                } else {
                    btn.className = 'btn btn-sm btn-outline-secondary py-1 px-2';
                    btn.innerHTML = `<i class="bi bi-eye me-1"></i> Show Excluded`;
                }
            }
            renderGantt();
        };

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

            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth() + 1;
            const currentDay = now.getDate();
            const currentMonthStr = currentMonth < 10 ? '0' + currentMonth : '' + currentMonth;
            const currentDayStr = currentDay < 10 ? '0' + currentDay : '' + currentDay;
            const currentMonthKey = `${currentYear}-${currentMonthStr}`;
            const currentDayKey = `${currentYear}-${currentMonthStr}-${currentDayStr}`;

            const getHighlightColClass = (col) => {
                if (currentViewMode === 'month' && col.monthKey === currentMonthKey) {
                    return ' current-month-col';
                }
                if (currentViewMode === 'day' && col.key === currentDayKey) {
                    return ' current-day-col';
                }
                return '';
            };

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
                    <th rowspan="2" class="sticky-col-tree" style="vertical-align: middle;">
                        <div class="d-flex align-items-center overflow-hidden">
                            <span class="text-truncate flex-grow-1" title="WBS Work Item">WBS Work Item</span>
                        </div>
                        <div class="col-resizer" data-col="wbs" title="Drag to resize WBS Work Item column"></div>
                    </th>
                    <th rowspan="2" class="sticky-col-metric" style="vertical-align: middle;">
                        <div class="d-flex align-items-center overflow-hidden justify-content-end">
                            <span class="text-truncate" title="Scope">Scope</span>
                        </div>
                        <div class="col-resizer" data-col="scope" title="Drag to resize Scope column"></div>
                    </th>
                    <th rowspan="2" class="sticky-col-progress" style="vertical-align: middle;">
                        <div class="d-flex align-items-center overflow-hidden">
                            <span class="text-truncate" title="Accomplished">Accomplished</span>
                        </div>
                        <div class="col-resizer" data-col="acc" title="Drag to resize Accomplished column"></div>
                    </th>
                    <th colspan="${columns.length}" class="zoomed-month-header">
                        <i class="bi bi-calendar3 me-1"></i> ${escapeHtml(currentMonthTitle)}
                    </th>
                </tr>
                <tr class="day-sub-row">`;
                columns.forEach(col => {
                    const currentClass = getHighlightColClass(col);
                    headHtml += `<th class="day-header${currentClass}" data-month="${col.monthKey}" style="cursor: pointer;" title="${col.fullLabel}">${col.label}</th>`;
                });
                headHtml += `</tr>`;
            } else {
                headHtml += `<tr>
                    <th class="sticky-col-tree">
                        <div class="d-flex align-items-center overflow-hidden">
                            <span class="text-truncate flex-grow-1" title="WBS Work Item">WBS Work Item</span>
                        </div>
                        <div class="col-resizer" data-col="wbs" title="Drag to resize WBS Work Item column"></div>
                    </th>
                    <th class="sticky-col-metric">
                        <div class="d-flex align-items-center overflow-hidden justify-content-end">
                            <span class="text-truncate" title="Scope">Scope</span>
                        </div>
                        <div class="col-resizer" data-col="scope" title="Drag to resize Scope column"></div>
                    </th>
                    <th class="sticky-col-progress">
                        <div class="d-flex align-items-center overflow-hidden">
                            <span class="text-truncate" title="Accomplished">Accomplished</span>
                        </div>
                        <div class="col-resizer" data-col="acc" title="Drag to resize Accomplished column"></div>
                    </th>`;

                columns.forEach(col => {
                    const currentClass = getHighlightColClass(col);
                    headHtml += `<th class="month-header${currentClass}" data-month="${col.monthKey}" style="cursor: pointer;" title="Click zoom icon or double click to view days">
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
                const validContractItems = (section.contract_items || []).filter(ci => {
                    if (showExcludedComponents) return true;
                    return !ci.exclude_from_monitoring || ci.exclude_from_monitoring == 0;
                });
                if (validContractItems.length === 0) return;

                const sectionMatches = !filter || section.name.toLowerCase().includes(filter);
                const isSectionCollapsed = collapsedSections.has(section.id);
                const chevronIcon = isSectionCollapsed ? 'bi-chevron-right' : 'bi-chevron-down';

                // Section row
                bodyHtml += `
                <tr class="row-section" data-section-id="${section.id}" onclick="toggleSection(${section.id}, event)">
                    <td class="sticky-col-tree" title="${escapeHtml(section.name)}">
                        <div class="d-flex align-items-center overflow-hidden">
                            <i class="bi ${chevronIcon} me-1 text-muted flex-shrink-0" id="sec-chevron-${section.id}"></i>
                            <i class="bi bi-folder2 text-warning me-1 flex-shrink-0"></i>
                            <span class="text-truncate flex-grow-1">${escapeHtml(section.name)}</span>
                        </div>
                    </td>
                    <td class="sticky-col-metric text-muted text-truncate">-</td>
                    <td class="sticky-col-progress text-muted text-truncate">-</td>`;

                columns.forEach(col => {
                    const currentClass = getHighlightColClass(col);
                    const cellClass = currentViewMode === 'day' ? 'day-cell' : 'month-cell';
                    bodyHtml += `<td class="${cellClass}${currentClass}" data-month="${col.monthKey}"></td>`;
                });
                bodyHtml += `</tr>`;

                // Contract Items
                validContractItems.forEach(ci => {
                    const ciName = (ci.item_code ? `[${ci.item_code}] ` : '') + (ci.description || ci.name || '');
                    const ciMatches = !filter || ciName.toLowerCase().includes(filter) || sectionMatches;
                    if (filter && !ciMatches) return;

                    const displayCi = isSectionCollapsed ? 'display: none;' : '';
                    const isActive = selectedComponentId === ci.id ? 'active' : '';

                    const isCiExcluded = (ci.exclude_from_monitoring == 1);
                    const excludedClass = isCiExcluded ? 'row-component-excluded' : '';
                    const excludedBadge = isCiExcluded ? `<span class="badge bg-white text-dark ms-1" style="font-size: 8.5px; vertical-align: middle;">Excluded</span>` : '';

                    // Scope and Accomplishment metrics
                    const totalScope = parseFloat(ci.contract_quantity) || 0;
                    const unitText = ci.contract_unit_text || '';
                    
                    let overallLatestActual = null;
                    let firstActual = null;
                    let allTimeLatestTarget = null;
                    let latestPastTarget = null;
                    const todayTime = new Date();
                    todayTime.setHours(0,0,0,0);
                    const timeKeyAccomplishments = {};

                    if (ci.accomplishments) {
                        ci.accomplishments.forEach(acc => {
                            if (acc.type === 'ACTUAL') {
                                if (!firstActual || new Date(acc.entry_data || acc.created_at || 0) < new Date(firstActual.entry_data || firstActual.created_at || 0)) {
                                    firstActual = acc;
                                }

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

                            if (acc.type === 'TARGET') {
                                const targetDate = new Date(acc.entry_data || acc.created_at || 0);
                                if (!allTimeLatestTarget) {
                                    allTimeLatestTarget = acc;
                                } else {
                                    const latestTargetDate = new Date(allTimeLatestTarget.entry_data || allTimeLatestTarget.created_at || 0);
                                    if (targetDate > latestTargetDate || (targetDate.getTime() === latestTargetDate.getTime() && (acc.id || 0) > (allTimeLatestTarget.id || 0))) {
                                        allTimeLatestTarget = acc;
                                    }
                                }

                                const targetDateOnly = new Date(targetDate);
                                targetDateOnly.setHours(0,0,0,0);
                                if (targetDateOnly <= todayTime) {
                                    if (!latestPastTarget) {
                                        latestPastTarget = acc;
                                    } else {
                                        const pastTargetDate = new Date(latestPastTarget.entry_data || latestPastTarget.created_at || 0);
                                        pastTargetDate.setHours(0,0,0,0);
                                        if (targetDateOnly > pastTargetDate || (targetDateOnly.getTime() === pastTargetDate.getTime() && (acc.id || 0) > (latestPastTarget.id || 0))) {
                                            latestPastTarget = acc;
                                        }
                                    }
                                }
                            }

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
                    const latestTargetQty = latestPastTarget ? (parseFloat(latestPastTarget.quantity) || 0) : 0;
                    const isBehindTarget = !isCiExcluded && latestPastTarget && (totalActual < latestTargetQty);
                    const behindClass = isBehindTarget ? 'row-component-behind' : '';
                    const behindBadge = isBehindTarget ? `<span class="badge bg-danger ms-1" style="font-size: 8.5px; vertical-align: middle;">Behind Target</span>` : '';
                    const percentActual = totalScope > 0 ? Math.min(100, Math.round((totalActual / totalScope) * 100)) : 0;
                    const actualDateStr = overallLatestActual && overallLatestActual.entry_data ? overallLatestActual.entry_data.substring(0, 10) : '';

                    let ciVelocity = 0;
                    let ciEtaDateStr = null;
                    let daysElapsed = 0;
                    if (firstActual && overallLatestActual) {
                        const d1Str = (firstActual.entry_data || firstActual.created_at || '').substring(0, 10);
                        const d2Str = (overallLatestActual.entry_data || overallLatestActual.created_at || '').substring(0, 10);
                        if (d1Str && d2Str) {
                            const d1 = new Date(d1Str);
                            const d2 = new Date(d2Str);
                            daysElapsed = Math.max(1, Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1);
                            ciVelocity = totalActual / daysElapsed;
                            if (ciVelocity > 0 && totalActual < totalScope) {
                                const remainingQty = totalScope - totalActual;
                                const remainingDays = Math.ceil(remainingQty / ciVelocity);
                                const etaDate = new Date(d2);
                                etaDate.setDate(etaDate.getDate() + remainingDays);
                                ciEtaDateStr = etaDate.toISOString().substring(0, 10);
                            }
                        }
                    }

                    let requiredVelocity = 0;
                    let reqDaysLeft = 0;
                    let nextNearestTarget = null;
                    if (ci.accomplishments) {
                        const todayDate = new Date();
                        todayDate.setHours(0,0,0,0);

                        const sortedTargets = ci.accomplishments
                            .filter(acc => acc.type === 'TARGET' && (acc.entry_data || acc.created_at))
                            .map(acc => {
                                const dStr = (acc.entry_data || acc.created_at).substring(0, 10);
                                const d = new Date(dStr);
                                d.setHours(0,0,0,0);
                                return {
                                    acc: acc,
                                    date: d,
                                    dateStr: dStr,
                                    quantity: parseFloat(acc.quantity) || 0
                                };
                            })
                            .sort((a, b) => a.date - b.date);

                        // Find the next nearest upcoming target that has not yet been achieved
                        nextNearestTarget = sortedTargets.find(t => t.date >= todayDate && t.quantity > totalActual);

                        if (!nextNearestTarget) {
                            nextNearestTarget = sortedTargets.find(t => t.date >= todayDate);
                        }

                        if (!nextNearestTarget && sortedTargets.length > 0) {
                            nextNearestTarget = sortedTargets[sortedTargets.length - 1];
                        }
                    }

                    if (nextNearestTarget) {
                        const today = new Date();
                        today.setHours(0,0,0,0);
                        reqDaysLeft = Math.max(1, Math.ceil((nextNearestTarget.date - today) / (1000 * 60 * 60 * 24)));
                        const remainingQtyToNextTarget = Math.max(0, nextNearestTarget.quantity - totalActual);
                        if (remainingQtyToNextTarget > 0) {
                            requiredVelocity = remainingQtyToNextTarget / reqDaysLeft;
                        } else {
                            requiredVelocity = 0;
                        }
                    }

                    const unitPrice = parseFloat(ci.contract_unit_price) || 0;
                    const accomplishedAmount = totalActual * unitPrice;

                    const compStats = { 
                        velocity: ciVelocity, 
                        reqVelocity: requiredVelocity,
                        reqDaysLeft: reqDaysLeft,
                        targetDeadline: nextNearestTarget ? nextNearestTarget.dateStr : (allTimeLatestTarget ? (allTimeLatestTarget.entry_data || '').substring(0, 10) : null),
                        nextTargetQty: nextNearestTarget ? nextNearestTarget.quantity : null,
                        nextTargetDate: nextNearestTarget ? nextNearestTarget.dateStr : null,
                        eta: ciEtaDateStr, 
                        days: daysElapsed,
                        totalActual: totalActual,
                        totalScope: totalScope,
                        percentActual: percentActual,
                        unitPrice: unitPrice,
                        accomplishedAmount: accomplishedAmount,
                        totalContractAmount: totalScope * unitPrice
                    };

                    bodyHtml += `
                    <tr class="row-component ${excludedClass} ${behindClass} ${isActive}" id="comp-row-${ci.id}" style="${displayCi}" data-section-parent="${section.id}" data-ci-parent="${ci.id}" data-comp-id="${ci.id}" data-comp-name="${escapeHtml(ciName)}" data-comp-qty="${ci.contract_quantity || ''}" data-comp-unit="${escapeHtml(unitText)}" data-comp-excluded="${isCiExcluded ? '1' : '0'}" onclick="selectComponent(${ci.id}, '${escapeHtml(ciName)}')">
                        <td class="sticky-col-tree" style="padding-left: 28px; cursor: pointer;" title="${escapeHtml(ciName)}">
                            <div class="d-flex align-items-center overflow-hidden">
                                <i class="bi bi-diagram-3 ${isCiExcluded ? 'text-white' : (isBehindTarget ? 'text-danger' : 'text-info')} me-1 flex-shrink-0"></i>
                                <span class="comp-name-text text-truncate flex-grow-1" title="${escapeHtml(ciName)}">${escapeHtml(ciName)}</span>
                                ${behindBadge}
                                ${excludedBadge}
                            </div>
                        </td>
                        <td class="sticky-col-metric" title="${formatNumber(totalScope)} ${escapeHtml(unitText)} @ PHP ${formatNumber(unitPrice)}">
                            <div class="overflow-hidden text-truncate">
                                <span class="fw-semibold">${formatNumber(totalScope)}</span>
                                <small class="text-muted ms-1">${escapeHtml(unitText)}</small>
                            </div>
                            <div class="text-truncate text-muted" style="font-size: 10px;" title="Unit Price: PHP ${formatNumber(unitPrice)}">@ PHP ${formatNumber(unitPrice)}</div>
                        </td>
                        <td class="sticky-col-progress" title="Actual: ${formatNumber(totalActual)} (${percentActual}%) - PHP ${formatNumber(accomplishedAmount)}">
                            <div class="d-flex justify-content-between align-items-center overflow-hidden">
                                <span class="small fw-semibold ${isBehindTarget ? 'text-danger' : 'text-primary'} text-truncate">${formatNumber(totalActual)}</span>
                                <span class="badge ${isBehindTarget ? 'bg-danger' : (percentActual >= 100 ? 'bg-primary' : 'bg-secondary')} flex-shrink-0 ms-1" style="font-size: 10px;">${percentActual}%</span>
                            </div>
                            <div class="gantt-progress-bar">
                                <div class="progress-actual ${isBehindTarget ? 'bg-danger' : ''}" style="width: ${percentActual}%;"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted overflow-hidden" style="font-size: 9.5px; margin-top: 2px;">
                                <span class="${isBehindTarget ? 'text-danger' : 'text-primary'} fw-semibold text-truncate" title="Accomplished Amount: PHP ${formatNumber(accomplishedAmount)}">PHP ${formatNumber(accomplishedAmount)}</span>
                                ${actualDateStr ? `<span class="text-truncate ms-1" title="As of ${escapeHtml(actualDateStr)}">as of ${escapeHtml(actualDateStr)}</span>` : ''}
                            </div>
                        </td>`;

                    // Timeline Columns Cells (Month or Day)
                    columns.forEach(col => {
                        const entry = timeKeyAccomplishments[col.key];
                        const hasTarget = !!(entry && entry.latestTarget);
                        const hasActual = !!(entry && entry.latestActual);
                        const currentClass = getHighlightColClass(col);
                        const cellClass = currentViewMode === 'day' ? 'day-cell' : 'month-cell';

                        bodyHtml += `<td class="${cellClass}${currentClass}" data-month="${col.monthKey}" data-comp-id="${ci.id}" data-comp-name="${escapeHtml(ciName)}" data-comp-qty="${ci.contract_quantity || ''}" data-comp-unit="${escapeHtml(unitText)}" data-cell-key="${col.key}" data-cell-label="${escapeHtml(col.label)}">`;
                        if (hasTarget || hasActual) {
                            if (hasTarget) {
                                const targetQty = parseFloat(entry.latestTarget.quantity) || 0;
                                const targetDateStr = entry.latestTarget.entry_data ? entry.latestTarget.entry_data.substring(0, 10) : '';
                                const targetData = { ...entry.latestTarget, unitText: unitText, compName: ciName, compStats: compStats };
                                const targetDataEncoded = encodeURIComponent(JSON.stringify(targetData));
                                bodyHtml += `
                                <div class="gantt-pill pill-target" data-id="${entry.latestTarget.id}" style="cursor: pointer;" onclick="handlePillClick(event, '${targetDataEncoded}', this)" title="Target: ${formatNumber(targetQty)} ${escapeHtml(unitText)} as of ${escapeHtml(targetDateStr)}">
                                    <span>🎯 ${formatNumber(targetQty)}&nbsp;</span>
                                    <div class="d-flex align-items-center gap-1">
                                        <small class="ms-1">${escapeHtml(unitText)}</small>
                                        <i class="bi bi-x-circle text-danger ms-1 delete-pill-btn" onclick="event.stopPropagation(); deleteAccomplishment(${entry.latestTarget.id})" title="Delete Target"></i>
                                    </div>
                                </div>`;
                            } else {
                                bodyHtml += `
                                <div class="gantt-pill pill-placeholder pill-placeholder-target" title="No Target">
                                    <span>&mdash;</span>
                                </div>`;
                            }

                            if (hasActual) {
                                const actualQty = parseFloat(entry.latestActual.quantity) || 0;
                                const monthActualDateStr = entry.latestActual.entry_data ? entry.latestActual.entry_data.substring(0, 10) : '';
                                const actualData = { ...entry.latestActual, unitText: unitText, compName: ciName, compStats: compStats };
                                const actualDataEncoded = encodeURIComponent(JSON.stringify(actualData));
                                bodyHtml += `
                                <div class="gantt-pill pill-actual" data-id="${entry.latestActual.id}" style="cursor: pointer;" onclick="handlePillClick(event, '${actualDataEncoded}', this)" title="Actual: ${formatNumber(actualQty)} ${escapeHtml(unitText)} as of ${escapeHtml(monthActualDateStr)}">
                                    <span>✅ ${formatNumber(actualQty)}&nbsp;</span>
                                    <div class="d-flex align-items-center gap-1">
                                        <small class="ms-1">${escapeHtml(unitText)}</small>
                                        <i class="bi bi-x-circle text-danger ms-1 delete-pill-btn" onclick="event.stopPropagation(); deleteAccomplishment(${entry.latestActual.id})" title="Delete Actual"></i>
                                    </div>
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
            });

            if (!bodyHtml) {
                bodyHtml = `<tr><td colspan="${3 + columns.length}" class="text-center py-4 text-muted">No monitored contract items found in this project.</td></tr>`;
            }

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
        const ctxMenuAddActual = document.getElementById('ctxMenuAddActual');
        const ctxMenuAddTarget = document.getElementById('ctxMenuAddTarget');
        const ctxMenuDivider   = document.getElementById('ctxMenuDivider');
        const ctxMenuExclude   = document.getElementById('ctxMenuExclude');
        const ctxMenuInclude   = document.getElementById('ctxMenuInclude');

        document.getElementById('ganttTable').addEventListener('contextmenu', (e) => {
            const compRow = e.target.closest('.row-component');
            if (!compRow) return;

            const cell = e.target.closest('td');
            if (!cell) return;

            const compId = cell.dataset.compId || compRow.dataset.compId;
            const compName = cell.dataset.compName || compRow.dataset.compName;
            const compQty = cell.dataset.compQty || compRow.dataset.compQty;
            const compUnit = cell.dataset.compUnit || compRow.dataset.compUnit;
            const cellDate = cell.dataset.cellKey || '';
            const isExcluded = (compRow.dataset.compExcluded === '1');

            if (!compId) return;

            e.preventDefault();
            contextMenuTarget.compId = compId;
            contextMenuTarget.compName = compName;
            contextMenuTarget.compQty = compQty;
            contextMenuTarget.compUnit = compUnit;
            contextMenuTarget.cellDate = cellDate;
            contextMenuTarget.isExcluded = isExcluded;

            // Check if right-clicked inside the WBS Work Item column or other sticky non-timeline columns
            const isWbsCol = cell.classList.contains('sticky-col-tree') || cell.classList.contains('sticky-col-metric') || cell.classList.contains('sticky-col-progress');

            if (isExcluded) {
                // If excluded component: only show "Include in Monitoring"
                if (ctxMenuAddActual) ctxMenuAddActual.style.display = 'none';
                if (ctxMenuAddTarget) ctxMenuAddTarget.style.display = 'none';
                if (ctxMenuExclude) ctxMenuExclude.style.display = 'none';
                if (ctxMenuDivider) ctxMenuDivider.style.display = 'none';
                if (ctxMenuInclude) ctxMenuInclude.style.display = 'flex';
                contextMenuTitle.innerText = `${compName} (Excluded)`;
            } else {
                if (ctxMenuInclude) ctxMenuInclude.style.display = 'none';
                if (isWbsCol) {
                    // In WBS column: hide Add Actual, Add Target, and divider
                    if (ctxMenuAddActual) ctxMenuAddActual.style.display = 'none';
                    if (ctxMenuAddTarget) ctxMenuAddTarget.style.display = 'none';
                    if (ctxMenuDivider) ctxMenuDivider.style.display = 'none';
                    if (ctxMenuExclude) ctxMenuExclude.style.display = 'flex';
                    contextMenuTitle.innerText = `${compName}`;
                } else {
                    // In timeline columns: show Add Actual, Add Target, and divider
                    if (ctxMenuAddActual) ctxMenuAddActual.style.display = 'flex';
                    if (ctxMenuAddTarget) ctxMenuAddTarget.style.display = 'flex';
                    if (ctxMenuDivider) ctxMenuDivider.style.display = 'block';
                    if (ctxMenuExclude) ctxMenuExclude.style.display = 'flex';
                    contextMenuTitle.innerText = `${compName}${cell.dataset.cellLabel ? ' (' + cell.dataset.cellLabel + ')' : ''}`;
                }
            }

            // Position context menu
            let x = e.clientX;
            let y = e.clientY;
            if (x + 230 > window.innerWidth) x = window.innerWidth - 240;
            if (y + 180 > window.innerHeight) y = window.innerHeight - 190;

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

        window.excludeComponentFromMonitoring = async function() {
            ganttContextMenu.style.display = 'none';
            if (!contextMenuTarget.compId) return;

            const confirmed = await window.util.confirm(`Are you sure you want to exclude "${contextMenuTarget.compName}" from accomplishment monitoring? It will be removed from this chart.`);
            if (confirmed) {
                fetch('/api/accomplishment/contract_item/exclude', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ contract_item_id: contextMenuTarget.compId, component_id: contextMenuTarget.compId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 1) {
                        loadProjectData();
                    } else {
                        alert(data.message || 'Error excluding item');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('A network error occurred.');
                });
            }
        };

        window.includeComponentInMonitoring = async function() {
            ganttContextMenu.style.display = 'none';
            if (!contextMenuTarget.compId) return;

            const confirmed = await window.util.confirm(`Are you sure you want to include "${contextMenuTarget.compName}" back in accomplishment monitoring?`);
            if (confirmed) {
                fetch('/api/accomplishment/contract_item/include', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ contract_item_id: contextMenuTarget.compId, component_id: contextMenuTarget.compId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 1) {
                        loadProjectData();
                    } else {
                        alert(data.message || 'Error including item');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('A network error occurred.');
                });
            }
        };

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
                contract_item_id: contextMenuTarget.compId,
                component_id: contextMenuTarget.compId,
                total_quantity: contextMenuTarget.compQty,
                unit: contextMenuTarget.compUnit,
                type: type,
                entry_data: entryDate,
                successCallback: () => {
                    loadProjectData();
                }
            })).open();
        };

        window.deleteAccomplishment = async function(id) {
            const confirmed = await window.util.confirm('Are you sure you want to delete this accomplishment record?');
            if (confirmed) {
                fetch('/api/accomplishment/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 1) {
                        loadProjectData();
                    } else {
                        alert(data.message || 'Error deleting accomplishment');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('A network error occurred.');
                });
            }
        };

        window.viewAccomplishment = function(accDataStr) {
            try {
                const acc = JSON.parse(decodeURIComponent(accDataStr));
                const padId = String(acc.id || 0).padStart(6, '0');
                const compName = acc.compName || 'Component';
                const unit = acc.unitText || '';
                const typeBadgeClass = acc.type === 'TARGET' ? 'bg-success' : 'bg-primary';

                const stats = acc.compStats || {};
                const velocityStr = stats.velocity > 0 ? `${formatNumber(stats.velocity)} ${escapeHtml(unit)}/day` : 'N/A';
                const reqDaysText = stats.reqDaysLeft > 0 ? ` for ${stats.reqDaysLeft} day${stats.reqDaysLeft === 1 ? '' : 's'}` : '';
                const reqVelocityStr = stats.reqVelocity > 0 ? `${formatNumber(stats.reqVelocity)} ${escapeHtml(unit)}/day${reqDaysText}` : 'N/A';
                const etaStr = stats.eta ? stats.eta : (stats.percentActual >= 100 ? 'Completed' : 'Insufficient Data');
                const daysStr = stats.days ? `${stats.days} day(s) active` : '';
                const speedStatusClass = (stats.velocity && stats.reqVelocity) ? (stats.velocity >= stats.reqVelocity ? 'text-success' : 'text-danger') : 'text-info';
                const nextTargetColValue = (stats.nextTargetDate && stats.nextTargetQty !== null) 
                    ? `${formatNumber(stats.nextTargetQty)} ${escapeHtml(unit)}` 
                    : 'N/A';
                const nextTargetColSub = (stats.nextTargetDate && stats.nextTargetQty !== null) 
                    ? `<div class="text-muted small" style="font-size: 10px;">by ${stats.nextTargetDate}</div>` 
                    : '';

                const html = `
                    <div class="card mb-3 border-secondary bg-dark">
                        <div class="card-header bg-dark border-secondary py-2">
                            <span class="text-light fw-bold"><i class="bi bi-box-seam me-1"></i> ${escapeHtml(compName)}</span>
                        </div>
                    </div>

                    <div class="card mb-3 border-secondary bg-dark">
                        <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                            <span class="text-light fw-bold small"><i class="bi bi-speedometer2 text-info me-1"></i> Velocity & Target Rate</span>
                            <small class="text-muted">${daysStr}</small>
                        </div>
                        <div class="card-body py-2">
                            <div class="row g-2">
                                <div class="col-3">
                                    <div class="text-muted small" style="font-size: 11px;">Running Speed</div>
                                    <div class="fw-semibold ${speedStatusClass}">${velocityStr}</div>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small" style="font-size: 11px;">Est. Completion Date</div>
                                    <div class="fw-semibold text-warning">${escapeHtml(etaStr)}</div>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small" style="font-size: 11px;">Required Speed</div>
                                    <div class="fw-semibold text-light">${reqVelocityStr}</div>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small" style="font-size: 11px;">Next Target</div>
                                    <div class="fw-semibold text-info">${nextTargetColValue}</div>
                                    ${nextTargetColSub}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-container">
                        <div class="form-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold">Record ID</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-light" disabled value="${padId}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold">Type</label>
                                    <div>
                                        <span class="badge ${typeBadgeClass} fs-6 py-2 px-3">${escapeHtml(acc.type)}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold">Entry Date</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-light" disabled value="${acc.entry_data ? acc.entry_data.substring(0, 10) : 'N/A'}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold">Quantity</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-light" disabled value="${formatNumber(acc.quantity)} ${escapeHtml(unit)}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-semibold">Remarks</label>
                                    <textarea class="form-control bg-dark border-secondary text-light" rows="3" disabled>${escapeHtml(acc.remarks || 'None')}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold">Created By</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-light" disabled value="${escapeHtml(acc.creator_name || 'System')}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold">Created At</label>
                                    <input type="text" class="form-control bg-dark border-secondary text-light" disabled value="${acc.created_at ? new Date(acc.created_at).toLocaleString('en-US', { hour12: false }) : 'N/A'}">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-outline-danger me-2" onclick="window.util.drawerModal.close(); deleteAccomplishment(${acc.id});">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                    <button type="button" class="btn btn-secondary px-4" onclick="window.util.drawerModal.close()">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                const contentEl = document.createElement('div');
                contentEl.innerHTML = html;

                window.util.drawerModal.content(`Accomplishment Details #${padId}`, contentEl).open();
            } catch (err) {
                console.error('Error viewing accomplishment record:', err);
            }
        };

        /* ================= Zoom In / Out ================= */
        window.zoomOut = function() {
            currentViewMode = 'month';
            zoomedMonthKey = null;
            renderGantt();
        };

        // Double-click event: header zooms into month, component cell views component progress
        document.getElementById('ganttTable').addEventListener('dblclick', (e) => {
            // Header double-click to zoom
            const cell = e.target.closest('.month-header');
            if (cell && cell.dataset.month && currentViewMode === 'month') {
                zoomedMonthKey = cell.dataset.month;
                currentViewMode = 'day';
                renderGantt();
                return;
            }

            // Component tree cell double-click to view component progress
            const compTreeCell = e.target.closest('.row-component td.sticky-col-tree');
            if (compTreeCell) {
                const row = compTreeCell.closest('.row-component');
                const compId = row ? parseInt(row.id.replace('comp-row-', '')) : null;
                if (compId) {
                    showComponentProgress(compId);
                }
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

        function updateProjectHeader() {
            if (!projectData || !projectData.sections) return;
            let totalContractAmount = 0;
            projectData.sections.forEach(section => {
                if (section.contract_items) {
                    section.contract_items.forEach(ci => {
                        if (ci.exclude_from_monitoring == 1) return;
                        const totalScope = parseFloat(ci.contract_quantity) || 0;
                        const unitPrice = parseFloat(ci.contract_unit_price) || 0;
                        totalContractAmount += (totalScope * unitPrice);
                    });
                }
            });
            const projNameEl = document.querySelector('.studio-project-name');
            if (projNameEl) {
                const formattedAmount = 'P' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalContractAmount);
                const titleStr = `${projectData.name} — ${formattedAmount}`;
                projNameEl.textContent = titleStr;
                projNameEl.title = titleStr;
            }
        }

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
                    updateProjectHeader();
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

        window.showOverallProgress = function() {
            if (!projectData || !projectData.sections) return;

            let totalComponents = 0;
            let totalContractAmount = 0;

            let projectFirstActualDate = null;
            let projectLatestActualDate = null;
            let projectLatestTargetDate = null;
            const projectAllTargetDates = new Set();
            const today = new Date();
            today.setHours(0,0,0,0);
            const todayYear = today.getFullYear();
            const todayMonth = String(today.getMonth() + 1).padStart(2, '0');
            const todayDay = String(today.getDate()).padStart(2, '0');
            const todayDateStr = `${todayYear}-${todayMonth}-${todayDay}`;

            // First pass: collect dates and total contract amounts
            projectData.sections.forEach(section => {
                if (section.contract_items) {
                    section.contract_items.forEach(ci => {
                        if (ci.exclude_from_monitoring == 1) return;
                        totalComponents++;
                        const totalScope = parseFloat(ci.contract_quantity) || 0;
                        const unitPrice = parseFloat(ci.contract_unit_price) || 0;
                        totalContractAmount += (totalScope * unitPrice);

                        if (ci.accomplishments && ci.accomplishments.length > 0) {
                            ci.accomplishments.forEach(acc => {
                                const accDateStr = acc.entry_data ? acc.entry_data.substring(0, 10) : '';
                                if (acc.type === 'ACTUAL' && accDateStr) {
                                    if (!projectFirstActualDate || new Date(accDateStr) < new Date(projectFirstActualDate)) {
                                        projectFirstActualDate = accDateStr;
                                    }
                                    if (!projectLatestActualDate || new Date(accDateStr) > new Date(projectLatestActualDate)) {
                                        projectLatestActualDate = accDateStr;
                                    }
                                }
                                if (acc.type === 'TARGET' && accDateStr) {
                                    projectAllTargetDates.add(accDateStr);
                                    if (!projectLatestTargetDate || new Date(accDateStr) > new Date(projectLatestTargetDate)) {
                                        projectLatestTargetDate = accDateStr;
                                    }
                                }
                            });
                        }
                    });
                }
            });

            // Find next nearest project target date automatically
            let nextNearestProjectTargetDate = null;
            if (projectAllTargetDates.size > 0) {
                const sortedProjectTargetDates = Array.from(projectAllTargetDates)
                    .map(dStr => {
                        const d = new Date(dStr);
                        d.setHours(0,0,0,0);
                        return { dateStr: dStr, date: d };
                    })
                    .sort((a, b) => a.date - b.date);

                const upcoming = sortedProjectTargetDates.find(t => t.date >= today);
                if (upcoming) {
                    nextNearestProjectTargetDate = upcoming.dateStr;
                } else {
                    nextNearestProjectTargetDate = sortedProjectTargetDates[sortedProjectTargetDates.length - 1].dateStr;
                }
            }

            const targetDateToUse = projectLatestTargetDate;

            let sumTargetMoneyAmount = 0;
            let sumActualMoneyAmount = 0;
            let totalAccomplishedAmount = 0;
            let totalTargetAmount = 0;
            let sumAllTimeActualMoneyAmount = 0;
            let sumWeightedTarget = 0;
            let sumWeightedActual = 0;
            let sumNextWeightedTarget = 0;
            const itemBreakdownRows = [];

            // Second pass: compute target amounts and percentages based on the next nearest target date
            projectData.sections.forEach(section => {
                if (section.contract_items) {
                    section.contract_items.forEach(ci => {
                        if (ci.exclude_from_monitoring == 1) return;
                        const totalScope = parseFloat(ci.contract_quantity) || 0;
                        const unitPrice = parseFloat(ci.contract_unit_price) || 0;

                        let targetQty = 0;
                        let nextTargetQty = 0;
                        let allTimeLatestActual = null;

                        if (ci.accomplishments && ci.accomplishments.length > 0) {
                            let latestTarget = null;
                            let latestNextTarget = null;
                            ci.accomplishments.forEach(acc => {
                                const accDateStr = acc.entry_data ? acc.entry_data.substring(0, 10) : '';
                                if (acc.type === 'TARGET' && accDateStr) {
                                    if (accDateStr <= todayDateStr) {
                                        const currentLatestTargetDateStr = latestTarget && latestTarget.entry_data ? latestTarget.entry_data.substring(0, 10) : '';
                                        if (!latestTarget || accDateStr > currentLatestTargetDateStr || (accDateStr === currentLatestTargetDateStr && acc.id > latestTarget.id)) {
                                            latestTarget = acc;
                                        }
                                    }
                                    if (nextNearestProjectTargetDate && accDateStr <= nextNearestProjectTargetDate) {
                                        const currentNextDateStr = latestNextTarget && latestNextTarget.entry_data ? latestNextTarget.entry_data.substring(0, 10) : '';
                                        if (!latestNextTarget || accDateStr > currentNextDateStr || (accDateStr === currentNextDateStr && acc.id > latestNextTarget.id)) {
                                            latestNextTarget = acc;
                                        }
                                    }
                                }
                                if (acc.type === 'ACTUAL' && accDateStr) {
                                    if (!allTimeLatestActual || new Date(acc.entry_data) > new Date(allTimeLatestActual.entry_data) || (acc.entry_data === allTimeLatestActual.entry_data && acc.id > allTimeLatestActual.id)) {
                                        allTimeLatestActual = acc;
                                    }
                                }
                            });
                            if (latestTarget) targetQty = parseFloat(latestTarget.quantity) || 0;
                            if (latestNextTarget) nextTargetQty = parseFloat(latestNextTarget.quantity) || 0;
                        }

                        const actualQty = allTimeLatestActual ? (parseFloat(allTimeLatestActual.quantity) || 0) : 0;
                        totalTargetAmount += targetQty * unitPrice;
                        totalAccomplishedAmount += actualQty * unitPrice;

                        const ciContractAmount = totalScope * unitPrice;
                        const ciWeight = totalContractAmount > 0 ? (ciContractAmount / totalContractAmount) : 0;

                        let percentTarget = 0;
                        let percentActual = 0;
                        let percentNextTarget = 0;
                        if (totalScope > 0) {
                            percentTarget = Math.min(100, (targetQty / totalScope) * 100);
                            percentActual = Math.min(100, (actualQty / totalScope) * 100);
                            percentNextTarget = Math.min(100, (nextTargetQty / totalScope) * 100);
                        }

                        const targetMoneyAmount = (percentTarget / 100) * ciContractAmount;
                        const actualMoneyAmount = (percentActual / 100) * ciContractAmount;

                        sumTargetMoneyAmount += targetMoneyAmount;
                        sumActualMoneyAmount += actualMoneyAmount;
                        sumAllTimeActualMoneyAmount += actualMoneyAmount;

                        const weightedTarget = percentTarget * ciWeight;
                        const weightedActual = percentActual * ciWeight;
                        const weightedNextTarget = percentNextTarget * ciWeight;

                        sumWeightedTarget += weightedTarget;
                        sumWeightedActual += weightedActual;
                        sumNextWeightedTarget += weightedNextTarget;

                        const ciName = (ci.item_code ? `[${ci.item_code}] ` : '') + (ci.description || ci.name || '');
                        itemBreakdownRows.push({
                            name: ciName,
                            amount: ciContractAmount,
                            weight: ciWeight * 100,
                            percentActual: percentActual,
                            actualMoney: actualMoneyAmount,
                            weightedActual: weightedActual
                        });
                    });
                }
            });

            const overallTarget = sumWeightedTarget;
            const overallActual = sumWeightedActual;
            const variance = overallActual - overallTarget;
            const financialPercent = totalContractAmount > 0 ? Math.min(100, (totalAccomplishedAmount / totalContractAmount) * 100) : 0;
            
            // Running velocity across the full project lifecycle
            const currentOverallActual = sumWeightedActual;
            let runningDaysElapsed = 0;
            let runningVelocityPerDay = 0;
            let projectEtaStr = 'Insufficient Data';

            if (projectFirstActualDate && projectLatestActualDate) {
                const dStart = new Date(projectFirstActualDate);
                const dEnd = new Date(projectLatestActualDate);
                runningDaysElapsed = Math.max(1, Math.ceil((dEnd - dStart) / (1000 * 60 * 60 * 24)) + 1);
                runningVelocityPerDay = currentOverallActual / runningDaysElapsed;

                if (currentOverallActual >= 100) {
                    projectEtaStr = 'Completed';
                } else if (runningVelocityPerDay > 0) {
                    const remainingPercent = 100 - currentOverallActual;
                    const remainingDays = Math.ceil(remainingPercent / runningVelocityPerDay);
                    const etaDate = new Date();
                    etaDate.setDate(etaDate.getDate() + remainingDays);
                    projectEtaStr = etaDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ` (~${remainingDays} days)`;
                }
            } else if (currentOverallActual >= 100) {
                projectEtaStr = 'Completed';
            }

            const formattedRunningVelocity = runningVelocityPerDay > 0 ? `${runningVelocityPerDay.toFixed(2)}%/day` : '0.00%/day';

            let requiredVelocityPerDay = 0;
            let projectDaysLeft = 0;
            if (targetDateToUse && currentOverallActual < 100) {
                const dTarget = new Date(targetDateToUse);
                const today = new Date();
                today.setHours(0,0,0,0);
                dTarget.setHours(0,0,0,0);
                projectDaysLeft = Math.max(1, Math.ceil((dTarget - today) / (1000 * 60 * 60 * 24)));
                const remainingPercent = 100 - currentOverallActual;
                requiredVelocityPerDay = remainingPercent / projectDaysLeft;
            }
            const projectDaysText = projectDaysLeft > 0 ? ` for ${projectDaysLeft} day${projectDaysLeft === 1 ? '' : 's'}` : '';
            const formattedRequiredVelocity = requiredVelocityPerDay > 0 ? `${requiredVelocityPerDay.toFixed(2)}%/day${projectDaysText}` : 'N/A';
            const projectSpeedStatusClass = (runningVelocityPerDay && requiredVelocityPerDay) ? (runningVelocityPerDay >= requiredVelocityPerDay ? 'text-success' : 'text-danger') : 'text-info';

            const formatNum = (num) => num.toFixed(2) + '%';
            const varianceText = variance >= 0 ? `+${formatNum(variance)} Ahead` : `${formatNum(Math.abs(variance))} Behind`;
            const varianceClass = variance >= 0 ? 'text-success' : 'text-danger';

            const breakdownHtml = itemBreakdownRows.map(row => `
                <tr>
                    <td class="text-truncate" style="max-width: 180px;" title="${escapeHtml(row.name)}">${escapeHtml(row.name)}</td>
                    <td class="text-end">PHP ${formatNumber(row.amount)}</td>
                    <td class="text-end text-info">${formatNum(row.weight)}</td>
                    <td class="text-end">${formatNum(row.percentActual)}</td>
                    <td class="text-end text-success">PHP ${formatNumber(row.actualMoney)}</td>
                    <td class="text-end fw-semibold text-primary">${formatNum(row.weightedActual)}</td>
                </tr>
            `).join('');

            const html = `
                <div class="card mb-3 border-secondary bg-dark">
                    <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                        <span class="text-light fw-bold small"><i class="bi bi-cash-stack text-success me-1"></i> Financial Accomplishment (PHP)</span>
                        <span class="badge ${totalContractAmount > 0 && totalAccomplishedAmount >= totalTargetAmount ? 'bg-success' : 'bg-primary'}">${totalContractAmount > 0 ? ((totalAccomplishedAmount / totalContractAmount) * 100).toFixed(1) : 0}%</span>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-muted small" style="font-size: 11px;">Total Contract Value</div>
                                <div class="fw-semibold text-light">PHP ${formatNumber(totalContractAmount)}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small" style="font-size: 11px;">Accomplished Amount</div>
                                <div class="fw-semibold text-primary">PHP ${formatNumber(totalAccomplishedAmount)}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small" style="font-size: 11px;">Target Planned</div>
                                <div class="fw-semibold text-success">PHP ${formatNumber(totalTargetAmount)}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 border-secondary bg-dark">
                    <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                        <span class="text-light fw-bold small"><i class="bi bi-speedometer2 text-info me-1"></i> Velocity & Target Rate</span>
                        <small class="text-muted">${runningDaysElapsed > 0 ? runningDaysElapsed + ' day(s) tracked' : ''}</small>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Running Speed</div>
                                <div class="fw-semibold ${projectSpeedStatusClass}">${formattedRunningVelocity}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Est. Completion Date</div>
                                <div class="fw-semibold text-warning">${escapeHtml(projectEtaStr)}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Required Speed</div>
                                <div class="fw-semibold text-light">${formattedRequiredVelocity}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Next Target</div>
                                <div class="fw-semibold text-info">${nextNearestProjectTargetDate ? formatNum(sumNextWeightedTarget) + ' on ' + nextNearestProjectTargetDate : 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-dark border-secondary text-light mb-3">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="text-muted"><i class="bi bi-bullseye text-success me-1"></i> Target (Planned)</span>
                            <span class="fw-bold fs-5 text-success">${formatNum(overallTarget)}</span>
                        </div>
                        <div class="progress" style="height: 12px; background-color: var(--bg-darker);">
                            <div class="progress-bar bg-success" role="progressbar" style="width: ${overallTarget}%"></div>
                        </div>
                    </div>
                </div>

                <div class="card bg-dark border-secondary text-light mb-3">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="text-muted"><i class="bi bi-check-circle text-primary me-1"></i> Actual Accomplished</span>
                            <span class="fw-bold fs-5 text-primary">${formatNum(overallActual)}</span>
                        </div>
                        <div class="progress" style="height: 12px; background-color: var(--bg-darker);">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: ${overallActual}%"></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 border-secondary bg-dark">
                    <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                        <span class="text-light fw-bold small"><i class="bi bi-pie-chart text-info me-1"></i> Weighted Accomplishment Breakdown</span>
                        <small class="text-muted">${totalComponents} Contract Items</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-sm table-hover mb-0" style="font-size: 11px;">
                                <thead class="table-secondary text-dark">
                                    <tr>
                                        <th>Contract Item</th>
                                        <th class="text-end">Contract Amount</th>
                                        <th class="text-end">Weight %</th>
                                        <th class="text-end">Actual %</th>
                                        <th class="text-end">Accomplished Amount</th>
                                        <th class="text-end">Weighted Actual %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${breakdownHtml}
                                </tbody>
                                <tfoot class="border-top border-secondary fw-bold bg-dark">
                                    <tr>
                                        <td>Total</td>
                                        <td class="text-end">PHP ${formatNumber(totalContractAmount)}</td>
                                        <td class="text-end text-info">100.00%</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end text-success">PHP ${formatNumber(sumActualMoneyAmount)}</td>
                                        <td class="text-end text-primary">${formatNum(overallActual)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <span class="badge bg-dark border border-secondary text-light fs-6 py-2 px-4 shadow-sm">
                        Variance: <span class="${varianceClass} ms-2 fw-bold">${varianceText}</span>
                    </span>
                    <div class="text-muted small mt-2">Calculated as the sum of weighted actual accomplishments (${formatNum(overallActual)}) across ${totalComponents} contract items.</div>
                </div>

                <div class="text-end mt-5">
                    <button type="button" class="btn btn-secondary px-4" onclick="window.util.drawerModal.close()">Close</button>
                </div>
            `;

            const contentEl = document.createElement('div');
            contentEl.innerHTML = html;

            window.util.drawerModal.content('Overall Project Progress', contentEl).open();
        };

        window.showComponentProgress = function(compId) {
            if (!projectData || !projectData.sections) return;

            let comp = null;
            for (const section of projectData.sections) {
                if (section.contract_items) {
                    const found = section.contract_items.find(c => c.id === compId);
                    if (found) {
                        comp = found;
                        break;
                    }
                }
                if (comp) break;
            }
            if (!comp) return;

            const totalScope = parseFloat(comp.contract_quantity) || 0;
            const unitText = comp.contract_unit_text || '';
            const unitPrice = parseFloat(comp.contract_unit_price) || 0;

            let allTimeFirstActual = null;
            let allTimeLatestActual = null;
            let allTimeLatestTarget = null;
            let latestPastTarget = null;
            const today = new Date();
            today.setHours(0,0,0,0);

            if (comp.accomplishments && comp.accomplishments.length > 0) {
                comp.accomplishments.forEach(acc => {
                    const accDateStr = acc.entry_data ? acc.entry_data.substring(0, 10) : '';

                    // Running calculation (all time)
                    if (acc.type === 'ACTUAL' && accDateStr) {
                        if (!allTimeFirstActual || new Date(acc.entry_data || acc.created_at || 0) < new Date(allTimeFirstActual.entry_data || allTimeFirstActual.created_at || 0)) {
                            allTimeFirstActual = acc;
                        }
                        if (!allTimeLatestActual || new Date(acc.entry_data) > new Date(allTimeLatestActual.entry_data) || (acc.entry_data === allTimeLatestActual.entry_data && acc.id > allTimeLatestActual.id)) {
                            allTimeLatestActual = acc;
                        }
                    }

                    if (acc.type === 'TARGET' && accDateStr) {
                        if (!allTimeLatestTarget || new Date(acc.entry_data) > new Date(allTimeLatestTarget.entry_data) || (acc.entry_data === allTimeLatestTarget.entry_data && acc.id > allTimeLatestTarget.id)) {
                            allTimeLatestTarget = acc;
                        }

                        const targetDate = new Date(acc.entry_data);
                        targetDate.setHours(0,0,0,0);
                        if (targetDate <= today) {
                            if (!latestPastTarget || targetDate > new Date(latestPastTarget.entry_data) || (acc.entry_data === latestPastTarget.entry_data && acc.id > latestPastTarget.id)) {
                                latestPastTarget = acc;
                            }
                        }
                    }
                });
            }

            const allTimeActualQty = allTimeLatestActual ? (parseFloat(allTimeLatestActual.quantity) || 0) : 0;
            const actualQty = allTimeActualQty;

            // Find next nearest target automatically
            let nextNearestTarget = null;
            if (comp.accomplishments && comp.accomplishments.length > 0) {
                const sortedTargets = comp.accomplishments
                    .filter(acc => acc.type === 'TARGET' && (acc.entry_data || acc.created_at))
                    .map(acc => {
                        const dStr = (acc.entry_data || acc.created_at).substring(0, 10);
                        const d = new Date(dStr);
                        d.setHours(0,0,0,0);
                        return {
                            acc: acc,
                            date: d,
                            dateStr: dStr,
                            quantity: parseFloat(acc.quantity) || 0
                        };
                    })
                    .sort((a, b) => a.date - b.date);

                // Find next nearest upcoming target that has not yet been achieved
                nextNearestTarget = sortedTargets.find(t => t.date >= today && t.quantity > allTimeActualQty);

                if (!nextNearestTarget) {
                    nextNearestTarget = sortedTargets.find(t => t.date >= today);
                }

                if (!nextNearestTarget && sortedTargets.length > 0) {
                    nextNearestTarget = sortedTargets[sortedTargets.length - 1];
                }
            }

            const targetQty = allTimeLatestTarget ? (parseFloat(allTimeLatestTarget.quantity) || 0) : 0;
            const percentTarget = totalScope > 0 ? Math.min(100, (targetQty / totalScope) * 100) : 0;
            const percentActual = totalScope > 0 ? Math.min(100, (actualQty / totalScope) * 100) : 0;
            const variance = percentActual - percentTarget;

            const totalContractAmount = totalScope * unitPrice;
            const targetAmount = targetQty * unitPrice;
            const accomplishedAmount = actualQty * unitPrice;
            const remainingAmount = Math.max(0, (totalScope - actualQty) * unitPrice);

            // Calculate total contract amount across all contract items in the project
            let projectTotalContractAmount = 0;
            if (projectData && projectData.sections) {
                projectData.sections.forEach(section => {
                    if (section.contract_items) {
                        section.contract_items.forEach(ci => {
                            if (ci.exclude_from_monitoring == 1) return;
                            const ciQty = parseFloat(ci.contract_quantity) || 0;
                            const ciPrice = parseFloat(ci.contract_unit_price) || 0;
                            projectTotalContractAmount += (ciQty * ciPrice);
                        });
                    }
                });
            }

            const itemWeight = projectTotalContractAmount > 0 ? (totalContractAmount / projectTotalContractAmount) : 0;
            const weightedTarget = percentTarget * itemWeight;
            const weightedActual = percentActual * itemWeight;

            // Running Velocity & ETA calculation across entire component lifecycle
            let compVelocity = 0;
            let compEtaDateStr = 'Insufficient Data';
            let daysElapsed = 0;

            if (allTimeFirstActual && allTimeLatestActual) {
                const d1Str = (allTimeFirstActual.entry_data || allTimeFirstActual.created_at || '').substring(0, 10);
                const d2Str = (allTimeLatestActual.entry_data || allTimeLatestActual.created_at || '').substring(0, 10);
                if (d1Str && d2Str) {
                    const d1 = new Date(d1Str);
                    const d2 = new Date(d2Str);
                    daysElapsed = Math.max(1, Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1);
                    compVelocity = allTimeActualQty / daysElapsed;
                    if (compVelocity > 0 && allTimeActualQty < totalScope) {
                        const remQty = totalScope - allTimeActualQty;
                        const remainingDays = Math.ceil(remQty / compVelocity);
                        const etaDate = new Date();
                        etaDate.setDate(etaDate.getDate() + remainingDays);
                        compEtaDateStr = etaDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ` (~${remainingDays} days)`;
                    } else if (allTimeActualQty >= totalScope) {
                        compEtaDateStr = 'Completed';
                    }
                }
            } else if (allTimeActualQty >= totalScope && totalScope > 0) {
                compEtaDateStr = 'Completed';
            }

            let requiredVelocity = 0;
            let compDaysLeft = 0;
            if (nextNearestTarget) {
                compDaysLeft = Math.max(1, Math.ceil((nextNearestTarget.date - today) / (1000 * 60 * 60 * 24)));
                const remainingQtyToNextTarget = Math.max(0, nextNearestTarget.quantity - allTimeActualQty);
                if (remainingQtyToNextTarget > 0) {
                    requiredVelocity = remainingQtyToNextTarget / compDaysLeft;
                } else {
                    requiredVelocity = 0;
                }
            }

            const remainingQty = Math.max(0, totalScope - actualQty);
            const formatNum = (num) => num.toFixed(2) + '%';
            const varianceText = variance >= 0 ? `+${formatNum(variance)} Ahead` : `${formatNum(Math.abs(variance))} Behind`;
            const varianceClass = variance >= 0 ? 'text-success' : 'text-danger';
            const velocityStr = compVelocity > 0 ? `${formatNumber(compVelocity)} ${escapeHtml(unitText)}/day` : '0.00 ' + escapeHtml(unitText) + '/day';
            const compDaysText = compDaysLeft > 0 ? ` for ${compDaysLeft} day${compDaysLeft === 1 ? '' : 's'}` : '';
            const reqVelocityStr = requiredVelocity > 0 ? `${formatNumber(requiredVelocity)} ${escapeHtml(unitText)}/day${compDaysText}` : 'N/A';
            const speedStatusClass = (compVelocity && requiredVelocity) ? (compVelocity >= requiredVelocity ? 'text-success' : 'text-danger') : 'text-info';
            const pastTargetQty = latestPastTarget ? (parseFloat(latestPastTarget.quantity) || 0) : 0;
            const isCompBehindTarget = latestPastTarget && (allTimeActualQty < pastTargetQty);
            const nextTargetColValue = nextNearestTarget 
                ? `${formatNumber(nextNearestTarget.quantity)} ${escapeHtml(unitText)}` 
                : 'N/A';
            const nextTargetColSub = nextNearestTarget 
                ? `<div class="text-muted small" style="font-size: 10px;">by ${nextNearestTarget.dateStr}</div>` 
                : '';

            const html = `
                <div class="card mb-3 ${isCompBehindTarget ? 'border-danger' : 'border-secondary'} bg-dark">
                    <div class="card-header ${isCompBehindTarget ? 'border-danger bg-danger text-white' : 'border-secondary bg-dark'} py-2 d-flex justify-content-between align-items-center">
                        <span class="${isCompBehindTarget ? 'text-white' : 'text-light'} fw-bold"><i class="bi ${isCompBehindTarget ? 'bi-exclamation-triangle-fill' : 'bi-box-seam'} me-1"></i> ${escapeHtml(comp.name)}</span>
                        ${isCompBehindTarget ? '<span class="badge bg-light text-danger fw-bold">Behind Target</span>' : ''}
                    </div>
                </div>

                <div class="card mb-3 border-secondary bg-dark">
                    <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                        <span class="text-light fw-bold small"><i class="bi bi-cash-stack text-success me-1"></i> Accomplished Amount (PHP)</span>
                        <small class="text-muted">Unit Price: PHP ${formatNumber(unitPrice)}</small>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-muted small" style="font-size: 11px;">Total Contract Value</div>
                                <div class="fw-semibold text-light">PHP ${formatNumber(totalContractAmount)}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small" style="font-size: 11px;">Accomplished Amount</div>
                                <div class="fw-semibold text-primary">PHP ${formatNumber(accomplishedAmount)}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small" style="font-size: 11px;">Remaining Value</div>
                                <div class="fw-semibold text-warning">PHP ${formatNumber(remainingAmount)}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 border-secondary bg-dark">
                    <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                        <span class="text-light fw-bold small"><i class="bi bi-speedometer2 text-info me-1"></i> Velocity & Target Rate</span>
                        <small class="text-muted">${daysElapsed > 0 ? daysElapsed + ' day(s) active' : ''}</small>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Running Speed</div>
                                <div class="fw-semibold ${speedStatusClass}">${velocityStr}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Est. Completion Date</div>
                                <div class="fw-semibold text-warning">${escapeHtml(compEtaDateStr)}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Required Speed</div>
                                <div class="fw-semibold text-light">${reqVelocityStr}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small" style="font-size: 11px;">Next Target</div>
                                <div class="fw-semibold text-info">${nextTargetColValue}</div>
                                ${nextTargetColSub}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-dark border-secondary text-light mb-3">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="text-muted"><i class="bi bi-check-circle text-primary me-1"></i> Actual Accomplished</span>
                            <div class="text-end">
                                <span class="fw-bold fs-5 text-primary">${formatNum(percentActual)}</span>
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 12px; background-color: var(--bg-darker);">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: ${percentActual}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>${formatNumber(actualQty)} ${escapeHtml(unitText)} (PHP ${formatNumber(accomplishedAmount)})</span>
                            <span>of ${formatNumber(totalScope)} ${escapeHtml(unitText)} (PHP ${formatNumber(totalContractAmount)})</span>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 border border-secondary rounded bg-dark text-center">
                            <div class="text-muted small">Remaining Scope</div>
                            <div class="fw-bold text-light">${formatNumber(remainingQty)} ${escapeHtml(unitText)}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border border-secondary rounded bg-dark text-center">
                            <div class="text-muted small">Schedule Variance</div>
                            <div class="fw-bold ${varianceClass}">${varianceText}</div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-secondary px-4" onclick="window.util.drawerModal.close()">Close</button>
                </div>
            `;

            const contentEl = document.createElement('div');
            contentEl.innerHTML = html;

            window.util.drawerModal.content(`Progress: ${comp.name}`, contentEl).open();
        };

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

        /* ================= Bulk Selection & Actions ================= */
        const dragBox = document.getElementById('dragSelectionBox');
        const bulkToolbar = document.getElementById('bulkActionsToolbar');
        const bulkCountEl = document.getElementById('bulkSelectedCount');
        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;

        function updateBulkToolbar() {
            const selectedPills = document.querySelectorAll('.gantt-pill.selected[data-id]');
            if (selectedPills.length > 0) {
                bulkCountEl.innerText = `${selectedPills.length} item${selectedPills.length === 1 ? '' : 's'} selected`;
                bulkToolbar.style.display = 'block';
            } else {
                bulkToolbar.style.display = 'none';
            }
        }

        window.handlePillClick = function(event, accDataStr, pillEl) {
            if (event && (event.ctrlKey || event.metaKey)) {
                event.stopPropagation();
                event.preventDefault();
                pillEl.classList.toggle('selected');
                updateBulkToolbar();
                return;
            }
            viewAccomplishment(accDataStr);
        };

        window.clearSelectedPills = function() {
            document.querySelectorAll('.gantt-pill.selected').forEach(pill => pill.classList.remove('selected'));
            updateBulkToolbar();
        };

        window.deleteSelectedAccomplishments = async function() {
            const selectedPills = Array.from(document.querySelectorAll('.gantt-pill.selected[data-id]'));
            const ids = Array.from(new Set(selectedPills.map(p => p.getAttribute('data-id')))).filter(Boolean);

            if (ids.length === 0) return;

            const confirmed = await window.util.confirm(`Are you sure you want to delete ${ids.length} selected accomplishment record${ids.length === 1 ? '' : 's'}?`);
            if (confirmed) {
                fetch('/api/accomplishment/delete-multiple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 1) {
                        clearSelectedPills();
                        loadProjectData();
                    } else {
                        alert(data.message || 'Error deleting accomplishments');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('A network error occurred.');
                });
            }
        };

        const ganttScrollEl = document.getElementById('ganttScroll');

        ganttScrollEl.addEventListener('mousedown', (e) => {
            // Only trigger drag selection on primary click
            if (e.button !== 0) return;
            // Ignore if clicked directly on an interactive element (except the table background/cells)
            if (e.target.closest('.delete-pill-btn') || e.target.closest('#bulkActionsToolbar') || e.target.closest('.gantt-context-menu') || e.target.closest('.col-resizer')) {
                return;
            }

            // If clicking directly on a pill with Ctrl held, let pill click handler toggle it
            if (e.target.closest('.gantt-pill') && (e.ctrlKey || e.metaKey)) {
                return;
            }

            // If shift or ctrl is not held and not clicking a pill, clear current selection
            if (!e.ctrlKey && !e.metaKey && !e.shiftKey && !e.target.closest('.gantt-pill')) {
                clearSelectedPills();
            }

            // If clicking a pill without Ctrl, don't start dragging
            if (e.target.closest('.gantt-pill')) {
                return;
            }

            let dragInitiated = true;
            isDragging = false;
            dragStartX = e.clientX;
            dragStartY = e.clientY;

            function onMouseMove(moveEvent) {
                if (!dragInitiated) return;

                const currentX = moveEvent.clientX;
                const currentY = moveEvent.clientY;

                if (!isDragging) {
                    if (Math.abs(currentX - dragStartX) > 5 || Math.abs(currentY - dragStartY) > 5) {
                        isDragging = true;
                        dragBox.style.display = 'block';
                    } else {
                        return;
                    }
                }

                const left = Math.min(dragStartX, currentX);
                const top = Math.min(dragStartY, currentY);
                const width = Math.abs(currentX - dragStartX);
                const height = Math.abs(currentY - dragStartY);

                dragBox.style.left = `${left}px`;
                dragBox.style.top = `${top}px`;
                dragBox.style.width = `${width}px`;
                dragBox.style.height = `${height}px`;

                // Calculate bounding box collisions
                const boxRect = {
                    left: left,
                    top: top,
                    right: left + width,
                    bottom: top + height
                };

                const pills = document.querySelectorAll('.gantt-pill[data-id]');
                pills.forEach(pill => {
                    const rect = pill.getBoundingClientRect();
                    const isIntersecting = !(rect.right < boxRect.left || 
                                             rect.left > boxRect.right || 
                                             rect.bottom < boxRect.top || 
                                             rect.top > boxRect.bottom);

                    if (isIntersecting) {
                        pill.classList.add('selected');
                    } else if (!moveEvent.ctrlKey && !moveEvent.metaKey) {
                        // If Ctrl isn't pressed, remove selection on un-intersection
                        pill.classList.remove('selected');
                    }
                });

                updateBulkToolbar();
            }

            function onMouseUp() {
                dragInitiated = false;
                if (isDragging) {
                    isDragging = false;
                    dragBox.style.display = 'none';
                    updateBulkToolbar();
                }
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        // Keyboard Shortcuts: Ctrl+A to select all, Delete to remove selected
        window.addEventListener('keydown', (e) => {
            const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
            if (isInput) return;

            // Ctrl + A / Cmd + A: Select All
            if ((e.ctrlKey || e.metaKey) && (e.key === 'a' || e.key === 'A')) {
                e.preventDefault();
                const pills = document.querySelectorAll('.gantt-pill[data-id]');
                pills.forEach(pill => pill.classList.add('selected'));
                updateBulkToolbar();
            }

            // Delete or Backspace: Delete Selected
            if (e.key === 'Delete' || e.key === 'Backspace') {
                const selectedPills = document.querySelectorAll('.gantt-pill.selected[data-id]');
                if (selectedPills.length > 0) {
                    e.preventDefault();
                    deleteSelectedAccomplishments();
                }
            }

            // Escape: Clear Selection
            if (e.key === 'Escape') {
                clearSelectedPills();
            }
        });

        /* ================= Column Resizing ================= */
        // Restore saved column widths from localStorage
        ['wbs', 'scope', 'acc'].forEach(col => {
            const saved = localStorage.getItem(`mqms_studio_${col}_width`);
            if (saved) {
                document.documentElement.style.setProperty(`--col-${col}-width`, saved);
            }
        });

        ganttHead.addEventListener('mousedown', (e) => {
            const resizer = e.target.closest('.col-resizer');
            if (!resizer) return;

            e.preventDefault();
            e.stopPropagation();

            const colKey = resizer.dataset.col;
            const varName = `--col-${colKey}-width`;
            const currentComputed = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
            const startWidth = parseFloat(currentComputed) || (colKey === 'wbs' ? 280 : (colKey === 'scope' ? 90 : 120));
            const startX = e.clientX;

            resizer.classList.add('is-resizing');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';

            const minWidth = colKey === 'wbs' ? 40 : (colKey === 'scope' ? 30 : 40);

            function onMouseMove(moveEvent) {
                const deltaX = moveEvent.clientX - startX;
                const newWidth = Math.max(minWidth, Math.round(startWidth + deltaX));
                document.documentElement.style.setProperty(varName, `${newWidth}px`);
            }

            function onMouseUp() {
                resizer.classList.remove('is-resizing');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                const finalComputed = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
                localStorage.setItem(`mqms_studio_${colKey}_width`, finalComputed);
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        ganttHead.addEventListener('dblclick', (e) => {
            const resizer = e.target.closest('.col-resizer');
            if (!resizer) return;
            e.preventDefault();
            e.stopPropagation();
            const colKey = resizer.dataset.col;
            const defaultWidth = colKey === 'wbs' ? '280px' : (colKey === 'scope' ? '90px' : '120px');
            document.documentElement.style.setProperty(`--col-${colKey}-width`, defaultWidth);
            localStorage.removeItem(`mqms_studio_${colKey}_width`);
        });

        // Initial Load
        loadProjectData();
    </script>
</body>
</html>
