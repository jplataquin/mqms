@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="#">
                    <span>AI / MCP</span>
                </a>
            </li>
            <li>
                <a href="/mcp_tool_configs">
                    <span>Tool Configs</span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Edit: {{ $toolName }}
                    </span>
                    <i class="ms-2 bi bi-pencil-square"></i>
                </a>
            </li>
        </ul>
    </div>

    <hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            Configure Tool Description: <span class="font-monospace text-warning">{{ $toolName }}</span>
        </div>
        <div class="folder-form-body">
            <p class="text-muted mb-3">
                Edit the description exposed to AI agents via the MCP protocol. Providing a specific, clear prompt helps LLMs determine when to select this tool and what parameters or response fields to anticipate.
            </p>

            <div class="card mb-4 border-secondary bg-dark text-light">
                <div class="card-header bg-dark border-secondary py-2 d-flex justify-content-between align-items-center">
                    <span class="small fw-bold text-muted"><i class="bi bi-code-slash me-1"></i> Hardcoded Default Description (Reference)</span>
                    <button type="button" id="copyDefaultBtn" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 11px;">
                        Copy to Editor
                    </button>
                </div>
                <div class="card-body py-2">
                    <p class="mb-0 font-monospace small text-muted" id="defaultText" style="white-space: pre-wrap;">{{ $defaultDescription }}</p>
                </div>
            </div>

            <form id="editForm">
                <input type="hidden" id="toolName" value="{{ $toolName }}"/>

                <div class="form-group mb-3">
                    <label for="customDescription" class="form-label fw-bold">
                        Custom Description
                        @if($customDescription !== null && trim($customDescription) !== '')
                            <span class="badge bg-success ms-2">Currently Active</span>
                        @else
                            <span class="badge bg-secondary ms-2">Currently Using Default</span>
                        @endif
                    </label>
                    <textarea 
                        id="customDescription" 
                        class="form-control font-monospace" 
                        rows="6" 
                        placeholder="Enter custom description for {{ $toolName }}... Leave empty or click 'Reset to Default' to restore original hardcoded description."
                    >{{ $customDescription }}</textarea>
                    <div class="form-text text-muted">
                        Changes are saved directly to the database and will be reflected immediately in all subsequent MCP tool discovery calls.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <div>
                        <a href="/mcp_tool_configs" class="btn btn-outline-secondary" hx-select="#content" hx-target="#main">
                            Cancel
                        </a>
                        @if($customDescription !== null && trim($customDescription) !== '')
                            <button type="button" id="resetBtn" class="btn btn-outline-danger ms-2">
                                Reset to Default
                            </button>
                        @endif
                    </div>
                    <div>
                        <button type="submit" id="saveBtn" class="btn btn-primary px-4">
                            Save Description
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    const toolName          = $q('#toolName').first().value;
    const defaultText       = $q('#defaultText').first().innerText;
    const customDescription = $q('#customDescription').first();
    const copyDefaultBtn    = $q('#copyDefaultBtn').first();
    const saveBtn           = $q('#saveBtn').first();
    const resetBtn          = $q('#resetBtn').first();
    const editForm          = $q('#editForm').first();

    copyDefaultBtn.onclick = () => {
        customDescription.value = defaultText;
        customDescription.focus();
    };

    if (resetBtn) {
        resetBtn.onclick = () => {
            if (confirm(`Are you sure you want to reset the description for '${toolName}' to default?`)) {
                window.util.blockUI();
                window.util.$post('/api/mcp_tool_configs/reset', { tool_name: toolName })
                .then(res => {
                    window.util.unblockUI();
                    window.util.showMsg(res);
                    if (res.status == 1) {
                        window.util.navTo('/mcp_tool_configs');
                    }
                }).catch(err => {
                    window.util.unblockUI();
                    console.error(err);
                });
            }
        };
    }

    editForm.onsubmit = (e) => {
        e.preventDefault();
        window.util.blockUI();

        window.util.$post('/api/mcp_tool_configs/update', {
            tool_name: toolName,
            custom_description: customDescription.value
        })
        .then(res => {
            window.util.unblockUI();
            window.util.showMsg(res);
            if (res.status == 1) {
                window.util.navTo('/mcp_tool_configs');
            }
        }).catch(err => {
            window.util.unblockUI();
            console.error(err);
        });
    };
</script>
</div>
@endsection
