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
                        List
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>

    <hr>

    <div class="folder-form-container">
        <div class="folder-form-tab">
            MCP Tool Descriptions
        </div>
        <div class="folder-form-body">
            <p class="text-muted mb-2">
                Configure the descriptions for Model Context Protocol (MCP) tool endpoints. AI and LLM agents (such as Gemini CLI, Claude Desktop, and Hermes) use these descriptions during tool discovery to understand when and how to invoke each tool.
            </p>
            <div class="alert alert-info py-2 px-3 mb-3">
                <small><strong>Tip:</strong> Custom descriptions take effect immediately upon tool discovery without needing a server restart.</small>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search tool by name or description..."/>
                </div>
                <div class="col-md-6 text-end">
                    <button id="refreshBtn" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                </div>
            </div>
        </div>
    </div>
    
    <hr>

    <div class="container" id="list">
        <div class="text-center p-4 text-muted">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
            Loading MCP tools...
        </div>
    </div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    const list        = $q('#list').first();
    const searchInput = $q('#searchInput').first();
    const refreshBtn  = $q('#refreshBtn').first();
    
    const t = new Template();
    let allTools = [];

    function renderRows(data){
        $el.clear(list);

        if(!data || data.length === 0){
            let emptyMsg = t.div({class:'text-center p-4 text-muted'}, 'No matching MCP tools found.');
            $el.append(emptyMsg).to(list);
            return;
        }
        
        data.map(item => {
            let row = t.div({class:'item-container fade-in mb-3 border p-3 rounded'}, () => {
                t.div({class:'item-header d-flex justify-content-between align-items-center mb-2'}, () => {
                    t.div({class:'d-flex align-items-center gap-2'}, () => {
                        t.span({class:'badge bg-dark font-monospace fs-6 text-warning'}, item.tool_name);
                        if(item.is_customized){
                            t.span({class:'badge bg-success'}, 'Customized');
                        } else {
                            t.span({class:'badge bg-secondary'}, 'Default');
                        }
                    });
                    
                    t.div({class:'d-flex gap-2'}, () => {
                        if(item.is_customized){
                            t.button({class:'btn btn-sm btn-outline-danger'}, 'Reset').onclick = (e) => {
                                e.stopPropagation();
                                if(confirm(`Are you sure you want to reset '${item.tool_name}' description to its hardcoded default?`)){
                                    resetTool(item.tool_name);
                                }
                            };
                        }
                        t.a({
                            href: `/mcp_tool_configs/${item.tool_name}/edit`,
                            class: 'btn btn-sm btn-primary',
                            'hx-select': '#content',
                            'hx-target': '#main'
                        }, 'Edit Description');
                    });
                });

                t.div({class:'item-body'}, () => {
                    t.div({class:'mb-2'}, () => {
                        t.span({class:'text-muted small d-block mb-1'}, 'Active Description:');
                        t.p({class:'mb-1 font-monospace small text-body', style: 'white-space: pre-wrap;'}, item.current_description);
                    });

                    if(item.is_customized){
                        t.div({class:'mt-2 pt-2 border-top text-muted small d-flex justify-content-between'}, () => {
                            t.span({}, `Default: ${item.default_description.substring(0, 100)}...`);
                            t.span({}, `Updated by: ${item.updated_by}`);
                        });
                    }
                });
            });

            $el.append(row).to(list);
        });
    }

    function filterTools(){
        const query = searchInput.value.toLowerCase().trim();
        if(!query){
            renderRows(allTools);
            return;
        }

        const filtered = allTools.filter(item => {
            return item.tool_name.toLowerCase().includes(query) ||
                   item.current_description.toLowerCase().includes(query) ||
                   item.default_description.toLowerCase().includes(query);
        });
        renderRows(filtered);
    }

    function loadList(){
        window.util.fetch('/api/mcp_tool_configs/list', (res) => {
            if(res.status == 1){
                allTools = res.data;
                filterTools();
            } else {
                alert('Failed to load tool configurations');
            }
        });
    }

    function resetTool(toolName){
        window.util.fetch('/api/mcp_tool_configs/reset', {
            method: 'POST',
            body: { tool_name: toolName }
        }, (res) => {
            if(res.status == 1){
                loadList();
            } else {
                alert(res.message || 'Failed to reset description');
            }
        });
    }

    searchInput.oninput = filterTools;
    refreshBtn.onclick = loadList;

    loadList();
</script>
</div>
@endsection
