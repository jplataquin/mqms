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
                <a href="#">
                    <span>
                       Section
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
                <label>For Project</label>
                <input type="text" value="{{$project->name}}" disabled="true" class="form-control"/>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" id="sectionName" class="form-control"/>
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
    let sectionName            = $q('#sectionName').first();

    createBtn.onclick = (e) => {

        window.util.blockUI();

        window.util.$post('/api/section/create',{
            name: sectionName.value,
            project_id: '{{$project->id}}',
        }).then(reply=>{

            
            window.util.unblockUI();

            if(reply.status <= 0 ){
                window.util.showMsg(reply);
                return false;
            };

      
            window.util.navTo('/project/section/'+reply.data.id);

        
        });
    }

    cancelBtn.onclick = (e) => {
        window.util.navTo('/project/{{$project->id}}');
    }

</script>
</div>
@endsection