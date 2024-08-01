@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs">
        <ul>
            <li>
                <a href="#">
                    <span>
                       Project
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>		
                </a>
            </li>
        </ul>
    </div>
<hr>

    <div class="row">

        <div class="col-lg-6">
            <div class="form-group">
                <label>Project Name</label>
                <input type="text" id="projectName" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Status</label>
                <select id="status" class="form-control">
                    <option value="ACTV">Active</option>
                    <option value="INAC">Inactive</option>
                </select>
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

<script type="module">
    import {$q} from '/adarna.js';

    let createBtn              = $q('#createBtn').first();
    let cancelBtn              = $q('#cancelBtn').first();
    let projectName            = $q('#projectName').first();
    let status                 = $q('#status').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/project/create',{
            name: projectName.value,
            status: status.value
        }).then(reply=>{
            
            window.util.unblockUI();
            
            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

            window.util.navTo('/project/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/projects');
    }

</script>
</div>
@endsection