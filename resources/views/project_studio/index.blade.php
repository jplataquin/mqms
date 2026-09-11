<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MQMS Project Studio - {{ $project->name }}</title>

    <!-- Bootstrap 5 CSS via CDN to match dark theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- jsTree CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />

    <style>
        :root {
            --vscode-bg-darker: #1e1e1e;
            --vscode-bg-dark: #252526;
            --vscode-bg-light: #2d2d2d;
            --vscode-border: #3c3c3c;
            --vscode-text: #cccccc;
            --vscode-text-active: #ffffff;
            --vscode-accent: #007acc;
        }

        body {
            background-color: var(--vscode-bg-darker);
            color: var(--vscode-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .studio-topbar {
            height: 40px;
            background-color: var(--vscode-bg-dark);
            border-bottom: 1px solid var(--vscode-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            font-size: 13px;
        }

        .studio-brand {
            font-weight: 600;
            color: var(--vscode-text-active);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .studio-project-name {
            opacity: 0.8;
            font-size: 12px;
        }

        /* Main Layout */
        .studio-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Left Sidebar (VS Code Explorer style) */
        .studio-sidebar {
            width: 320px;
            background-color: var(--vscode-bg-dark);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Sidebar resizer split handle */
        .sidebar-resizer {
            width: 4px;
            background-color: var(--vscode-border);
            cursor: col-resize;
            transition: background-color 0.15s;
            position: relative;
            z-index: 100;
            margin-left: -2px;
            margin-right: -2px;
            flex-shrink: 0;
        }

        .sidebar-resizer:hover, .sidebar-resizer.resizing {
            background-color: var(--vscode-accent);
        }

        /* Prevents iframe from capturing mouse events during drag */
        .resizing-active iframe {
            pointer-events: none !important;
        }

        .sidebar-header {
            padding: 10px 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--vscode-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-search {
            padding: 8px 12px;
            border-bottom: 1px solid var(--vscode-border);
        }

        .sidebar-search input {
            background-color: var(--vscode-bg-light);
            border: 1px solid var(--vscode-border);
            color: var(--vscode-text-active);
            font-size: 12px;
            border-radius: 3px;
            padding: 4px 8px;
            width: 100%;
        }

        .sidebar-search input:focus {
            outline: 1px solid var(--vscode-accent);
            border-color: var(--vscode-accent);
            box-shadow: none;
        }

        .tree-container {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        /* Customize jsTree for VS Code Dark Look */
        .jstree-default {
            color: var(--vscode-text);
        }
        .jstree-default .jstree-hovered {
            background: rgba(255, 255, 255, 0.05) !important;
            border: none !important;
            box-shadow: none !important;
            color: var(--vscode-text-active);
        }
        .jstree-default .jstree-clicked {
            background: rgba(0, 122, 204, 0.2) !important;
            border: none !important;
            box-shadow: none !important;
            color: var(--vscode-text-active);
        }
        .jstree-default .jstree-anchor {
            color: var(--vscode-text) !important;
            font-size: 13px;
        }
        .jstree-default .jstree-icon {
            filter: brightness(0.8);
        }

        /* Main Workspace Area */
        .studio-workspace {
            flex: 1;
            background-color: var(--vscode-bg-darker);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .workspace-tabs {
            height: 35px;
            background-color: var(--vscode-bg-dark);
            border-bottom: 1px solid var(--vscode-border);
            display: flex;
            align-items: center;
            padding: 0 10px;
        }

        .workspace-tab {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 15px;
            background-color: var(--vscode-bg-darker);
            border-right: 1px solid var(--vscode-border);
            border-top: 2px solid var(--vscode-accent);
            font-size: 12px;
            color: var(--vscode-text-active);
        }

        .workspace-tab i {
            margin-right: 6px;
        }

        .iframe-container {
            flex: 1;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: var(--vscode-bg-darker);
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background-color: transparent;
        }

        /* Welcome Screen (VS Code Style) */
        .welcome-screen {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
            padding: 20px;
            background-color: var(--vscode-bg-darker);
        }

        .welcome-logo {
            font-size: 64px;
            color: var(--vscode-accent);
            margin-bottom: 20px;
        }

        .welcome-title {
            font-size: 24px;
            font-weight: 300;
            color: var(--vscode-text-active);
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            font-size: 14px;
            opacity: 0.6;
            max-width: 450px;
            margin-bottom: 30px;
        }

        .welcome-shortcuts {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13px;
            text-align: left;
            background-color: var(--vscode-bg-dark);
            padding: 20px;
            border-radius: 6px;
            border: 1px solid var(--vscode-border);
            width: 320px;
        }

        .shortcut-item {
            display: flex;
            justify-content: space-between;
        }

        .shortcut-key {
            background-color: var(--vscode-bg-light);
            border: 1px solid var(--vscode-border);
            border-radius: 3px;
            padding: 2px 6px;
            font-size: 11px;
            font-family: monospace;
        }

        /* Context Menu Styles to Override default look if needed */
        .vakata-context {
            background-color: var(--vscode-bg-light) !important;
            border: 1px solid var(--vscode-border) !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5) !important;
            border-radius: 4px !important;
            padding: 5px 0 !important;
            min-width: 180px !important;
            width: auto !important;
            z-index: 1000 !important;
        }
        .vakata-context li > a {
            color: var(--vscode-text) !important;
            text-shadow: none !important;
            font-size: 12px !important;
            padding: 6px 15px !important;
            white-space: nowrap !important;
            border: none !important;
            box-shadow: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .vakata-context li > a i {
            font-size: 13px;
        }
        .vakata-context li > a:hover,
        .vakata-context .vakata-context-hover > a {
            background-color: var(--vscode-accent) !important;
            color: var(--vscode-text-active) !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        .vakata-context .vakata-context-separator a {
            border-top: 1px solid var(--vscode-border) !important;
            background: transparent !important;
        }
    </style>
</head>
<body>

    <!-- Top Navigation / Menu Bar -->
    <div class="studio-topbar">
        <div class="studio-brand">
            <i class="bi bi-braces-asterisk text-primary"></i>
            <span>Project Studio</span>
            <span class="text-muted">|</span>
            <span class="studio-project-name text-info">{{ $project->name }}</span>
        </div>
        <div class="studio-actions">
            <button class="btn btn-sm btn-outline-secondary py-1 px-3" onclick="window.close();">
                <i class="bi bi-box-arrow-left me-1"></i> Exit Studio
            </button>
        </div>
    </div>

    <!-- Main Workspace Layout -->
    <div class="studio-container">
        
        <!-- Sidebar Workspace Explorer -->
        <div class="studio-sidebar">
            <div class="sidebar-header">
                <span>Explorer : Project Structure</span>
                <i class="bi bi-folder2-open text-warning"></i>
            </div>
            
            <div class="sidebar-search">
                <input type="text" id="tree-search" placeholder="Search structure...">
            </div>

            <div class="tree-container">
                <div id="jstree-workspace"></div>
            </div>
        </div>

        <!-- Sidebar Resizer Split Handle -->
        <div class="sidebar-resizer" id="sidebar-resizer"></div>

        <!-- Main Code/Form Editor Area -->
        <div class="studio-workspace">
            
            <!-- Tab Headers -->
            <div class="workspace-tabs" id="editor-tabs" style="display: none;">
                <div class="workspace-tab">
                    <i class="bi bi-file-earmark-ruled text-success" id="active-tab-icon"></i>
                    <span id="active-tab-title">Project Node</span>
                </div>
            </div>

            <!-- Welcome Screen (Default) -->
            <div id="welcome-view" class="welcome-screen">
                <div class="welcome-logo">
                    <i class="bi bi-layers-half"></i>
                </div>
                <div class="welcome-title">Welcome to Project Studio</div>
                <div class="welcome-subtitle">
                    Select a node from the explorer to view and edit its form in real time, or right-click to manage its relationships.
                </div>
                <div class="welcome-shortcuts">
                    <div class="shortcut-item">
                        <span>Open Project</span>
                        <span class="shortcut-key">Click</span>
                    </div>
                    <div class="shortcut-item">
                        <span>Context Menu</span>
                        <span class="shortcut-key">Right Click</span>
                    </div>
                    <div class="shortcut-item">
                        <span>Refresh Tree</span>
                        <span class="shortcut-key">F5</span>
                    </div>
                </div>
            </div>

            <!-- Editor View containing the form IFrame -->
            <div id="editor-view" class="iframe-container" style="display: none;">
                <iframe id="studio-iframe" src="about:blank"></iframe>
            </div>

        </div>

    </div>

    <!-- Load jQuery & jsTree via CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <!-- SweetAlert2 for beautiful input prompts during creation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const projectId = '{{ $project->id }}';
            const iframe = $('#studio-iframe');
            const welcomeView = $('#welcome-view');
            const editorView = $('#editor-view');
            const editorTabs = $('#editor-tabs');
            const activeTabTitle = $('#active-tab-title');
            const activeTabIcon = $('#active-tab-icon');

            // Setup CSRF header for all ajax calls
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Set up search field binding
            let searchTimeout = null;
            $('#tree-search').keyup(function() {
                if (searchTimeout) { clearTimeout(searchTimeout); }
                searchTimeout = setTimeout(function() {
                    const v = $('#tree-search').val();
                    $('#jstree-workspace').jstree(true).search(v);
                }, 250);
            });

            // Load node types mappings to beautiful icons
            const iconMapping = {
                'project': 'bi bi-building text-info',
                'section': 'bi bi-folder-fill text-warning',
                'contract_item': 'bi bi-file-earmark-spreadsheet text-success',
                'component': 'bi bi-gear-wide-connected text-primary',
                'component_item': 'bi bi-puzzle text-danger'
            };

            // Initialize jsTree
            $('#jstree-workspace').jstree({
                'core': {
                    'data': {
                        'url': function(node) {
                            return node.id === '#' 
                                ? '/api/project/studio/node'
                                : '/api/project/studio/node/children';
                        },
                        'data': function(node) {
                            return node.id === '#' 
                                ? { 'project_id': projectId }
                                : { 'type': node.original.type, 'id': node.original.real_id };
                        }
                    },
                    'check_callback': true,
                    'themes': {
                        'name': 'default',
                        'responsive': true,
                        'dots': true,
                        'icons': true
                    }
                },
                'plugins': ['contextmenu', 'search', 'types', 'state'],
                'types': {
                    'project': { 'icon': iconMapping.project },
                    'section': { 'icon': iconMapping.section },
                    'contract_item': { 'icon': iconMapping.contract_item },
                    'component': { 'icon': iconMapping.component },
                    'component_item': { 'icon': iconMapping.component_item }
                },
                'contextmenu': {
                    'items': function(node) {
                        const items = {};
                        const type = node.original.type;
                        const realId = node.original.real_id;

                        if (type === 'project') {
                            items['add_section'] = {
                                'label': 'Add Section',
                                'icon': 'bi bi-folder-plus text-warning',
                                'action': function() {
                                    createNodePrompt('section', '/api/section/create', { project_id: realId }, node);
                                }
                            };
                            items['edit_project'] = {
                                'label': 'Edit Project details',
                                'icon': 'bi bi-pencil-square text-info',
                                'action': function() {
                                    loadForm('/project/' + realId + '?studio=1', 'Project: ' + node.text, iconMapping.project);
                                }
                            };
                        } else if (type === 'section') {
                            items['add_contract_item'] = {
                                'label': 'Add Contract Item',
                                'icon': 'bi bi-file-earmark-plus text-success',
                                'action': function() {
                                    createNodePrompt('contract_item', '/api/contract_item/create', { section_id: realId }, node);
                                }
                            };
                            items['edit_section'] = {
                                'label': 'Edit Section details',
                                'icon': 'bi bi-pencil-square text-info',
                                'action': function() {
                                    loadForm('/project/section/' + realId + '?studio=1', 'Section: ' + node.text, iconMapping.section);
                                }
                            };
                            items['delete_section'] = {
                                'label': 'Delete Section',
                                'icon': 'bi bi-trash text-danger',
                                'action': function() {
                                    deleteNodeConfirm('/api/section/delete', realId, node);
                                }
                            };
                        } else if (type === 'contract_item') {
                            items['add_component'] = {
                                'label': 'Add Component',
                                'icon': 'bi bi-plus-circle text-primary',
                                'action': function() {
                                    createComponentPrompt(realId, node);
                                }
                            };
                            items['edit_contract_item'] = {
                                'label': 'Edit Contract Item details',
                                'icon': 'bi bi-pencil-square text-info',
                                'action': function() {
                                    loadForm('/project/section/contract_item/' + realId + '?studio=1', 'Contract Item: ' + node.text, iconMapping.contract_item);
                                }
                            };
                            items['delete_contract_item'] = {
                                'label': 'Delete Contract Item',
                                'icon': 'bi bi-trash text-danger',
                                'action': function() {
                                    deleteNodeConfirm('/api/contract_item/delete', realId, node);
                                }
                            };
                        } else if (type === 'component') {
                            items['add_component_item'] = {
                                'label': 'Add Material/Item',
                                'icon': 'bi bi-plus-square text-danger',
                                'action': function() {
                                    createComponentItemPrompt(realId, node);
                                }
                            };
                            items['edit_component'] = {
                                'label': 'Edit Component details',
                                'icon': 'bi bi-pencil-square text-info',
                                'action': function() {
                                    loadForm('/project/section/contract_item/component/' + realId + '?studio=1', 'Component: ' + node.text, iconMapping.component);
                                }
                            };
                            items['delete_component'] = {
                                'label': 'Delete Component',
                                'icon': 'bi bi-trash text-danger',
                                'action': function() {
                                    deleteNodeConfirm('/api/component/delete', realId, node);
                                }
                            };
                        } else if (type === 'component_item') {
                            items['edit_component_item'] = {
                                'label': 'Edit Component Item details',
                                'icon': 'bi bi-pencil-square text-info',
                                'action': function() {
                                    // There is no dedicated view for a component_item, editing is typically done on the component display itself
                                    const parentNode = $('#jstree-workspace').jstree(true).get_node(node.parent);
                                    loadForm('/project/section/contract_item/component/' + parentNode.original.real_id + '?studio=1', 'Component: ' + parentNode.text, iconMapping.component);
                                }
                            };
                            items['delete_component_item'] = {
                                'label': 'Delete Component Item',
                                'icon': 'bi bi-trash text-danger',
                                'action': function() {
                                    deleteNodeConfirm('/api/component_item/delete', realId, node);
                                }
                            };
                        }

                        return items;
                    }
                }
            });

            // Select node event handler to load target form into the frame
            $('#jstree-workspace').on('select_node.jstree', function(e, data) {
                const node = data.node;
                const type = node.original.type;
                const realId = node.original.real_id;

                let targetUrl = '';
                let title = '';
                let iconClass = '';

                if (type === 'project') {
                    targetUrl = '/project/' + realId + '?studio=1';
                    title = 'Project: ' + node.text;
                    iconClass = iconMapping.project;
                } else if (type === 'section') {
                    targetUrl = '/project/section/' + realId + '?studio=1';
                    title = 'Section: ' + node.text;
                    iconClass = iconMapping.section;
                } else if (type === 'contract_item') {
                    targetUrl = '/project/section/contract_item/' + realId + '?studio=1';
                    title = 'Contract Item: ' + node.text;
                    iconClass = iconMapping.contract_item;
                } else if (type === 'component') {
                    targetUrl = '/project/section/contract_item/component/' + realId + '?studio=1';
                    title = 'Component: ' + node.text;
                    iconClass = iconMapping.component;
                } else if (type === 'component_item') {
                    // Open parent component display
                    const parentNode = $('#jstree-workspace').jstree(true).get_node(node.parent);
                    targetUrl = '/project/section/contract_item/component/' + parentNode.original.real_id + '?studio=1';
                    title = 'Component: ' + parentNode.text;
                    iconClass = iconMapping.component;
                }

                if (targetUrl) {
                    const currentSrc = iframe.attr('src') || '';
                    const relativeSrc = currentSrc.replace(window.location.origin, '');
                    if (relativeSrc !== targetUrl) {
                        loadForm(targetUrl, title, iconClass);
                    }
                }
            });

            // Helper function to load forms into the workspace iframe
            function loadForm(url, title, iconClass) {
                welcomeView.hide();
                editorTabs.show();
                editorView.show();
                
                activeTabTitle.text(title);
                activeTabIcon.attr('class', iconClass);
                
                iframe.attr('src', url);
            }

            // Real-time communication: Refresh tree when sub-frame updates/reloads/saves
            window.addEventListener('message', function(event) {
                if (event.data === 'reload-tree') {
                    refreshTree();
                }
            });

            function refreshTree(nodeToRefresh = null) {
                const tree = $('#jstree-workspace').jstree(true);
                if (nodeToRefresh) {
                    tree.load_node(nodeToRefresh);
                } else {
                    tree.refresh();
                }
            }

            // Beautified prompts for node creation
            function createNodePrompt(type, apiUrl, extraData, parentNode) {
                Swal.fire({
                    title: 'Create ' + type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' '),
                    input: 'text',
                    inputLabel: 'Name',
                    inputPlaceholder: 'Enter name or title...',
                    showCancelButton: true,
                    confirmButtonColor: '#007acc',
                    cancelButtonColor: '#3c3c3c',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to enter a name!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const payload = Object.assign({ name: result.value, status: 'ACTV' }, extraData);
                        
                        $.post(apiUrl, payload, function(res) {
                            if (res.status > 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Created!',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                                refreshTree(parentNode);
                            } else {
                                Swal.fire('Error', res.message || 'Could not create record.', 'error');
                            }
                        }).fail(function() {
                            Swal.fire('Error', 'API request failed.', 'error');
                        });
                    }
                });
            }

            // Specialized Component Creation Prompt (it requires a unit or other inputs sometimes)
            function createComponentPrompt(contractItemId, parentNode) {
                Swal.fire({
                    title: 'Create Component',
                    html: `
                        <input id="swal-comp-name" class="swal2-input" placeholder="Component Name">
                        <select id="swal-comp-unit" class="swal2-select" style="width: 80%;">
                            <option value="">Select Unit (Optional)</option>
                            @foreach($unit_options as $option)
                                <option value="{{ $option->id }}">{{ $option->text }}</option>
                            @endforeach
                        </select>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#007acc',
                    cancelButtonColor: '#3c3c3c',
                    preConfirm: () => {
                        const name = document.getElementById('swal-comp-name').value;
                        const unitId = document.getElementById('swal-comp-unit').value;
                        if (!name) {
                            Swal.showValidationMessage('Name is required');
                            return false;
                        }
                        return { name: name, unit_id: unitId };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const payload = {
                            contract_item_id: contractItemId,
                            name: result.value.name,
                            unit_id: result.value.unit_id,
                            status: 'ACTV'
                        };
                        
                        $.post('/api/component/create', payload, function(res) {
                            if (res.status > 0) {
                                Swal.fire('Created!', '', 'success');
                                refreshTree(parentNode);
                            } else {
                                Swal.fire('Error', res.message || 'Could not create component.', 'error');
                            }
                        });
                    }
                });
            }

            // Specialized Component Item Creation Prompt
            function createComponentItemPrompt(componentId, parentNode) {
                // To create a component item, the API is /api/component_item/create, which might require a material_item_id, quantity, etc.
                // We will load a nice prompt that asks for material item search or selection.
                // For a highly efficient experience, we can offer simple name input and let the iframe/system handle details, or let them input details directly.
                // Looking at standard MQMS behavior, adding a component item takes component_id and details.
                // Let's ask for details:
                Swal.fire({
                    title: 'Add Component Item',
                    html: `
                        <input id="swal-ci-name" class="swal2-input" placeholder="Component Item/Work Item Name">
                        <input id="swal-ci-qty" class="swal2-input" type="number" step="any" placeholder="Quantity">
                        <p class="text-muted text-start px-4 small">Details can be configured later in the main editor.</p>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#007acc',
                    cancelButtonColor: '#3c3c3c',
                    preConfirm: () => {
                        const name = document.getElementById('swal-ci-name').value;
                        const qty = document.getElementById('swal-ci-qty').value;
                        if (!name) {
                            Swal.showValidationMessage('Name is required');
                            return false;
                        }
                        return { name: name, quantity: qty };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const payload = {
                            component_id: componentId,
                            name: result.value.name,
                            quantity: result.value.quantity || 1,
                            status: 'ACTV'
                        };
                        
                        $.post('/api/component_item/create', payload, function(res) {
                            if (res.status > 0) {
                                Swal.fire('Added!', '', 'success');
                                refreshTree(parentNode);
                            } else {
                                Swal.fire('Error', res.message || 'Could not add component item.', 'error');
                            }
                        });
                    }
                });
            }

            // Generic Node Deletion Confirmation
            function deleteNodeConfirm(apiUrl, realId, node) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `This will delete "${node.text}" and cannot be easily undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3c3c3c',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(apiUrl, { id: realId }, function(res) {
                            if (res.status > 0) {
                                Swal.fire('Deleted!', '', 'success');
                                
                                // Remove node from tree
                                const tree = $('#jstree-workspace').jstree(true);
                                const parentId = node.parent;
                                tree.delete_node(node);
                                
                                // If the deleted node is currently open in the iframe, reset view
                                if (iframe.attr('src').indexOf(realId) !== -1) {
                                    iframe.attr('src', 'about:blank');
                                    welcomeView.show();
                                    editorTabs.hide();
                                    editorView.hide();
                                }
                            } else {
                                Swal.fire('Error', res.message || 'Could not delete item.', 'error');
                            }
                        });
                    }
                });
            }

            // Sidebar Drag Resize Logic (VS Code-style panel resizer)
            const resizerSplit = document.getElementById('sidebar-resizer');
            const explorerSidebar = document.querySelector('.studio-sidebar');
            const studioContainer = document.querySelector('.studio-container');

            let isResizingPanel = false;

            resizerSplit.addEventListener('mousedown', function(e) {
                isResizingPanel = true;
                resizerSplit.classList.add('resizing');
                studioContainer.classList.add('resizing-active');
                document.body.style.cursor = 'col-resize';
                e.preventDefault(); // Prevent text highlights during resize dragging
            });

            document.addEventListener('mousemove', function(e) {
                if (!isResizingPanel) return;

                const containerRect = studioContainer.getBoundingClientRect();
                let width = e.clientX - containerRect.left;

                // Restrict resizing width (clamped min: 220px, max: 600px)
                if (width < 220) width = 220;
                if (width > 600) width = 600;

                explorerSidebar.style.width = width + 'px';
            });

            document.addEventListener('mouseup', function(e) {
                if (isResizingPanel) {
                    isResizingPanel = false;
                    resizerSplit.classList.remove('resizing');
                    studioContainer.classList.remove('resizing-active');
                    document.body.style.cursor = 'default';
                }
            });
        });
    </script>
</body>
</html>
