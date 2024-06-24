@extends('layouts.app')

@section('content')
<div class="container">
<h6>Master Data » Project » {{$project->id}} » Section » Create</h6>
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

            if(reply.status <= 0 ){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            };

            window.util.unblockUI();
      
            document.location.href = '/project/section/'+reply.data.id;

        
        });
    }

    cancelBtn.onclick = (e) => {
        document.location.href = '/project/sections';
    }

</script>

@endsection