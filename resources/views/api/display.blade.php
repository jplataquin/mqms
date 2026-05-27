@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/api_credentials">
                    <span>API Credentials</span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Display
                    </span>
                    <i class="ms-2 bi bi-eye"></i>		
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            API Credential Details
        </div>
        <div class="form-body">
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>System Name / Description</label>
                        <input type="text" class="form-control" value="{{ $credential->name }}" readonly/>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>API Key</label>
                        <div class="input-group">
                            <input type="text" id="apiKey" class="form-control" value="{{ $credential->api_key }}" readonly/>
                            <button class="btn btn-outline-secondary copy-btn" data-target="apiKey">Copy</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Secret Key</label>
                        <div class="input-group">
                            <input type="password" id="secretKey" class="form-control" value="{{ $credential->secret_key }}" readonly/>
                            <button class="btn btn-outline-secondary" id="toggleSecretBtn">Show</button>
                            <button class="btn btn-outline-secondary copy-btn" data-target="secretKey">Copy</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="text-muted small">
                        Created By: {{ $credential->CreatedByUser()->name }}<br>
                        Created At: {{ $credential->created_at }}
                    </div>
                </div>
                <div class="col-lg-6 text-end">
                    <button class="btn btn-danger" id="deleteBtn">Delete Credential</button>
                    <button class="btn btn-secondary" id="backBtn">Back to List</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const deleteBtn      = $q('#deleteBtn').first();
    const backBtn        = $q('#backBtn').first();
    const toggleSecretBtn = $q('#toggleSecretBtn').first();
    const secretKeyInput = $q('#secretKey').first();

    toggleSecretBtn.onclick = () => {
        if (secretKeyInput.type === "password") {
            secretKeyInput.type = "text";
            toggleSecretBtn.innerText = "Hide";
        } else {
            secretKeyInput.type = "password";
            toggleSecretBtn.innerText = "Show";
        }
    };

    deleteBtn.onclick = () => {
        if(confirm('Are you sure you want to delete this API credential? This action cannot be undone and will immediately revoke access for the 3rd-party system.')){
            window.util.blockUI();
            window.util.$post('/api/api_credentials/delete',{
                id: {{ $credential->id }}
            }).then(reply => {
                window.util.unblockUI();
                window.util.showMsg(reply);
                if(reply.status > 0){
                    window.util.navTo('/api_credentials');
                }
            });
        }
    };

    backBtn.onclick = () => {
        window.util.navTo('/api_credentials');
    };

    $q('.copy-btn').items().map(btn => {
        btn.onclick = () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            input.select();
            document.execCommand('copy');
            btn.innerText = 'Copied!';
            setTimeout(() => { btn.innerText = 'Copy'; }, 2000);
        };
    });
</script>
</div>
@endsection
