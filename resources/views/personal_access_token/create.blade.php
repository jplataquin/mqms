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
                <a href="/personal_access_tokens">
                    <span>Tokens</span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Generate
                    </span>
                    <i class="ms-2 bi bi-file-earmark-plus"></i>		
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div id="formSection" class="form-container">
        <div class="form-header">
            Generate Personal Access Token
        </div>
        <div class="form-body">
            <div class="row mt-3 mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Token Name / Client Description</label>
                        <input type="text" id="name" class="form-control" placeholder="e.g. Gemini, Claude Desktop, Cursor"/>
                        <small class="text-muted">Give your token a descriptive name so you remember which AI client is using it.</small>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                    <button class="btn btn-primary" id="createBtn">Generate Token</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="resultSection" class="form-container d-none">
        <div class="form-header bg-success text-white">
            Token Generated Successfully
        </div>
        <div class="form-body">
            <div class="alert alert-warning">
                <strong>Important!</strong> Please copy this token now. For your security, it will <strong>never</strong> be shown again.
            </div>
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Bearer Token</label>
                        <div class="input-group">
                            <input type="text" id="resToken" class="form-control" readonly/>
                            <button class="btn btn-outline-secondary" id="copyBtn">Copy</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>How to use:</strong> In your AI / MCP client, pass this value in the standard <code>Authorization</code> header:
                <br>
                <code>Authorization: Bearer &lt;token&gt;</code>
                <br>
                <div class="mt-2">
                    <strong>MCP Server Endpoint:</strong> <code>{{ url('/api/mcp') }}</code>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                    <button class="btn btn-primary" id="doneBtn">Done</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const createBtn      = $q('#createBtn').first();
    const cancelBtn      = $q('#cancelBtn').first();
    const doneBtn        = $q('#doneBtn').first();
    const copyBtn        = $q('#copyBtn').first();
    const name           = $q('#name').first();
    
    const formSection    = $q('#formSection').first();
    const resultSection  = $q('#resultSection').first();
    const resToken       = $q('#resToken').first();

    createBtn.onclick = () => {
        if (!name.value.trim()) {
            alert('Please provide a token name.');
            return;
        }

        window.util.blockUI();

        window.util.$post('/api/personal_access_tokens/create', {
            name: name.value
        }).then(reply => {
            window.util.unblockUI();

            if (reply.status > 0) {
                resToken.value = reply.data.plain_text;
                formSection.classList.add('d-none');
                resultSection.classList.remove('d-none');
            } else {
                window.util.showMsg(reply);
            }
        });
    };

    copyBtn.onclick = () => {
        resToken.select();
        navigator.clipboard.writeText(resToken.value).then(() => {
            copyBtn.innerText = 'Copied!';
            setTimeout(() => {
                copyBtn.innerText = 'Copy';
            }, 2000);
        });
    };

    cancelBtn.onclick = () => {
        window.util.navTo('/personal_access_tokens');
    };

    doneBtn.onclick = () => {
        window.util.navTo('/personal_access_tokens');
    };
</script>
</div>
@endsection
