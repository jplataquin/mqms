@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/personal_access_tokens">
                    <span>Personal Access Tokens</span>
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
            Personal Access Tokens
        </div>
        <div class="folder-form-body">
            <p class="text-muted">
                Personal Access Tokens can be used to authenticate with the MCP Server, Gemini, Claude Desktop, and other external AI agents using standard Bearer Token authentication.
            </p>

            <div class="row mt-3">
                <div class="col-lg-12 text-end">
                    <button id="createBtn" class="btn btn-warning">Generate New Token</button>
                </div>
            </div>

        </div>
    </div>
    
    <hr>

    <div class="container" id="list"></div>

</div>

<script type="module">
    import {$q,Template,$el} from '/adarna.js';

    const list            = $q('#list').first();
    const createBtn       = $q('#createBtn').first();
    
    const t = new Template();

    function renderRows(data){
        $el.clear(list);

        if(!data || data.length === 0){
            let emptyMsg = t.div({class:'text-center p-4 text-muted'}, 'No personal access tokens found. Click "Generate New Token" to create one.');
            $el.append(emptyMsg).to(list);
            return;
        }
        
        data.map(item=>{

            let row = t.div({class:'item-container fade-in mb-3'},()=>{
                t.div({class:'item-header d-flex justify-content-between align-items-center'},()=>{
                    t.span({class:'fw-bold'}, item.name);
                    t.button({class:'btn btn-danger btn-sm'},'Revoke').onclick = (e)=>{
                        e.stopPropagation();
                        if(confirm('Are you sure you want to revoke this token? Any AI client using it will immediately lose access.')){
                            deleteToken(item.id);
                        }
                    };
                });
                t.div({class:'item-body'},()=>{
                    t.div({}, ()=>{
                        t.span({class:'text-muted'}, 'Created: ');
                        t.span(item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A');
                    });
                    t.div({}, ()=>{
                        t.span({class:'text-muted'}, 'Last Used: ');
                        t.span(item.last_used_at ? new Date(item.last_used_at).toLocaleString() : 'Never');
                    });
                });
            });

            $el.append(row).to(list);
            
        });

    }

    function deleteToken(id){
        window.util.blockUI();
        window.util.$post('/api/personal_access_tokens/delete',{id: id})
        .then(reply=>{
            window.util.unblockUI();
            window.util.showMsg(reply);
            if(reply.status > 0){
                showData();
            }
        });
    }

    function showData(){
        window.util.blockUI();

        window.util.$get('/api/personal_access_tokens/list', {})
        .then(reply=>{
            window.util.unblockUI();
            if(reply.status > 0){
                renderRows(reply.data);
            }else{
                window.util.showMsg(reply);
            }
        });
    }

    createBtn.onclick = ()=>{
        window.util.navTo('/personal_access_tokens/create');
    }

    showData();

</script>
</div>
@endsection
