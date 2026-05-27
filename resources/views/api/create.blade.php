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
                        Create
                    </span>
                    <i class="ms-2 bi bi-file-earmark-plus"></i>		
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div id="formSection" class="form-container">
        <div class="form-header">
            Create API Credential
        </div>
        <div class="form-body">
            <div class="row mt-3 mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>System Name / Description</label>
                        <input type="text" id="name" class="form-control" placeholder="e.g. Inventory System Sync"/>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                    <button class="btn btn-primary" id="createBtn">Generate Credentials</button>
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="resultSection" class="form-container d-none">
        <div class="form-header bg-success text-white">
            Credentials Generated
        </div>
        <div class="form-body">
            <div class="alert alert-warning">
                <strong>Important!</strong> Please copy these credentials now. You will not be able to see the Secret Key again.
            </div>
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>API Key</label>
                        <div class="input-group">
                            <input type="text" id="resApiKey" class="form-control" readonly/>
                            <button class="btn btn-outline-secondary copy-btn" data-target="resApiKey">Copy</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Secret Key</label>
                        <div class="input-group">
                            <input type="text" id="resSecretKey" class="form-control" readonly/>
                            <button class="btn btn-outline-secondary copy-btn" data-target="resSecretKey">Copy</button>
                        </div>
                    </div>
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

    const createBtn              = $q('#createBtn').first();
    const cancelBtn              = $q('#cancelBtn').first();
    const doneBtn                = $q('#doneBtn').first();
    const name                   = $q('#name').first();
    
    const formSection            = $q('#formSection').first();
    const resultSection          = $q('#resultSection').first();
    const resApiKey              = $q('#resApiKey').first();
    const resSecretKey           = $q('#resSecretKey').first();

    createBtn.onclick = (e) => {

        if(!name.value){
            alert('Please enter a name for the system.');
            return;
        }

        window.util.blockUI();

        window.util.$post('/api/api_credentials/create',{
            name: name.value
        }).then(reply=>{
            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            resApiKey.value = reply.data.api_key;
            resSecretKey.value = reply.data.secret_key;

            formSection.classList.add('d-none');
            resultSection.classList.remove('d-none');
        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/api_credentials');
    }

    doneBtn.onclick = (e) => {
        window.util.navTo('/api_credentials');
    }

    $q('.copy-btn').map(btn => {
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
