@extends('layouts.app')

@section('content')
<div class="container">
<h5>Master Data » Material Group » Display</h5>
<hr>

    <div class="row">

        <div class="col-12">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="materialGroupName" disabled="true" value="{{$name}}" class="form-control"/>
            </div>
        </div>

    </div>

    <div class="row mt-5">
        <div class="col-12 text-end">
        <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
        <button class="btn btn-primary" id="editBtn">Edit</button>
        <button class="btn btn-warning d-none" id="updateBtn">Update</button>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    let materialGroupName   = $q('#materialGroupName').first();
    let editBtn             = $q('#editBtn').first();
    let updateBtn           = $q('#updateBtn').first();
    let cancelBtn           = $q('#cancelBtn').first();


    editBtn.onclick = (e)=>{
        e.preventDefault();

        editBtn.classList.add('d-none');

        materialGroupName.disabled = false;

        updateBtn.classList.remove('d-none');
        
        cancelBtn.onclick = ()=>{
            document.location.reload(true);
        }
    }

    updateBtn.onclick = (e)=>{

        window.util.blockUI();

        window.util.$post('/api/material/group/update',{
            name: materialGroupName.value,
            id: '{{$id}}'
        }).then(reply=>{

            if(reply.status <= 0){
                window.util.unblockUI();
                alert(reply.message);
                return false;
            }

            document.location.reload(true);
        });
    }


    cancelBtn.onclick = (e)=>{
        document.location.href = '/master_data/material/groups';
    }

</script>

@endsection