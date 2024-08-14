@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true">
        <ul>
            <li>
                <a href="/master_data/material/groups">
                    <span>
                       Material Groups
                    </span>
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
    <div>
    
    <hr>

    <div class="form-container">
        <div class="form-header">
            Create Material Group
        </div>

        <div class="form-body">
            <div class="row">

                <div class="col-12">
                    <div class="form-group">
                        <label>Material Group Name</label>
                        <input type="text" id="materialGroup" class="form-control"/>
                    </div>
                </div>

            </div>

            <div class="row mt-5">
                <div class="col-12 text-end">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="createBtn">Create</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {$q} from '/adarna.js';

    let createBtn       = $q('#createBtn').first();
    let cancelBtn       = $q('#cancelBtn').first();
    let materialGroup   = $q('#materialGroup').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/material/group/create',{
            name: materialGroup.value
        }).then(reply=>{

            window.util.unblockUI();
                
            if(reply.status <= 0 ){
                
                window.util.showMsg(reply);
                return false;
            };

      
            window.util.navTo('/master_data/material/group/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
         window.util.navTo('/master_data/material/groups');
    }

</script>
</div>
@endsection